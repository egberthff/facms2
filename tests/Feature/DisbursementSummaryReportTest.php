<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Claim;
use App\Models\ClaimType;
use App\Models\Member;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisbursementSummaryReportTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    public function test_the_date_range_filters_cards_and_campus_rows_by_release_date(): void
    {
        $main = Campus::create(['campus_name' => 'Main Campus', 'acronym' => 'MC', 'location' => 'Bohol', 'is_active' => true]);
        $bilar = Campus::create(['campus_name' => 'Bilar Campus', 'acronym' => 'BLC', 'location' => 'Bohol', 'is_active' => true]);

        // Submitted before the range but released inside it: counted
        $this->claim($main, 'Disbursed', 1000, submittedAt: '2026-01-20', disbursedAt: '2026-02-05');
        $this->claim($main, 'Disbursed', 3000, submittedAt: '2026-02-10', disbursedAt: '2026-02-28');
        $this->claim($bilar, 'Disbursed', 500, submittedAt: '2026-02-01', disbursedAt: '2026-02-15');

        // Released outside the range: excluded
        $this->claim($main, 'Disbursed', 9000, submittedAt: '2026-02-03', disbursedAt: '2026-03-02');
        $this->claim($bilar, 'Disbursed', 7000, submittedAt: '2025-12-01', disbursedAt: '2026-01-10');

        // Approved and submitted inside the range: pending release
        $this->claim($main, 'Approved', 800, submittedAt: '2026-02-12');

        $report = app(ReportService::class)->generate('disbursement-summary', [
            'report_type' => 'disbursement-summary',
            'date_from' => '2026-02-01',
            'date_to' => '2026-02-28',
            'status' => 'Pending', // left over from another report; must be ignored
        ]);

        $cards = collect($report['cards'])->pluck('value', 'key');
        $this->assertSame(3, $cards['released']);
        $this->assertEqualsWithDelta(4500, $cards['amount'], 0.001);
        $this->assertEqualsWithDelta(1500, $cards['average'], 0.001);
        $this->assertSame(1, $cards['pending']);

        $rows = collect($report['rows'])->keyBy('campus');
        $this->assertSame(2, $rows['MC']['released']);
        $this->assertEqualsWithDelta(4000, $rows['MC']['amount'], 0.001);
        $this->assertSame(1, $rows['BLC']['released']);
        $this->assertEqualsWithDelta(500, $rows['BLC']['amount'], 0.001);

        $released = collect($report['context']['filters'])->firstWhere('label', 'Released');
        $this->assertSame('Feb 01, 2026 to Feb 28, 2026', $released['value']);
    }

    private function claim(Campus $campus, string $status, float $amount, string $submittedAt, ?string $disbursedAt = null): Claim
    {
        $user = User::factory()->create(['is_active' => true]);
        $member = Member::create([
            'user_id' => $user->user_id,
            'campus_id' => $campus->id,
            'employee_number' => 'EMP-' . (++$this->sequence),
            'first_name' => 'Test',
            'last_name' => 'Member' . $this->sequence,
            'gender' => 'Female',
            'department' => 'CTAS',
            'position' => 'Instructor',
            'employment_status' => 'Permanent',
            'membership_status' => 'Active',
            'contact_number' => '09170000000',
            'address' => 'Bohol',
        ]);

        return Claim::create([
            'claim_number' => 'CLM-' . $this->sequence,
            'member_id' => $member->id,
            'claim_type_id' => ClaimType::firstOrCreate(['name' => 'Medical'], ['required_document' => 'Medical certificate', 'required_amount' => 100, 'is_active' => true])->id,
            'payment_method_id' => PaymentMethod::firstOrCreate(['name' => 'GCash'], ['is_active' => true])->id,
            'requested_amount' => $amount,
            'approved_amount' => $amount,
            'annual_limit' => 50000,
            'status' => $status,
            'submitted_at' => $submittedAt,
            'disbursed_at' => $disbursedAt,
        ]);
    }
}
