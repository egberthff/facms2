<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\SystemSetting;
use App\Models\TreasuryLedger;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AssociationFundTest extends TestCase
{
    use RefreshDatabase;

    public function test_association_fund_cash_in_accumulates_and_creates_a_treasury_credit(): void
    {
        $user = $this->globalAccessUser();
        $campus = Campus::create(['campus_name' => 'Test Campus', 'acronym' => 'TC', 'is_active' => true]);
        $setting = SystemSetting::create([
            'setting_key' => 'association_fund',
            'setting_value' => '1000.00',
            'description' => 'Association fund entry',
            'campus_id' => $campus->id,
        ]);

        $response = $this->actingAs($user)->put(route('settings.update', $setting), [
            'setting_value' => '500.00',
            'campus_filter' => $campus->id,
        ]);

        $response->assertRedirect(route('settings.index', ['campus_filter' => $campus->id]));
        $this->assertDatabaseHas('system_settings', [
            'id' => $setting->id,
            'setting_value' => '1500.00',
            'cycle_label' => null,
        ]);
        $this->assertDatabaseHas('treasury_ledger', [
            'campus_id' => $campus->id,
            'type' => 'Credit',
            'amount' => '500.00',
            'running_balance' => '500.00',
            'description' => 'Association fund cash-in',
        ]);
    }

    public function test_admin_dashboard_balance_uses_latest_ledger_balance_per_campus(): void
    {
        $campus = Campus::create(['campus_name' => 'Test Campus', 'acronym' => 'TC', 'is_active' => true]);
        $user = $this->globalAccessUser();

        TreasuryLedger::create([
            'campus_id' => $campus->id,
            'reference_number' => 'AF-TEST-1',
            'description' => 'Association fund cash-in',
            'type' => 'Credit',
            'amount' => '500.00',
            'running_balance' => '500.00',
            'entry_by' => $user->user_id,
        ]);
        TreasuryLedger::create([
            'campus_id' => $campus->id,
            'reference_number' => 'CT-TEST-1',
            'description' => 'Contribution',
            'type' => 'Credit',
            'amount' => '250.00',
            'running_balance' => '750.00',
            'entry_by' => $user->user_id,
        ]);

        $metrics = app(DashboardService::class)->getAdminMetrics();

        $this->assertSame(750.0, $metrics['balance']);
    }

    private function globalAccessUser(): User
    {
        Permission::findOrCreate('view all campus data', 'web');

        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo('view all campus data');

        return $user;
    }
}
