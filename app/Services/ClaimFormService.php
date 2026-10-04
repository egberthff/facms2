<?php

namespace App\Services;

use App\Models\Campus;
use App\Models\Claim;
use App\Models\Member;
use App\Models\SystemSetting;
use App\Models\User;
use NumberFormatter;

class ClaimFormService
{
    /**
     * Build every value printed on the FCFA medical claim forms (Forms 1-4).
     * Signatories are resolved from current role holders / campus settings so
     * the forms always reflect the officers in office.
     */
    public function buildFormData(Claim $claim): array
    {
        $claim->loadMissing(['documents', 'claimType']);

        // Bypass CampusMemberScope: federated officers may belong to another campus.
        $member = Member::withoutGlobalScopes()->with('user')->find($claim->member_id);
        $campus = Campus::withoutGlobalScopes()->find($member?->campus_id);
        $campusId = $campus?->id;

        $amount = (float) ($claim->approved_amount ?? $claim->requested_amount);

        return [
            'claim' => $claim,
            'claimant' => [
                'name' => $this->personName($member, $member?->user),
            ],
            'campus' => [
                'name' => $campus ? 'BISU ' . $this->campusShortName($campus->campus_name) . ' Campus' : '',
                'short_name' => $this->campusShortName($campus?->campus_name),
                'location' => $campus?->location ? $campus->location . ', Bohol, Philippines' : '',
            ],
            'monthLabel' => $claim->submitted_at?->format('F Y') ?? '',
            'amount' => $amount,
            'amountWords' => $this->amountInWords($amount),
            'medicalReason' => $claim->description ?? '',
            'hospital' => $claim->hospital_name ?? '',
            'treatmentStart' => $claim->treatment_start_date?->format('F d, Y') ?? '',
            'treatmentEnd' => $claim->treatment_end_date?->format('F d, Y') ?? '',
            'attachedDocuments' => $claim->documents
                ->pluck('original_filename')
                ->map(fn ($name) => pathinfo($name, PATHINFO_FILENAME))
                ->take(4)
                ->pad(4, '')
                ->values()
                ->all(),
            'yearsOfMembership' => $member?->active_member_on
                ? (string) (int) $member->active_member_on->diffInYears(now())
                : '',
            'dateReceived' => $claim->disbursed_at?->format('F d, Y') ?? '',
            'campusPresident' => $this->officer('President', $campusId),
            'campusTreasurer' => $this->officer('Treasurer', $campusId),
            'federatedPresident' => $this->officer('Federated President'),
            'federatedTreasurer' => $this->officer('Federated Treasurer'),
            'committeeChair' => $this->assignedMember('claims_committee_chair', $campusId),
            'committeeMembers' => [
                $this->assignedMember('claims_committee_member_1', $campusId),
                $this->assignedMember('claims_committee_member_2', $campusId),
            ],
        ];
    }

    /**
     * Resolve the current holder of a role. Campus roles are matched against the claimant's campus.
     */
    public function officer(string $role, ?string $campusId = null): array
    {
        $user = User::role($role)
            ->when($campusId, fn ($query) => $query->whereHas(
                'member',
                fn ($q) => $q->withoutGlobalScopes()->where('campus_id', $campusId)
            ))
            ->first();

        $member = $user
            ? Member::withoutGlobalScopes()->where('user_id', $user->user_id)->first()
            : null;

        $name = $this->personName($member, $user);

        $lastName = $member?->last_name ?: str($name)->afterLast(' ')->toString();
        $honorific = $this->honorific($member?->gender);

        return [
            'name' => $name,
            'last_name' => $lastName,
            'honorific' => $honorific,
            // e.g. "Ms. Perenio"; empty when the role is unassigned so the form prints a blank line
            'salutation' => $lastName !== '' ? "{$honorific} {$lastName}" : '',
        ];
    }

    /**
     * Member-picker settings (Committee on Claims, report signatories) store a member id of the campus.
     */
    public function assignedMember(string $key, ?string $campusId): string
    {
        if (!$campusId) {
            return '';
        }

        $memberId = SystemSetting::query()
            ->where('setting_key', $key)
            ->where('campus_id', $campusId)
            ->whereNull('cycle_label')
            ->value('setting_value');

        if (!$memberId) {
            return '';
        }

        $member = Member::withoutGlobalScopes()->with('user')->find($memberId);

        return $member ? $this->personName($member, $member->user) : '';
    }

    private function honorific(?string $gender): string
    {
        return in_array(strtolower((string) $gender), ['female', 'f'], true) ? 'Ms.' : 'Mr.';
    }

    /**
     * Member full name (collapsing blanks left by a missing middle name/suffix), falling back to the user name.
     */
    private function personName(?Member $member, ?User $user): string
    {
        $name = $member ? str($member->full_name)->squish()->toString() : '';

        return $name !== '' ? $name : ($user?->name ?? '');
    }

    /**
     * "Bohol Island State University - Balilihan Campus" -> "Balilihan"; used where the forms read "Balilihan Campus".
     */
    public function campusShortName(?string $campusName): string
    {
        $name = str((string) $campusName)->afterLast(' - ');

        return $name->replaceMatches('/\b(BISU|Campus)\b/i', '')->squish()->toString();
    }

    /**
     * 23,500.50 -> "Twenty-Three Thousand Five Hundred Pesos and 50/100"
     */
    private function amountInWords(float $amount): string
    {
        $pesos = (int) floor($amount);
        $centavos = (int) round(($amount - $pesos) * 100);

        $words = ucwords((new NumberFormatter('en', NumberFormatter::SPELLOUT))->format($pesos), " -");
        $words .= $pesos === 1 ? ' Peso' : ' Pesos';

        return $centavos > 0
            ? sprintf('%s and %02d/100', $words, $centavos)
            : $words . ' Only';
    }
}
