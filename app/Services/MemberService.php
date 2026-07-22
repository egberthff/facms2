<?php 

namespace App\Services;

use App\Models\Member;
use App\Models\User;
use App\Models\Campus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Enums\MembershipStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator; 
use Exception;

class MemberService

{
    public function getMembers(Request $request): LengthAwarePaginator
    {

        $search = $request->search;

         return Member::query()
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
    }

   /**
     * Create a new user and their associated member profile.
     */
    public function createMember(array $data): Member
    {
        return DB::transaction(function () use ($data) {
            // 1. Construct full name string cleanly
            $fullName = collect([
                $data['first_name'] ?? null,
                $data['middle_name'] ?? null,
                $data['last_name'] ?? null,
                $data['suffix'] ?? null,
            ])->filter()->implode(' ');

            // 2. Create the base login account
            $user = User::create([
                'name' => $fullName,
                'email' => $data['email'],
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]);

            // 3. Assign default system role
            $user->assignRole('Member');

            // 4. Create and return the linked Member profile record
            return $user->member()->create([
                'employee_number' => $data['employee_number'],
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'suffix' => $data['suffix'] ?? null,
                'gender' => $data['gender'],
                'birth_date' => $data['birth_date'] ?? null,
                'department' => $data['department'],
                'position' => $data['position'],
                'employment_status' => $data['employment_status'],
                'membership_status' => MembershipStatus::Active->value,
                'contact_number' => $data['contact_number'],
                'address' => $data['address'],
                'gcash_number' => $data['gcash_number'] ?? null,
                'gcash_account_name' => $data['gcash_account_name'] ?? null,
                'campus_id' => $data['campus_id'] ?? null,
            ]);
        });
    }

     public function updateMember(Member $member, array $data): Member
    {
        return DB::transaction(function () use ($member, $data) {
            if ($member->user) {
                $fullName = collect([
                    $data['first_name'] ?? $member->first_name,
                    $data['middle_name'] ?? $member->middle_name,
                    $data['last_name'] ?? $member->last_name,
                    $data['suffix'] ?? $member->suffix,
                ])->filter()->implode(' ');

                $member->user->update([
                    'name' => $fullName,
                ]);
            }

            $member->update($data);

            return $member->load('user');
        });
    }

    public function getCampus(?string $campusId = null): Collection|Campus|null
    {
        if ($campusId) {
            return Campus::find($campusId); 
        }

        return Campus::all()->where('is_active', TRUE);
    }

}