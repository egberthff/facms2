<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Member;
use App\Models\User;
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
    }

    public function test_it_can_store_manual_collection_summary_items(): void
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

        $member = Member::create([
            'user_id' => $user->user_id,
            'campus_id' => $campus->id,
            'first_name' => 'Treasurer',
            'last_name' => 'User',
            'gender' => 'Male',
            'department' => 'Administration',
            'position' => 'Treasurer',
            'employment_status' => 'Permanent',
            'contact_number' => '09123456789',
            'address' => 'Bohol',
            'employee_number' => 'T-001',
            'membership_status' => 'Active',
        ]);

        $service = app(CollectionSummaryService::class);

        $summary = $service->store([
            'campus_id' => $campus->id,
            'period_label' => 'NOVEMBER 2024 - SEPT 2025',
            'description' => 'BISU Balilihan FFA collection summary',
            'items' => [
                ['section' => 'collections', 'label' => 'Annual Dues', 'amount' => 5000.00],
                ['section' => 'collections', 'label' => 'Medical Assistance', 'amount' => 93750.00],
                ['section' => 'disbursements', 'label' => 'CNA Share 2024', 'amount' => 5000.00],
            ],
        ], $user);

        $this->assertDatabaseHas('collection_summary_periods', [
            'campus_id' => $campus->id,
            'period_label' => 'NOVEMBER 2024 - SEPT 2025',
        ]);

        $this->assertCount(3, $summary->items);
        $this->assertSame(98750.00, (float) $summary->items()->where('section', 'collections')->sum('amount'));
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

        $summary = app(CollectionSummaryService::class)->store([
            'campus_id' => $campus->id,
            'period_label' => 'NOVEMBER 2024 - SEPT 2025',
            'description' => 'BISU Balilihan FFA collection summary',
            'items' => [
                ['section' => 'collections', 'label' => 'Annual Dues', 'amount' => 5000.00],
                ['section' => 'collections', 'label' => 'Medical Assistance', 'amount' => 93750.00],
                ['section' => 'disbursements', 'label' => 'CNA Share 2024', 'amount' => 5000.00],
                ['section' => 'disbursements', 'label' => 'Mortuary Aid', 'amount' => 0],
            ],
        ], $user);

        $response = $this->actingAs($user)->get(route('reports.collection-summary.print', ['campus_id' => $campus->id]));

        $response->assertOk();
        $response->assertSeeText('BISU BALILIHAN FACULTY ASSOCIATION');
        $response->assertSee('Annual Dues');
        $response->assertSee('CNA Share 2024');
    }
}
