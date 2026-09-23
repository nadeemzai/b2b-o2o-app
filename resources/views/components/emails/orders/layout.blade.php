<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $subject ?? 'OZ Group Notification' }}</title>
<style>
  body { margin: 0; padding: 0; background: #f4f5f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1a1a2e; }
  .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
  .header { background: #1e3a5f; padding: 24px 32px; }
  .header h1 { margin: 0; color: #fff; font-size: 22px; font-weight: 700; letter-spacing: -0.3px; }
  .header p { margin: 4px 0 0; color: #a8c4e0; font-size: 13px; }
  .body { padding: 32px; }
  .body h2 { margin: 0 0 8px; font-size: 18px; color: #1e3a5f; }
  .body p { margin: 0 0 16px; font-size: 14px; line-height: 1.6; color: #444; }
  .stat-row { display: flex; gap: 16px; margin: 16px 0; }
  .stat { flex: 1; background: #f4f5f7; border-radius: 8px; padding: 14px 16px; }
  .stat-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #888; margin-bottom: 4px; }
  .stat-value { font-size: 20px; font-weight: 700; color: #1e3a5f; }
  table.items { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 13px; }
  table.items th { background: #f4f5f7; padding: 8px 10px; text-align: left; color: #666; font-weight: 600; font-size: 11px; text-transform: uppercase; }
  table.items td { padding: 9px 10px; border-bottom: 1px solid #eee; }
  table.items tr:last-child td { border-bottom: none; }
  .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: 0.3px; text-transform: uppercase; }
  .badge-pending { background: #fff3cd; color: #856404; }
  .badge-verified { background: #d1ecf1; color: #0c5460; }
  .badge-transferred { background: #cce5ff; color: #004085; }
  .badge-fulfilling { background: #d4edda; color: #155724; }
  .badge-delivered { background: #d4edda; color: #155724; }
  .footer { padding: 20px 32px; background: #f8f9fa; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #999; }
  .btn { display: inline-block; background: #1e3a5f; color: #fff !important; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; margin: 8px 0; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>OZ Group B2B</h1>
    <p>Wholesale Marketplace</p>
  </div>
  <div class="body">
    {{ $slot }}
  </div>
  <div class="footer">
    &copy; {{ date('Y') }} OZ Group. This email was sent automatically — please do not reply.
  </div>
</div>
</body>
</html>
