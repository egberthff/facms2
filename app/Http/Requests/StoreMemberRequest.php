<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Enums\MembershipStatus;
use App\Http\Requests\Concerns\MemberProfileRules;
use Illuminate\Validation\Rule;

class StoreMemberRequest extends FormRequest
{
    use MemberProfileRules;

    public function authorize(): bool
    {
        return Auth::user()->hasAnyRole([
            'President',
            'Federated President',
            'Treasurer',
            'Federated Treasurer'
        ]) || Auth::user()->can('manage user');
    }

    public function rules(): array
    {
        return array_merge($this->memberProfileRules(), [

            'membership_status' => [
                'required',
                Rule::enum(MembershipStatus::class)
            ],

            'active_member_on' => [
                'nullable',
                'date'
            ],

            'inactive_member_on' => [
                'nullable',
                'date'
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'campus_id' => [
                'nullable',
                'string',
                'max:36',
            ]
        ]);
    }
}
