<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\ClaimPayment;
use App\Services\DisbursementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisbursementController extends Controller
{
    private DisbursementService $disbursementService;

    public function __construct(DisbursementService $disbursementService)
    {
        $this->disbursementService = $disbursementService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = [
            'year' => $request->input('year'),
            'claim_type' => $request->input('claim_type'),
            'status' => $request->input('status'),
            'search' => $request->input('search'),
            'campus_filter' => $request->input('campus_filter'),
        ];

        // $data['isPresident'] = Auth::user()->can('view all campus data') || Auth::user()->hasAnyRole(['President', 'Treasurer']);
        $data['isPresident'] = (Auth::user()->can('view all campus data') || Auth::user()->hasAnyRole(['Federated President', 'Federated Treasurer']));
        // dd($data['isPresident']); 
        $selectedCampusId = $data['isPresident'] ? $filters['campus_filter'] : null;

        $data['disbursements'] = $this->disbursementService->getDisbursmentAll(array_merge($filters, ['campus_filter' => $selectedCampusId]));
        $data['summary'] = $this->disbursementService->getDisbursementSummary($selectedCampusId);
        $data['filters'] = $filters;
        $data['yearOptions'] = $this->disbursementService->getYearOptions();
        $data['claimTypeOptions'] = $this->disbursementService->getClaimTypeOptions();

        if ($data['isPresident']) {
            $data['allCampuses'] = Campus::where('is_active', true)->get();
            $data['selectedCampusId'] = $selectedCampusId;
        }

        return view('disbursements.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ClaimPayment $claimPayment)
    {
        $claimPayment->load(['paymentMethod', 'claim.claimType', 'claim.member']);

        return view('disbursements.show', compact('claimPayment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit(Disbursement $disbursement)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, Disbursement $disbursement)
    // {
    //     //
    // }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(Disbursement $disbursement)
    // {
    //     //
    // }
}
