<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\FundAccount;
use App\Models\FundTransfer;
use App\Models\User;
use App\Models\TreasuryLedger;
use App\Services\CollectionSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CollectionSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Federated Treasurer', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view reports', 'guard_name' => 'web']);
        FundAccount::firstOrCreate(
            ['code' => 'FFA'],
            ['name' => 'FFA Global Fund', 'is_active' => true],
        );
    }

    public function test_an_approved_transfer_posts_both_ledger_entries(): void
    {
        $campus = Campus::create([
            'campus_name' => 'Balilihan Campus',
            'acronym' => 'BC',
            'location' => 'Balilihan, Bohol',
            'is_active' => true,
        ]);

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('Federated Treasurer');
        $user->givePermissionTo('view reports');

        $service = app(CollectionSummaryService::class);
        $this->actingAs($user);

        $transfer = $service->createTransfer([
            'campus_id' => $campus->id,
            'type' => 'campus_remittance',
            'reference_number' => 'REM-001',
            'description' => 'Annual dues remittance',
            'amount' => 5000.00,
        ], $user);
        $this->assertSame('pending', $transfer->status);
        $this->assertCount(0, TreasuryLedger::withoutGlobalScopes()->get());

        $service->approveTransfer($transfer, $user);

        $this->assertDatabaseHas('fund_transfers', ['id' => $transfer->id, 'status' => 'approved']);
        $this->assertSame(2, TreasuryLedger::withoutGlobalScopes()->where('transfer_id', $transfer->id)->count());
        $this->assertSame(-5000.00, (float) TreasuryLedger::withoutGlobalScopes()->where('campus_id', $campus->id)->value('running_balance'));
    }

    public function test_it_renders_the_printable_collection_summary_view(): void
    {
        $campus = Campus::create([
            'campus_name' => 'Balilihan Campus',
            'acronym' => 'BC',
            'location' => 'Balilihan, Bohol',
            'is_active' => true,
        ]);

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('Federated Treasurer');
        $user->givePermissionTo('view reports');

        $service = app(CollectionSummaryService::class);
        $this->actingAs($user);
        $transfer = $service->createTransfer([
            'campus_id' => $campus->id,
            'type' => 'ffa_disbursement',
            'reference_number' => 'DIS-001',
            'description' => 'CNA Share 2024',
            'amount' => 5000.00,
        ], $user);
        $service->approveTransfer($transfer, $user);

        $response = $this->get(route('reports.collection-summary.print', [
            'campus_id' => $campus->id,
            'from' => now()->subDay()->toDateString(),
            'to' => now()->addDay()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertSeeText('BISU BALILIHAN CAMPUS');
        $response->assertSee('CNA Share 2024');

        $data = $service->buildPrintableData($campus->id, now()->subDay()->toDateString(), now()->addDay()->toDateString());
        $this->assertSame(5000.00, $data['disbursement_total']);
        $this->assertCount(0, $data['collections']);
    }

    public function test_print_url_without_campus_id_renders_the_template(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('Federated Treasurer');
        $user->givePermissionTo('view reports');

        $response = $this->actingAs($user)->get(route('reports.collection-summary.print', [
            'auto_print' => 1,
        ]));

        $response->assertOk();
        $response->assertSeeText('ALL APPROVED TRANSFERS');
    }
}
