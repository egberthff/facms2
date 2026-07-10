<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
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

                    // Resolve your service class directly from Laravel's Container
                    // Replace MemberClaimService with the actual name of your service class
                    $balanceService = app(\App\Services\DashboardService::class);

                    // Calculate the balance data using your new logic
                    $balanceData = $balanceService->calculateMemberBalanceData($user);
                    $actualBalance = $balanceData['actualBalance'];

                    // Enforce actualBalance limit constraint
                    if ($value > $actualBalance) {
                        $fail("The requested amount exceeds your remaining cycle allowance balance (₱" . number_format($actualBalance, 2) . ").");
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
