<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * Same authorization, normalization, and messages as creating a claim type;
 * only the unique name rule ignores the claim type being edited.
 */
class UpdateClaimTypeRequest extends StoreClaimTypeRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'name' => ['required', 'string', 'max:100', Rule::unique('claim_types', 'name')->ignore($this->route('claimType'))],
        ]);
    }
}
