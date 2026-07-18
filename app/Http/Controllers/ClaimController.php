<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; 
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Str;
use App\Http\Requests\StoreClaimRequest;
use App\Http\Requests\UpdateClaimRequest;
use App\Enums\ClaimStatus;
use App\Models\ClaimType;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\Claim;
use App\Models\ClaimDocument;
use App\Models\SystemSetting;
use App\Services\ClaimService;
use App\Services\TreasuryLedgerService;

use Exception;
class ClaimController extends Controller
{
    private ClaimService $claimService;
    private TreasuryLedgerService $ledgerService;

     public function __construct(ClaimService $claimService, TreasuryLedgerService $ledgerService)
    {
        $this->claimService = $claimService;
        $this->ledgerService = $ledgerService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $data['claims'] = $this->claimService->getAllClaims();

       return view('claims.claims-list', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = $this->claimService->getFormData(); 

        return view('claims.index', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClaimRequest $request): JsonResponse
    {
        try {
            // Pass validated data and files directly to the service
            $this->claimService->createClaim(
                $request->validated(), 
                $request->file('documents', [])
            );

            return response()->json([
                'success' => true,
                'message' => 'Claim submitted successfully.',
            ], 200);

        } catch (Exception $e) {
            // Log the actual error here for debugging: 
            \Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while processing your claim.',
            ], 500);
        }
    }
    
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, string $id)
    // {
    //     //
    // }

    /**
     * Update the status of a specific claim.
     */
    public function update(UpdateClaimRequest $request, Claim $claim): JsonResponse
    {
         try {
            $data = $request->validated();
            $data['proof_file'] = $request->file('proof_file');

            $this->claimService->updateStatus($claim, $data);

         return response()->json([
                'success' => true,
                'message' => 'Status updated successfully!',
            ], 200);
         }catch(Exception $e){
             \Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while processing update.',
            ], 500);
         }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function myClaims(User $user)
    {
        Gate::authorize('viewMyClaims', [Claim::class, $user]);

        $data['claims'] = $this->claimService->getClaimsForUser($user);

        return view('claims.claims-list', $data);
    }

}
