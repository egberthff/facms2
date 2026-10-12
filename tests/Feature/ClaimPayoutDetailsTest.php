<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Claim;
use App\Models\ClaimType;
use App\Models\Member;
use App\Models\PaymentMethod;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ClaimPayoutDetailsTest extends TestCase
{
    use RefreshDatabase;

    // Real GCash QR Ph payload shape: no mobile number, masked name
    private const GCASH_PAYLOAD = '00020101021127830012com.p2pqrpay0111GXCHPHM2XXX02089996440303152170200000006560417DWQM4TK3JDNWJJ0OK5204601653036085802PH5914JU** DE** CR**6007Lataban610412346304A0F3';

    private const BDO_PAYLOAD = '00020101021127510012com.p2pqrpay0111BNORPHMMXXX0312001234567890520460165303608';

    private Campus $campus;

    private ClaimType $claimType;

    private PaymentMethod $gcash;

    private PaymentMethod $bank;

    private Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Mail::fake();
        Notification::fake();

        $this->campus = Campus::create(['campus_name' => 'Main Campus', 'acronym' => 'MC', 'location' => 'Bohol', 'is_active' => true]);
        foreach (['claim_cycle_years' => '2', 'annual_claim_limit' => '23000.00'] as $key => $value) {
            SystemSetting::create([
                'setting_key' => $key,
                'setting_value' => $value,
                'description' => $key,
                'campus_id' => $this->campus->id,
                'cycle_label' => null,
            ]);
        }

        $this->claimType = ClaimType::create([
            'name' => 'Medical',
            'required_document' => 'Prescription',
            'description' => 'Medical assistance',
            'required_amount' => '100.00',
            'is_active' => true,
        ]);
        $this->gcash = PaymentMethod::create(['name' => 'GCash', 'is_active' => true]);
        $this->bank = PaymentMethod::create(['name' => 'Bank Transfer', 'is_active' => true]);

        $user = User::factory()->create(['is_active' => true]);
        Permission::firstOrCreate(['name' => 'create claims', 'guard_name' => 'web']);
        $user->givePermissionTo('create claims');

        $this->member = Member::create([
            'user_id' => $user->user_id,
            'campus_id' => $this->campus->id,
            'employee_number' => 'EMP-1',
            'first_name' => 'Member',
            'last_name' => 'One',
            'gender' => 'Female',
            'department' => 'CTAS',
            'position' => 'Instructor',
            'employment_status' => 'Permanent',
            'membership_status' => 'Active',
            'contact_number' => '09170000000',
            'address' => 'Bohol',
        ]);
    }

    public function test_a_gcash_qr_replaces_the_typed_number_and_is_stored(): void
    {
        $this->submit([
            'paymentMethod' => $this->gcash->id,
            'payoutQr' => UploadedFile::fake()->image('gcash-qr.png'),
            'payoutQrPayload' => self::GCASH_PAYLOAD,
        ])->assertOk();

        $claim = Claim::sole();
        $this->assertNotNull($claim->payout_qr_path);
        $this->assertSame(self::GCASH_PAYLOAD, $claim->payout_qr_payload);
        $this->assertNull($claim->payout_account_number);
        Storage::disk('public')->assertExists($claim->payout_qr_path);
    }

    public function test_gcash_requires_a_number_and_name_without_a_qr(): void
    {
        $this->submit(['paymentMethod' => $this->gcash->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['gcashNumber', 'gcashName']);
    }

    public function test_typed_gcash_details_are_stored(): void
    {
        $this->submit([
            'paymentMethod' => $this->gcash->id,
            'gcashNumber' => '09171234567',
            'gcashName' => 'Juan Dela Cruz',
        ])->assertOk();

        $claim = Claim::sole();
        $this->assertSame('09171234567', $claim->payout_account_number);
        $this->assertSame('Juan Dela Cruz', $claim->payout_account_name);
        $this->assertNull($claim->payout_qr_path);
    }

    public function test_a_bank_qr_is_rejected_for_gcash(): void
    {
        $this->submit([
            'paymentMethod' => $this->gcash->id,
            'payoutQr' => UploadedFile::fake()->image('bank-qr.png'),
            'payoutQrPayload' => self::BDO_PAYLOAD,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('payoutQrPayload');
    }

    public function test_a_non_payment_qr_is_rejected(): void
    {
        $this->submit([
            'paymentMethod' => $this->bank->id,
            'payoutQr' => UploadedFile::fake()->image('qr.png'),
            'payoutQrPayload' => 'https://example.com',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('payoutQrPayload');
    }

    public function test_typed_bank_details_are_stored(): void
    {
        $this->submit([
            'paymentMethod' => $this->bank->id,
            'bank' => 'BDO',
            'accountNumber' => '001234567890',
            'accountName' => 'Juan Dela Cruz',
            'branch' => 'Tagbilaran',
        ])->assertOk();

        $claim = Claim::sole();
        $this->assertSame('BDO', $claim->payout_bank_name);
        $this->assertSame('001234567890', $claim->payout_account_number);
        $this->assertSame('Tagbilaran', $claim->payout_branch);
    }

    private function submit(array $payout)
    {
        return $this->withoutMiddleware()
            ->actingAs($this->member->user)
            ->post(route('claim.store'), [
                'claimType' => $this->claimType->id,
                'amount' => 500,
                'description' => 'Hospitalization',
                'incidentDate' => now()->toDateString(),
                ...$payout,
            ], ['Accept' => 'application/json']);
    }
}
