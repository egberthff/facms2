<?php

namespace Tests\Feature;

use App\Enums\ClaimStatus;
use App\Models\Campus;
use App\Models\Claim;
use App\Models\ClaimType;
use App\Models\Member;
use App\Models\MemberClaimCycle;
use App\Models\PaymentMethod;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\ClaimService;
use App\Services\MemberClaimCycleService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use LogicException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MemberClaimCycleTest extends TestCase
{
    use RefreshDatabase;

    private Campus $campus;

    private ClaimType $claimType;

    private PaymentMethod $paymentMethod;

    private MemberClaimCycleService $cycles;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->campus = Campus::create(['campus_name' => 'Main Campus', 'acronym' => 'MC', 'location' => 'Bohol', 'is_active' => true]);
        $this->setting('claim_cycle_years', '2');
        $this->setting('annual_claim_limit', '23000.00');

        $this->claimType = ClaimType::create([
            'name' => 'Medical',
            'required_document' => 'Prescription',
            'description' => 'Medical assistance',
            'required_amount' => '100.00',
            'is_active' => true,
        ]);
        $this->paymentMethod = PaymentMethod::create(['name' => 'GCash', 'is_active' => true]);
        $this->cycles = app(MemberClaimCycleService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_first_claim_starts_the_first_cycle(): void
    {
        $member = $this->makeMember();

        $claim = $this->submit($member, '2026-08-14', 1000);

        $cycle = $claim->memberClaimCycle;
        $this->assertSame('2026-08-01', $cycle->cycle_start->toDateString());
        $this->assertSame('2028-07-31', $cycle->cycle_end->toDateString());
        $this->assertSame(2, $cycle->duration_years);
        $this->assertSame('23000.00', (string) $cycle->claim_limit);
    }

    public function test_each_member_has_an_independent_cycle(): void
    {
        $memberA = $this->makeMember();
        $memberB = $this->makeMember();

        $this->submit($memberB, '2026-02-10', 1000);
        $this->submit($memberA, '2026-08-20', 1000);

        $this->assertCycles($memberA, [['2026-08-01', '2028-07-31']]);
        $this->assertCycles($memberB, [['2026-02-01', '2028-01-31']]);
    }

    public function test_claims_inside_the_active_cycle_share_the_same_cycle(): void
    {
        $member = $this->makeMember();

        $first = $this->submit($member, '2026-08-01', 5000);
        $second = $this->submit($member, '2027-03-15', 5000);
        $last = $this->submit($member, '2028-07-31', 5000);

        $this->assertSame($first->member_claim_cycle_id, $second->member_claim_cycle_id);
        $this->assertSame($first->member_claim_cycle_id, $last->member_claim_cycle_id);
        $this->assertCycles($member, [['2026-08-01', '2028-07-31']]);
        $this->assertEqualsWithDelta(8000, $this->cycles->getRemainingClaimAllowance($member, Carbon::parse('2028-07-31')), 0.001);
    }

    public function test_claims_are_rejected_once_the_allowance_is_exhausted_before_the_cycle_ends(): void
    {
        $member = $this->makeMember();
        $this->submit($member, '2026-08-05', 23000);

        Carbon::setTestNow('2028-01-10 09:00:00');

        try {
            $this->cycles->assertEligible($member, 1, now());
            $this->fail('A claim inside an exhausted cycle must be rejected.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('August 01, 2028', $e->errors()['amount'][0]);
        }

        Permission::firstOrCreate(['name' => 'create claims', 'guard_name' => 'web']);
        $member->user->givePermissionTo('create claims');

        $this->withoutMiddleware()
            ->actingAs($member->user)
            ->postJson(route('claim.store'), $this->payload(500))
            ->assertStatus(422)
            ->assertJsonValidationErrors('amount');

        $this->assertSame(1, Claim::where('member_id', $member->id)->count());
    }

    public function test_a_claim_exceeding_the_remaining_allowance_is_rejected_at_submission(): void
    {
        $member = $this->makeMember();
        $this->submit($member, '2026-08-05', 20000);

        $this->expectException(ValidationException::class);
        $this->submit($member, '2026-09-05', 3500);
    }

    public function test_unused_allowance_is_forfeited_and_not_carried_into_the_next_cycle(): void
    {
        $member = $this->makeMember();
        $this->submit($member, '2026-08-05', 15000);

        $this->assertEqualsWithDelta(8000, $this->cycles->getRemainingClaimAllowance($member, Carbon::parse('2028-07-31')), 0.001);

        $next = $this->submit($member, '2028-08-02', 1000);

        $this->assertSame('23000.00', (string) $next->memberClaimCycle->claim_limit);
        $this->assertEqualsWithDelta(22000, $this->cycles->getRemainingClaimAllowance($member, Carbon::parse('2028-08-02')), 0.001);
    }

    public function test_a_claim_right_after_expiry_starts_the_next_cycle(): void
    {
        $member = $this->makeMember();
        $this->submit($member, '2026-08-05', 1000);

        $this->submit($member, '2028-08-01', 1000);

        $this->assertCycles($member, [
            ['2026-08-01', '2028-07-31'],
            ['2028-08-01', '2030-07-31'],
        ]);
    }

    public function test_a_gap_between_cycles_is_allowed(): void
    {
        $member = $this->makeMember();
        $this->submit($member, '2026-08-05', 1000);

        Carbon::setTestNow('2028-09-15 10:00:00');
        $this->assertNull($this->cycles->getActiveCycleForMember($member));

        $this->submit($member, '2028-10-03', 1000);

        $this->assertCycles($member, [
            ['2026-08-01', '2028-07-31'],
            ['2028-10-01', '2030-09-30'],
        ]);
        $this->assertNull($this->cycles->getCycleForDate($member, Carbon::parse('2028-08-15')));
    }

    public function test_a_member_without_claims_has_no_cycle(): void
    {
        $member = $this->makeMember();

        $this->assertSame(0, $member->claimCycles()->count());
        $this->assertNull($this->cycles->getActiveCycleForMember($member));
        $this->assertEqualsWithDelta(23000, $this->cycles->getRemainingClaimAllowance($member), 0.001);
    }

    public function test_historical_claims_are_grouped_into_member_cycles_chronologically(): void
    {
        $member = $this->makeMember();
        $other = $this->makeMember();

        $aug2022 = $this->legacyClaim($member, '2022-08-10', ClaimStatus::Disbursed);
        $mar2023 = $this->legacyClaim($member, '2023-03-04', ClaimStatus::Disbursed);
        $rejected = $this->legacyClaim($member, '2024-09-01', ClaimStatus::Rejected);
        $dec2024 = $this->legacyClaim($member, '2024-12-20', ClaimStatus::Approved);
        $otherClaim = $this->legacyClaim($other, '2023-05-02', ClaimStatus::Disbursed);

        $this->artisan('claims:rebuild-member-cycles')->assertSuccessful();

        $this->assertCycles($member, [
            ['2022-08-01', '2024-07-31'],
            ['2024-12-01', '2026-11-30'],
        ]);
        $this->assertCycles($other, [['2023-05-01', '2025-04-30']]);

        $first = MemberClaimCycle::where('member_id', $member->id)->orderBy('cycle_start')->first();
        $this->assertSame($first->id, $aug2022->fresh()->member_claim_cycle_id);
        $this->assertSame($first->id, $mar2023->fresh()->member_claim_cycle_id);
        $this->assertNotSame($first->id, $dec2024->fresh()->member_claim_cycle_id);
        // The rejected claim falls in the gap, so it neither starts nor joins a cycle
        $this->assertNull($rejected->fresh()->member_claim_cycle_id);
        $this->assertNotNull($otherClaim->fresh()->member_claim_cycle_id);

        // Idempotent
        $this->artisan('claims:rebuild-member-cycles')->assertSuccessful();
        $this->assertSame(2, MemberClaimCycle::where('member_id', $member->id)->count());
    }

    public function test_overlapping_cycles_cannot_be_created_for_a_member(): void
    {
        $member = $this->makeMember();
        $this->submit($member, '2026-08-05', 1000);

        try {
            $this->cycles->startNewCycleForMember($member, Carbon::parse('2027-01-10'));
            $this->fail('An overlapping cycle must not be created.');
        } catch (LogicException) {
            $this->assertSame(1, $member->claimCycles()->count());
        }

        $this->expectException(QueryException::class);
        MemberClaimCycle::create([
            'member_id' => $member->id,
            'cycle_start' => Carbon::parse('2026-08-01'),
            'cycle_end' => Carbon::parse('2028-07-31'),
            'duration_years' => 2,
            'claim_limit' => 23000,
        ]);
    }

    public function test_pending_claims_reserve_the_allowance(): void
    {
        $member = $this->makeMember();
        $this->submit($member, '2026-08-05', 18000);

        $this->assertEqualsWithDelta(5000, $this->cycles->getRemainingClaimAllowance($member, Carbon::parse('2026-08-06')), 0.001);
        $this->assertFalse($this->cycles->isMemberEligibleForClaim($member, 5000.01, Carbon::parse('2026-08-06')));
        $this->assertTrue($this->cycles->isMemberEligibleForClaim($member, 5000, Carbon::parse('2026-08-06')));
    }

    public function test_rejecting_the_only_claim_releases_its_cycle(): void
    {
        $member = $this->makeMember();
        $claim = $this->submit($member, '2026-08-05', 1000);

        app(ClaimService::class)->updateStatus($claim, ['status' => ClaimStatus::Rejected->value]);

        $this->assertSame(0, $member->claimCycles()->count());
        $this->assertNull($claim->fresh()->member_claim_cycle_id);

        $next = $this->submit($member, '2026-11-20', 1000);
        $this->assertSame('2026-11-01', $next->memberClaimCycle->cycle_start->toDateString());
    }

    public function test_changing_the_configured_duration_does_not_alter_an_existing_cycle(): void
    {
        $member = $this->makeMember();
        $this->submit($member, '2026-08-05', 1000);

        SystemSetting::where('setting_key', 'claim_cycle_years')->update(['setting_value' => '3']);
        $this->submit($member, '2027-01-05', 1000);
        $this->submit($member, '2028-09-01', 1000);

        $this->assertCycles($member, [
            ['2026-08-01', '2028-07-31'],
            ['2028-09-01', '2031-08-31'],
        ]);
    }

    public function test_allowance_reports_use_each_members_own_cycle(): void
    {
        $memberA = $this->makeMember();
        $memberB = $this->makeMember();
        $idle = $this->makeMember();

        $this->submit($memberB, '2026-02-10', 4000);
        $this->submit($memberA, '2026-08-20', 15000);
        Carbon::setTestNow('2028-09-15 10:00:00');
        $this->submit($memberB, '2028-09-15', 2000); // B's first cycle expired Jan 2028; new one from Sep 2028
        Carbon::setTestNow('2028-09-20 10:00:00');

        $reports = app(\App\Services\ReportService::class);

        $utilization = collect($reports->generate('allowance-utilization', ['cycle' => 'current'])['rows'])->keyBy('member');
        $this->assertSame('September 2028 - August 2030', $utilization[$memberB->full_name]['cycle']);
        $this->assertEqualsWithDelta(21000, $utilization[$memberB->full_name]['remaining'], 0.001);
        // A's cycle (Aug 2026 - Jul 2028) has expired, so A has no active cycle, like a member who never claimed
        $this->assertSame('No active cycle', $utilization[$memberA->full_name]['cycle']);
        $this->assertSame('No active cycle', $utilization[$idle->full_name]['cycle']);
        $this->assertEqualsWithDelta(23000, $utilization[$idle->full_name]['remaining'], 0.001);

        $forfeiture = collect($reports->generate('allowance-forfeiture', ['cycle' => 'current'])['rows'])->keyBy('member');
        $this->assertCount(2, $forfeiture);
        $this->assertSame('August 2026 - July 2028', $forfeiture[$memberA->full_name]['cycle']);
        $this->assertEqualsWithDelta(8000, $forfeiture[$memberA->full_name]['forfeited'], 0.001);
        $this->assertSame('February 2026 - January 2028', $forfeiture[$memberB->full_name]['cycle']);
        $this->assertEqualsWithDelta(19000, $forfeiture[$memberB->full_name]['forfeited'], 0.001);

        $all = $reports->generate('allowance-utilization', ['cycle' => 'all'])['rows'];
        $this->assertCount(3, $all);

        $summary = $reports->generate('campus-summary', ['cycle' => 'current'])['rows'];
        $this->assertSame(1, $summary[0]['claims']);
    }

    public function test_medical_assistance_and_payroll_coverage_use_the_claimants_cycle(): void
    {
        $member = $this->makeMember();
        $this->submit($member, '2026-08-05', 15000);
        $claim = $this->submit($member, '2027-02-05', 8000);
        $claim->update(['status' => ClaimStatus::Approved->value, 'approved_amount' => '8000.00']);

        foreach (['Federated Treasurer', 'Federated President', 'Treasurer', 'President'] as $role) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
        $treasurer = User::factory()->create(['is_active' => true]);
        $treasurer->assignRole('Federated Treasurer');
        $this->actingAs($treasurer);

        $report = app(\App\Services\ReportService::class)->generate('medical-assistance-report', ['cycle' => 'current', 'campus_id' => $this->campus->id]);

        $this->assertCount(1, $report['rows']);
        $row = $report['rows'][0];
        $this->assertEqualsWithDelta(8000, $row['approved_amount'], 0.001);
        $this->assertEqualsWithDelta(0, $row['balance'], 0.001);
        $this->assertSame('August 2028', explode('Can claim by ', $row['remarks'])[1]);
        $this->assertEqualsWithDelta(23000, $row['max_claimable_amount'], 0.001);

        $coverage = (new \ReflectionMethod(\App\Services\ReportService::class, 'formatPayrollCoverage'))
            ->invoke(app(\App\Services\ReportService::class), $claim->fresh('member'));
        $this->assertSame('August 2026-July 2028', $coverage);
    }

    public function test_claim_lists_carry_the_claimants_cycle_summary(): void
    {
        $member = $this->makeMember();
        $this->submit($member, '2026-08-05', 15000);
        $this->submit($member, '2027-02-05', 3000);

        $claims = app(ClaimService::class)->getClaimsForUser($member->user);
        $payload = $claims->first()->toArray();

        $this->assertSame('August 2026 - July 2028', $payload['cycle_summary']['label']);
        $this->assertEqualsWithDelta(23000, $payload['cycle_summary']['limit'], 0.001);
        $this->assertEqualsWithDelta(5000, $payload['cycle_summary']['remaining'], 0.001);
        $this->assertTrue($payload['cycle_summary']['is_active']);

        // Display-only data must never be persisted
        $claims->first()->update(['remarks' => 'Checked']);
        $this->assertSame('Checked', $claims->first()->fresh()->remarks);
    }

    private function setting(string $key, string $value): void
    {
        SystemSetting::create([
            'setting_key' => $key,
            'setting_value' => $value,
            'description' => $key,
            'campus_id' => $this->campus->id,
            'cycle_label' => null,
        ]);
    }

    private function makeMember(): Member
    {
        $user = User::factory()->create(['is_active' => true]);

        return Member::create([
            'user_id' => $user->user_id,
            'campus_id' => $this->campus->id,
            'employee_number' => 'EMP-' . (++$this->sequence),
            'first_name' => 'Member',
            'last_name' => (string) $this->sequence,
            'gender' => 'Female',
            'department' => 'CTAS',
            'position' => 'Instructor',
            'employment_status' => 'Permanent',
            'membership_status' => 'Active',
            'contact_number' => '09170000000',
            'address' => 'Bohol',
        ]);
    }

    /**
     * Submit through the real ClaimService flow at the given date.
     */
    private function submit(Member $member, string $date, float $amount): Claim
    {
        Carbon::setTestNow(Carbon::parse($date . ' 10:00:00'));
        $this->actingAs($member->user);

        return app(ClaimService::class)->createClaim($this->payload($amount), [])->fresh('memberClaimCycle');
    }

    private function payload(float $amount): array
    {
        return [
            'claimType' => $this->claimType->id,
            'paymentMethod' => $this->paymentMethod->id,
            'amount' => $amount,
            'description' => 'Hospitalization',
            'incidentDate' => now()->toDateString(),
        ];
    }

    /**
     * A claim recorded before member cycles existed (no cycle link).
     */
    private function legacyClaim(Member $member, string $date, ClaimStatus $status): Claim
    {
        $approved = $status === ClaimStatus::Rejected ? null : '1000.00';

        return Claim::create([
            'claim_number' => 'CLM-LEGACY-' . (++$this->sequence),
            'member_id' => $member->id,
            'claim_type_id' => $this->claimType->id,
            'payment_method_id' => $this->paymentMethod->id,
            'requested_amount' => '1000.00',
            'approved_amount' => $approved,
            'annual_limit' => '23000.00',
            'status' => $status->value,
            'submitted_at' => Carbon::parse($date . ' 09:00:00'),
        ]);
    }

    private function assertCycles(Member $member, array $expected): void
    {
        $actual = MemberClaimCycle::where('member_id', $member->id)
            ->orderBy('cycle_start')
            ->get()
            ->map(fn (MemberClaimCycle $cycle) => [$cycle->cycle_start->toDateString(), $cycle->cycle_end->toDateString()])
            ->all();

        $this->assertSame($expected, $actual);
    }
}
