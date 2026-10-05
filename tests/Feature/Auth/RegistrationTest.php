<?php

namespace Tests\Feature\Auth;

use App\Models\Campus;
use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private Campus $campus;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('Member', 'web');

        $this->campus = Campus::create([
            'campus_name' => 'Balilihan Campus',
            'acronym' => 'BC',
            'is_active' => true,
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'email' => 'Jane.Doe@bisu.edu.ph',
            'password' => 'Secret-Pass-2026',
            'password_confirmation' => 'Secret-Pass-2026',
            'employee_number' => 'EMP-2026-0099',
            'first_name' => 'Jane',
            'middle_name' => 'Santos',
            'last_name' => 'Doe',
            'suffix' => null,
            'gender' => 'Female',
            'birth_date' => '1990-05-14',
            'address' => 'Magsija, Balilihan, Bohol',
            'contact_number' => '09123456789',
            'department' => 'College of Engineering',
            'position' => 'Instructor I',
            'employment_status' => 'Permanent',
            'campus_id' => $this->campus->id,
            'gcash_number' => null,
            'gcash_account_name' => null,
        ], $overrides);
    }

    public function test_registration_screen_lists_active_campuses_only(): void
    {
        Campus::create(['campus_name' => 'Closed Campus', 'is_active' => false]);

        $this->get('/register')
            ->assertOk()
            ->assertSee('Balilihan Campus')
            ->assertDontSee('Closed Campus');
    }

    public function test_login_page_links_to_signup(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee(route('register'))
            ->assertSee('Create an Account');
    }

    public function test_new_member_can_register_and_must_verify_email(): void
    {
        Notification::fake();

        $response = $this->post('/register', $this->validPayload());

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();

        $user = User::where('email', 'jane.doe@bisu.edu.ph')->firstOrFail();
        $this->assertNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('Member'));
        $this->assertSame('Jane Santos Doe', $user->name);

        $member = Member::withoutGlobalScopes()->where('user_id', $user->user_id)->firstOrFail();
        $this->assertSame($this->campus->id, $member->campus_id);
        $this->assertSame('Inactive', $member->membership_status);
        $this->assertSame('EMP-2026-0099', $member->employee_number);
        $this->assertSame('College of Engineering', $member->department);
        $this->assertNull($member->active_member_on);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_signup_cannot_set_officer_controlled_fields(): void
    {
        Notification::fake();

        $this->post('/register', $this->validPayload([
            'membership_status' => 'Active',
            'active_member_on' => '2020-01-01',
        ]))->assertRedirect(route('verification.notice'));

        $member = Member::withoutGlobalScopes()->where('employee_number', 'EMP-2026-0099')->firstOrFail();
        $this->assertSame('Inactive', $member->membership_status);
        $this->assertNull($member->active_member_on);
    }

    public static function requiredFields(): array
    {
        return collect([
            'email', 'password', 'employee_number', 'first_name', 'last_name', 'gender',
            'address', 'contact_number', 'department', 'position', 'employment_status', 'campus_id',
        ])->mapWithKeys(fn ($field) => [$field => [$field]])->all();
    }

    #[DataProvider('requiredFields')]
    public function test_each_required_field_must_be_provided(string $field): void
    {
        $response = $this->from('/register')->post('/register', $this->validPayload([$field => '']));

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors($field);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public static function invalidInputs(): array
    {
        return [
            'mismatched confirmation' => [['password_confirmation' => 'Something-Else-1'], 'password'],
            'invalid email' => [['email' => 'not-an-email'], 'email'],
            'unknown campus' => [['campus_id' => '00000000-0000-0000-0000-000000000000'], 'campus_id'],
            'non-uuid campus' => [['campus_id' => 'abc'], 'campus_id'],
            'invalid gender' => [['gender' => 'Other'], 'gender'],
            'invalid employment status' => [['employment_status' => 'Retired'], 'employment_status'],
            'future birth date' => [['birth_date' => '2999-01-01'], 'birth_date'],
        ];
    }

    #[DataProvider('invalidInputs')]
    public function test_invalid_input_is_rejected(array $overrides, string $errorField): void
    {
        $this->post('/register', $this->validPayload($overrides))
            ->assertSessionHasErrors($errorField);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_inactive_campus_is_rejected(): void
    {
        $closed = Campus::create(['campus_name' => 'Closed Campus', 'is_active' => false]);

        $this->post('/register', $this->validPayload(['campus_id' => $closed->id]))
            ->assertSessionHasErrors('campus_id');
    }

    public function test_duplicate_email_and_employee_number_are_rejected(): void
    {
        Notification::fake();
        $this->post('/register', $this->validPayload());
        auth()->logout();

        $this->post('/register', $this->validPayload(['email' => 'JANE.DOE@bisu.edu.ph']))
            ->assertSessionHasErrors(['email', 'employee_number']);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_unverified_user_cannot_access_authenticated_routes(): void
    {
        $user = User::factory()->unverified()->create(['is_active' => true]);

        $this->actingAs($user)->get('/dashboard')
            ->assertRedirect(route('verification.notice'));
    }
}
