<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Payment Update</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc;padding:24px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="620" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
          <tr>
            <td style="padding:18px 22px;background:#0f172a;color:#e2e8f0;">
              <p style="margin:0;font-size:12px;letter-spacing:0.12em;text-transform:uppercase;">Champions Edge</p>
              <h1 style="margin:8px 0 0;font-size:20px;color:#ffffff;">Reservation Payment Update</h1>
            </td>
          </tr>
          <tr>
            <td style="padding:20px 22px;">
              <p style="margin:0 0 10px;font-size:14px;">Hi {{ $name }},</p>
              <p style="margin:0 0 16px;font-size:13px;line-height:1.7;color:#334155;">
                A payment has been recorded for your reservation. Here is your latest balance and payment status.
              </p>

              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;border:1px solid #e2e8f0;">
                <tr style="background:#f8fafc;">
                  <td style="padding:10px 12px;font-size:12px;font-weight:700;width:45%;">Reservation ID</td>
                  <td style="padding:10px 12px;font-size:12px;">#{{ $details['reservation_id'] ?? '-' }}</td>
                </tr>
                <tr>
                  <td style="padding:10px 12px;font-size:12px;font-weight:700;">Facility</td>
                  <td style="padding:10px 12px;font-size:12px;">{{ $details['facility'] ?? 'N/A' }}</td>
                </tr>
                <tr style="background:#f8fafc;">
                  <td style="padding:10px 12px;font-size:12px;font-weight:700;">Last Payment</td>
                  <td style="padding:10px 12px;font-size:12px;">LKR {{ $details['last_payment_amount'] ?? '0.00' }} ({{ $details['last_payment_method'] ?? '-' }})</td>
                </tr>
                <tr>
                  <td style="padding:10px 12px;font-size:12px;font-weight:700;">Payment Date</td>
                  <td style="padding:10px 12px;font-size:12px;">{{ $details['last_payment_date'] ?? '-' }}</td>
                </tr>
                <tr style="background:#f8fafc;">
                  <td style="padding:10px 12px;font-size:12px;font-weight:700;">Total Amount</td>
                  <td style="padding:10px 12px;font-size:12px;">LKR {{ $details['reservation_amount'] ?? '0.00' }}</td>
                </tr>
                <tr>
                  <td style="padding:10px 12px;font-size:12px;font-weight:700;">Paid Amount</td>
                  <td style="padding:10px 12px;font-size:12px;">LKR {{ $details['paid_amount'] ?? '0.00' }}</td>
                </tr>
                <tr style="background:#f8fafc;">
                  <td style="padding:10px 12px;font-size:12px;font-weight:700;">Remaining Balance</td>
                  <td style="padding:10px 12px;font-size:12px;">LKR {{ $details['remaining_balance'] ?? '0.00' }}</td>
                </tr>
                <tr>
                  <td style="padding:10px 12px;font-size:12px;font-weight:700;">Payment Status</td>
                  <td style="padding:10px 12px;font-size:12px;">{{ $details['payment_status'] ?? 'Pending' }}</td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
