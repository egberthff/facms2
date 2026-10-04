<?php

namespace Tests\Feature;

use App\Enums\ClaimStatus;
use App\Models\Campus;
use App\Models\Claim;
use App\Models\ClaimType;
use App\Models\Member;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\ClaimService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClaimDisbursementPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_claim_update_accepts_browser_multipart_payload(): void
    {
        $user = User::create([
            'name' => 'Treasurer',
            'email' => 'treasurer2@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->actingAs($user);
        $role = Role::create(['name' => 'President']);
        $user->assignRole($role);

        $claimType = ClaimType::create([
            'name' => 'Medical',
            'required_document' => 'Prescription',
            'description' => 'Medical assistance',
            'required_amount' => '1000.00',
            'is_active' => true,
        ]);
        $paymentMethod = PaymentMethod::create([
            'name' => 'GCash',
            'is_active' => true,
        ]);
        $campus = Campus::create(['campus_name' => 'Main Campus', 'acronym' => 'MC', 'location' => 'Bohol', 'is_active' => true]);
        $member = Member::create([
            'campus_id' => $campus->id,
            'user_id' => $user->user_id,
            'employee_number' => 'EMP-1002',
            'first_name' => 'Ana',
            'last_name' => 'Dela Cruz',
            'gender' => 'Female',
            'department' => 'Finance',
            'position' => 'Staff',
            'employment_status' => 'Permanent',
            'membership_status' => 'Active',
            'contact_number' => '09170000001',
            'address' => 'Test address',
        ]);
        $claim = Claim::create([
            'claim_number' => 'CLM-TEST-002',
            'member_id' => $member->id,
            'claim_type_id' => $claimType->id,
            'payment_method_id' => $paymentMethod->id,
            'requested_amount' => '1000.00',
            'approved_amount' => '1000.00',
            'annual_limit' => '1000.00',
            'status' => ClaimStatus::Approved->value,
            'submitted_at' => now(),
        ]);

        // Skip only the policy gate; disabling all middleware would also drop route-model binding
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class);

        $response = $this->call(
            'POST',
            route('claim.update', $claim),
            [
                '_method' => 'PUT',
                'status' => ClaimStatus::Disbursed->value,
                'amount' => '1250.00',
                'reference_number' => 'REF-002',
                'payment_remarks' => 'Receipt uploaded',
            ],
            [],
            [
                'proof_file' => UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf'),
            ]
        );

        $response->assertStatus(200);
        $claim->refresh();
        $this->assertSame(ClaimStatus::Disbursed->value, $claim->status);
    }

    public function test_disbursing_a_claim_creates_or_updates_the_claim_payment_record(): void
    {
        $user = User::create([
            'name' => 'Treasurer',
            'email' => 'treasurer@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->actingAs($user);

        $claimType = ClaimType::create([
            'name' => 'Medical',
            'required_document' => 'Prescription',
            'description' => 'Medical assistance',
            'required_amount' => '1000.00',
            'is_active' => true,
        ]);
        $paymentMethod = PaymentMethod::create([
            'name' => 'GCash',
            'is_active' => true,
        ]);
        $campus = Campus::create(['campus_name' => 'Main Campus', 'acronym' => 'MC', 'location' => 'Bohol', 'is_active' => true]);
        $member = Member::create([
            'campus_id' => $campus->id,
            'user_id' => $user->user_id,
            'employee_number' => 'EMP-1001',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'gender' => 'Male',
            'department' => 'Finance',
            'position' => 'Staff',
            'employment_status' => 'Permanent',
            'membership_status' => 'Active',
            'contact_number' => '09170000000',
            'address' => 'Test address',
        ]);
        $claim = Claim::create([
            'claim_number' => 'CLM-TEST-001',
            'member_id' => $member->id,
            'claim_type_id' => $claimType->id,
            'payment_method_id' => $paymentMethod->id,
            'requested_amount' => '1000.00',
            'approved_amount' => '1000.00',
            'annual_limit' => '1000.00',
            'status' => ClaimStatus::Approved->value,
            'submitted_at' => now(),
        ]);

        $service = app(ClaimService::class);

        $service->updateStatus($claim, [
            'status' => ClaimStatus::Disbursed->value,
            'amount' => '1250.00',
            'reference_number' => 'REF-001',
            'payment_remarks' => 'Released to member',
        ]);

        $claim->refresh();
        $payment = $claim->payment()->first();

        $this->assertNotNull($payment);
        $this->assertSame('1250.00', (string) $payment->amount);
        $this->assertSame('REF-001', $payment->reference_number);
        $this->assertSame($paymentMethod->id, $payment->payment_method_id);
        $this->assertSame($user->user_id, $payment->processed_by);
        $this->assertSame(ClaimStatus::Disbursed->value, $claim->status);
        $this->assertNotNull($claim->disbursed_at);
    }
}
