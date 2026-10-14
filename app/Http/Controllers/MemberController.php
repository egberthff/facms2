<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Member;
use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Requests\UpdateOwnProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Enums\MembershipStatus;
use Illuminate\Support\Facades\Log;
use App\Services\MemberService;
use App\Services\EmailService;
use App\Services\ContributionService;
use Illuminate\Http\JsonResponse;
use App\Services\DashboardService;
use App\Models\MembershipFee;
use App\Enums\MembershipFeeStatus;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

// 1. Move the enum here (outside of the class)
// enum MembershipStatus: string
// {
//     case Active = 'Active';
//     case Inactive = 'Inactive';
// }

use Exception;
class MemberController extends Controller
{
    private MemberService $memberService;
    private EmailService $emailService;
    private DashboardService $dashboardService;
    private ContributionService $contributionService;

    public function __construct(
        MemberService $memberService, 
        EmailService $emailService,
        DashboardService $dashboardService,
        ContributionService $contributionService
        )
    {
        // $this->authorizeResource(Member::class, 'member');
        $this->memberService = $memberService;
        $this->emailService = $emailService;
        $this->dashboardService = $dashboardService;
        $this->contributionService = $contributionService;
    }

    /**
     * Display a listing of members.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $data['title'] = "Members List";
        // $data['isPresident'] = $user->can('view all campus data') || $user->hasAnyRole(['President', 'Federated President']);
        $data['isPresident'] = $user->hasAnyRole(['President', 'Federated President']);
        $data['isTreasurer'] = $user->hasAnyRole(['Treasurer', 'Federated Treasurer']);
        $data['allCampuses'] = Campus::where('is_active', true)->get();
        $data['selectedCampusId'] = $data['isPresident'] ? $request->input('campus_filter') : null;
        $data['currentStatus'] = $request->status;

        $data['campuses'] = $this->memberService->getCampus();
        $data['members'] = $this->memberService->getMembers($request, $data['selectedCampusId']);

        return view('members.index',  $data);
    }

    /**
     * Show the create form.
     */
    public function create()
    {
        $data['campuses'] = $this->memberService->getCampus();
        return view('members.create', $data);
    }

