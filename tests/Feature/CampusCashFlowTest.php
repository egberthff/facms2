<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Claim;
use App\Models\ClaimPayment;
use App\Models\ClaimType;
use App\Models\CollectionSummaryItem;
use App\Models\CollectionSummaryPeriod;
use App\Models\FundAccount;
use App\Models\Member;
use App\Models\PaymentMethod;
use App\Models\SystemSetting;
use App\Models\TreasuryLedger;
use App\Models\User;
use App\Services\CollectionSummaryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Figures mirror the Balilihan campus cash flow as of September 22, 2026.
 */
class CampusCashFlowTest extends TestCase
{
    use RefreshDatabase;

    private Campus $campus;

    private Campus $otherCampus;

    private User $treasurer;

    private User $federatedTreasurer;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['view reports', 'manage funds', 'view all campus data'] as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'Treasurer', 'guard_name' => 'web'])->syncPermissions(['view reports', 'manage funds']);
        Role::firstOrCreate(['name' => 'Federated Treasurer', 'guard_name' => 'web'])->syncPermissions(['view reports', 'manage funds', 'view all campus data']);
        FundAccount::firstOrCreate(['code' => 'FFA'], ['name' => 'FFA Global Fund', 'is_active' => true]);

        $this->campus = $this->makeCampus('Balilihan Campus', 'BC');
        $this->otherCampus = $this->makeCampus('Bilar Campus', 'BLC');

        $this->treasurer = $this->makeUser('Treasurer', $this->campus, 'Catherine', 'Garo');
        $this->federatedTreasurer = $this->makeUser('Federated Treasurer', $this->otherCampus, 'Fed', 'Treasurer');
    }

    public function test_a_draft_snapshots_ffa_funds_and_medical_releases_and_matches_the_sample_totals(): void
    {
        $this->seedSampleActivity();

        $period = $this->createSampleDraft();

        $funds = $period->items->where('section', CollectionSummaryItem::SECTION_FUNDS);
        $medical = $period->items->where('source_type', CollectionSummaryItem::SOURCE_CLAIM_PAYMENT);

        // Pending, other-campus and out-of-range records are excluded
        $this->assertCount(6, $funds);
        $this->assertCount(8, $medical);
        $this->assertEqualsWithDelta(71466.75, $medical->sum('amount'), 0.001);
        $this->assertSame(['January 2026', 'February 2026', 'May 2026', 'June 2026'], $medical->pluck('group_label')->unique()->values()->all());

        $this->addSampleExpenses($period);
        $period->refresh();

        $this->assertSame('December 2025', $period->forwarded_label);
        $this->assertSame('5603.25', $period->forwarded_balance);
        $this->assertSame('145320.00', $period->total_funds);
        $this->assertSame('133103.75', $period->total_disbursements);
        $this->assertSame('12216.25', $period->cash_on_hand);
    }

    public function test_finalizing_posts_manual_lines_to_the_ledger_and_carries_cash_on_hand_forward(): void
    {
        $this->seedSampleActivity();
        $period = $this->createSampleDraft();
        $this->addSampleExpenses($period);

        $ledgerRowsBefore = TreasuryLedger::withoutGlobalScopes()->where('campus_id', $this->campus->id)->count();

        $this->actingAs($this->treasurer)
            ->post(route('reports.cash-flow.finalize', $period))
            ->assertRedirect();

        $period->refresh();
        $this->assertTrue($period->isFinalized());
        $this->assertSame('Catherine Garo', $period->prepared_by_name);

        $manualEntries = TreasuryLedger::withoutGlobalScopes()
            ->where('campus_id', $this->campus->id)
            ->where('sourceable_type', CollectionSummaryItem::class)
            ->get();
        $this->assertCount(5, $manualEntries);
        $this->assertTrue($manualEntries->every(fn ($entry) => $entry->type === 'Debit'));
        $this->assertSame($ledgerRowsBefore + 5, TreasuryLedger::withoutGlobalScopes()->where('campus_id', $this->campus->id)->count());

        // Finalized periods are read-only
        $this->actingAs($this->treasurer)
            ->post(route('reports.cash-flow.items.store', $period), ['section' => 'disbursements', 'label' => 'Late expense', 'amount' => 10])
            ->assertSessionHasErrors('period');

        // The next period starts from this period's cash on hand, no opening balance needed
        $this->actingAs($this->treasurer)
            ->post(route('reports.cash-flow.store'), ['period_from' => '2026-09-23', 'period_to' => '2026-12-31'])
            ->assertRedirect();

        $next = CollectionSummaryPeriod::query()->cashFlow()->whereDate('period_from', '2026-09-23')->firstOrFail();
        $this->assertSame('12216.25', $next->forwarded_balance);
        $this->assertSame('September 2026', $next->forwarded_label);
    }

    public function test_the_first_period_requires_an_opening_balance_and_periods_cannot_overlap(): void
    {
        $this->actingAs($this->treasurer)
            ->post(route('reports.cash-flow.store'), ['period_from' => '2026-01-01', 'period_to' => '2026-09-22'])
            ->assertSessionHasErrors('opening_balance');

        $this->createSampleDraft();

        $this->actingAs($this->treasurer)
            ->post(route('reports.cash-flow.store'), ['period_from' => '2026-09-01', 'period_to' => '2026-10-31', 'opening_balance' => 0])
            ->assertSessionHasErrors('period_from');
    }

    public function test_campus_officers_are_limited_to_their_own_campus(): void
    {
        $period = $this->createSampleDraft();
        $outsider = $this->makeUser('Treasurer', $this->otherCampus, 'Other', 'Officer');

        $this->actingAs($outsider)->get(route('reports.cash-flow.show', $period))->assertForbidden();
        $this->actingAs($outsider)->get(route('reports.cash-flow.print', $period))->assertForbidden();
        $this->actingAs($outsider)->post(route('reports.cash-flow.finalize', $period))->assertForbidden();

        // A campus treasurer cannot open a period for another campus by posting its id
        $this->actingAs($outsider)->post(route('reports.cash-flow.store'), [
            'campus_id' => $this->campus->id,
            'period_from' => '2026-01-01',
            'period_to' => '2026-03-31',
            'opening_balance' => 100,
        ]);
        $this->assertSame(1, CollectionSummaryPeriod::where('campus_id', $this->campus->id)->count());
        $this->assertSame(1, CollectionSummaryPeriod::where('campus_id', $this->otherCampus->id)->count());

        $this->actingAs($this->federatedTreasurer)->get(route('reports.cash-flow.show', $period))->assertOk();
        $this->actingAs($this->federatedTreasurer)->get(route('reports.cash-flow.index', ['campus_filter' => $this->campus->id]))->assertOk()->assertSee('Open');
        $this->actingAs($this->treasurer)->get(route('reports.cash-flow.show', $period))->assertOk()->assertSee('Finalize');
    }

    public function test_refresh_keeps_label_edits_and_the_print_view_renders_the_report(): void
    {
        $this->seedSampleActivity();
        $period = $this->createSampleDraft();
        $this->addSampleExpenses($period);

        $checker = $this->makeMember($this->campus, 'Julie', 'Bitasolo');
        SystemSetting::create([
            'setting_key' => 'cash_flow_checked_by',
            'setting_value' => $checker->id,
            'campus_id' => $this->campus->id,
        ]);

        $release = $period->items()->where('source_type', CollectionSummaryItem::SOURCE_CLAIM_PAYMENT)->where('amount', 1360)->firstOrFail();
        $this->actingAs($this->treasurer)
            ->put(route('reports.cash-flow.items.update', $release), ['label' => $release->label, 'notes' => 'with P10 transaction fee', 'amount' => 1])
            ->assertRedirect();

        $this->actingAs($this->treasurer)->post(route('reports.cash-flow.refresh', $period))->assertRedirect();

        $release = $period->items()->where('source_reference', $release->source_reference)->firstOrFail();
        $this->assertSame('with P10 transaction fee', $release->notes);
        $this->assertSame('1360.00', $release->amount);

        $this->actingAs($this->treasurer)
            ->get(route('reports.cash-flow.print', $period))
            ->assertOk()
            ->assertSeeText('BISU BALILIHAN FACULTY ASSOCIATION')
            ->assertSeeText('(As of September 22, 2026)')
            ->assertSeeText('Medical Assistance Reimbursement (P71,466.75)')
            ->assertSeeText('(with P10 transaction fee)')
            ->assertSeeText('P145,320.00')
            ->assertSeeText('P12,216.25')
            ->assertSeeText('Catherine Garo')
            ->assertSeeText('Julie Bitasolo');
    }

    public function test_the_editor_gets_json_responses_with_field_errors(): void
    {
        $period = $this->createSampleDraft();

        $this->actingAs($this->treasurer)
            ->postJson(route('reports.cash-flow.items.store', $period), ['section' => 'disbursements', 'label' => '', 'amount' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['label', 'amount']);

        $this->actingAs($this->treasurer)
            ->postJson(route('reports.cash-flow.items.store', $period), ['section' => 'disbursements', 'label' => 'Snacks', 'amount' => 250.50])
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Line added.', 'redirect' => null]);

        $this->actingAs($this->treasurer)
            ->deleteJson(route('reports.cash-flow.destroy', $period))
            ->assertOk()
            ->assertJson(['success' => true, 'redirect' => route('reports.cash-flow.index', ['campus_filter' => $this->campus->id])]);
    }

    private function createSampleDraft(): CollectionSummaryPeriod
    {
        $this->actingAs($this->treasurer)
            ->post(route('reports.cash-flow.store'), [
                'period_from' => '2026-01-01',
                'period_to' => '2026-09-22',
                'opening_balance' => 5603.25,
                'opening_label' => 'December 2025',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        return CollectionSummaryPeriod::query()->cashFlow()->where('campus_id', $this->campus->id)->with('items')->firstOrFail();
    }

    private function addSampleExpenses(CollectionSummaryPeriod $period): void
    {
        foreach ([
            ['Tarpaulin Printing (CNA Day December 2025)', 432],
            ['Released CNA Share 2025 (42 pax * P1300)', 54600],
            ['Snacks for Campus FA Meeting', 855],
            ['Snacks for the FA Special General Assembly', 1250],
            ['Food Provision for FFA Monthly Meeting July 2026', 4500],
        ] as [$label, $amount]) {
            $this->actingAs($this->treasurer)
                ->post(route('reports.cash-flow.items.store', $period), [
                    'section' => 'disbursements',
                    'label' => $label,
                    'amount' => $amount,
                ])
                ->assertSessionHasNoErrors();
        }
    }

    private function seedSampleActivity(): void
    {
        $transfers = app(CollectionSummaryService::class);
        $this->actingAs($this->federatedTreasurer); // ledger entries record the acting user

        Carbon::setTestNow('2026-08-15 10:00:00');
        foreach ([
            ['CNA Share 2025 for COS Personnel and Guards (42pax *P1300)', 54600],
            ['Snacks for Special General Assembly', 1250],
            ['FFA July 2026 Monthly Meeting (Food Budget)', 4500],
            ['Medical Assistance (including transaction charges)', 71466.75],
            ['Annual Dues Share 2026 (29 members *100)', 2900],
            ['CNA Day Share 2026', 5000],
        ] as [$description, $amount]) {
            $transfer = $transfers->createTransfer([
                'campus_id' => $this->campus->id,
                'type' => 'ffa_disbursement',
                'reference_number' => 'DIS-' . (++$this->sequence),
                'description' => $description,
                'amount' => $amount,
            ], $this->federatedTreasurer);
            $transfers->approveTransfer($transfer, $this->federatedTreasurer);
        }

        // Excluded: still pending, and approved for another campus
        $transfers->createTransfer(['campus_id' => $this->campus->id, 'type' => 'ffa_disbursement', 'reference_number' => 'DIS-PENDING', 'description' => 'Pending', 'amount' => 999], $this->federatedTreasurer);
        $other = $transfers->createTransfer(['campus_id' => $this->otherCampus->id, 'type' => 'ffa_disbursement', 'reference_number' => 'DIS-OTHER', 'description' => 'Other campus', 'amount' => 888], $this->federatedTreasurer);
        $transfers->approveTransfer($other, $this->federatedTreasurer);
        Carbon::setTestNow();

        foreach ([
            ['2026-01-20', 'Evangielyn', 'Lumantas', 23000],
            ['2026-01-21', 'Marc Benigno', 'Olaguir', 5520],
            ['2026-01-22', 'Shella', 'Olaguir', 3060],
            ['2026-02-10', 'Estrella Marie', 'Bungabong', 3570],
            ['2026-05-11', 'Estrella Marie', 'Bungabong', 1360],
            ['2026-05-12', 'Jocelyn', 'Lumactud', 6685],
            ['2026-06-15', 'Emiliano', 'Maravilla', 16720],
            ['2026-06-16', 'Riame', 'Tuico', 11551.75],
        ] as [$date, $first, $last, $amount]) {
            $this->releaseClaim($this->makeMember($this->campus, $first, $last), $amount, $date);
        }

        // Excluded: released before the period, and released to another campus's member
        $this->releaseClaim($this->makeMember($this->campus, 'Early', 'Release'), 700, '2025-12-15');
        $this->releaseClaim($this->makeMember($this->otherCampus, 'Other', 'Campus'), 600, '2026-03-01');
    }

    private function releaseClaim(Member $member, float $amount, string $releasedAt): void
    {
        $claimType = ClaimType::firstOrCreate(['name' => 'Medical'], ['required_document' => 'Medical certificate', 'required_amount' => 100, 'is_active' => true]);
        $paymentMethod = PaymentMethod::firstOrCreate(['name' => 'GCash'], ['is_active' => true]);

        $claim = Claim::create([
            'claim_number' => 'CLM-' . (++$this->sequence),
            'member_id' => $member->id,
            'claim_type_id' => $claimType->id,
            'payment_method_id' => $paymentMethod->id,
            'requested_amount' => $amount,
            'approved_amount' => $amount,
            'annual_limit' => 50000,
            'status' => 'Disbursed',
            'submitted_at' => $releasedAt,
            'disbursed_at' => $releasedAt,
        ]);

        ClaimPayment::create([
            'claim_id' => $claim->id,
            'payment_method_id' => $paymentMethod->id,
            'reference_number' => 'REF-' . $this->sequence,
            'amount' => $amount,
            'processed_by' => $this->treasurer->user_id,
            'released_at' => Carbon::parse($releasedAt)->setTime(9, 0),
        ]);
    }

    private function makeCampus(string $name, string $acronym): Campus
    {
        return Campus::create(['campus_name' => $name, 'acronym' => $acronym, 'location' => 'Bohol', 'is_active' => true]);
    }

    private function makeUser(string $role, Campus $campus, string $first, string $last): User
    {
        $user = User::factory()->create(['name' => "{$first} {$last}", 'is_active' => true]);
        $user->assignRole($role);
        $this->makeMember($campus, $first, $last, $user);

        return $user;
    }

    private function makeMember(Campus $campus, string $first, string $last, ?User $user = null): Member
    {
        $user ??= User::factory()->create(['name' => "{$first} {$last}", 'is_active' => true]);

        return Member::create([
            'user_id' => $user->user_id,
            'campus_id' => $campus->id,
            'employee_number' => 'EMP-' . (++$this->sequence),
            'first_name' => $first,
            'last_name' => $last,
            'gender' => 'Female',
            'department' => 'CTAS',
            'position' => 'Instructor',
            'employment_status' => 'Permanent',
            'membership_status' => 'Active',
            'contact_number' => '09170000000',
            'address' => 'Balilihan, Bohol',
        ]);
    }
}
