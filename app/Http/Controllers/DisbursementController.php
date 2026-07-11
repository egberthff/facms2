<?php

namespace App\Http\Controllers;

use App\Models\ClaimPayment;
use App\Services\DisbursementService;
use App\Http\Requests\StoreDisbursementRequest;
use App\Http\Requests\UpdateDisbursementRequest;
use Illuminate\Http\Request;

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
        ];

        $data['disbursements'] = $this->disbursementService->getDisbursmentAll($filters);
        $data['summary'] = $this->disbursementService->getDisbursementSummary();
        $data['filters'] = $filters;
        $data['yearOptions'] = $this->disbursementService->getYearOptions();
        $data['claimTypeOptions'] = $this->disbursementService->getClaimTypeOptions();

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
    public function store(StoreDisbursementRequest $request)
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
    public function edit(Disbursement $disbursement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDisbursementRequest $request, Disbursement $disbursement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Disbursement $disbursement)
    {
        //
    }
}
