<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
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
