<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Payment Received</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f1f5f9; margin: 0; padding: 30px 0; }
        .wrapper { max-width: 580px; margin: 0 auto; }
        .card { background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #16a34a, #22c55e); padding: 36px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; }
        .header p { color: rgba(255,255,255,0.85); margin: 8px 0 0; font-size: 14px; }
        .icon { font-size: 40px; margin-bottom: 12px; }
        .amount-banner { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; text-align: center; padding: 24px; margin: 28px 0; }
        .amount-banner .label { font-size: 13px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 6px; }
        .amount-banner .amount { font-size: 36px; font-weight: 800; color: #15803d; margin: 0; letter-spacing: -1px; }
        .body { padding: 0 40px 36px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table tr td { padding: 11px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .info-table tr:last-child td { border-bottom: none; }
        .info-table .label { color: #64748b; font-weight: 500; width: 140px; }
        .info-table .value { color: #0f172a; font-weight: 600; }
        .badge { display: inline-block; background: #dcfce7; color: #166534; padding: 2px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .footer { text-align: center; padding: 24px 40px; background: #f8fafc; border-top: 1px solid #e2e8f0; }
        .footer p { margin: 0; color: #94a3b8; font-size: 12px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            <div class="icon">💰</div>
            <h1>New Payment Received!</h1>
            <p>A customer has completed a payment on your page</p>
        </div>
        <div class="body">
            <div class="amount-banner">
                <p class="label">Amount Received</p>
                <p class="amount">{{ $transaction->currency }} {{ number_format($transaction->amount, 0) }}</p>
            </div>
            <table class="info-table">
                <tr>
                    <td class="label">Page</td>
                    <td class="value">{{ $pageName }}</td>
                </tr>
                <tr>
                    <td class="label">Buyer Name</td>
                    <td class="value">{{ $transaction->buyer_name }}</td>
                </tr>
                <tr>
                    <td class="label">Buyer Phone</td>
                    <td class="value">{{ $transaction->buyer_phone }}</td>
                </tr>
                <tr>
                    <td class="label">Gateway</td>
                    <td class="value">{{ ucfirst($transaction->gateway) }}</td>
                </tr>
                <tr>
                    <td class="label">Reference</td>
                    <td class="value">{{ $transaction->reference ?? $transaction->order_id }}</td>
                </tr>
                <tr>
                    <td class="label">Status</td>
                    <td class="value"><span class="badge">✓ COMPLETED</span></td>
                </tr>
                <tr>
                    <td class="label">Time</td>
                    <td class="value">{{ $transaction->updated_at->format('d M Y, H:i') }} EAT</td>
                </tr>
            </table>
        </div>
        <div class="footer">
            <p>This is an automated payment notification from {{ config('app.name') }}.</p>
        </div>
    </div>
</div>
</body>
</html>
