<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Dashboard');
    }

    public function test_admin_user_sees_dashboard_stats(): void
    {
        $user = User::factory()->create();
        $user->assignRole('President');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertViewHasAll([
            'members',
            'pendingClaims',
            'approvedClaims',
            'rejectedClaims',
        ]);
    }
}
