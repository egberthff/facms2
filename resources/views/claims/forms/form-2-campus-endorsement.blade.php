{{-- Form 2 – CFA Endorsement Letter of Faculty Claimant from Campus FA President --}}
<div class="form-page">
    <div class="form-label">Form 2 – CFA Endorsement Letter of Faculty Claimant from Campus FA President</div>

    <div class="header">
        Republic of the Philippines<br>
        {{ $campus['location'] ?: 'Tagbilaran City, 6300, Bohol, Philippines' }}<br>
        Office of the <span class="office">CVSCAFT FACULTY ASSOCIATION (CFA)</span>
    </div>

    <div class="title">CAMPUS ENDORSEMENT LETTER FOR MEDICAL EXPENSES CLAIM</div>
    <div class="rule">---------------------------------------------------------------------------------------------------</div>

    <div class="month-indicator">
        For the month of <span class="blank">{{ $monthLabel }}</span>
    </div>

    <div class="addressee">
        <strong>{{ $campusPresident['name'] }}</strong><br>
        CVSCAFT Faculty Association President<br>
        {{ $campus['short_name'] }} Campus
    </div>

    <div class="subject">Subject: Letter of Endorsement – Medical Expenses Assistance</div>

    <p>Dear @if($campusPresident['salutation']){{ $campusPresident['salutation'] }}@else<span class="blank"></span>@endif,</p>

    <p class="indent justify" style="margin-bottom:4px;">
        This letter serves as a formal endorsement for the medical expenses assistance claim submitted by
    </p>
    <p style="margin-bottom:4px;">
        <span class="field" style="width:4.4in;">
            <span class="blank full">{{ $claimant['name'] }}</span>
            <span class="caption">[Faculty Member's Name]</span>
        </span>
        of
        <span class="field" style="width:2.6in;">
            <span class="blank full">{{ $campus['name'] }}</span>
            <span class="caption">(Campus)</span>
        </span>
    </p>
    <p>a valued member of our faculty association.</p>

    <p class="indent justify" style="margin-bottom:4px;">
        <span class="field" style="width:2.1in;">
            <span class="blank full">{{ $claimant['name'] }}</span>
            <span class="caption">[Faculty Member's Name]</span>
        </span>
        has been a dedicated member of our faculty for
        <span class="field">
            <span class="blank sm">{{ $yearsOfMembership }}</span>
            <span class="caption">[Number of years]</span>
        </span>
        years,
    </p>
    <p class="justify" style="margin-bottom:4px;">
        consistently demonstrating exceptional commitment. The claim for medical assistance stems from
    </p>
    <p>
        <span class="field" style="width:5.2in;">
            <span class="blank full">{{ $medicalReason }}</span>
            <span class="caption" style="text-align:left;">[briefly describe the medical reason)</span>
        </span>.
    </p>

    <p class="indent justify" style="margin-bottom:4px;">
        We have reviewed the supporting documentation provided, and found them to be complete and accurate.
    </p>
    <p>
        The total amount claimed is
        <span class="field" style="width:3.1in;">
            <span class="blank full">{{ $amountWords }}</span>
            <span class="caption">(Amount in words)</span>
        </span>
        (₱<span class="field" style="width:1.3in;"><span class="blank full">{{ number_format($amount, 2) }}</span><span class="caption">(Amount in numbers)</span></span>).
    </p>

    <p class="indent justify">
        The Faculty Association strongly supports this application for medical assistance. We believe that
        approving this claim aligns with the principles of supporting our faculty members' well-being and acknowledges
        the significant financial burden that unforeseen medical expenses can impose.
    </p>

    <p class="indent">Please do not hesitate to contact me if you require any further information.</p>

    <div class="sig-right">
        <p>Sincerely yours,</p>
        <div style="margin-top:14px;">
            <div class="sig-name">{{ $committeeChair }}</div>
            <div>Chair, Committee on Claim</div>
        </div>
        @foreach($committeeMembers as $committeeMember)
            <div style="margin-top:14px;">
                <div class="sig-name">{{ $committeeMember }}</div>
                <div>Member, Committee on Claims</div>
            </div>
        @endforeach
    </div>

    <div class="sig-row" style="margin-top:10px;">
        <div class="sig-col">
            <div class="italic">Received and noted for federated endorsemnt:</div>
            <div style="margin-top:18px;">
                <div class="sig-name">{{ $campusTreasurer['name'] }}</div>
                <div>Campus Faculty Association Treasurer</div>
            </div>
        </div>
        <div class="sig-col">
            <div class="italic">Attested:</div>
            <div style="margin-top:18px;">
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
