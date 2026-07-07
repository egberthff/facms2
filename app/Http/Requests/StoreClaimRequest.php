<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreClaimRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // return false;
        return Auth::user()->hasRole('Member');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'claimType' => [
                'required',
                'string',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:1'
            ],
            'description' => [
                'required',
                'string',
            ],
            'incidentDate' => [
                'required',
                'date',
            ],
            'hospital' => [
                'nullable',
                'string',
                'max:255'
            ],
            'documents' => [
                'nullable',
                'array',
            ],
            'documents.*' => [
                'file',
                'mimes:pdf,jpeg,jpeg,png',
                'max:10240'
            ],
            'paymentMethod' => [
                'required',
                'string'
            ],
            'bank' => [
                'nullable', 
                'string'
            ],
            'accountName' => [
                'nullable',
                'string'
            ],
            'accountNumber' => [
                'nullable',
                'string'
            ],
            'branch' => [
                'nullable',
                'string'
            ],
    
            'gcashName' => [
                'nullable',
                'string'
            ],
            'gcashNumber' => [
                'nullable',
                'string'
            ],
    
        ];
    }
}
