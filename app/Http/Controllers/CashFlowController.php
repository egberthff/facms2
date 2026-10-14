<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\CollectionSummaryItem;
use App\Models\CollectionSummaryPeriod;
use App\Models\User;
use App\Services\CashFlowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CashFlowController extends Controller
{
    public function __construct(private CashFlowService $service)
    {
    }

    public function index(Request $request): View
    {
        $user = Auth::user();
        $canViewAll = $user->can('view all campus data');
        $selectedCampusId = $canViewAll ? $request->input('campus_filter') : $this->userCampusId($user);

        $periods = CollectionSummaryPeriod::query()
            ->cashFlow()
            ->with('campus')
            ->when($selectedCampusId, fn ($query) => $query->where('campus_id', $selectedCampusId))
            ->when(!$canViewAll && !$selectedCampusId, fn ($query) => $query->whereRaw('1 = 0'))
            ->latest('period_to')
            ->get();

        $lastPeriod = $selectedCampusId ? $periods->firstWhere('campus_id', $selectedCampusId) : null;

        return view('reports.cash-flow.index', [
            'campuses' => $canViewAll ? Campus::where('is_active', true)->orderBy('campus_name')->get() : collect(),
            'canViewAll' => $canViewAll,
            'canManage' => $user->can('manage funds') && filled($selectedCampusId),
            'selectedCampusId' => $selectedCampusId,
            'periods' => $periods,
            'needsOpeningBalance' => $selectedCampusId ? $this->service->needsOpeningBalance($selectedCampusId) : false,
            'suggestedFrom' => $lastPeriod?->period_to?->copy()->addDay()->toDateString(),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->can('manage funds'), 403);

        $validated = $request->validate([
            'campus_id' => [Rule::requiredIf($user->can('view all campus data')), 'nullable', 'exists:campuses,id'],
            'period_from' => ['required', 'date'],
            'period_to' => ['required', 'date', 'after_or_equal:period_from'],
            'opening_balance' => ['nullable', 'numeric', 'decimal:0,2'],
            'opening_label' => ['nullable', 'string', 'max:100'],
        ]);

        // Campus officers can only open periods for their own campus
        $validated['campus_id'] = $user->can('view all campus data')
            ? $validated['campus_id']
            : $this->userCampusId($user);
        abort_unless($validated['campus_id'], 403);

        $period = $this->service->createDraft($validated, $user);

        return $this->done($request, 'Cash flow draft created.', route('reports.cash-flow.show', $period));
    }

    public function show(CollectionSummaryPeriod $period): View
    {
        $this->authorizeView($period);

        return view('reports.cash-flow.editor', [
            ...$this->service->printableData($period),
            'canManage' => Auth::user()->can('manage funds') && !$period->isFinalized(),
            'openingEditable' => !$this->service->previousPeriod($period->campus_id, $period->period_from->toDateString()),
        ]);
    }

    public function print(CollectionSummaryPeriod $period): View
    {
        $this->authorizeView($period);

        return view('reports.cash-flow.print', $this->service->printableData($period));
    }

    public function refresh(Request $request, CollectionSummaryPeriod $period): JsonResponse|RedirectResponse
    {
        $this->authorizeManage($period);
        $this->service->refresh($period);

        return $this->done($request, 'Funds and medical assistance releases refreshed from the system.');
    }

    public function updateOpeningBalance(Request $request, CollectionSummaryPeriod $period): JsonResponse|RedirectResponse
    {
        $this->authorizeManage($period);

        $validated = $request->validate([
            'opening_balance' => ['required', 'numeric', 'decimal:0,2'],
            'opening_label' => ['nullable', 'string', 'max:100'],
        ]);

        $this->service->updateOpeningBalance($period, (float) $validated['opening_balance'], $validated['opening_label'] ?? null);

        return $this->done($request, 'Forwarded balance updated.');
    }

    public function finalize(Request $request, CollectionSummaryPeriod $period): JsonResponse|RedirectResponse
    {
        $this->authorizeManage($period);
        $this->service->finalize($period, Auth::user());

        return $this->done($request, 'Cash flow finalized. Manual lines were posted to the campus treasury ledger.');
    }

    public function destroy(Request $request, CollectionSummaryPeriod $period): JsonResponse|RedirectResponse
    {
        $this->authorizeManage($period);
        $campusId = $period->campus_id;
        $this->service->deleteDraft($period);

        return $this->done(
            $request,
            'Cash flow draft deleted.',
            route('reports.cash-flow.index', ['campus_filter' => $campusId]),
        );
    }

    public function storeItem(Request $request, CollectionSummaryPeriod $period): JsonResponse|RedirectResponse
    {
        $this->authorizeManage($period);

        $this->service->addManualItem($period, $request->validate($this->itemRules()));

        return $this->done($request, 'Line added.');
    }

    public function updateItem(Request $request, CollectionSummaryItem $item): JsonResponse|RedirectResponse
    {
        $this->authorizeManage($item->summary);

        $rules = $item->is_manual
            ? $this->itemRules()
            : collect($this->itemRules())->only(['label', 'notes'])->all();

        $this->service->updateItem($item, $request->validate($rules));

        return $this->done($request, 'Line updated.');
    }

    public function destroyItem(Request $request, CollectionSummaryItem $item): JsonResponse|RedirectResponse
    {
        $this->authorizeManage($item->summary);
        $this->service->deleteItem($item);

        return $this->done($request, 'Line removed.');
    }

    /**
     * The editor submits over fetch and expects JSON; plain form posts keep the redirect.
     */
    private function done(Request $request, string $message, ?string $redirect = null): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'redirect' => $redirect]);
        }

        return ($redirect ? redirect($redirect) : back())->with('success', $message);
    }

    private function itemRules(): array
    {
        return [
            'section' => ['required', Rule::in([CollectionSummaryItem::SECTION_FUNDS, CollectionSummaryItem::SECTION_DISBURSEMENTS])],
            'label' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'entry_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function authorizeView(CollectionSummaryPeriod $period): void
    {
        abort_unless($period->report_type === CollectionSummaryPeriod::TYPE_CASH_FLOW, 404);

        $user = Auth::user();
        abort_unless($user->can('view all campus data') || $period->campus_id === $this->userCampusId($user), 403);
    }

    private function authorizeManage(CollectionSummaryPeriod $period): void
    {
        $this->authorizeView($period);
        abort_unless(Auth::user()->can('manage funds'), 403);
    }

    /**
     * Read directly: User::getCachedCampusId() caches statically across users within one process.
     */
    private function userCampusId(User $user): ?string
    {
        return $user->member()->withoutGlobalScopes()->value('campus_id');
    }
}
