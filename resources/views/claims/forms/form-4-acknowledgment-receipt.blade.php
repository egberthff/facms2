{{-- Form 4 – FCFA Acknowledgment Receipt of Medical Assistance --}}
<div class="form-page">
    <div class="form-label">Form 4 – FCFA Acknowledgment Receipt of Medical Assistance</div>

    <div class="header">
        Republic of the Philippines<br>
        Tagbilaran City, 6300, Bohol, Philippines<br>
        Office of the <span class="office">FEDERATED CVSCAFT FACULTY ASSOCIATION (FCFA)</span>
    </div>

    <div class="title" style="margin-top:28px;">ACKNOWLEDGMENT RECEIPT OF MEDICAL EXPENSES CLAIM</div>
    <div class="rule">---------------------------------------------------------------------------------------------------</div>

    <div class="month-indicator" style="margin-top:20px;">
        For the month of <span class="blank">{{ $monthLabel }}</span>
    </div>

    <div class="addressee">
        <strong>{{ $federatedTreasurer['name'] }}</strong><br>
        Federated CVSCAFT Faculty Association Treasurer
    </div>

    <p>Dear @if($federatedTreasurer['salutation']){{ $federatedTreasurer['salutation'] }}@else<span class="blank"></span>@endif</p>

    <p class="indent">Greetings!</p>

    <p class="indent justify" style="margin-bottom:4px;">
        I,
        <span class="field" style="width:2.2in;">
            <span class="blank full">{{ $claimant['name'] }}</span>
            <span class="caption">(Name of Claimant)</span>
        </span>,
        <span class="field" style="width:2.2in;">
            <span class="blank full">{{ $campus['name'] }}</span>
            <span class="caption">(Campus)</span>
        </span>,
        acknowledge the receipt of the
    </p>
    <p class="justify" style="margin-bottom:4px;">
        financial assistance from the Federated CVSCAFT Faculty Association (FCFA) in the amount of
    </p>
    <p>
        <span class="field" style="width:4.2in;">
            <span class="blank full">{{ $amountWords }} (₱{{ number_format($amount, 2) }})</span>
            <span class="caption">(Amount Received)</span>
        </span>,
        received on
        <span class="field" style="width:1.8in;">
            <span class="blank full">{{ $dateReceived }}</span>
            <span class="caption">(Date Received)</span>
        </span>.
    </p>

    <p class="indent justify">
        This support will greatly assist me in my <strong>Medical Expenses</strong>. I appreciate the association's immediate assistance
        and commitment to supporting the faculty members.
    </p>

    <p class="indent">Thank you once again for this assistance.</p>

    <div class="sig-right" style="margin-top:30px;">
        <div>Received by:</div>
        <div class="center" style="margin-top:30px; border-bottom:1px solid #000; font-weight:bold; text-transform:uppercase;">
            {{ $claimant['name'] }}
        </div>
        <div class="center">Signature Over Printed Name of Claimant</div>
        <div class="center">Date: <span class="blank"></span></div>
    </div>

    <div style="margin-top:20px; width:55%;">
        <div>Released by:</div>
        <div class="center" style="margin-top:30px;">
            <div class="sig-name underline">{{ $campusTreasurer['name'] }}</div>
            <div>Signature Over Printed Name of Campus FA Treasurer</div>
            <div>Date: <span class="blank"></span></div>
        </div>
    </div>

    <div class="note" style="margin-top:0.9in;">
        Note: The Federated Faculty Association Treasurer must retain this form. After complying with the required documents,
        the claimant must personally sign this acknowledgment receipt of the Medical Assistance.
    </div>
</div>
