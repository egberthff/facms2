<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreClaimTypeRequest extends FormRequest
{
    /**
     * Claim types are shared across campuses, so only full-access users may manage them.
     */
    public function authorize(): bool
    {
        $user = Auth::user();

        return $user->can('view all campus data') || $user->hasAnyRole(['President', 'Treasurer']);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? Str::title(Str::squish($this->name)) : $this->name,
            // The amount field displays thousands separators; strip them before numeric validation.
            'required_amount' => is_string($this->required_amount) ? str_replace(',', '', trim($this->required_amount)) : $this->required_amount,
            'required_document' => is_string($this->required_document) ? trim($this->required_document) : $this->required_document,
            'description' => is_string($this->description) && trim($this->description) !== '' ? trim($this->description) : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('claim_types', 'name')],
            'required_document' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'required_amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'is_active' => ['required', 'boolean'],
            'campus_filter' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A claim type with this name already exists.',
            'required_amount.gt' => 'The required amount must be greater than zero.',
            'required_amount.decimal' => 'The required amount may have at most 2 decimal places.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'claim type name',
            'required_document' => 'required document',
            'required_amount' => 'required amount',
            'is_active' => 'active status',
        ];
    }
}
