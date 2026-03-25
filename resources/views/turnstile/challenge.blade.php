<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Feedback Survey — Verification</title>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0a0f1e;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #e2e8f0;
        }

        .card {
            background: #111827;
            border: 1px solid #1e3a5f;
            border-radius: 16px;
            padding: 48px 40px;
            max-width: 420px;
            width: 90%;
            text-align: center;
            box-shadow: 0 0 40px rgba(0, 210, 255, 0.06);
        }

        .logo {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #00d2ff, #0077ff);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 26px;
        }

        h1 {
            font-size: 1.35rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: #f1f5f9;
        }

        p {
            font-size: 0.875rem;
            color: #94a3b8;
            margin-bottom: 28px;
            line-height: 1.6;
        }

        .widget-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 24px;
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.8rem;
            color: #fca5a5;
            margin-bottom: 20px;
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #00d2ff, #0077ff);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        button[type="submit"]:hover { opacity: 0.88; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">🛡️</div>
        <h1>Security Verification</h1>
        <p>Please complete the verification below to access the IT Support Feedback Survey.</p>

        @if ($errors->has('turnstile'))
            <div class="error">{{ $errors->first('turnstile') }}</div>
        @endif

        <form method="POST" action="{{ route('turnstile.verify') }}">
            @csrf
            <div class="widget-wrap">
                <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
            </div>
            <button type="submit">Continue to Survey</button>
        </form>
    </div>
</body>
</html>
