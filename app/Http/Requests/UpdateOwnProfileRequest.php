<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * A member editing their own profile. Only contact and payout details are allowed;
 * identity, employment and membership fields stay with the campus officers.
 */
class UpdateOwnProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->member()->exists() ?? false;
    }

    public function rules(): array
    {
        return [
            'gender' => [
                'required',
                'in:Male,Female',
            ],

            'birth_date' => [
                'nullable',
                'date',
                'before:today',
            ],

            'contact_number' => [
                'required',
                'string',
                'max:20',
            ],

            'address' => [
                'required',
                'string',
            ],

            'gcash_number' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^09\d{9}$/',
            ],

            'gcash_account_name' => [
                'nullable',
                'required_with:gcash_number',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'gcash_number.regex' => 'Enter an 11-digit mobile number starting with 09.',
            'gcash_account_name.required_with' => 'Enter the name registered to this GCash number.',
        ];
    }
}
