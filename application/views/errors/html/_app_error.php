<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$heading = isset($app_error_heading) ? (string) $app_error_heading : 'Application Error';
$message = isset($app_error_message) ? (string) $app_error_message : 'The request could not be completed.';
$code = isset($app_error_code) ? (string) $app_error_code : (string) http_response_code();
$category = isset($app_error_category) ? (string) $app_error_category : 'Application Error';
$error_id = isset($app_error_id) ? (string) $app_error_id : '';
$log_reference = isset($app_error_log_reference)
    ? (string) $app_error_log_reference
    : ('application/logs/log-' . date('Y-m-d') . '.php');
$source = isset($app_error_source) ? trim((string) $app_error_source) : '';
$causes = isset($app_error_causes) && is_array($app_error_causes)
    ? $app_error_causes
    : array(
        'The request may contain invalid, stale, or conflicting data.',
        'A required permission, session, service, or database operation may have failed.',
        'An unexpected application condition may have stopped the request.'
    );
$steps = isset($app_error_steps) && is_array($app_error_steps)
    ? $app_error_steps
    : array(
        'Retry the action once after refreshing the page.',
        'If the issue continues, use the Error ID below to search the application log.',
        'Check the relevant controller, model, or service referenced by the log entry.'
    );

$safe_heading = htmlspecialchars(strip_tags($heading), ENT_QUOTES, 'UTF-8');
$safe_message = htmlspecialchars(strip_tags($message), ENT_QUOTES, 'UTF-8');
$safe_code = htmlspecialchars(strip_tags($code), ENT_QUOTES, 'UTF-8');
$safe_category = htmlspecialchars(strip_tags($category), ENT_QUOTES, 'UTF-8');
$safe_error_id = htmlspecialchars(strip_tags($error_id), ENT_QUOTES, 'UTF-8');
$safe_log_reference = htmlspecialchars(strip_tags($log_reference), ENT_QUOTES, 'UTF-8');
$safe_source = htmlspecialchars(strip_tags($source), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $safe_heading; ?> · Inventory Management System</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f7fb;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --border: #e2e8f0;
            --text: #0f172a;
            --muted: #64748b;
            --brand: #2563eb;
            --brand-dark: #1d4ed8;
            --danger: #dc2626;
            --danger-soft: #fef2f2;
            --warning-soft: #fffbeb;
            --shadow: 0 24px 70px rgba(15, 23, 42, .12);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 28px;
            background:
                radial-gradient(circle at top left, rgba(37,99,235,.08), transparent 28%),
                var(--bg);
            color: var(--text);
            font-family: Inter, ui-sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .page {
            width: min(920px, 100%);
            margin: 5vh auto 0;
        }

        .shell {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: var(--surface);
            box-shadow: var(--shadow);
        }

        .topbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            background: rgba(255,255,255,.96);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 11px;
            font-size: 13px;
            font-weight: 800;
        }

        .brand-mark {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: #0f172a;
            color: #fff;
            font-size: 16px;
        }

        .badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .badge {
            padding: 7px 10px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--surface-soft);
            color: var(--muted);
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 11px;
            font-weight: 800;
        }

        .content {
            padding: 34px;
        }

        .hero {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }

        .icon {
            display: grid;
            place-items: center;
            width: 54px;
            height: 54px;
            border: 1px solid #fecaca;
            border-radius: 16px;
            background: var(--danger-soft);
            color: var(--danger);
            font-size: 24px;
            font-weight: 900;
        }

        .eyebrow {
            margin: 2px 0 7px;
            color: var(--danger);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .09em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-size: clamp(26px, 4vw, 38px);
            line-height: 1.08;
            letter-spacing: -.035em;
        }

        .message {
            max-width: 720px;
            margin: 12px 0 0;
            color: #475569;
            font-size: 15px;
            line-height: 1.65;
        }

        .debug-card {
            margin-top: 26px;
            padding: 18px;
            border: 1px solid #fde68a;
            border-radius: 14px;
            background: var(--warning-soft);
        }

        .debug-title {
            margin-bottom: 12px;
            color: #92400e;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .07em;
            text-transform: uppercase;
        }

        .debug-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .debug-item {
            min-width: 0;
            padding: 12px;
            border: 1px solid rgba(146,64,14,.13);
            border-radius: 10px;
            background: rgba(255,255,255,.65);
        }

        .debug-label {
            display: block;
            margin-bottom: 5px;
            color: #92400e;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .debug-value {
            display: block;
            overflow-wrap: anywhere;
            color: #451a03;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 12px;
            font-weight: 700;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .panel {
            padding: 18px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--surface-soft);
        }

        .panel-title {
            margin-bottom: 10px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .07em;
            text-transform: uppercase;
        }

        ul {
            margin: 0;
            padding-left: 19px;
            color: #475569;
            font-size: 13px;
            line-height: 1.6;
        }

        li + li { margin-top: 5px; }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 24px;
        }

        button, a.action {
            min-height: 42px;
            padding: 0 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: #fff;
            color: var(--text);
            font: inherit;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
        }

        button.primary {
            border-color: var(--brand);
            background: var(--brand);
            color: #fff;
        }

        button.primary:hover { background: var(--brand-dark); }

        .footer {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 10px;
            margin-top: 18px;
            color: #94a3b8;
            font-size: 11px;
            line-height: 1.5;
        }

        @media (max-width: 700px) {
            body { padding: 14px; }
            .page { margin-top: 1vh; }
            .content { padding: 24px 20px; }
            .hero { grid-template-columns: 1fr; }
            .grid, .debug-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="shell">
            <div class="topbar">
                <div class="brand">
                    <span class="brand-mark">IMS</span>
                    <span>Inventory Management System</span>
                </div>

                <div class="badges">
                    <span class="badge"><?php echo $safe_category; ?></span>
                    <span class="badge"><?php echo $safe_code !== '' ? 'HTTP ' . $safe_code : 'ERROR'; ?></span>
                </div>
            </div>

            <div class="content">
                <div class="hero">
                    <div class="icon">!</div>
                    <div>
                        <div class="eyebrow">Request could not be completed</div>
                        <h1 data-error-heading><?php echo $safe_heading; ?></h1>
                        <p class="message app-error-message" data-error-message>
                            <?php echo $safe_message; ?>
                        </p>
                    </div>
                </div>

                <section class="debug-card" aria-label="Error reference">
                    <div class="debug-title">Developer reference</div>
                    <div class="debug-grid">
                        <div class="debug-item">
                            <span class="debug-label">Error ID</span>
                            <span class="debug-value" data-error-id>
                                <?php echo $safe_error_id !== '' ? $safe_error_id : 'Unavailable'; ?>
                            </span>
                        </div>

                        <div class="debug-item">
                            <span class="debug-label">Application log</span>
                            <span class="debug-value" data-error-log>
                                <?php echo $safe_log_reference; ?>
                            </span>
                        </div>

                        <?php if ($safe_source !== ''): ?>
                            <div class="debug-item">
                                <span class="debug-label">Development source</span>
                                <span class="debug-value"><?php echo $safe_source; ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="debug-item">
                            <span class="debug-label">How to find it</span>
                            <span class="debug-value">
                                Open the log file and search the Error ID exactly.
                            </span>
                        </div>
                    </div>
                </section>

                <div class="grid">
                    <section class="panel">
                        <div class="panel-title">Possible causes</div>
                        <ul>
                            <?php foreach ($causes as $cause): ?>
                                <li><?php echo htmlspecialchars(strip_tags((string) $cause), ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </section>

                    <section class="panel">
                        <div class="panel-title">What to do</div>
                        <ul>
                            <?php foreach ($steps as $step): ?>
                                <li><?php echo htmlspecialchars(strip_tags((string) $step), ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                </div>

                <div class="actions">
                    <button type="button" class="primary" onclick="window.location.reload()">Try Again</button>
                    <button type="button" onclick="history.length > 1 ? history.back() : window.location.assign('/')">Go Back</button>
                    <a class="action" href="/">Home</a>
                </div>

                <div class="footer">
                    <span>Sensitive stack traces and absolute server paths are not shown here.</span>
                    <span><?php echo date('Y-m-d H:i:s'); ?></span>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
