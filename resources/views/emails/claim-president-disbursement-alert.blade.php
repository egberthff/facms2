<x-email-layout portalName="Campus Executive Administration" footerNotice="Administrative Control Notice: This ledger confirmation statement is bound strictly to regional campus records.">
    <h1 style="margin: 0 0 16px 0; color: #1e293b; font-size: 24px; font-weight: 700; line-height: 32px;">Claim Lifecycle Concluded</h1>
    
    <p style="margin: 0 0 20px 0; color: #475569; font-size: 15px; line-height: 24px;">Hello {{ $emailData['recipientName'] }},</p>

    <p style="margin: 0 0 20px 0; color: #475569; font-size: 15px; line-height: 24px;">An official update has been recorded for a reimbursement claim initiated within your campus. The workflow tracking status has officially shifted to <strong>{{ $emailData['status'] }}</strong>.</p>
    
    <!-- Info Box -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 24px;">
        <tr>
            <td style="padding: 20px;">
                <p style="margin: 0 0 12px 0; color: #1e293b; font-size: 13px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">Campus Audit Snapshot</p>
                <p style="margin: 0 0 8px 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Claim Number:</strong> {{ $emailData['claimNumber'] }}</p>
                <p style="margin: 0 0 8px 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Claim Type:</strong> {{ $emailData['claimType'] }}</p>
                <p style="margin: 0 0 8px 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Final Disbursed Amount:</strong> <span style="font-family: monospace; font-weight: 600; color: #0f172a;">PHP {{ number_format($emailData['amount'], 2) }}</span></p>
                <p style="margin: 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Disbursement Date:</strong> {{ $emailData['updatedAt'] }}</p>
            </td>
        </tr>
    </table>

    @if(!empty($emailData['remarks']))
    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 4px solid #94a3b8; margin-bottom: 24px; background-color: #fdfdfd;">
        <tr>
            <td style="padding: 4px 0 4px 16px;">
                <p style="margin: 0 0 4px 0; color: #475569; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Audit Trail Comments:</p>
                <p style="margin: 0; color: #334155; font-size: 14px; line-height: 22px; font-style: italic;">"{{ $emailData['remarks'] }}"</p>
            </td>
        </tr>
    </table>
    @endif

    <p style="margin: 0 0 32px 0; color: #64748b; font-size: 13px; line-height: 18px; font-style: italic;">Executive Note: This lifecycle closure follows structural approval checkpoints at both the local campus level and the final federated verification channel.</p>

    <!-- CTA Button -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 36px;">
        <tr>
            <td align="left">
                <a href="{{ $emailData['url'] }}" target="_blank" style="background-color: #1e293b; color: #ffffff; display: inline-block; padding: 14px 28px; font-size: 15px; font-weight: 600; text-decoration: none; border-radius: 6px; border-bottom: 2px solid #0f172a;">Open Administration Registry</a>
            </td>
        </tr>
    </table>

    <p style="margin: 0; color: #1e293b; font-size: 15px; line-height: 24px;">Local Governance Automated Services,<br><strong style="color: #1e293b;">The {{ config('app.name') }} System Engine</strong></p>
</x-email-layout>
