<?php

namespace App\Http\Controllers;

use App\Models\Disbursement;
use App\Services\DisbursementService;
use App\Http\Requests\StoreDisbursementRequest;
use App\Http\Requests\UpdateDisbursementRequest;

class DisbursementController extends Controller
{
    private ClaimService $claimService;
    public function __construct(DisbursementService $disbursementService)
    {
        $this->disbursementService = $disbursementService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['disbursements'] = $this->disbursementService->getDisbursmentAll()->all();
        return view('disbursements.index',  $data);
        
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
    public function show(Disbursement $disbursement)
    {
        //
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
