@php
    $questionChunks = $questions->isNotEmpty() ? $questions->chunk(3) : collect();
    $totalSteps     = 1 + $questionChunks->count();

    // Build step labels
    $stepLabels = ['Your Info'];
    foreach ($questionChunks as $ci => $chunk) {
        $stepLabels[] = 'Part ' . ($ci + 1);
    }

    // Determine which step to open when validation errors exist
    $initialStep = 0;
    if ($errors->any() && ! $errors->hasAny(['respondent_name', 'position', 'agent_ids', 'issue_type_id'])) {
        foreach ($questionChunks as $ci => $chunk) {
            foreach ($chunk as $q) {
                if ($errors->has("responses.{$q->id}")) {
                    $initialStep = $ci + 1;
                    break 2;
                }
            }
        }
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Technical Support – Service Feedback</title>
    <style>
        :root {
            --bg-dark:    #0a0e1a;
            --bg-card:    #0f1629;
            --bg-input:   #131d35;
            --border:     #1e3a5f;
            --border-glow:#0ea5e9;
            --cyan:       #06b6d4;
            --cyan-light: #67e8f9;
            --cyan-dark:  #0891b2;
            --text:       #e2e8f0;
            --text-muted: #64748b;
            --text-dim:   #94a3b8;
            --star-off:   #1e293b;
            --star-on:    #06b6d4;
            --success:    #10b981;
            --danger:     #ef4444;
            --radius:     10px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background-color: var(--bg-dark);
            color: var(--text);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            padding: 2rem 1rem 4rem;
            background-image:
                radial-gradient(ellipse 80% 50% at 50% -20%, rgba(6,182,212,0.10) 0%, transparent 70%),
                repeating-linear-gradient(0deg, transparent, transparent 39px, rgba(6,182,212,0.03) 39px, rgba(6,182,212,0.03) 40px),
                repeating-linear-gradient(90deg, transparent, transparent 39px, rgba(6,182,212,0.03) 39px, rgba(6,182,212,0.03) 40px);
        }

        /* ── Header ─────────────────────────────── */
        .header {
            text-align: center;
            max-width: 760px;
            margin: 0 auto 2.5rem;
        }
        .header-badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: rgba(6,182,212,.12);
            border: 1px solid rgba(6,182,212,.30);
            color: var(--cyan);
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
            padding: .35rem .9rem;
            border-radius: 999px;
            margin-bottom: 1.2rem;
        }
        .header-badge svg { width:14px; height:14px; }
        .header h1 {
            font-size: clamp(1.6rem, 4vw, 2.4rem);
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
        }
        .header h1 span { color: var(--cyan); }
        .header p {
            margin-top: .75rem;
            color: var(--text-dim);
            font-size: .95rem;
            max-width: 520px;
            margin-inline: auto;
        }
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--cyan), transparent);
            margin: 1.5rem auto;
            max-width: 200px;
            opacity: .5;
        }

        /* ── Card ─────────────────────────────────── */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 2rem;
            max-width: 760px;
            margin: 0 auto 1.5rem;
            position: relative;
        }
        .card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: var(--radius);
            background: linear-gradient(135deg, rgba(6,182,212,.04) 0%, transparent 60%);
            pointer-events: none;
        }
        .card-title {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--cyan);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .card-title svg { width:15px; height:15px; }
        .card-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
            margin-left: .5rem;
        }

        /* ── Form elements ───────────────────────── */
        .form-grid { display: grid; gap: 1.25rem; }
        .form-grid-2 { grid-template-columns: 1fr 1fr; }
        @media (max-width: 560px) { .form-grid-2 { grid-template-columns: 1fr; } }

        .field { display: flex; flex-direction: column; gap: .45rem; }
        label {
            font-size: .8rem;
            font-weight: 600;
            color: var(--text-dim);
            letter-spacing: .03em;
        }
        label .req { color: var(--cyan); margin-left: 2px; }

        input[type="text"],
        input[type="email"],
        select,
        textarea {
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 7px;
            color: var(--text);
            font-size: .9rem;
            padding: .65rem .9rem;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            font-family: inherit;
            width: 100%;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--border-glow);
            box-shadow: 0 0 0 3px rgba(6,182,212,.12);
        }
        select option { background: #0f1629; }
        textarea { resize: vertical; min-height: 90px; }

        .field-error {
            font-size: .78rem;
            color: var(--danger);
            display: flex;
            align-items: center;
            gap: .3rem;
        }

        /* ── Multi-select dropdown ─────────────────── */
        .ms-wrap { position: relative; }
        .ms-trigger {
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 7px;
            color: var(--text);
            font-size: .9rem;
            padding: .65rem .9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            user-select: none;
            transition: border-color .2s, box-shadow .2s;
            min-height: 42px;
        }
        .ms-trigger:focus,
        .ms-wrap.open .ms-trigger {
            border-color: var(--border-glow);
            box-shadow: 0 0 0 3px rgba(6,182,212,.12);
            outline: none;
        }
        .ms-trigger-text { flex: 1; flex-wrap: wrap; display: flex; gap: .35rem; align-items: center; }
        .ms-placeholder { color: var(--text-muted); }
        .ms-tag {
            background: rgba(6,182,212,.18);
            border: 1px solid rgba(6,182,212,.35);
            color: var(--cyan-light);
            font-size: .72rem;
            font-weight: 600;
            padding: .15rem .5rem;
            border-radius: 4px;
            white-space: nowrap;
        }
        .ms-arrow {
            flex-shrink: 0;
            width: 16px; height: 16px;
            color: var(--text-muted);
            transition: transform .2s;
        }
        .ms-wrap.open .ms-arrow { transform: rotate(180deg); }

        .ms-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0; right: 0;
            background: #0d1526;
            border: 1px solid var(--border-glow);
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0,0,0,.5);
            z-index: 200;
            overflow: hidden;
        }
        .ms-wrap.open .ms-dropdown { display: block; }

        .ms-search-wrap {
            padding: .6rem .75rem;
            border-bottom: 1px solid var(--border);
        }
        .ms-search {
            width: 100%;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text);
            font-size: .85rem;
            padding: .4rem .7rem;
            outline: none;
        }
        .ms-search:focus { border-color: var(--border-glow); }

        .ms-list { max-height: 220px; overflow-y: auto; }
        .ms-list::-webkit-scrollbar { width: 5px; }
        .ms-list::-webkit-scrollbar-track { background: transparent; }
        .ms-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

        .ms-option {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .6rem .85rem;
            cursor: pointer;
            font-size: .88rem;
            transition: background .15s;
        }
        .ms-option:hover { background: rgba(6,182,212,.07); }
        .ms-option.all-option {
            border-bottom: 1px solid var(--border);
            font-weight: 600;
            color: var(--cyan);
        }
        .ms-option input[type="checkbox"] { display: none; }
        .ms-checkbox {
            width: 16px; height: 16px;
            flex-shrink: 0;
            border: 1.5px solid var(--border);
            border-radius: 4px;
            background: var(--bg-input);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s, border-color .15s;
        }
        .ms-option input:checked ~ .ms-checkbox,
        .ms-option.all-checked .ms-checkbox {
            background: var(--cyan);
            border-color: var(--cyan);
        }
        .ms-checkbox svg { display: none; width: 10px; height: 10px; color: #fff; }
        .ms-option input:checked ~ .ms-checkbox svg,
        .ms-option.all-checked .ms-checkbox svg { display: block; }
        .ms-option-label { flex: 1; }
        .ms-option-sub { font-size: .72rem; color: var(--text-muted); }

        /* ── Question card ───────────────────────── */
        .question-item {
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.25rem 1.5rem;
            transition: border-color .2s;
        }
        .question-item:focus-within { border-color: rgba(6,182,212,.4); }
        .question-meta {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            margin-bottom: 1rem;
        }
        .q-number {
            flex-shrink: 0;
            width: 28px; height: 28px;
            background: rgba(6,182,212,.15);
            border: 1px solid rgba(6,182,212,.3);
            color: var(--cyan);
            border-radius: 6px;
            font-size: .75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .q-type-badge {
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            padding: .2rem .55rem;
            border-radius: 4px;
            flex-shrink: 0;
        }
        .q-type-badge.rating { background: rgba(6,182,212,.12); color: var(--cyan); }
        .q-type-badge.text   { background: rgba(99,102,241,.12); color: #a5b4fc; }
        .q-text {
            font-size: .9rem;
            color: var(--text);
            line-height: 1.5;
        }

        /* ── Star Rating ─────────────────────────── */
        /*
         * DOM order is reversed (5→1) and flex-direction: row-reverse
         * renders them visually as 1→5 left-to-right.
         * The ~ sibling selector then naturally covers "this star + all
         * lower-value stars to the left" for both hover and checked states.
         */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: .5rem;
            align-items: center;
        }
        .star-rating input[type="radio"] { display: none; }
        .star-rating label {
            cursor: pointer;
            font-size: 1.8rem;
            color: var(--star-off);
            transition: color .15s, transform .15s;
            line-height: 1;
            padding: 0;
            user-select: none;
        }
        /* Hover: reset all, then light up hovered star + all lower-value stars */
        .star-rating:hover label { color: var(--star-off); }
        .star-rating label:hover,
        .star-rating label:hover ~ label { color: var(--star-on) !important; }
        /* Selected: light up checked star + all lower-value stars */
        .star-rating input:checked ~ label { color: var(--star-on); }

        .star-labels {
            display: flex;
            justify-content: space-between;
            font-size: .7rem;
            color: var(--text-muted);
            margin-top: .4rem;
            padding: 0 .1rem;
            max-width: 160px;
        }

        /* ── Alert ─────────────────────────────────── */
        .alert-error {
            background: rgba(239,68,68,.08);
            border: 1px solid rgba(239,68,68,.3);
            border-radius: 8px;
            padding: 1rem 1.25rem;
            color: #fca5a5;
            font-size: .85rem;
            max-width: 760px;
            margin: 0 auto 1.25rem;
        }
        .alert-error ul { padding-left: 1.25rem; margin-top: .4rem; }

        /* ── Footer ─────────────────────────────────── */
        .footer {
            text-align: center;
            color: var(--text-muted);
            font-size: .75rem;
            max-width: 760px;
            margin: 2rem auto 0;
        }
        .footer a { color: var(--cyan); text-decoration: none; }

        /* ══════════════════════════════════════════════
           MULTI-STEP
           ══════════════════════════════════════════════ */

        /* ── Steps ───────────────────────────────────── */
        .step { display: none; }
        .step.active { display: block; }

        /* ── Step Indicator ─────────────────────────── */
        .step-indicator {
            position: relative;
            max-width: 760px;
            margin: 0 auto 2.25rem;
            padding: 0 1rem;
        }
        /* Background track (full width, behind dots) */
        .si-track {
            position: absolute;
            top: 18px;
            left: calc(1rem + 18px);
            right: calc(1rem + 18px);
            height: 2px;
            background: var(--border);
            border-radius: 1px;
        }
        /* Filled progress (JS-controlled width) */
        .si-progress {
            height: 100%;
            background: linear-gradient(90deg, var(--cyan-dark), var(--cyan));
            border-radius: 1px;
            transition: width .45s ease;
            width: 0%;
        }
        .si-steps {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
        }
        .si-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .45rem;
        }
        .si-dot {
            width: 36px; height: 36px;
            border-radius: 50%;
            border: 2px solid var(--border);
            background: var(--bg-dark);
            color: var(--text-muted);
            font-size: .78rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            transition: border-color .3s, background .3s, color .3s, box-shadow .3s;
        }
        .si-step.active .si-dot {
            border-color: var(--cyan);
            background: rgba(6,182,212,.15);
            color: var(--cyan);
            box-shadow: 0 0 0 5px rgba(6,182,212,.12);
        }
        .si-step.done .si-dot {
            border-color: var(--cyan);
            background: var(--cyan);
            color: #fff;
        }
        .si-num { line-height: 1; }
        .si-check { display: none; width: 13px; height: 13px; flex-shrink: 0; }
        .si-step.done .si-num  { display: none; }
        .si-step.done .si-check { display: block; }
        .si-label {
            font-size: .62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--text-muted);
            text-align: center;
            white-space: nowrap;
            transition: color .3s;
        }
        .si-step.active .si-label { color: var(--cyan); }
        .si-step.done   .si-label { color: var(--cyan-dark); }
        @media (max-width: 500px) {
            .si-label { display: none; }
            .si-dot   { width: 30px; height: 30px; font-size: .72rem; }
            .si-track { top: 15px; left: calc(1rem + 15px); right: calc(1rem + 15px); }
        }

        /* ── Navigation buttons ─────────────────────── */
        .step-nav {
            display: flex;
            gap: .75rem;
            max-width: 760px;
            margin: .25rem auto 0;
            align-items: stretch;
        }
        .btn-prev {
            flex-shrink: 0;
            padding: .9rem 1.5rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: transparent;
            color: var(--text-dim);
            font-size: .95rem;
            font-weight: 600;
            cursor: pointer;
            transition: border-color .2s, color .2s, background .2s;
            font-family: inherit;
            white-space: nowrap;
        }
        .btn-prev:hover {
            border-color: var(--cyan);
            color: var(--cyan);
            background: rgba(6,182,212,.05);
        }
        .btn-next,
        .btn-submit {
            flex: 1;
            padding: .9rem;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, #0891b2, #06b6d4);
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: .05em;
            cursor: pointer;
            transition: opacity .2s, transform .1s, box-shadow .2s;
            box-shadow: 0 0 20px rgba(6,182,212,.25);
            font-family: inherit;
            display: block;
            width: 100%;
            margin: 0;
        }
        .btn-next:hover, .btn-submit:hover { opacity: .9; box-shadow: 0 0 30px rgba(6,182,212,.4); }
        .btn-next:active, .btn-submit:active { transform: scale(.99); }
        .hidden { display: none !important; }

        /* ── Validation error highlight ──────────────── */
        .input-error {
            border-color: var(--danger) !important;
            box-shadow: 0 0 0 3px rgba(239,68,68,.12) !important;
        }
        .ms-trigger.input-error {
            border-color: var(--danger) !important;
            box-shadow: 0 0 0 3px rgba(239,68,68,.12) !important;
        }
        .question-item.item-error {
            border-color: rgba(239,68,68,.5) !important;
        }
        .step-validation-msg {
            max-width: 760px;
            margin: 0 auto .75rem;
            background: rgba(239,68,68,.08);
            border: 1px solid rgba(239,68,68,.3);
            border-radius: 8px;
            padding: .7rem 1rem;
            color: #fca5a5;
            font-size: .82rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .step-validation-msg.hidden { display: none !important; }

        /* ── Single-select searchable dropdown ─────────── */
        .ss-wrap { position: relative; }
        .ss-trigger {
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 7px;
            color: var(--text);
            font-size: .9rem;
            padding: .65rem .9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            user-select: none;
            transition: border-color .2s, box-shadow .2s;
            min-height: 42px;
        }
        .ss-trigger:focus,
        .ss-wrap.open .ss-trigger {
            border-color: var(--border-glow);
            box-shadow: 0 0 0 3px rgba(6,182,212,.12);
            outline: none;
        }
        .ss-trigger-text { flex: 1; }
        .ss-placeholder { color: var(--text-muted); }
        .ss-arrow {
            flex-shrink: 0;
            width: 16px; height: 16px;
            color: var(--text-muted);
            transition: transform .2s;
        }
        .ss-wrap.open .ss-arrow { transform: rotate(180deg); }
        .ss-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0; right: 0;
            background: #0d1526;
            border: 1px solid var(--border-glow);
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0,0,0,.5);
            z-index: 200;
            overflow: hidden;
        }
        .ss-wrap.open .ss-dropdown { display: block; }
        .ss-search-wrap {
            padding: .6rem .75rem;
            border-bottom: 1px solid var(--border);
        }
        .ss-search {
            width: 100%;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text);
            font-size: .85rem;
            padding: .4rem .7rem;
            outline: none;
        }
        .ss-search:focus { border-color: var(--border-glow); }
        .ss-list { max-height: 220px; overflow-y: auto; }
        .ss-list::-webkit-scrollbar { width: 5px; }
        .ss-list::-webkit-scrollbar-track { background: transparent; }
        .ss-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
        .ss-option {
            padding: .6rem .85rem;
            cursor: pointer;
            font-size: .88rem;
            transition: background .15s;
        }
        .ss-option:hover { background: rgba(6,182,212,.07); }
        .ss-option.selected { background: rgba(6,182,212,.12); color: var(--cyan); }
        .ss-empty {
            padding: .75rem .85rem;
            color: var(--text-muted);
            font-size: .85rem;
            text-align: center;
        }
        .ss-trigger.input-error {
            border-color: var(--danger) !important;
            box-shadow: 0 0 0 3px rgba(239,68,68,.12) !important;
        }

        /* ── Turnaround notice ───────────────────────────── */
        .turnaround-notice {
            display: flex;
            align-items: center;
            gap: .5rem;
            background: rgba(6,182,212,.08);
            border: 1px solid rgba(6,182,212,.25);
            border-radius: 6px;
            padding: .55rem .85rem;
            font-size: .82rem;
            color: var(--text-dim);
            margin-top: .45rem;
        }
        .turnaround-notice svg { width: 14px; height: 14px; color: var(--cyan); flex-shrink: 0; }
        .turnaround-notice strong { color: var(--cyan); }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <div class="header-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>
            </svg>
            IT Technical Support Services
        </div>
        <h1>Service <span>Feedback</span> Form</h1>
        <div class="divider"></div>
        <p>Help us improve our IT support services by sharing your experience. Your feedback is valued and confidential.</p>
    </div>

    @if ($totalSteps > 1)
    <!-- Step Indicator -->
    <div class="step-indicator" id="step-indicator">
        <div class="si-track">
            <div class="si-progress" id="si-progress"></div>
        </div>
        <div class="si-steps">
            @foreach ($stepLabels as $si => $label)
            <div class="si-step {{ $si === $initialStep ? 'active' : ($si < $initialStep ? 'done' : '') }}" data-si="{{ $si }}">
                <div class="si-dot">
                    <span class="si-num">{{ $si + 1 }}</span>
                    <svg class="si-check" viewBox="0 0 14 12" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="1 6 5 10 13 1"/>
                    </svg>
                </div>
                <div class="si-label">{{ $label }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Inline step validation message (shown by JS) -->
    <div class="step-validation-msg hidden" id="step-validation-msg">
        ⚠ Please fill in all required fields before continuing.
    </div>

    <!-- Server-side validation errors -->
    @if ($errors->any())
        <div class="alert-error">
            <strong>Please correct the following errors:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('feedback.store') }}" novalidate>
        @csrf

        <!-- ── Step 0: Respondent info ─────────────── -->
        <div class="step {{ $initialStep === 0 ? 'active' : '' }}" data-step="0">
            <div class="card">
                <div class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                    </svg>
                    Respondent Information
                </div>

                <div class="form-grid form-grid-2">
                    <div class="field">
                        <label for="respondent_name">Full Name <span class="req">*</span></label>
                        <input type="text" id="respondent_name" name="respondent_name"
                               value="{{ old('respondent_name') }}"
                               placeholder="Enter your full name" autocomplete="name">
                        @error('respondent_name')
                            <span class="field-error">⚠ {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="position">Position / Designation <span class="req">*</span></label>
                        <input type="text" id="position" name="position"
                               value="{{ old('position') }}"
                               placeholder="e.g. Accounting Manager" autocomplete="organization-title">
                        @error('position')
                            <span class="field-error">⚠ {{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-grid" style="margin-top:1.25rem">
                    <div class="field">
                        <label>IT Support Agent(s) That Assisted You <span class="req">*</span></label>

                        @php $oldAgents = old('agent_ids', []); @endphp
                        <div id="agent-hidden-inputs">
                            @foreach ($oldAgents as $aid)
                                <input type="hidden" name="agent_ids[]" value="{{ $aid }}">
                            @endforeach
                        </div>

                        <div class="ms-wrap" id="agent-ms">
                            <div class="ms-trigger" tabindex="0" id="agent-trigger" aria-haspopup="listbox" aria-expanded="false">
                                <span class="ms-trigger-text" id="agent-tags">
                                    <span class="ms-placeholder" id="agent-placeholder">— Select agent(s) —</span>
                                </span>
                                <svg class="ms-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </div>

                            <div class="ms-dropdown" role="listbox" aria-multiselectable="true">
                                <div class="ms-search-wrap">
                                    <input type="text" class="ms-search" id="agent-search" placeholder="Search agents…" autocomplete="off">
                                </div>
                                <div class="ms-list" id="agent-list">
                                    <div class="ms-option all-option" data-all="true" role="option">
                                        <span class="ms-checkbox">
                                            <svg viewBox="0 0 12 10" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="1 5 4.5 8.5 11 1"/>
                                            </svg>
                                        </span>
                                        <span class="ms-option-label">Select All</span>
                                    </div>
                                    @foreach ($agents as $agent)
                                    <div class="ms-option"
                                         data-value="{{ $agent->id }}"
                                         data-label="{{ $agent->name }}"
                                         data-search="{{ strtolower($agent->name . ' ' . $agent->employee_id) }}"
                                         role="option">
                                        <input type="checkbox" value="{{ $agent->id }}"
                                               {{ in_array($agent->id, $oldAgents) ? 'checked' : '' }}>
                                        <span class="ms-checkbox">
                                            <svg viewBox="0 0 12 10" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="1 5 4.5 8.5 11 1"/>
                                            </svg>
                                        </span>
                                        <span class="ms-option-label">
                                            {{ $agent->name }}
                                            @if ($agent->employee_id)
                                                <span class="ms-option-sub">&nbsp;{{ $agent->employee_id }}</span>
                                            @endif
                                        </span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        @error('agent_ids')
                            <span class="field-error">⚠ {{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-grid" style="margin-top:1.25rem">
                    <div class="field">
                        <label>Issue / Request Type <span class="req">*</span></label>

                        <input type="hidden" name="issue_type_id" id="issue-type-hidden" value="{{ old('issue_type_id', '') }}">

                        <div class="ss-wrap" id="issue-type-ss">
                            <div class="ss-trigger" tabindex="0" id="issue-type-trigger" aria-haspopup="listbox" aria-expanded="false">
                                <span id="issue-type-display">
                                    <span class="ss-placeholder">— Select issue type —</span>
                                </span>
                                <svg class="ss-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </div>
                            <div class="ss-dropdown" role="listbox">
                                <div class="ss-search-wrap">
                                    <input type="text" class="ss-search" id="issue-type-search" placeholder="Search issue types…" autocomplete="off">
                                </div>
                                <div class="ss-list" id="issue-type-list">
                                    @forelse ($issueTypes as $it)
                                    <div class="ss-option {{ old('issue_type_id') == $it->id ? 'selected' : '' }}"
                                         data-value="{{ $it->id }}"
                                         data-label="{{ $it->name }}"
                                         data-turnaround="{{ $it->turnaround_time }}"
                                         data-search="{{ strtolower($it->name) }}"
                                         role="option">
                                        {{ $it->name }}
                                    </div>
                                    @empty
                                    <div class="ss-empty">No issue types have been configured yet.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="turnaround-notice hidden" id="turnaround-notice">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <span>Expected resolution time: <strong id="turnaround-time-text"></strong></span>
                        </div>

                        @error('issue_type_id')
                            <span class="field-error">⚠ {{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-grid" style="margin-top:1.25rem">
                    <div class="field">
                        <label for="issue_description">
                            Brief Issue Description
                            <span style="color:var(--text-muted);font-weight:400;font-size:.75rem">(optional)</span>
                        </label>
                        <textarea id="issue_description" name="issue_description"
                                  placeholder="Briefly describe the issue or request you needed help with…"
                                  rows="3">{{ old('issue_description') }}</textarea>
                        @error('issue_description')
                            <span class="field-error">⚠ {{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Question steps (3 per step) ──────────── -->
        @php $globalQ = 0; @endphp
        @foreach ($questionChunks as $chunk)
        @php $chunkStep = $loop->index + 1; @endphp
        <div class="step {{ $initialStep === $chunkStep ? 'active' : '' }}" data-step="{{ $chunkStep }}">
            <div class="card">
                <div class="card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3M12 17h.01"/>
                    </svg>
                    Survey Questions
                </div>

                <div class="form-grid" style="gap:1rem">
                    @foreach ($chunk as $question)
                    @php $globalQ++ @endphp
                    <div class="question-item">
                        <div class="question-meta">
                            <div class="q-number">{{ $globalQ }}</div>
                            <div>
                                <span class="q-type-badge {{ $question->type }}">
                                    {{ $question->type === 'rating' ? '★ Rating' : '✎ Comment' }}
                                </span>
                            </div>
                            <div class="q-text">{{ $question->question_text }}</div>
                        </div>

                        @if ($question->type === 'rating')
                            <div class="star-rating" id="stars-{{ $question->id }}">
                                @for ($star = 5; $star >= 1; $star--)
                                    <input type="radio"
                                           name="responses[{{ $question->id }}]"
                                           id="q{{ $question->id }}_s{{ $star }}"
                                           value="{{ $star }}"
                                           {{ old("responses.{$question->id}") == $star ? 'checked' : '' }}>
                                    <label for="q{{ $question->id }}_s{{ $star }}" title="{{ $star }} Star{{ $star > 1 ? 's' : '' }}">★</label>
                                @endfor
                            </div>
                            <div class="star-labels">
                                <span>1 – Poor</span>
                                <span>5 – Excellent</span>
                            </div>
                            @error("responses.{$question->id}")
                                <span class="field-error" style="margin-top:.5rem">⚠ {{ $message }}</span>
                            @enderror

                        @else
                            <textarea name="responses[{{ $question->id }}]"
                                      placeholder="Share your thoughts here…"
                                      rows="3">{{ old("responses.{$question->id}") }}</textarea>
                            @error("responses.{$question->id}")
                                <span class="field-error">⚠ {{ $message }}</span>
                            @enderror
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        @if ($questions->isEmpty())
        <!-- No questions configured – single step, show a note -->
        <div class="card" style="max-width:760px;margin:0 auto 1.5rem;text-align:center;color:var(--text-muted);padding:2.5rem">
            <p>No survey questions have been configured yet.</p>
            <p style="font-size:.8rem;margin-top:.5rem">An administrator can add questions from the admin panel.</p>
        </div>
        @endif

        <!-- ── Navigation ─────────────────────────── -->
        <div class="step-nav" id="step-nav">
            <button type="button" class="btn-prev {{ $initialStep === 0 ? 'hidden' : '' }}" id="btn-prev">← Back</button>
            <button type="button" class="btn-next {{ $initialStep === $totalSteps - 1 ? 'hidden' : '' }}" id="btn-next">Continue →</button>
            <button type="submit"  class="btn-submit {{ $initialStep !== $totalSteps - 1 ? 'hidden' : '' }}" id="btn-submit">⟶ &nbsp; Submit Feedback</button>
        </div>
    </form>

    <div class="footer">
        <p>This form is for IT Technical Support service evaluation only.</p>
        <p style="margin-top:.3rem">Responses are reviewed by management and kept confidential.</p>
    </div>

<script>
/* ── Multi-select agent dropdown ─────────────────────────── */
(function () {
    const wrap       = document.getElementById('agent-ms');
    const trigger    = document.getElementById('agent-trigger');
    const tagsEl     = document.getElementById('agent-tags');
    const placeholder= document.getElementById('agent-placeholder');
    const searchEl   = document.getElementById('agent-search');
    const listEl     = document.getElementById('agent-list');
    const hiddenWrap = document.getElementById('agent-hidden-inputs');
    const allOption  = listEl.querySelector('[data-all]');
    const options    = [...listEl.querySelectorAll('.ms-option:not([data-all])')];

    let selected = new Set(
        [...hiddenWrap.querySelectorAll('input')].map(i => i.value)
    );

    function render() {
        tagsEl.innerHTML = '';
        hiddenWrap.innerHTML = '';

        if (selected.size === 0) {
            tagsEl.appendChild(placeholder);
            placeholder.style.display = '';
        } else {
            placeholder.style.display = 'none';
            selected.forEach(val => {
                const opt = listEl.querySelector(`[data-value="${val}"]`);
                if (!opt) return;
                const tag = document.createElement('span');
                tag.className = 'ms-tag';
                tag.textContent = opt.dataset.label;
                tagsEl.appendChild(tag);

                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'agent_ids[]';
                inp.value = val;
                hiddenWrap.appendChild(inp);
            });
        }

        options.forEach(opt => {
            opt.querySelector('input[type="checkbox"]').checked = selected.has(opt.dataset.value);
        });

        const allChecked = options.length > 0 && options.every(o => selected.has(o.dataset.value));
        allOption.classList.toggle('all-checked', allChecked);
    }

    trigger.addEventListener('click', () => toggleOpen());
    trigger.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggleOpen(); }
        if (e.key === 'Escape') close();
    });

    function toggleOpen() {
        const isOpen = wrap.classList.toggle('open');
        trigger.setAttribute('aria-expanded', isOpen);
        if (isOpen) { searchEl.focus(); }
    }
    function close() {
        wrap.classList.remove('open');
        trigger.setAttribute('aria-expanded', 'false');
    }

    document.addEventListener('click', e => {
        if (!wrap.contains(e.target)) close();
    });

    options.forEach(opt => {
        opt.addEventListener('click', () => {
            const val = opt.dataset.value;
            if (selected.has(val)) { selected.delete(val); } else { selected.add(val); }
            render();
            // Clear agent error highlight on selection
            trigger.classList.remove('input-error');
        });
    });

    allOption.addEventListener('click', () => {
        const allChecked = options.every(o => selected.has(o.dataset.value));
        if (allChecked) { selected.clear(); } else { options.forEach(o => selected.add(o.dataset.value)); }
        render();
        trigger.classList.remove('input-error');
    });

    searchEl.addEventListener('input', () => {
        const q = searchEl.value.toLowerCase();
        options.forEach(opt => {
            opt.style.display = opt.dataset.search.includes(q) ? '' : 'none';
        });
    });

    render();
})();

/* ── Single-select issue type dropdown ──────────────────── */
(function () {
    const wrap      = document.getElementById('issue-type-ss');
    const trigger   = document.getElementById('issue-type-trigger');
    const display   = document.getElementById('issue-type-display');
    const searchEl  = document.getElementById('issue-type-search');
    const listEl    = document.getElementById('issue-type-list');
    const hiddenEl  = document.getElementById('issue-type-hidden');
    const notice    = document.getElementById('turnaround-notice');
    const taText    = document.getElementById('turnaround-time-text');
    const options   = [...listEl.querySelectorAll('.ss-option[data-value]')];

    if (!wrap) return;

    function applySelection(val, label, turnaround) {
        hiddenEl.value = val;
        display.innerHTML = label;
        options.forEach(o => o.classList.toggle('selected', o.dataset.value === val));
        if (turnaround) {
            taText.textContent = turnaround;
            notice.classList.remove('hidden');
        } else {
            notice.classList.add('hidden');
        }
        trigger.classList.remove('input-error');
    }

    function select(val, label, turnaround) {
        applySelection(val, label, turnaround);
        close();
    }

    function close() {
        wrap.classList.remove('open');
        trigger.setAttribute('aria-expanded', 'false');
        searchEl.value = '';
        options.forEach(o => o.style.display = '');
    }

    trigger.addEventListener('click', () => {
        const isOpen = wrap.classList.toggle('open');
        trigger.setAttribute('aria-expanded', isOpen);
        if (isOpen) { searchEl.focus(); }
    });
    trigger.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); wrap.classList.toggle('open'); }
        if (e.key === 'Escape') close();
    });
    document.addEventListener('click', e => {
        if (!wrap.contains(e.target)) close();
    });

    options.forEach(opt => {
        opt.addEventListener('click', () => {
            select(opt.dataset.value, opt.dataset.label, opt.dataset.turnaround);
        });
    });

    searchEl.addEventListener('input', () => {
        const q = searchEl.value.toLowerCase();
        options.forEach(opt => {
            opt.style.display = opt.dataset.search.includes(q) ? '' : 'none';
        });
    });

    // Restore old value on page reload after validation error
    const oldVal = hiddenEl.value;
    if (oldVal) {
        const pre = listEl.querySelector(`.ss-option[data-value="${CSS.escape(oldVal)}"]`);
        if (pre) { applySelection(pre.dataset.value, pre.dataset.label, pre.dataset.turnaround); }
    }
})();

/* ── Multi-step navigation ───────────────────────────────── */
(function () {
    const totalSteps  = {{ $totalSteps }};
    let   currentStep = {{ $initialStep }};

    const btnPrev     = document.getElementById('btn-prev');
    const btnNext     = document.getElementById('btn-next');
    const btnSubmit   = document.getElementById('btn-submit');
    const validMsg    = document.getElementById('step-validation-msg');

    // ── Indicator update ──────────────────────────
    function updateIndicator(step) {
        document.querySelectorAll('.si-step').forEach((el, i) => {
            el.classList.toggle('done',   i < step);
            el.classList.toggle('active', i === step);
        });
        if (totalSteps > 1) {
            const pct = (step / (totalSteps - 1)) * 100;
            document.getElementById('si-progress').style.width = pct + '%';
        }
    }

    // ── Show/hide steps and buttons ──────────────
    function showStep(step) {
        document.querySelectorAll('.step').forEach(el => {
            el.classList.toggle('active', Number(el.dataset.step) === step);
        });
        btnPrev.classList.toggle('hidden',   step === 0);
        btnNext.classList.toggle('hidden',   step === totalSteps - 1);
        btnSubmit.classList.toggle('hidden', step !== totalSteps - 1);
        updateIndicator(step);
        validMsg.classList.add('hidden');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ── Client-side validation ────────────────────
    function validateStep(step) {
        let valid = true;
        let firstError = null;

        if (step === 0) {
            const nameEl = document.getElementById('respondent_name');
            const posEl  = document.getElementById('position');
            const agentTrigger = document.querySelector('#agent-ms .ms-trigger');
            const agentCount   = document.querySelectorAll('#agent-hidden-inputs input').length;

            if (!nameEl.value.trim()) {
                nameEl.classList.add('input-error');
                valid = false;
                if (!firstError) firstError = nameEl;
            }
            if (!posEl.value.trim()) {
                posEl.classList.add('input-error');
                valid = false;
                if (!firstError) firstError = posEl;
            }
            if (agentCount === 0) {
                agentTrigger.classList.add('input-error');
                valid = false;
                if (!firstError) firstError = agentTrigger;
            }
            const issueTrigger = document.getElementById('issue-type-trigger');
            if (!document.getElementById('issue-type-hidden').value) {
                issueTrigger.classList.add('input-error');
                valid = false;
                if (!firstError) firstError = issueTrigger;
            }
        } else {
            // Validate rating questions only (text questions are optional)
            const stepEl = document.querySelector(`.step[data-step="${step}"]`);
            stepEl.querySelectorAll('.question-item').forEach(item => {
                const sr = item.querySelector('.star-rating');
                if (!sr) return; // text question, skip
                const radioName = sr.querySelector('input[type="radio"]').name;
                const checked   = document.querySelector(`input[name="${CSS.escape(radioName)}"]:checked`);
                if (!checked) {
                    item.classList.add('item-error');
                    valid = false;
                    if (!firstError) firstError = item;
                }
            });
        }

        if (!valid) {
            validMsg.classList.remove('hidden');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
        return valid;
    }

    // ── Clear error highlights on interaction ─────
    document.getElementById('respondent_name').addEventListener('input', function () {
        this.classList.remove('input-error');
        validMsg.classList.add('hidden');
    });
    document.getElementById('position').addEventListener('input', function () {
        this.classList.remove('input-error');
        validMsg.classList.add('hidden');
    });
    document.querySelectorAll('.question-item').forEach(item => {
        item.addEventListener('change', () => {
            item.classList.remove('item-error');
            validMsg.classList.add('hidden');
        });
    });

    // ── Button handlers ───────────────────────────
    btnNext.addEventListener('click', () => {
        if (!validateStep(currentStep)) return;
        currentStep++;
        showStep(currentStep);
    });

    btnPrev.addEventListener('click', () => {
        currentStep--;
        showStep(currentStep);
    });

    // ── Init ──────────────────────────────────────
    showStep(currentStep);
})();
</script>

</body>
</html>
