<?php

namespace Tests\Feature;

use App\Enums\ClaimStatus;
use App\Models\Campus;
use App\Models\Claim;
use App\Models\ClaimType;
use App\Models\Member;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    private ClaimType $claimType;

    private PaymentMethod $paymentMethod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->claimType = ClaimType::create([
            'name' => 'Medical',
            'required_document' => 'Prescription',
            'description' => 'Medical assistance',
            'required_amount' => '1000.00',
            'is_active' => true,
        ]);
        $this->paymentMethod = PaymentMethod::create(['name' => 'GCash', 'is_active' => true]);
    }

    public function test_campus_user_only_counts_claims_from_their_campus(): void
    {
        $main = Campus::create(['campus_name' => 'Main Campus', 'acronym' => 'MC', 'is_active' => true]);
        $other = Campus::create(['campus_name' => 'Other Campus', 'acronym' => 'OC', 'is_active' => true]);

        $president = User::factory()->create(['is_active' => true]);
        $mainMember = $this->member($main, $president);
        $otherMember = $this->member($other);

        $this->claim($mainMember, ClaimStatus::Pending);
        $this->claim($mainMember, ClaimStatus::Disbursed, now());
        $this->claim($otherMember, ClaimStatus::Pending);
        $this->claim($otherMember, ClaimStatus::Rejected);

        $metrics = app(DashboardService::class)->getAdminMetrics($president);

        $this->assertSame(2, $metrics['totalClaims']);
        $this->assertSame(1, $metrics['pendingClaims']);
        $this->assertSame(1, $metrics['disbursedClaims']);
        $this->assertSame(0, $metrics['rejectedClaims']);
        $this->assertNull($metrics['chartData']['balanceByCampus']);

        Permission::findOrCreate('view all campus data', 'web');
        $federated = User::factory()->create(['is_active' => true]);
        $federated->givePermissionTo('view all campus data');

        $globalMetrics = app(DashboardService::class)->getAdminMetrics($federated);

        $this->assertSame(4, $globalMetrics['totalClaims']);
        $this->assertSame(2, $globalMetrics['pendingClaims']);
        $this->assertSame(1, $globalMetrics['rejectedClaims']);
        $this->assertIsArray($globalMetrics['chartData']['balanceByCampus']);
    }

    public function test_chart_data_groups_disbursements_by_month_status_and_claim_type(): void
    {
        $campus = Campus::create(['campus_name' => 'Main Campus', 'acronym' => 'MC', 'is_active' => true]);
        $member = $this->member($campus);

        $this->claim($member, ClaimStatus::Disbursed, now()->subMonths(2)->startOfMonth()->addDays(3), '1500.00');
        $this->claim($member, ClaimStatus::Disbursed, now(), '700.00');
        $this->claim($member, ClaimStatus::Disbursed, now(), '300.00');
        $this->claim($member, ClaimStatus::Disbursed, now()->subYears(2), '9999.00'); // outside the 12-month window
        $this->claim($member, ClaimStatus::Pending);

        $chartData = app(DashboardService::class)->getAdminMetrics()['chartData'];

        $monthly = $chartData['monthlyDisbursements'];
        $this->assertCount(12, $monthly['labels']);
        $this->assertSame(now()->format('M Y'), end($monthly['labels']));
        $this->assertEquals(1000.0, $monthly['values'][11]);
        $this->assertEquals(1500.0, $monthly['values'][9]);
        $this->assertEquals(2500.0, array_sum($monthly['values']));

        $status = array_combine($chartData['claimsByStatus']['labels'], $chartData['claimsByStatus']['values']);
        $this->assertSame('Pending', $chartData['claimsByStatus']['labels'][0]);
        $this->assertSame(4, $status['Disbursed']);
        $this->assertSame(1, $status['Pending']);
        $this->assertSame(0, $status['Rejected']);

        $this->assertSame(['Medical'], $chartData['amountByClaimType']['labels']);
        $this->assertEquals([12499.0], $chartData['amountByClaimType']['values']);
    }

    private function member(Campus $campus, ?User $user = null): Member
    {
        $user ??= User::factory()->create(['is_active' => true]);

        return Member::create([
            'campus_id' => $campus->id,
            'user_id' => $user->user_id,
            'employee_number' => 'EMP-' . Str::random(8),
            'first_name' => 'Test',
            'last_name' => 'Member',
            'gender' => 'Female',
            'department' => 'Finance',
            'position' => 'Staff',
            'employment_status' => 'Permanent',
            'membership_status' => 'Active',
            'contact_number' => '09170000000',
            'address' => 'Test address',
        ]);
    }

    private function claim(Member $member, ClaimStatus $status, $disbursedAt = null, string $amount = '1000.00'): Claim
    {
        return Claim::create([
            'claim_number' => 'CLM-' . Str::random(10),
            'member_id' => $member->id,
            'claim_type_id' => $this->claimType->id,
            'payment_method_id' => $this->paymentMethod->id,
            'requested_amount' => $amount,
            'approved_amount' => $amount,
            'annual_limit' => '10000.00',
            'status' => $status->value,
            'submitted_at' => now(),
            'disbursed_at' => $disbursedAt,
        ]);
    }
}
