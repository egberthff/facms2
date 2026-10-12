<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InactiveMembershipAccessTest extends TestCase
{
    use RefreshDatabase;

    private function memberWithStatus(string $status): User
    {
        $role = Role::findOrCreate('Member', 'web');
        $role->syncPermissions(collect(['create claims', 'view own claims', 'view member metrics', 'view own contribution'])
            ->map(fn ($name) => Permission::findOrCreate($name, 'web')));

        $campus = Campus::create(['campus_name' => 'Balilihan Campus', 'acronym' => 'BC', 'is_active' => true]);

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        $user->member()->create([
            'employee_number' => 'EMP-'.uniqid(),
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'gender' => 'Female',
            'department' => 'Engineering',
            'position' => 'Instructor I',
            'employment_status' => 'Permanent',
            'membership_status' => $status,
            'contact_number' => '09123456789',
            'address' => 'Balilihan, Bohol',
            'campus_id' => $campus->id,
        ]);

        return $user;
    }

    public function test_inactive_member_can_open_dashboard_and_profile(): void
    {
        $user = $this->memberWithStatus('Inactive');

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Membership pending activation')
            ->assertSee(route('members.profile'))
            ->assertDontSee(route('claim.create'));

        $this->actingAs($user)->get(route('members.profile'))->assertOk();
    }

    public function test_inactive_member_is_redirected_away_from_other_pages(): void
    {
        $user = $this->memberWithStatus('Inactive');

        foreach ([route('claim.create'), route('balance.my-metrics'), route('member.my-contributions', $user)] as $url) {
            $this->actingAs($user)->get($url)
                ->assertRedirect(route('dashboard'))
                ->assertSessionHas('error');
        }
    }

    public function test_inactive_member_gets_403_for_json_requests(): void
    {
        $user = $this->memberWithStatus('Inactive');

        $this->actingAs($user)->getJson(route('balance.my-metrics'))->assertForbidden();
    }

    public function test_active_member_is_not_restricted(): void
    {
        $user = $this->memberWithStatus('Active');

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Membership pending activation');

        $this->actingAs($user)->get(route('balance.my-metrics'))
            ->assertSessionMissing('error');
    }
}
