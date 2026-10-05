<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Concerns\MemberProfileRules;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Public self-service signup. Collects only the member-owned profile fields;
 * officer-controlled fields (membership status and its dates) are never
 * accepted here and are set server-side.
 */
class RegisterRequest extends FormRequest
{
    use MemberProfileRules;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower($this->input('email'))]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = $this->memberProfileRules();

        $rules['birth_date'][] = 'before:today';
        $rules['address'][] = 'max:500';

        return array_merge($rules, [
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:'.User::class.',email',
            ],

            'password' => [
                'required',
                'string',
                'confirmed',
                Password::defaults(),
            ],

            'campus_id' => [
                'required',
                'uuid',
                Rule::exists('campuses', 'id')->where('is_active', true),
            ],
        ]);
    }

    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'email.unique' => 'An account with this email address already exists.',
            'employee_number.unique' => 'This employee number is already registered.',
            'password.confirmed' => 'The password confirmation does not match.',
            'campus_id.required' => 'Please select your campus.',
            'campus_id.uuid' => 'Please select a valid campus.',
            'campus_id.exists' => 'The selected campus is not available.',
            'gender.in' => 'Please select a valid gender.',
            'employment_status.in' => 'Please select a valid employment status.',
            'birth_date.before' => 'The birth date must be a date in the past.',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'email address',
            'employee_number' => 'employee number',
            'first_name' => 'first name',
            'middle_name' => 'middle name',
            'last_name' => 'last name',
            'birth_date' => 'birth date',
            'employment_status' => 'employment status',
            'contact_number' => 'contact number',
            'gcash_number' => 'GCash number',
            'gcash_account_name' => 'GCash account name',
            'campus_id' => 'campus',
        ];
    }
}
