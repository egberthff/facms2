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
            // 'amount' => [
            //     'required',
            //     'numeric',
            //     'min:1',
            //     function ($attribute, $value, $fail) {
            //         $user = Auth::user();
            //         $member = $user->member;

            //         if (!$member) {
            //             return $fail('No active member profile linked to your user account.');
            //         }

            //         $latestClaim = $member->claims()->latest('submitted_at')->first();

            //         // Check if a claim exists and if its submitted_at year matches the current year
            //         if ($latestClaim && \Carbon\Carbon::parse($latestClaim->submitted_at)->isCurrentYear()) {
            //             $annualLimit = (float) $latestClaim->annual_limit;
            //         } else {
            //             $latestLimit = SystemSetting::where('setting_key', 'annual_claim_limit')->first();
            //             $annualLimit = $latestLimit ? (float) $latestLimit->setting_value : 0.0; // Added fallback value
            //         }

            //         $approvedYearTotal = $member->claims()->approvedThisYear()->sum('approved_amount');
            //         $availableBalance = $annualLimit - $approvedYearTotal;

            //         if ($value > $availableBalance) {
            //             $fail("The requested amount exceeds your remaining annual allowance allocation balance (₱" . number_format($availableBalance, 2) . ").");
            //         }
            //     },
            // ],
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
            'description' => [
                'required'
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
