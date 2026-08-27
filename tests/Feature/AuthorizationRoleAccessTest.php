<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthorizationRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function test_president_can_access_contribution_monitoring(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::findOrCreate('President', 'web');
        $user->assignRole($role);

        $response = $this->actingAs($user)->get(route('contributions.monitoring'));

        $response->assertOk();
    }

    public function test_member_cannot_access_contribution_monitoring(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::findOrCreate('Member', 'web');
        $user->assignRole($role);

        $response = $this->actingAs($user)->get(route('contributions.monitoring'));

        $response->assertForbidden();
    }

    public function test_user_with_view_all_contribution_permission_can_access_monitoring(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        Permission::findOrCreate('view all contribution', 'web');
        $user->givePermissionTo('view all contribution');

        $response = $this->actingAs($user)->get(route('contributions.monitoring'));

        $response->assertOk();
    }

    public function test_treasurer_can_access_disbursements_list(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::findOrCreate('Treasurer', 'web');
        $user->assignRole($role);

        $response = $this->actingAs($user)->get(route('disbursement.index'));

        $response->assertOk();
    }

    public function test_member_cannot_access_disbursements_list(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::findOrCreate('Member', 'web');
        $user->assignRole($role);

        $response = $this->actingAs($user)->get(route('disbursement.index'));

        $response->assertForbidden();
    }

    public function test_user_with_view_disbursment_permission_can_access_disbursements_list(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        Permission::findOrCreate('view disbursement', 'web');
        $user->givePermissionTo('view disbursement');

        $response = $this->actingAs($user)->get(route('disbursement.index'));

        $response->assertOk();
    }
}
