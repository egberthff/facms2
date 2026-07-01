<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use Illuminate\Support\Facades\Auth;
use App\Enums\MembershipStatus;
use Illuminate\Support\Facades\Log;

// 1. Move the enum here (outside of the class)
// enum MembershipStatus: string
// {
//     case Active = 'Active';
//     case Inactive = 'Inactive';
// }

class MemberController extends Controller
{
    public function __construct()
    {
        // $this->authorizeResource(Member::class, 'member');
    }

    /**
     * Display a listing of members.
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $members = Member::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('employee_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('department', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

            $title = 'Members List';
        return view('members.index', compact('members', 'title'));
    }

    /**
     * Show the create form.
     */
    public function create()
    {
        return view('members.create');
    }

    /**
     * Store a newly created member.
     */
    public function store(StoreMemberRequest $request)
    {

        $member = DB::transaction(function () use ($request) {
            
            $user = User::create([
                'name' => collect([
                    $request->first_name,
                    $request->middle_name,
                    $request->last_name,
                    $request->suffix,
                ])->filter()->implode(' '),
                'email' => $request->email,
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]);

            $user->assignRole('Member');
            return $user->hasOne(Member::class, 'user_id', 'user_id')->create([
                    'employee_number' => $request->employee_number,
                    'first_name' => $request->first_name,
                    'middle_name' => $request->middle_name,
                    'last_name' => $request->last_name,
                    'suffix' => $request->suffix,
                    'gender' => $request->gender,
                    'birth_date' => $request->birth_date,
                    'department' => $request->department,
                    'position' => $request->position,
                    'employment_status' => $request->employment_status,
                    'membership_status' => MembershipStatus::Active->value,
                    'contact_number' => $request->contact_number,
                    'address' => $request->address,
                    'gcash_number' => $request->gcash_number,
                    'gcash_account_name' => $request->gcash_account_name,
                ]);
        });

        return response()->json([
            'status' => 200,   
            'message' =>"Member Added Successfully",
            'member' => $member,
        ]);
        // 4. Redirect using the Member instance, matching the show() method parameter
        // return redirect()
        //     ->route('members.show', $member)
        //     ->with('success', 'Member created successfully.');
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
    public function update(UpdateMemberRequest $request, Member $member)
    {
        DB::transaction(function () use ($request, $member) {
            // Safe check to ensure the linked user relation exists before updating it
            if ($member->user) {
                $fullName = collect([
                    $request->input('first_name'),
                    $request->input('middle_name'),
                    $request->input('last_name'),
                    $request->input('suffix'),
                ])->filter()->implode(' ');

                $member->user->update([
                    'name' => $fullName,
                    // 'email' => $request->input('email'),
                ]);
            }

            // Updates the member fields using your validated array data safely
            $member->update($request->validated());
        });

        return response()->json([
            'status' => 200,   
            'message' => "Member Updated Successfully",
            'member' => $member->load('user'), // Load relationship to return fresh data
        ]);
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
}
