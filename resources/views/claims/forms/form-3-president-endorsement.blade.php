{{-- Form 3 – FA Campus President Endorsement --}}
<div class="form-page">
    <div class="form-label">Form 3 – FA Campus President Endorsement</div>

    <div class="header">
        Republic of the Philippines<br>
        Tagbilaran City, 6300, Bohol, Philippines<br>
        Office of the <span class="office">FEDERATED CVSCAFT FACULTY ASSOCIATION (FCFA)</span>
    </div>

    <div class="title">CAMPUS PRESIDENT’S ENDORSEMENT LETTER FOR MEDICAL EXPENSES CLAIM</div>
    <div class="rule">---------------------------------------------------------------------------------------------------</div>

    <div class="month-indicator">
        For the month of <span class="blank">{{ $monthLabel }}</span>
    </div>

    <div class="addressee">
        <strong>{{ $federatedPresident['name'] }}</strong><br>
        Federated CVSCAFT Faculty Association President
    </div>

    <div class="subject" style="margin-left:0.4in; font-size:12pt;">Subject: REQUEST FOR APPROVAL OF MEDICAL EXPENSES CLAIM</div>

    <p>Dear {{ $federatedPresident['honorific'] === 'Ms.' ? 'Madam' : 'Mr.' }} President,</p>

    <p class="indent justify" style="margin-bottom:4px;">
        This letter formally requests your approval for the medical expenses claimed by
    </p>
    <p style="margin-bottom:4px;">
        <span class="field" style="width:3.1in;">
            <span class="blank full">{{ $claimant['name'] }}</span>
            <span class="caption">(Name of Faculty Claimant)</span>
        </span>,
        of
        <span class="field" style="width:1.8in;">
            <span class="blank full">{{ strtoupper($campus['name']) }}</span>
            <span class="caption">(Campus)</span>
        </span>,
        a valued member of our Faculty
    </p>
    <p class="justify">
        Association. The Committee on Claims has meticulously reviewed all supporting documents submitted, including medical
        bills, receipts, and doctor's notes, and found them to be in order and compliant with the Association's guidelines.
    </p>

    <p class="indent" style="margin-bottom:4px;">
        The total amount claimed is
        <span class="field" style="width:2.8in;">
            <span class="blank full">{{ $amountWords }}</span>
            <span class="caption">(Total Amount in Words)</span>
        </span>
        (₱<span class="field" style="width:1.4in;"><span class="blank full">{{ number_format($amount, 2) }}</span><span class="caption">(Amount in Numbers)</span></span>).
    </p>
    <p class="indent justify">
        A detailed breakdown of the expenses is attached for your review. The campus Committee on Claims unanimously
        recommends the approval of this claim.
    </p>

    <p class="indent justify">
        We understand the importance of responsible financial management and assure you that all necessary checks and
        balances have been followed in this process. We believe that approving this claim aligns with the Association's commitment
        to supporting the well-being of its members.
    </p>

    <p class="indent">Thank you for your time and consideration. We look forward to your prompt response.</p>

    <div class="sig-right">
        <p>Respectfully endorsed,</p>
        <div style="margin-top:14px;">
            <div class="sig-name">{{ $campusPresident['name'] }}</div>
            <div>{{ $campus['short_name'] }} Campus CVSCAFT</div>
            <div>Faculty Association President</div>
            <div>Date: <span class="blank"></span></div>
        </div>
    </div>

    <div style="margin-top:0; width:48%;">
        <div>Received and noted by:</div>
        <div class="center" style="margin-top:20px;">
            <div class="sig-name">{{ $federatedTreasurer['name'] }}</div>
            <div>Signature Over Printed Name of</div>
            <div>Federated CVSCAFT Faculty Association Treasurer</div>
            <div>Date: <span class="blank"></span></div>
        </div>
    </div>

    <div style="margin: 0 0 0 48%; width:52%;">
        <div style="padding-left:0.3in;">Approved:</div>
        <div class="center" style="margin-top:20px;">
            <div class="sig-name">{{ $federatedPresident['name'] }}</div>
            <div>Signature Over Printed Name of</div>
            <div>Federated CVSCAFT Faculty Association President</div>
            <div>Date: <span class="blank"></span></div>
        </div>
    </div>

    <div class="note">
        Note: The Federated CVSCAFT Faculty Association Treasurer must retain this form together with the supporting documents
        of the Medical Expenses Claim for future reference.
    </div>
</div>
