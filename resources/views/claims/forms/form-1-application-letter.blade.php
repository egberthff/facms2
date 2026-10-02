{{-- Form 1 – FCFA Application Letter of Faculty Claimant --}}
<div class="form-page">
    <div class="form-label">Form 1 – FCFA Application Letter of Faculty Claimant</div>

    <div class="header">
        Republic of the Philippines<br>
        Tagbilaran City, 6300, Bohol, Philippines<br>
        Office of the <span class="office">CVSCAFT FACULTY ASSOCIATION (CFA)</span>
    </div>

    <div class="title">CLAIMANT’S APPLICATION LETTER FOR MEDICAL EXPENSES CLAIM</div>
    <div class="rule">---------------------------------------------------------------------------------------------------</div>

    <div class="month-indicator">
        For the month of <span class="blank">{{ $monthLabel }}</span>
    </div>

    <div class="addressee">
        <strong>{{ $campusPresident['name'] }}</strong><br>
        CVSCAFT Faculty Association President<br>
        {{ $campus['short_name'] }} Campus
    </div>

    <div class="subject">Subject: Application for Medical Expenses Assistance</div>

    <p>Dear @if($campusPresident['salutation']){{ $campusPresident['salutation'] }}@else<span class="blank"></span>@endif,</p>

    <p class="indent justify">
        I am writing to request assistance with medical expenses incurred due to
    </p>
    <p style="margin-top:-8px;">
        <span class="blank full">{{ $medicalReason }}</span>
        <span class="caption" style="text-align:left;">[briefly describe the medical reason, e.g., a recent illness, accident, or ongoing condition].</span>
    </p>

    <p class="indent">
        The received medical treatment was at
        <span class="field" style="width:4.3in;">
            <span class="blank full">{{ $hospital }}</span>
            <span class="caption">[Name of Hospital/Clinic]</span>
        </span>
    </p>
    <p>
        from
        <span class="field">
            <span class="blank">{{ $treatmentStart }}</span>
            <span class="caption">[Start of Treatment Date]</span>
        </span>
        to
        <span class="field">
            <span class="blank lg">{{ $treatmentEnd }}</span>
            <span class="caption">[End of Treatment Date].</span>
        </span>
    </p>

    <p class="indent">Attached to this letter are copies of the following documents:</p>
    <table style="width:90%; margin: 0 0 14px 0.3in; border-collapse:collapse;">
        <tr>
            <td style="width:50%; padding:2px 0;">1. <span class="blank lg" style="min-width:2.6in;">{{ $attachedDocuments[0] }}</span></td>
            <td style="width:50%; padding:2px 0;">3. <span class="blank lg" style="min-width:2.6in;">{{ $attachedDocuments[2] }}</span></td>
        </tr>
        <tr>
            <td style="padding:2px 0;">2. <span class="blank lg" style="min-width:2.6in;">{{ $attachedDocuments[1] }}</span></td>
            <td style="padding:2px 0;">4. <span class="blank lg" style="min-width:2.6in;">{{ $attachedDocuments[3] }}</span></td>
        </tr>
    </table>

    <p>
        The total amount of these medical expenses is
        <span class="field" style="width:4.2in;">
            <span class="blank full">{{ $amountWords }}</span>
            <span class="caption">[Amount in words]</span>
        </span>
    </p>
    <p class="justify">
        (₱<span class="field"><span class="blank">{{ number_format($amount, 2) }}</span><span class="caption">[Amount in numbers].</span></span>).
        This significant expense has created a considerable financial gap for me and my family.
    </p>

    <p class="indent justify">
        I understand that the Faculty Association offers assistance programs to faculty facing financial difficulties
        due to medical expenses. I would be grateful if you would consider my application and review the supporting
        documentation. I am available to provide any further information you may require.
    </p>

    <p class="indent">Thank you for your time, consideration, and assistance.</p>

    <div class="sig-right">
        <p>Sincerely yours,</p>
        <div class="center" style="margin-top:26px; border-bottom:1px solid #000; font-weight:bold; text-transform:uppercase;">
            {{ $claimant['name'] }}
        </div>
        <div class="center">Signature Over Printed Name of Claimant</div>
    </div>

    <div class="sig-row">
        <div class="sig-col">
            <div class="italic">Received and noted for document evaluation:</div>
            <div style="margin-top:22px; padding-left:0.2in;">
                <div class="sig-name">{{ $committeeChair }}</div>
                <div>Chair, Committee on Claims</div>
            </div>
        </div>
        <div class="sig-col">
            <div class="italic">Attested:</div>
            <div style="margin-top:22px;">
                <div class="sig-name">{{ $campusPresident['name'] }}</div>
                <div>{{ $campus['short_name'] }} Campus FA President</div>
            </div>
        </div>
    </div>

    <div class="note">
        Note: The Committee on Claims will verify and validate supporting documents of the Medical Assistance Claims and must
        retain this form together with the supporting documents of the Medical Expenses Claim for future reference.
    </div>
</div>
