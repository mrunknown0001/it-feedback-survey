<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You – IT Technical Support Feedback</title>
    <style>
        :root {
            --bg-dark: #0a0e1a;
            --bg-card: #0f1629;
            --border:  #1e3a5f;
            --cyan:    #06b6d4;
            --text:    #e2e8f0;
            --text-dim:#94a3b8;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg-dark);
            color: var(--text);
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background-image:
                radial-gradient(ellipse 80% 50% at 50% -20%, rgba(6,182,212,0.12) 0%, transparent 70%);
        }
        .container {
            text-align: center;
            max-width: 520px;
        }
        .icon-wrap {
            width: 80px; height: 80px;
            background: rgba(6,182,212,.12);
            border: 1px solid rgba(6,182,212,.35);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.75rem;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(6,182,212,.25); }
            50%       { box-shadow: 0 0 0 16px rgba(6,182,212,0); }
        }
        .icon-wrap svg { width: 38px; height: 38px; color: var(--cyan); }
        h1 { font-size: 2rem; font-weight: 700; color: #fff; margin-bottom: .75rem; }
        h1 span { color: var(--cyan); }
        p { color: var(--text-dim); line-height: 1.65; margin-bottom: .5rem; }
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--cyan), transparent);
            margin: 1.75rem auto;
            max-width: 180px;
            opacity: .4;
        }
        .btn {
            display: inline-block;
            margin-top: 1.5rem;
            padding: .75rem 2rem;
            background: linear-gradient(135deg, #0891b2, #06b6d4);
            color: #fff;
            font-weight: 700;
            border-radius: 8px;
            text-decoration: none;
            font-size: .95rem;
            box-shadow: 0 0 20px rgba(6,182,212,.3);
            transition: opacity .2s;
        }
        .btn:hover { opacity: .85; }
        .footer { margin-top: 2.5rem; font-size: .75rem; color: #334155; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
            </svg>
        </div>

        <h1>Thank <span>You!</span></h1>
        <div class="divider"></div>
        <p>Your feedback has been successfully submitted.</p>
        <p>We appreciate you taking the time to evaluate our IT Technical Support services. Your insights help us continuously improve.</p>

        <a href="{{ route('feedback.form') }}" class="btn">Submit Another Response</a>

        <div class="footer">
            <p>IT Technical Support Services &nbsp;·&nbsp; Service Feedback System</p>
        </div>
    </div>
</body>
</html>
