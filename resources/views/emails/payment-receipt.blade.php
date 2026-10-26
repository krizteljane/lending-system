<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment receipt</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f2;font-family:Arial,Helvetica,sans-serif;color:#183b2b;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">Your payment has been recorded. Keep this receipt for your records.</div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f1f5f2;padding:32px 12px;">
        <tr><td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;background:#ffffff;border:1px solid #dce7df;border-radius:12px;overflow:hidden;">
                <tr><td style="height:8px;background:#12613f;font-size:0;line-height:0;">&nbsp;</td></tr>
                <tr><td style="padding:28px 32px 12px;">
                    <p style="margin:0;color:#12613f;font-size:13px;font-weight:bold;letter-spacing:1.2px;">SALUAG LOAN</p>
                    <h1 style="margin:18px 0 8px;font-size:24px;line-height:1.3;color:#183b2b;">Payment received</h1>
                    <p style="margin:0;color:#52675b;font-size:15px;line-height:1.6;">Hello {{ $borrowerName }}, your payment has been recorded. Please keep this receipt for your records.</p>
                </td></tr>
                <tr><td style="padding:18px 32px 28px;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #dce7df;border-radius:8px;">
                        <tr><td style="padding:16px 18px;border-bottom:1px solid #e7eee9;color:#52675b;font-size:14px;">Amount received</td><td align="right" style="padding:16px 18px;border-bottom:1px solid #e7eee9;color:#12613f;font-size:20px;font-weight:bold;white-space:nowrap;">₱{{ $amount }}</td></tr>
                        <tr><td style="padding:14px 18px;border-bottom:1px solid #e7eee9;color:#52675b;font-size:14px;">Payment date</td><td align="right" style="padding:14px 18px;border-bottom:1px solid #e7eee9;color:#183b2b;font-size:14px;font-weight:bold;">{{ $paymentDate }}</td></tr>
                        <tr><td style="padding:14px 18px;color:#52675b;font-size:14px;">Loan reference</td><td align="right" style="padding:14px 18px;color:#183b2b;font-size:14px;font-weight:bold;">{{ $loanReference }}</td></tr>
                    </table>
                </td></tr>
                <tr><td style="padding:0 32px 28px;color:#52675b;font-size:13px;line-height:1.6;">If you have questions or believe a detail is incorrect, please contact the cooperative.</td></tr>
                <tr><td style="padding:18px 32px;background:#f7faf8;border-top:1px solid #e7eee9;color:#52675b;font-size:12px;line-height:1.6;">San Luis Agri-Ventures Agriculture Cooperative<br>Purok 1, Banoyo, San Luis, Batangas</td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
