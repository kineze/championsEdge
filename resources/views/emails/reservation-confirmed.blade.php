<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Confirmed</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc;padding:24px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
          <tr>
            <td style="padding:20px 24px;background:#0f172a;color:#e2e8f0;">
              <p style="margin:0;font-size:12px;letter-spacing:0.12em;text-transform:uppercase;">Champions Edge</p>
              <h1 style="margin:8px 0 0;font-size:22px;color:#ffffff;">Reservation Confirmed</h1>
            </td>
          </tr>

          <tr>
            <td style="padding:22px 24px;">
              <p style="margin:0 0 12px;font-size:15px;">Hi {{ $name }},</p>
              <p style="margin:0 0 16px;font-size:14px;line-height:1.7;color:#334155;">
                Your reservation has been approved by our admin team. Please complete your payment using the details below.
              </p>

              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
                <tr style="background:#f8fafc;">
                  <td style="padding:10px 12px;font-weight:700;font-size:13px;width:45%;">Reservation ID</td>
                  <td style="padding:10px 12px;font-size:13px;">#{{ $details['reservation_id'] ?? '-' }}</td>
                </tr>
                <tr>
                  <td style="padding:10px 12px;font-weight:700;font-size:13px;">Facility</td>
                  <td style="padding:10px 12px;font-size:13px;">{{ $details['facility'] ?? 'N/A' }}</td>
                </tr>
                <tr style="background:#f8fafc;">
                  <td style="padding:10px 12px;font-weight:700;font-size:13px;">Start</td>
                  <td style="padding:10px 12px;font-size:13px;">{{ $details['start_at'] ?? '-' }}</td>
                </tr>
                <tr>
                  <td style="padding:10px 12px;font-weight:700;font-size:13px;">End</td>
                  <td style="padding:10px 12px;font-size:13px;">{{ $details['end_at'] ?? '-' }}</td>
                </tr>
                <tr style="background:#f8fafc;">
                  <td style="padding:10px 12px;font-weight:700;font-size:13px;">Total Amount</td>
                  <td style="padding:10px 12px;font-size:13px;">LKR {{ $details['reservation_amount'] ?? '0.00' }}</td>
                </tr>
                <tr>
                  <td style="padding:10px 12px;font-weight:700;font-size:13px;">Paid Amount</td>
                  <td style="padding:10px 12px;font-size:13px;">LKR {{ $details['paid_amount'] ?? '0.00' }}</td>
                </tr>
                <tr style="background:#f8fafc;">
                  <td style="padding:10px 12px;font-weight:700;font-size:13px;">Remaining Balance</td>
                  <td style="padding:10px 12px;font-size:13px;">LKR {{ $details['remaining_balance'] ?? '0.00' }}</td>
                </tr>
                <tr>
                  <td style="padding:10px 12px;font-weight:700;font-size:13px;">Payment Status</td>
                  <td style="padding:10px 12px;font-size:13px;">{{ $details['payment_status'] ?? 'Pending' }}</td>
                </tr>
              </table>

              @if (!empty($details['bank_details']) && is_array($details['bank_details']))
                <h3 style="margin:20px 0 10px;font-size:15px;color:#0f172a;">Bank Transfer Details</h3>
                @foreach ($details['bank_details'] as $bank)
                  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:10px;">
                    <tr style="background:#f8fafc;">
                      <td style="padding:9px 12px;font-size:12px;font-weight:700;width:40%;">Bank</td>
                      <td style="padding:9px 12px;font-size:12px;">{{ $bank['bank'] ?? '-' }}</td>
                    </tr>
                    <tr>
                      <td style="padding:9px 12px;font-size:12px;font-weight:700;">Account Number</td>
                      <td style="padding:9px 12px;font-size:12px;">{{ $bank['account_number'] ?? '-' }}</td>
                    </tr>
                    <tr style="background:#f8fafc;">
                      <td style="padding:9px 12px;font-size:12px;font-weight:700;">Account Holder</td>
                      <td style="padding:9px 12px;font-size:12px;">{{ $bank['account_holder_name'] ?? '-' }}</td>
                    </tr>
                    <tr>
                      <td style="padding:9px 12px;font-size:12px;font-weight:700;">Branch</td>
                      <td style="padding:9px 12px;font-size:12px;">{{ $bank['branch'] ?? '-' }}</td>
                    </tr>
                  </table>
                @endforeach
              @endif

              <p style="margin:12px 0 0;font-size:13px;line-height:1.6;color:#475569;">
                Once payment is completed, please keep your payment reference and share it with our team if requested.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
