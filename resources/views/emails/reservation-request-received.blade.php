<div style="background-color:#f5f7fb;padding:24px;font-family:Arial,sans-serif;color:#0f172a;">
  <div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;">
    <div style="background:linear-gradient(135deg,#0891b2,#0ea5e9);padding:20px 24px;">
      <h2 style="margin:0;color:#ffffff;font-size:22px;">Reservation Request Received</h2>
      <p style="margin:8px 0 0;color:#e0f2fe;font-size:13px;">We have received your request and will review it soon.</p>
    </div>

    <div style="padding:24px;">
      <p style="margin:0 0 14px;font-size:15px;">Hi <strong>{{ $name }}</strong>,</p>
      <p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#334155;">
        Thanks for your reservation request. Our admin team has received it and will get back to you after review.
      </p>

      <div style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
          <tbody>
            <tr>
              <td style="padding:10px 12px;background:#f8fafc;font-size:13px;color:#475569;width:42%;">Facility</td>
              <td style="padding:10px 12px;font-size:13px;">{{ $details['facility'] ?? '-' }}</td>
            </tr>
            <tr>
              <td style="padding:10px 12px;background:#f8fafc;font-size:13px;color:#475569;">Start Date Time</td>
              <td style="padding:10px 12px;font-size:13px;">{{ $details['start_at'] ?? '-' }}</td>
            </tr>
            <tr>
              <td style="padding:10px 12px;background:#f8fafc;font-size:13px;color:#475569;">End Date Time</td>
              <td style="padding:10px 12px;font-size:13px;">{{ $details['end_at'] ?? '-' }}</td>
            </tr>
            <tr>
              <td style="padding:10px 12px;background:#f8fafc;font-size:13px;color:#475569;">Plan</td>
              <td style="padding:10px 12px;font-size:13px;text-transform:capitalize;">{{ str_replace('_', ' ', $details['plan'] ?? '-') }}</td>
            </tr>
            <tr>
              <td style="padding:10px 12px;background:#f8fafc;font-size:13px;color:#475569;">Actual Duration (Hours)</td>
              <td style="padding:10px 12px;font-size:13px;">{{ $details['duration_hours'] ?? '0.00' }}</td>
            </tr>
            <tr>
              <td style="padding:10px 12px;background:#f8fafc;font-size:13px;color:#475569;">Billable Hours</td>
              <td style="padding:10px 12px;font-size:13px;">{{ $details['billable_units'] ?? '0.00' }}</td>
            </tr>
            <tr>
              <td style="padding:10px 12px;background:#f8fafc;font-size:13px;color:#475569;">Unit Price</td>
              <td style="padding:10px 12px;font-size:13px;">LKR {{ $details['unit_price'] ?? '0.00' }}</td>
            </tr>
            <tr>
              <td style="padding:10px 12px;background:#f8fafc;font-size:13px;color:#475569;">Deposit Amount</td>
              <td style="padding:10px 12px;font-size:13px;">LKR {{ $details['deposit_amount'] ?? '0.00' }}</td>
            </tr>
            <tr>
              <td style="padding:10px 12px;background:#f8fafc;font-size:13px;color:#475569;">Reservation Amount</td>
              <td style="padding:10px 12px;font-size:13px;">LKR {{ $details['reservation_amount'] ?? '0.00' }}</td>
            </tr>
            <tr>
              <td style="padding:10px 12px;background:#f8fafc;font-size:13px;color:#475569;">Current Status</td>
              <td style="padding:10px 12px;font-size:13px;text-transform:capitalize;">{{ $details['status'] ?? 'draft' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <p style="margin:16px 0 0;font-size:12px;color:#64748b;">
        If you need to update your request details, please contact our support team.
      </p>
    </div>
  </div>
</div>