    /**
     * Store member through CSV
     */
    public function storeCSV(Request $request, MemberService $memberService, EmailService $emailService): JsonResponse
    {
        try {
            // Only pass $request since the service handles the logic
            $members = $memberService->createMemberCSV($request);

            // 4. Send the email *outside* of the database transaction context
            $emailService->sendWelcomeEmails($members);
            return response()->json([
                'status' => 200,
                'message' => 'Members Added Successfully',
                'members' => $members, // Pluralized since it's a batch collection
            ], 200);
        } catch (Exception $e) {
            // Safe input logging that won't crash on standard Requests
            $logInput = $request->except(['password', 'member_csv_file']);
            DB::rollBack();
            Log::error('Member CSV creation failed: ' . $e->getMessage(), [
                'input' => $logInput,
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'status' => 500,
                'message' => "Failed to create members from CSV. Please check the following details for potential issues: CSV format, data integrity, or duplicate entries.",
                'error' => app()->environment('local', 'testing') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    /**
     * Store a newly created member.
     */
   public function store(StoreMemberRequest $request, MemberService $memberService, EmailService $emailService ): JsonResponse
    {
        try {

            $member = $memberService->createMember($request->validated());

            $emailService->sendWelcomeEmails($member);
            return response()->json([
                'status' => 200,   
                'message' => "Member Added Successfully",
                'member' => $member,
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Member creation failed: ' . $e->getMessage(), [
                'input' => $request->safe()->except(['password']),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 500,
                'message' => "Failed to create member. Please check the following details for potential issues: Data integrity, or duplicate entries.",
                'error' => app()->environment('local', 'testing') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }
    /**
     * Display a member.
     */
    public function show(Member $member)
    {
        return view('members.show', compact('member'));
    }

    /**
     * Show the edit form.
     */
    public function edit(Member $member)
    {
        return view('members.edit', compact('member'));
    }

    /**
     * Update a member.
     */
   public function update(UpdateMemberRequest $request, Member $member, MemberService $memberService): JsonResponse
    {
        try {

        $updatedMember = $memberService->updateMember($member, $request->validated());

            return response()->json([
                'status' => 200,   
                'message' => "Member Updated Successfully",
                'member' => $updatedMember,
            ], 200);

        } catch (Exception $e) {
            Log::error('Member update failed: ' . $e->getMessage(), [
                'member_id' => $member->id,
                'input' => $request->validated(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 500,
                'message' => "Failed to update member. Please check the following details for potential issues: Data integrity, or duplicate entries.",
                'error' => app()->environment('local', 'testing') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    // public function update(UpdateMemberRequest $request, Member $member)
    // {
    //     DB::transaction(function () use ($request, $member) {
    //         $member->user->update([
    //             'name' => collect([
    //                 $request->first_name,
    //                 $request->middle_name,
    //                 $request->last_name,
    //                 $request->suffix,
    //             ])->filter()->implode(' '),
    //             'email' => $request->email,
    //         ]);

    //         $member->update($request->validated());
    //     });

    //     return response()->json([
    //         'status' => 200,   
    //         'message' =>"Member Updated Successfully",
    //         'member' => $member,
    //     ]);
    // }

    /**
     * We never delete members.
     */
    public function destroy(Member $member)
    {
        abort(403);
    }

    public function getHistory(Member $member, string $type): JsonResponse 
    {
        // 1. Ensure the request parameter matches expected historical scopes
        if (! in_array($type, ['claims', 'contributions'])) {
            return response()->json(['error' => 'Invalid history type requested'], 400);
        }

        $user = $member->user;
        $balance = $this->dashboardService->calculateMemberBalanceData($user);

        switch ($type) {
            case 'claims':
                $member->load(['claims' => function ($query) {
                    $query->select(['id', 'member_id', 'claim_number', 'requested_amount', 'status', 'submitted_at'])
                        ->latest('submitted_at');
                }]);

                // Format claims payload
                $records = $member->claims;
                break;

            case 'contributions':
                $member->load([
                    'contributions' => function ($query) {
                        $query->select(['id', 'member_id', 'contribution_batch_id', 'amount', 'status', 'paid_at'])
                            ->latest('created_at')
                            ->with(['contributionBatch' => function ($batchQuery) {
                                $batchQuery->select(['id', 'batch_reference', 'generated_at']);
                            }]);
                    }, 
                    'annualContributions' => function ($query) {
                        $query->select(['id', 'member_id', 'reference_number', 'amount_paid', 'status', 'paid_at'])
                            ->latest('created_at');
                    }, 
                    'cnaShares' => function ($query) {
                        $query->select(['id', 'member_id','reference_number', 'due_amount', 'status', 'paid_at']) // Fixed missing array syntax here
                            ->latest('created_at');
                    }
                ]);

                // Format grouped contributions payload
                $records = [
                    'claims' => $member->contributions,
                    'annual_dues'   => $member->annualContributions,
                    'cna'      => $member->cnaShares,
                ];
                break;
        }
            \Log::error(print_r($records, true));
        return response()->json([
            'member'  => $member->only(['id', 'first_name', 'last_name']),
            'balance' => $balance,
            'records' => $records,
        ]);
    }

    // public function getHistory(Member $member, string $type): JsonResponse
    // {
    //     // 1. Ensure the request parameter matches expected historical scopes
    //     if (! in_array($type, ['claims', 'contributions'])) {
    //         return response()->json(['error' => 'Invalid history type requested'], 400);
    //     }

    //     $user = $member->user;
    //     $balance = $this->dashboardService->calculateMemberBalanceData($user);

    //     switch ($type) {
    //         case 'claims':
    //             $member->load(['claims' => function ($query) {
    //                 $query->select(['id', 'member_id', 'claim_number', 'requested_amount', 'status', 'submitted_at'])
    //                 ->latest('submitted_at');
    //             }]);
    //             break;

    //             case 'contributions':
    //                 $member->load(['contributions' => function ($query) {
    //                     $query->select(['id', 'member_id', 'contribution_batch_id', 'amount', 'status', 'paid_at'])
    //                           ->latest('created_at')
    //                           ->with(['contributionBatch' => function ($batchQuery) {
    //                               $batchQuery->select(['id', 'batch_reference', 'generated_at']); 
    //                           }]);
    //                 }, 'annualContributions' => function ($query) {
    //                     $query->select(['id', 'member_id', 'amount_paid', 'status', 'paid_at'])
    //                           ->latest('created_at');
    //                 }, 'cnaShares' => function ($query) {
    //                     $query->select('id','member_id', 'due_amount', 'status', 'paid_at')
    //                           ->latest('created_at');
    //                 }]);
    //                 break;
                
    //     }

    //     return response()->json([
    //         'member'  => $member->only(['id', 'first_name', 'last_name']),
    //         'records' => $member->getRelation($type), 
    //         'balance' => $balance,
    //     ]);
    // }

    public function getProfilePage() {
        
        $member = Member::with(['user', 'campus', 'claims', 'contributions'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('profile.index', compact('member'));
    }

    /**
     * A member updating their own contact and payout details.
     */
    public function updateProfile(UpdateOwnProfileRequest $request): RedirectResponse|JsonResponse
    {
        // Always the signed-in user's own record, never one from the URL
        $member = Member::where('user_id', Auth::id())->firstOrFail();

        $member->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your profile has been updated.',
                'redirect' => route('members.profile'),
            ]);
        }

        return redirect()->route('members.profile')->with('success', 'Profile updated.');
    }

    public function searchMembers(Request $request)
    {
        $search = trim($request->input('search', ''));
        
        if (mb_strlen($search) < 2) {
            return response()->json([]);
        }
        
        $members = Member::query()
            ->with('campus:id,acronym')
            ->where(function ($query) use ($search) {
                $query->where('employee_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
        
                    'employee_number' => $member->employee_number,
        
                    'name' => collect([
                        $member->first_name,
                        $member->middle_name,
                        $member->last_name,
                        $member->suffix,
                    ])->filter()->implode(' '),
        
                    'department' => $member->department,
                    'position' => $member->position,
                    'membership_status' => $member->membership_status,
        
                    'campus' => $member->campus
                        ? $member->campus->acronym
                        : null,
                ];
            });
        
        return response()->json($members);
    }

    public function membershipFee(Request $request){ 
        $result = $this->memberService->membershipFee($request);
        return response()->json(
            $result,
            $result['success'] ? 201 : 500
        );
    }

    public function pay(string $id, Request $request): JsonResponse
    {
        // Validated outside the try so the catch below can't turn a 422 into a 404
        $data = $request->validate([
            'payment_method_id' => ['required', 'uuid', 'exists:payment_methods,id'],
            'reference_number' => ['required', 'string', 'max:255', Rule::unique('membership_fees', 'reference_number')->ignore($id)],
        ]);

        // Marking paid posts a ledger credit, so never do it twice
        if (MembershipFee::whereKey($id)->where('status', MembershipFeeStatus::Paid->value)->exists()) {
            throw ValidationException::withMessages([
                'status' => 'This membership fee is already marked as paid.',
            ]);
        }

        try {
            $updatedDetail = $this->contributionService->markAsPaid($id, 'membership_fee', $data);
 
            return response()->json([
                'success' => true,
                 'message' => 'Membership fee has been marked as paid successfully.',
                'data'    => $updatedDetail
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to record payment line item assignment.'
            ], 404);
        }
    }
}
