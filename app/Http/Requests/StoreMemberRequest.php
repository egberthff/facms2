<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Enums\MembershipStatus;
use Illuminate\Validation\Rule;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()->hasAnyRole([
            'President',
            'Federated President',
            'Treasurer',
            'Federated Treasurer'
        ]) || Auth::user()->can('manage user');
    }

    public function rules(): array
    {
        return [

            'employee_number' => [
                'required',
                'string',
                'unique:members,employee_number',
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
                Rule::enum(MembershipStatus::class)
            ],

            'active_member_on' => [
                'nullable',
                'date'
            ],

            'inactive_member_on' => [
                'nullable',
                'date'
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
                'required',
                'email',
                'max:255',
                'unique:users,email',
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
                'max:36',
            ]
        ];
    }
}