<x-email-layout portalName="Treasury & Finance Portal" footerNotice="Administrative Control Notice: This automated broadcast tracks regional campus account ledger milestones and transaction history.">
    
    @php
        $statusLower = strtolower(trim($emailData['status']));
    @endphp

    <!-- 1. DYNAMIC HEADER BASED ON WORKFLOW STEP & REJECT PATH -->
    @if($statusLower === 'rejected')
        <h1 style="margin: 0 0 16px 0; color: #dc2626; font-size: 24px; font-weight: 700; line-height: 32px;">Transaction Voided / Cancelled</h1>
    @elseif($statusLower === 'approved')
        <h1 style="margin: 0 0 16px 0; color: #1e293b; font-size: 24px; font-weight: 700; line-height: 32px;">Collect Contributions & Prepare Report</h1>
    @else
        <h1 style="margin: 0 0 16px 0; color: #1e293b; font-size: 24px; font-weight: 700; line-height: 32px;">Authorization Received: Disburse Funds</h1>
    @endif
    
    <p style="margin: 0 0 20px 0; color: #475569; font-size: 15px; line-height: 24px;">Hello {{ $emailData['recipientName'] }},</p>

    <!-- 2. DYNAMIC ACTION MESSAGE BASED ON WORKFLOW STEP & REJECT PATH -->
    @if($statusLower === 'rejected')
        <p style="margin: 0 0 20px 0; color: #475569; font-size: 15px; line-height: 24px;">Please be advised that this benefit claim request has been marked as <strong>{{ strtoupper($emailData['status']) }}</strong> by the administrative review tier. Any pending allocation queues, ledgers, or collection spreadsheets associated with this claim reference should be frozen or voided.</p>
    @elseif($statusLower === 'approved')
        <p style="margin: 0 0 20px 0; color: #475569; font-size: 15px; line-height: 24px;">The Campus President has approved a new claim. The tracking status is now <strong>{{ $emailData['status'] }}</strong>. Please begin collecting financial contributions from campus members and prepare your Financial Report. <strong style="color: #ea580c;">Do not release funds at this stage.</strong></p>
    @else
        <p style="margin: 0 0 20px 0; color: #475569; font-size: 15px; line-height: 24px;">The Federated Treasurer has verified the report and issued the official <strong>"go signal"</strong> [Status: <strong>{{ $emailData['status'] }}</strong>]. You are now fully authorized to release and disburse the confirmed amount to the claimant.</p>
    @endif
    
    <!-- 3. Claim Details Info Box -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 24px;">
        <tr>
            <td style="padding: 20px;">
                <p style="margin: 0 0 12px 0; color: #1e293b; font-size: 13px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">Financial & Ledger Summary</p>
                <p style="margin: 0 0 8px 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Claim Number:</strong> {{ $emailData['claimNumber'] }}</p>
                <p style="margin: 0 0 8px 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Claim Type:</strong> {{ $emailData['claimType'] }}</p>
                
                @if($statusLower === 'rejected')
                    <p style="margin: 0 0 8px 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Voided Amount:</strong> <span style="font-family: monospace; font-weight: 600; color: #dc2626;">PHP {{ number_format($emailData['amount'], 2) }}</span></p>
                @else
                    <p style="margin: 0 0 8px 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Allocation Amount:</strong> <span style="font-family: monospace; font-weight: 600; color: #16a34a;">PHP {{ number_format($emailData['amount'], 2) }}</span></p>
                @endif
                
                <p style="margin: 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Last Updated:</strong> {{ $emailData['updatedAt'] }}</p>
            </td>
        </tr>
    </table>

    <!-- 4. Rejection / Evaluation Remarks Section -->
    @if(!empty($emailData['remarks']))
    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 4px solid {{ $statusLower === 'rejected' ? '#dc2626' : '#3b82f6' }}; margin-bottom: 24px; background-color: #fdfdfd;">
        <tr>
            <td style="padding: 4px 0 4px 16px;">
                <p style="margin: 0 0 4px 0; color: #475569; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                    {{ $statusLower === 'rejected' ? 'Rejection Reason & Notes:' : 'Evaluation Remarks:' }}
                </p>
                <p style="margin: 0; color: #334155; font-size: 14px; line-height: 22px; font-style: italic;">"{{ $emailData['remarks'] }}"</p>
            </td>
        </tr>
    </table>
    @endif

    <!-- 5. DYNAMIC PROCESS CONTROL SUMMARY SUB-TEXT -->
    @if($statusLower === 'rejected')
        <p style="margin: 0 0 32px 0; color: #64748b; font-size: 13px; line-height: 18px; font-style: italic;">Control Notice: No financial disbursement should be processed for this specific reference record. If contributions were partially organized, refer to campus policy regarding fund adjustments or rollbacks.</p>
    @elseif($statusLower === 'approved')
        <p style="margin: 0 0 32px 0; color: #64748b; font-size: 13px; line-height: 18px; font-style: italic;">Step 3 Control Rule: Funds must remain securely held. Your active action item is generating the surplus/deficit report before forwarding to the federated tier.</p>
    @else
        <p style="margin: 0 0 32px 0; color: #64748b; font-size: 13px; line-height: 18px; font-style: italic;">Step 7 Control Rule: This transaction can now be safely executed. Once paid out, update the ledger status in the management panel to trigger member payout closure.</p>
    @endif

    <!-- 6. DYNAMIC CALL TO ACTION BUTTON STYLE -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 36px;">
        <tr>
            <td align="left">
                @if($statusLower === 'rejected')
                    <a href="{{ $emailData['url'] }}" target="_blank" style="background-color: #64748b; color: #ffffff; display: inline-block; padding: 14px 28px; font-size: 15px; font-weight: 600; text-decoration: none; border-radius: 6px; border-bottom: 2px solid #475569;">View Audit Registry</a>
                @elseif($statusLower === 'approved')
                    <a href="{{ $emailData['url'] }}" target="_blank" style="background-color: #1e293b; color: #ffffff; display: inline-block; padding: 14px 28px; font-size: 15px; font-weight: 600; text-decoration: none; border-radius: 6px; border-bottom: 2px solid #0f172a;">Compile Financial Report</a>
                @else
                    <a href="{{ $emailData['url'] }}" target="_blank" style="background-color: #16a34a; color: #ffffff; display: inline-block; padding: 14px 28px; font-size: 15px; font-weight: 600; text-decoration: none; border-radius: 6px; border-bottom: 2px solid #14532d;">Process Disbursement Payout</a>
                @endif
            </td>
        </tr>
    </table>

    <p style="margin: 0; color: #1e293b; font-size: 15px; line-height: 24px;">Treasury Automation Subsystem,<br><strong style="color: #1e293b;">The {{ config('app.name') }} Core Engine</strong></p>
</x-email-layout>
