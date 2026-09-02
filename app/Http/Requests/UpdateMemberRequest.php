<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Match the exact same role-based security clearance as creating a member
        return Auth::user()->hasAnyRole([
            'President',
            'Federated President',
            'Treasurer',
            'Federated Treasurer'
        ]) || Auth::user()->can('view all campus data');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // 1. Fetch the member instance being passed through the dynamic URL route binding
        $member = $this->route('member'); 
        
        // 2. Safely grab the linked user ID if it exists, to isolate the email unique check
        $userId = $member ? $member->user_id : null;
        $memberId = $member ? $member->id : null;

        return [
            'employee_number' => [
                'required',
                'string',
                // IGNORE the current member's record when evaluating uniqueness
                Rule::unique('members', 'employee_number')->ignore($memberId),
            ],

            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'middle_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'suffix' => [
                'nullable',
                'string',
                'max:20',
            ],

            'gender' => [
                'required',
                'in:Male,Female',
            ],

            'birth_date' => [
                'nullable',
                'date',
            ],

            'department' => [
                'required',
                'string',
            ],

            'position' => [
                'required',
                'string',
            ],

            'employment_status' => [
                'required',
                'in:Permanent,Probationary,Contractual,Part-time',
            ],

            'membership_status' => [
                'required',
                'in:Active,Inactive,Suspended',
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

            'email' => [
                'nullable',
                'email',
                'max:255',
                // IGNORE the current user account record when evaluating uniqueness
                Rule::unique('users', 'email')->ignore($userId, 'user_id'),
            ],

            'gcash_number' => [
                'nullable',
                'string',
                'max:20',
            ],

            'gcash_account_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'campus_id' => [
                'nullable',
                'string',
                'max:36'
            ]
        ];
    }
}
