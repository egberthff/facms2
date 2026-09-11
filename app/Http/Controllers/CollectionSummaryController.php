<?php

namespace App\Http\Controllers;

use App\Models\Campus;
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
        ]);
    }

    public function print(Request $request): View
    {
        $validated = $request->validate([
            'campus_id' => ['nullable', 'exists:campuses,id'],
        ]);

        $data = $this->service->buildPrintableData($validated['campus_id'] ?? null);

        return view('reports.collection-summary', [
            'summary' => $data['summary'],
            'campus' => $data['summary']?->campus ?? Campus::find($validated['campus_id'] ?? null),
            'collections' => $data['collections'],
            'disbursements' => $data['disbursements'],
            'collection_total' => $data['collection_total'],
            'disbursement_total' => $data['disbursement_total'],
            'generatedDate' => now()->format('F d, Y'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'period_label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'items' => ['required', 'array'],
            'items.*.section' => ['required', 'in:collections,disbursements'],
            'items.*.label' => ['required', 'string', 'max:255'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $summary = $this->service->store($validated, Auth::user());

        return redirect()->route('reports.collection-summary')->with('success', 'Collection summary saved successfully.');
    }
}
