<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Enums\ClaimStatus;

class UpdateClaimRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // return false;
        return Auth::user()->hasAnyRole([
            'President',
            'Treasurer',
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $claim = $this->route('claim');
        
        return [
            'remarks' => [
                'nullable',
                'string'
            ],
            'payment_remarks' => [
                'nullable',
                'string'
            ],
            'amount' => [
                'nullable',
                'numeric',
                'min:0'
            ],
            'reference_number' => [
                'nullable',
                'string'
            ],
            'payment_method_id' => [
                'nullable',
                'string',
                'exists:payment_methods,id'
            ],
            'status' => [
                'required',
                'string',
                Rule::enum(ClaimStatus::class), // Validates against Pending, Approved, Rejected, Disbursed
            ],
        ];
    }
}
