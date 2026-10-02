<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; 
use Illuminate\Http\JsonResponse;
use App\Models\Campus;

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
use App\Services\ClaimFormService;
use App\Services\TreasuryLedgerService;
use App\Services\ReportService;
use App\Services\EmailService;
use App\Services\DashboardService;
use App\Services\NotificationService;

use Exception;
class ClaimController extends Controller
{
    private ClaimService $claimService;
    private TreasuryLedgerService $ledgerService;
    private EmailService $emailService;
    private ReportService $reportService;
    private NotificationService $notificationService;
    
    private DashboardService $dashboardService;

     public function __construct(
        ClaimService $claimService, 
        TreasuryLedgerService $ledgerService,
        EmailService $emailService,
        ReportService $reportService,
        DashboardService $dashboardService,
        NotificationService $notificationService,
        )
    {
        $this->claimService = $claimService;
        $this->ledgerService = $ledgerService;
        $this->emailService = $emailService;
        $this->reportService = $reportService;
        $this->dashboardService = $dashboardService;
        $this->notificationService = $notificationService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $data['isPresident'] = $user->can('view all campus data') || $user->hasAnyRole(['President', 'Treasurer']);
        $selectedCampusId = $data['isPresident'] ? $request->input('campus_filter') : null;

        // $isFederatedTreasuer = $user->hasRole('Federated Treasurer');
        $roles = ['President', 'Treasurer', 'Federated President', 'Federated Treasurer'];
        // Get only the roles the user actually has
        $userRoles = array_values(array_filter($roles, fn($role) => $user->hasRole($role)));
        $data['userRole'] = $userRoles[0] ?? null;
        $data['claims'] = $this->claimService->getAllClaims($userRoles, $selectedCampusId);
        $data['cycles'] = $this->reportService->getAllowanceCycles() ?? [];
        $data['isMemberMenu'] = false;

        if ($data['isPresident']) {
            $data['allCampuses'] = Campus::where('is_active', true)->get();
            $data['selectedCampusId'] = $selectedCampusId;
            $data['userRoles'] = $userRoles; // Pass the user roles to the view
        }

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
    public function store(StoreClaimRequest $request, EmailService $emailService): JsonResponse
    {
        try {
            // Pass validated data and files directly to the service
           $claim = $this->claimService->createClaim(
                        $request->validated(), 
                        $request->file('documents', [])
                    );

            $emailService->newSubmittedClaimAlert($claim); 
            $this->notificationService->newSubmittedClaimAlert($claim);

            return response()->json([
                'success' => true,
                'message' => 'Claim submitted successfully.',
            ], 200);

        } catch (Exception $e) {
            // Log the actual error here for debugging: 
            DB::rollback();
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
    public function update(UpdateClaimRequest $request, Claim $claim, EmailService $emailService): JsonResponse
    {
         try {
            $data = $request->validated();
            $data['proof_file'] = $request->file('proof_file');

            $claimUpdated = $this->claimService->updateStatus($claim, $data);
            $emailService->sendClaimStatusUpdate($claimUpdated);

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
        $user->load(['member']); 
        $data['claims'] = $this->claimService->getClaimsForUser($user);
        // dd($data['claims']);   
        $data['isPresident'] = Auth::user()->can('view all campus data') || Auth::user()->hasAnyRole(['President', 'Treasurer']);
        $data['userRole'] = collect(['President', 'Treasurer', 'Federated President', 'Federated Treasurer'])
            ->first(fn($role) => Auth::user()->hasRole($role));

        if ($data['isPresident']) {
            $data['allCampuses'] = Campus::where('is_active', true)->get();
            $data['selectedCampusId'] = null;
        }
        $data['isMemberMenu'] = true;
        $data['cycles'] = $this->reportService->getAllowanceCycles() ?? [];

        return view('claims.claims-list', $data);
    }

    public function printForms(Claim $claim, ClaimFormService $claimFormService)
    {
        return view('claims.forms.medical-claim-forms', $claimFormService->buildFormData($claim));
    }

    public function getRunningBalance(User $user)
    {
        $data = $this->dashboardService->getMemberMetrics($user);   
          
        return response()->json(array('amount' => $data['balance']), 200);
    }

}
