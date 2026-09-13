<x-email-layout portalName="Member Portal" footerNotice="This is a system generation message sent automatically regarding your tracking metrics.">
    @php $statusLower = strtolower(trim($emailData['status'])); @endphp

    @if($statusLower === 'disbursed')
        <h1 style="margin: 0 0 16px 0; color: #16a34a; font-size: 24px; font-weight: 700; line-height: 32px;">Reimbursement Released!</h1>
    @elseif($statusLower === 'rejected')
        <h1 style="margin: 0 0 16px 0; color: #dc2626; font-size: 24px; font-weight: 700; line-height: 32px;">Claim Returned / Rejected</h1>
    @else
        <h1 style="margin: 0 0 16px 0; color: #1e293b; font-size: 24px; font-weight: 700; line-height: 32px;">Claim Status Update</h1>
    @endif
    
    <p style="margin: 0 0 20px 0; color: #475569; font-size: 15px; line-height: 24px;">Hello {{ $emailData['recipientName'] }},</p>

    @if($statusLower === 'disbursed')
        <p style="margin: 0 0 20px 0; color: #475569; font-size: 15px; line-height: 24px;">Great news! Your benefit reimbursement claim has been officially processed and funds have been released by your Campus Treasurer...</p>
    @else
        <p style="margin: 0 0 20px 0; color: #475569; font-size: 15px; line-height: 24px;">The processing status of your submitted benefit claim has been successfully updated by our administrative tracking team...</p>
    @endif
    
    <!-- Info Box -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 24px;">
        <tr>
            <td style="padding: 20px;">
                <p style="margin: 0 0 8px 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Claim Number:</strong> {{ $emailData['claimNumber'] }}</p>
                <p style="margin: 0 0 8px 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Amount Requested:</strong> <strong>PHP {{ number_format($emailData['amount'], 2) }}</strong></p>
            </td>
        </tr>
    </table>

    <!-- CTA Button -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 36px;">
        <tr>
            <td align="left">
                <a href="{{ $emailData['url'] }}" target="_blank" style="background-color: #1e293b; color: #ffffff; display: inline-block; padding: 14px 28px; font-size: 15px; font-weight: 600; text-decoration: none; border-radius: 6px; border-bottom: 2px solid #0f172a;">View Claim History</a>
            </td>
        </tr>
    </table>

    <p style="margin: 0; color: #1e293b; font-size: 15px; line-height: 24px;">Sincerely,<br><strong>The {{ config('app.name') }} Support Core</strong></p>
</x-email-layout>
