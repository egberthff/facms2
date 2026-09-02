<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('APP_NAME') }} - New Claim Notification</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; width: 100% !important;">
    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #f1f5f9; padding: 40px 20px;">
        <tr>
            <td align="center">
                <!-- Main Email Container -->
                <table width="100%" class="content-table" border="0" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); overflow: hidden;">
                    
                    <!-- Top Brand Bar -->
                    <tr>
                        <td style="background-color: #1e293b; padding: 24px 40px; border-bottom: 4px solid #3b82f6;">
                            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <span style="color: #ffffff; font-size: 20px; font-weight: 700; letter-spacing: 0.5px;">{{ env('APP_NAME') }}</span>
                                        <div style="color: #94a3b8; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-top: 2px;">Admin Portal</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 40px 32px 40px;">
                            <h1 style="margin: 0 0 16px 0; color: #1e293b; font-size: 24px; font-weight: 700; line-height: 32px;">New Claim Submitted</h1>
                            
                            <p style="margin: 0 0 20px 0; color: #475569; font-size: 15px; line-height: 24px;">A member has successfully submitted a new benefit claim through the member portal. Please review the claim details below to begin processing.</p>
                            
                            <p style="margin: 0 0 24px 0; color: #475569; font-size: 15px; line-height: 24px;">Summary of the submitted claim:</p>
                            
                            <!-- Claim Details Info Box -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="margin: 0 0 12px 0; color: #1e293b; font-size: 13px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">Claim Reference Details</p>
                                        <p style="margin: 0 0 8px 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Member Name:</strong> {{ $memberName }}</p>
                                        <p style="margin: 0 0 8px 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Claim Type:</strong> {{ $claimType }}</p>
                                        <p style="margin: 0 0 8px 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Claim Number:</strong> {{ $claimNumber }}</p>
                                        <p style="margin: 0 0 8px 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Amount Requested:</strong> <span style="font-family: monospace; font-weight: 600; color: #0f172a;">{{ number_format($amount, 2) }}</span></p>
                                        <p style="margin: 0; color: #334155; font-size: 14px; line-height: 20px;"><strong>Submission Date:</strong> {{ $submissionDate }}</p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 32px 0; color: #64748b; font-size: 13px; line-height: 18px; font-style: italic;">Processing Note: Ensure all mandatory supporting receipts and documents are attached and readable within the management queue before approval.</p>

                            <!-- CTA Button matching Dashboard Style -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 36px;">
                                <tr>
                                    <td align="left">
                                        <a href="{{ $url }}" target="_blank" style="background-color: #1e293b; color: #ffffff; display: inline-block; padding: 14px 28px; font-size: 15px; font-weight: 600; text-decoration: none; border-radius: 6px; border-bottom: 2px solid #0f172a;">Review Claim Details</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0; color: #1e293b; font-size: 15px; line-height: 24px;">System Notification,<br><strong style="color: #1e293b;">The {{ env('APP_NAME') }} Automated Core</strong></p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 24px 40px; border-top: 1px solid #e2e8f0; text-align: center;">
                            <p style="margin: 0 0 6px 0; color: #94a3b8; font-size: 12px; line-height: 18px;">This is an automated backend message intended strictly for administrative processors.</p>
                            <p style="margin: 0; color: #94a3b8; font-size: 12px; line-height: 18px;">&copy; 2026 Admin Management Stack. All rights reserved.</p>
                        </td>
                    </tr>

                </table>
                <!-- End Email Container -->
            </td>
        </tr>
    </table>
</body>
</html>
