<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login Alert</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f1f5f9; margin: 0; padding: 30px 0; }
        .wrapper { max-width: 580px; margin: 0 auto; }
        .card { background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #4f46e5, #6366f1); padding: 36px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; letter-spacing: -0.3px; }
        .header p { color: rgba(255,255,255,0.8); margin: 8px 0 0; font-size: 14px; }
        .icon { font-size: 40px; margin-bottom: 12px; }
        .body { padding: 36px 40px; }
        .alert-box { background: #fefce8; border: 1px solid #fde047; border-radius: 8px; padding: 16px 20px; margin-bottom: 28px; }
        .alert-box p { margin: 0; color: #713f12; font-size: 14px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table tr td { padding: 12px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .info-table tr:last-child td { border-bottom: none; }
        .info-table .label { color: #64748b; font-weight: 500; width: 140px; }
        .info-table .value { color: #0f172a; font-weight: 600; }
        .footer { text-align: center; padding: 24px 40px; background: #f8fafc; border-top: 1px solid #e2e8f0; }
        .footer p { margin: 0; color: #94a3b8; font-size: 12px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            <div class="icon">🔐</div>
            <h1>Admin Login Detected</h1>
            <p>Someone just signed in to your admin panel</p>
        </div>
        <div class="body">
            <div class="alert-box">
                <p>⚠️ If this wasn't you, change your password immediately from the admin <strong>Settings</strong> page.</p>
            </div>
            <table class="info-table">
                <tr>
                    <td class="label">Time</td>
                    <td class="value">{{ $loginTime }}</td>
                </tr>
                <tr>
                    <td class="label">IP Address</td>
                    <td class="value">{{ $ipAddress }}</td>
                </tr>
                <tr>
                    <td class="label">Browser / Device</td>
                    <td class="value">{{ $userAgent }}</td>
                </tr>
                <tr>
                    <td class="label">App</td>
                    <td class="value">{{ config('app.name') }} Admin Panel</td>
                </tr>
            </table>
        </div>
        <div class="footer">
            <p>This is an automated security notification from {{ config('app.name') }}.</p>
        </div>
    </div>
</div>
</body>
</html>
