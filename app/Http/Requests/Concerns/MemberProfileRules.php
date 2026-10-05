<?php

namespace App\Http\Requests\Concerns;

/**
 * Member profile validation rules shared by the officer "Add Member" form
 * and the public self-service signup form.
 */
trait MemberProfileRules
{
    protected function memberProfileRules(): array
    {
        return [
            'employee_number' => [
                'required',
                'string',
                'max:255',
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
                'max:255',
            ],

            'position' => [
                'required',
                'string',
                'max:255',
            ],

            'employment_status' => [
                'required',
                'in:Permanent,Probationary,Contractual,Part-time',
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
            ],

            'gcash_account_name' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}
