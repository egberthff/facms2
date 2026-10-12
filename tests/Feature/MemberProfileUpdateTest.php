<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $campus = Campus::create(['campus_name' => 'Main Campus', 'acronym' => 'MC', 'is_active' => true]);
        $this->user = User::factory()->create(['is_active' => true]);
        $this->member = Member::create([
            'campus_id' => $campus->id,
            'user_id' => $this->user->user_id,
            'employee_number' => 'EMP-2001',
            'first_name' => 'Ana',
            'last_name' => 'Dela Cruz',
            'gender' => 'Female',
            'department' => 'Finance',
            'position' => 'Staff',
            'employment_status' => 'Permanent',
            'membership_status' => 'Active',
            'contact_number' => '09170000000',
            'address' => 'Old address',
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'gender' => 'Female',
            'birth_date' => '1990-05-12',
            'contact_number' => '09181234567',
            'address' => 'New address, Tagbilaran City',
            'gcash_number' => '09171234567',
            'gcash_account_name' => 'Ana Dela Cruz',
        ], $overrides);
    }

    public function test_member_can_update_contact_and_payout_details(): void
    {
        $response = $this->actingAs($this->user)->patch(route('members.profile.update'), $this->validPayload());

        $response->assertRedirect(route('members.profile'));
        $response->assertSessionHas('success');

        $this->member->refresh();
        $this->assertSame('09181234567', $this->member->contact_number);
        $this->assertSame('New address, Tagbilaran City', $this->member->address);
        $this->assertSame('1990-05-12', $this->member->birth_date->toDateString());
        $this->assertSame('09171234567', $this->member->gcash_number);
        $this->assertSame('Ana Dela Cruz', $this->member->gcash_account_name);
    }

    public function test_identity_and_employment_fields_are_ignored(): void
    {
        $this->actingAs($this->user)->patch(route('members.profile.update'), $this->validPayload([
            'first_name' => 'Hacked',
            'employee_number' => 'EMP-9999',
            'membership_status' => 'Inactive',
            'campus_id' => 'another-campus',
            'position' => 'President',
        ]))->assertRedirect(route('members.profile'));

        $this->member->refresh();
        $this->assertSame('Ana', $this->member->first_name);
        $this->assertSame('EMP-2001', $this->member->employee_number);
        $this->assertSame('Active', $this->member->membership_status);
        $this->assertSame('Staff', $this->member->position);
    }

    public function test_user_without_member_profile_is_forbidden(): void
    {
        $officer = User::factory()->create(['is_active' => true]);

        $this->actingAs($officer)
            ->patch(route('members.profile.update'), $this->validPayload())
            ->assertForbidden();
    }

    public function test_gcash_number_must_be_a_valid_mobile_number(): void
    {
        $this->actingAs($this->user)
            ->from(route('members.profile'))
            ->patch(route('members.profile.update'), $this->validPayload(['gcash_number' => '12345']))
            ->assertRedirect(route('members.profile'))
            ->assertSessionHasErrors('gcash_number');

        $this->assertNull($this->member->refresh()->gcash_number);
    }

    public function test_gcash_number_requires_an_account_name(): void
    {
        $this->actingAs($this->user)
            ->patch(route('members.profile.update'), $this->validPayload(['gcash_account_name' => '']))
            ->assertSessionHasErrors('gcash_account_name');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->patch(route('members.profile.update'), $this->validPayload())
            ->assertRedirect(route('login'));
    }
}
