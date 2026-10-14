<?php 

namespace App\Services;

use App\Models\Member;
use App\Models\User;
use App\Models\Campus;
use App\Models\MembershipFee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Enums\MembershipStatus;
use App\Enums\MembershipFeeStatus;
use App\Services\TreasuryLedgerService;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator; 
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class MemberService
{
    private TreasuryLedgerService $ledgerService;

    public function __construct(TreasuryLedgerService $ledgerService){
        $this->ledgerService = $ledgerService;
    }
    public function getMembers(Request $request, ?string $selectedCampusId = null)
    {
        $search = $request->search;

         return Member::query()
            ->with('user')
            ->when($selectedCampusId, function ($query) use ($selectedCampusId) {
                $query->where('campus_id', $selectedCampusId);
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('membership_status', $request->status);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('employee_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('department', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();
            // ->paginate(10)
            // ->withQueryString();
    }

    // public function getMembers(
    //     Request $request, 
    //     ?string $selectedCampusId = null, 
    //     int $perPage = 30, 
    //     int $offset = 0
    // ): LengthAwarePaginator {
    //     $search = $request->input('search');
    
    //     // 1. Build the base query
    //     $query = Member::query()
    //         ->with(['campus'])
    //         ->when($selectedCampusId, fn ($q) => $q->where('campus_id', $selectedCampusId))
    //         ->when($search, fn ($q) => $q->where(
    //             fn ($sub) => $sub->where('employee_number', 'like', "%{$search}%")
    //                 ->orWhere('first_name', 'like', "%{$search}%")
    //                 ->orWhere('last_name', 'like', "%{$search}%")
    //                 ->orWhere('department', 'like', "%{$search}%")
    //         ))
    //         ->latest();
    
    //     // 2. Get the total count before applying limit/offset
    //     $total = $query->count();
    
    //     // 3. Fetch the exact slice of data using your custom offset and perPage limit
    //     $items = $query->skip($offset)->take($perPage)->get();
    
    //     // 4. Manually construct the Paginator to keep frontend navigation working
    //     return new LengthAwarePaginator(
    //         $items,
    //         $total,
    //         $perPage,
    //         LengthAwarePaginator::resolveCurrentPage(),
    //         ['path' => LengthAwarePaginator::resolveCurrentPath()]
    //     );
    // }

   /**
     * Create a new user and their associated member profile.
     */
    public function createMember(array $data): Member
    {
        return DB::transaction(function () use ($data) {
            // 1. Create the base login account
            $user = User::create([
                'name' => $this->buildFullName($data),
                'email' => $data['email'],
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]);

            // 2. Officer-created accounts are vouched for, so they skip email verification
            $user->forceFill(['email_verified_at' => now()])->save();

            // 3. Assign default system role
            $user->assignRole('Member');

            // 4. Create and return the linked Member profile record
            return $this->createMemberProfile($user, $data, MembershipStatus::Active->value);
        });
    }

    /**
     * Register a member through the public signup form.
     *
     * The account starts unverified (the Registered event sends the verification link)
     * and the membership starts Inactive until a campus officer activates it.
     */
    public function registerSelfServiceMember(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $this->buildFullName($data),
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_active' => true,
            ]);

            $user->assignRole('Member');

            // Officer-controlled fields are never taken from signup input
            $this->createMemberProfile($user, [
                ...$data,
                'active_member_on' => null,
                'inactive_member_on' => null,
            ], MembershipStatus::Inactive->value);

            return $user;
        });
    }

    private function buildFullName(array $data): string
    {
        return collect([
            $data['first_name'] ?? null,
            $data['middle_name'] ?? null,
            $data['last_name'] ?? null,
            $data['suffix'] ?? null,
        ])->filter()->implode(' ');
    }

    private function createMemberProfile(User $user, array $data, string $membershipStatus): Member
    {
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
            'membership_status' => $membershipStatus,
            'active_member_on' =>$data['active_member_on'] ?? null,
            'inactive_member_on' => $data['inactive_member_on'] ?? null,
            'contact_number' => $data['contact_number'],
            'address' => $data['address'],
            'gcash_number' => $data['gcash_number'] ?? null,
            'gcash_account_name' => $data['gcash_account_name'] ?? null,
            'campus_id' => $data['campus_id'] ?? null,
        ]);
    }

    /**
     * Create member from CSV data. This method assumes that the CSV has been validated and parsed into an array of associative arrays.
     */
   public function createMemberCSV(Request $request): Collection
    {
        $createdMembers = new Collection;
    
        if ($request->hasFile('member_csv_file')) {
            $file = $request->file('member_csv_file');
            $path = $file->getRealPath();
    
            $data = array_map('str_getcsv', file($path));
            $header = array_shift($data);
    
            $header = array_map(function ($head) {
                return trim(preg_replace('/^[\xef\xbb\xbf\xff\xfe]/', '', $head));
            }, $header);
    
            $csvData = array_map(function ($row) use ($header) {
                if (count($header) !== count($row)) {
                    $row = array_pad($row, count($header), null);
                }
                return array_combine($header, $row);
            }, $data);
    
            return DB::transaction(function () use ($csvData, $createdMembers) {
                foreach ($csvData as $row) {
                    $member = $this->createMember($row);
                    $createdMembers->push($member);
                }
    
                return $createdMembers;
            });
        }
    
        return $createdMembers; 
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

    public function getTreasurer($data, ?string $campusId = null): Collection|Member|User|null
    {
        if($campusId) {
            return User::role('Treasurer')
            ->whereHas('member', function ($query) use ($data) {
                $query->where('campus_id', $data['campus_id']);
            })
            ->with('member')
            ->first();
        } 

        return User::role('Federated Treasurer')->with('member')->first();
    } 

    public function getPresident($data, ?string $campusId = null): Collection|Member|User|null
    {
        if($campusId) {
            return User::role('President')
            ->whereHas('member', function ($query) use ($data) {
                $query->where('campus_id', $data['campus_id']);
            })
            ->with('member')
            ->first();
        } 

        return User::role('Federated President')->with('member')->first();
    } 

    public function getAuditor($data, ?string $campusId = null): Collection|Member|User|null   
    {
         if($campusId) {
            return User::role('Auditor')
            ->whereHas('member', function ($query) use ($data) {
                $query->where('campus_id', $data['campus_id']);
            })
            ->with('member')
            ->first();
        } 

        return User::role('Federated Auditor')->with('member')->first();
    } 

    public function membershipFee(Request $request)
    {
        // input() so a missing status fails validation (422) instead of erroring
        $isPaid = $request->input('status') === MembershipFeeStatus::Paid->value;

        $validated = $request->validate([
            'member_id' => ['required', 'uuid', 'exists:members,id'],
            'amount' => ['required', 'regex:/^\d{1,3}(,\d{3})*(\.\d+)?$/', 'min:0'], 
            'status' => ['required', Rule::enum(MembershipFeeStatus::class)],
            'payment_method_id' => [Rule::requiredIf($isPaid),'nullable','uuid','exists:payment_methods,id'],
            'paid_at' => [Rule::requiredIf($isPaid), 'nullable', 'date'],
            'reference_number' => [Rule::requiredIf($isPaid),'nullable','string','max:255','unique:membership_fees,reference_number'],
            'remarks' => ['nullable','string','max:1000'],
        ]);

       
        try {
            $membershipFee = DB::transaction(function () use ($validated, $isPaid) {
                $member = Member::query()
                    ->findOrFail($validated['member_id']);
                    $reference_code = 'BMRF-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
                $membershipFee = MembershipFee::create([
                    'reference_code' => $reference_code,
                    'member_id' => $member->id,
                    'amount' => str_replace(',','', $validated['amount']),
                    'status' => $validated['status'],
                    'paid_at' => $validated['paid_at'] ?? null,
                    'reference_number' => $validated['reference_number'] ?? null,
                    'payment_method_id' => $validated['payment_method_id'] ?? null,
                    'remarks' => $validated['remarks'] ?? null,
                    'created_by' => Auth::id(),
                ]);

                // Only a paid fee is cash received; pending ones post when marked paid (ContributionService::markAsPaid)
                if ($isPaid) {

                    $this->ledgerService->recordTransaction([
                        'campus_id' => $member->campus_id,

                        'sourceable_type' => MembershipFee::class,
                        'sourceable_id' => $membershipFee->id,

                        'reference_number' => $membershipFee->reference_number,

                        'description' => sprintf(
                            'Membership registration fee - %s %s',
                            $member->first_name,
                            $member->last_name
                        ),

                        'type' => 'Credit',

                        'amount' => (float) $membershipFee->amount,
                    ]);
                }

                return $membershipFee;
            });

            return [
                'success' => true,
                'message' => 'Membership fee successfully recorded.',
                'data' => $membershipFee->fresh(),
            ];

        } catch (Throwable $e) {

            Log::error('Membership fee transaction failed.', [
                'member_id' => $validated['member_id'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'message' => 'Unable to record the membership fee.',
                'data' => null,
            ];
        }
    
    }

}