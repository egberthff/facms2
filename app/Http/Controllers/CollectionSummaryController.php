<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\FundTransfer;
use App\Services\CollectionSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CollectionSummaryController extends Controller
{
    public function __construct(private CollectionSummaryService $service)
    {
    }

    public function index()
    {
        return view('reports.collection-summary-editor', [
            'campuses' => Campus::where('is_active', true)->get(),
            'pendingTransfers' => FundTransfer::where('status', 'pending')->latest()->get(),
        ]);
    }

    public function print(Request $request): View
    {
        $validated = $request->validate([
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $data = $this->service->buildPrintableData(
            $validated['campus_id'] ?? null,
            $validated['from'] ?? null,
            $validated['to'] ?? null,
        );

        return view('reports.collection-summary', [
            'summary' => null,
            'campus' => $data['campus'],
            'collections' => $data['collections'],
            'disbursements' => $data['disbursements'],
            'collection_total' => $data['collection_total'],
            'disbursement_total' => $data['disbursement_total'],
            'periodLabel' => $data['period_label'],
            'campusTreasurer' => $data['campus_treasurer'],
            'campusAuditor' => $data['campus_auditor'],
            'campusPresident' => $data['campus_president'],
            'generatedDate' => now()->format('F d, Y'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'type' => ['required', 'in:campus_remittance,ffa_disbursement'],
            'reference_number' => ['required', 'string', 'max:255', 'unique:fund_transfers,reference_number'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $this->service->createTransfer($validated, Auth::user());

        return redirect()->route('reports.collection-summary')->with('success', 'Fund transfer submitted for approval.');
    }

    public function approve(FundTransfer $transfer)
    {
        abort_unless(Auth::user()->hasAnyRole(['Federated President', 'Federated Treasurer']) || Auth::user()->can('view all campus data'), 403);
        $this->service->approveTransfer($transfer, Auth::user());

        return back()->with('success', 'Fund transfer approved and posted to both ledgers.');
    }
}
