<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()->hasAnyRole([
            'President',
            'Treasurer',
        ]);
    }

    public function rules(): array
    {
        return [

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'unique:users,email',
            ],

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