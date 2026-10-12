<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\PaymentMethod;
// use App\Services\DashboardService;

class StoreClaimRequest extends FormRequest
{
    // private DashboardService  $dashboardService;
    // public function __construct(DashboardService $dashboardService)
    // {
    //     $this->dashboardService = $dashboardService;
    // }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // return false;
        // return Auth::user()->hasRole('Member');
        // $this->authorize('create claims', Claim::class);
        return Auth::user()->can('create claims');

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
            // 'amount' => [
            //     'required',
            //     'numeric',
            //     'min:1'
            // ],
            'amount' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) {
                    $user = Auth::user();

                    // Ensure user is logged in
                    if (!$user) {
                        return $fail('You must be logged in to perform this action.');
                    }

                    if (!$user->member) {
                        return $fail('No active member profile linked to your user account.');
                    }

                    // Early feedback against the member's own claim cycle; ClaimService re-checks under a row lock
                    try {
                        app(\App\Services\MemberClaimCycleService::class)
                            ->assertEligible($user->member, (float) $value, now());
                    } catch (ValidationException $e) {
                        $fail(collect($e->errors())->flatten()->first());
                    }
                },
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
            'treatmentEndDate' => [
                'nullable',
                'date',
                'after_or_equal:incidentDate',
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
                'string',
                'exists:payment_methods,id',
            ],

            // A scannable payment QR stands in for typed account details
            'payoutQr' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:5120',
            ],
            'payoutQrPayload' => [
                'nullable',
                'required_with:payoutQr',
                'string',
                'max:1000',
                function ($attribute, $value, $fail) {
                    // QR Ph / EMVCo payloads always start with the format indicator
                    if (! str_starts_with($value, '000201')) {
                        return $fail('The uploaded QR is not a valid payment QR code.');
                    }

                    if ($this->payoutMethod() === 'GCash' && ! str_contains($value, 'GXCHPHM2')) {
                        $fail('The uploaded QR is not a GCash QR code.');
                    }
                },
            ],

            'bank' => [
                Rule::requiredIf(fn () => $this->payoutMethod() === 'Bank Transfer' && ! $this->hasFile('payoutQr')),
                'nullable',
                'string',
                'max:100',
            ],
            'accountName' => [
                Rule::requiredIf(fn () => $this->payoutMethod() === 'Bank Transfer' && ! $this->hasFile('payoutQr')),
                'nullable',
                'string',
                'max:255',
            ],
            'accountNumber' => [
                Rule::requiredIf(fn () => $this->payoutMethod() === 'Bank Transfer' && ! $this->hasFile('payoutQr')),
                'nullable',
                'regex:/^\d{8,20}$/',
            ],
            'branch' => [
                'nullable',
                'string',
                'max:255',
            ],

            'gcashName' => [
                Rule::requiredIf(fn () => $this->payoutMethod() === 'GCash' && ! $this->hasFile('payoutQr')),
                'nullable',
                'string',
                'max:255',
            ],
            'gcashNumber' => [
                Rule::requiredIf(fn () => $this->payoutMethod() === 'GCash' && ! $this->hasFile('payoutQr')),
                'nullable',
                'regex:/^09\d{9}$/',
            ],

        ];
    }

    public function messages(): array
    {
        return [
            'bank.required' => 'Please select a bank or upload your bank QR code.',
            'accountName.required' => 'Please enter the account name or upload your bank QR code.',
            'accountNumber.required' => 'Please enter the account number or upload your bank QR code.',
            'accountNumber.regex' => 'Account number must be 8 to 20 digits.',
            'gcashName.required' => 'Please enter the account name or upload your GCash QR code.',
            'gcashNumber.required' => 'Please enter your GCash number or upload your GCash QR code.',
            'gcashNumber.regex' => 'GCash number must be 11 digits (09XXXXXXXXX).',
        ];
    }

    /**
     * Name of the selected payment method, e.g. "GCash" or "Bank Transfer".
     */
    public function payoutMethod(): ?string
    {
        return once(fn () => PaymentMethod::find($this->input('paymentMethod'))?->name);
    }
}
