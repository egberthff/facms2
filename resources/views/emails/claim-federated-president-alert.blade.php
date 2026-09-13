<x-email-layout portalName="Federated Executive Council" footerNotice="Confidentiality Note: This administrative broadcast is intended solely for the Federated President's review actions.">
    <h1 style="margin: 0 0 16px 0; color: #1e293b; font-size: 24px; font-weight: 700; line-height: 32px;">Federated Record Review</h1>
    
    <p style="margin: 0 0 20px 0; color: #475569; font-size: 15px; line-height: 24px;">Hello {{ $emailData['recipientName'] }},</p>

    <p style="margin: 0 0 20px 0; color: #475569; font-size: 15px; line-height: 24px;">The Campus Treasurer has completed contribution collections and submitted the comprehensive Financial Report. This claim package is now <strong>{{ $emailData['status'] }}</strong> and requires your executive evaluation and federated-level filing.</p>
    
    <!-- Info Box -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 24px;">
        <tr>
            <td style="padding: 20px;">
                <p style="margin: 0 0 12px 0; color: #1e293b; font-size: 13px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">Docket & Ledger Highlights</p>
                <p style="margin: 0 0 8px 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Claim Number:</strong> {{ $emailData['claimNumber'] }}</p>
                <p style="margin: 0 0 8px 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Claim Type:</strong> {{ $emailData['claimType'] }}</p>
                <p style="margin: 0 0 8px 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Assessed Amount:</strong> <span style="font-family: monospace; font-weight: 600; color: #0f172a;">PHP {{ number_format($emailData['amount'], 2) }}</span></p>
                <p style="margin: 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Submission Date:</strong> {{ $emailData['updatedAt'] }}</p>
            </td>
        </tr>
    </table>

    @if(!empty($emailData['remarks']))
    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 4px solid #3b82f6; margin-bottom: 24px; background-color: #fdfdfd;">
        <tr>
            <td style="padding: 4px 0 4px 16px;">
                <p style="margin: 0 0 4px 0; color: #475569; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Campus Treasury Comments:</p>
                <p style="margin: 0; color: #334155; font-size: 14px; line-height: 22px; font-style: italic;">"{{ $emailData['remarks'] }}"</p>
            </td>
        </tr>
    </table>
    @endif

    <p style="margin: 0 0 32px 0; color: #64748b; font-size: 13px; line-height: 18px; font-style: italic;">Action Required: Please evaluate the accompanying financial report, log the official record details, and forward the verified file to the Federated Treasurer for final authorization.</p>

    <!-- CTA Button -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 36px;">
        <tr>
            <td align="left">
                <a href="{{ $emailData['url'] }}" target="_blank" style="background-color: #1e293b; color: #ffffff; display: inline-block; padding: 14px 28px; font-size: 15px; font-weight: 600; text-decoration: none; border-radius: 6px; border-bottom: 2px solid #0f172a;">Evaluate & File Record</a>
            </td>
        </tr>
    </table>

    <p style="margin: 0; color: #1e293b; font-size: 15px; line-height: 24px;">Executive System Services,<br><strong style="color: #1e293b;">The {{ config('app.name') }} Core</strong></p>
</x-email-layout>
