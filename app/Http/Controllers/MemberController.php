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

class MemberController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Member::class, 'member');
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

        return view('members.index', compact('members'));
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
        enum MembershipStatus: string
        {
            case Active = 'Active';
            case Inactive = 'Inactive';
        }
        DB::transaction(function () use ($request) {

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

            $user->member()->create([

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

        // return redirect()
        //     ->route('members.index')
        //     ->with('success', 'Member created successfully.');
        return redirect()
            ->route('members.show', $user)
            ->with('success', 'Member created successfully.');
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

            $member->user->update([

                'name' => collect([
                    $request->first_name,
                    $request->middle_name,
                    $request->last_name,
                    $request->suffix,
                ])->filter()->implode(' '),

                'email' => $request->email,

            ]);

            $member->update($request->validated());

        });

        return redirect()
            ->route('members.index')
            ->with('success', 'Member updated successfully.');
    }

    /**
     * We never delete members.
     */
    public function destroy(Member $member)
    {
        abort(403);
    }
}