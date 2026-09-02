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
use Illuminate\Support\Facades\Auth;
use App\Enums\MembershipStatus;
use Illuminate\Support\Facades\Log;
use App\Services\MemberService;
use App\Services\EmailService;
use Illuminate\Http\JsonResponse;

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
    public function __construct(MemberService $memberService, EmailService $emailService)
    {
        // $this->authorizeResource(Member::class, 'member');
        $this->memberService = $memberService;
        $this->emailService = $emailService;
    }

    /**
     * Display a listing of members.
     */
    public function index(Request $request)
    {
        $data['title'] = "Members List";
        $data['isPresident'] = Auth::user()->can('view all campus data') || Auth::user()->hasAnyRole(['President', 'Treasurer']);
        $data['allCampuses'] = Campus::where('is_active', true)->get();
        $data['selectedCampusId'] = $data['isPresident'] ? $request->input('campus_filter') : null;

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
                'message' => "Failed to create members from CSV. The operation was safely rolled back.",
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
                'message' => "Failed to create member. The operation was safely rolled back.",
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
                'message' => "Failed to update member. Changes were safely rolled back.",
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

        switch ($type) {
            case 'claims':
                $member->load(['claims' => function ($query) {
                    $query->select(['id', 'member_id', 'claim_number', 'requested_amount', 'status', 'submitted_at'])
                    ->latest('submitted_at');
                }]);
                break;

            case 'contributions':
                $member->load(['contributions' => function ($query) {
                    $query->select(['member_id', 'contribution_batch_id', 'amount', 'status', 'paid_at'])
                    ->latest('created_at');;
                        //   ->with(['contributionBatch:id,generated_at']); 
                }]);
                break;
        }

        return response()->json([
            'member' => $member->only(['id', 'first_name', 'last_name']),
            'records' => $member->getRelation($type), 
        ]);
    }

}
