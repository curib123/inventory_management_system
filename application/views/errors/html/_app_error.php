<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$heading = isset($app_error_heading) ? (string) $app_error_heading : 'Application Error';
$message = isset($app_error_message) ? (string) $app_error_message : 'The request could not be completed.';
$code = isset($app_error_code) ? (string) $app_error_code : (string) http_response_code();
$causes = isset($app_error_causes) && is_array($app_error_causes) ? $app_error_causes : array();
$steps = isset($app_error_steps) && is_array($app_error_steps) ? $app_error_steps : array();

$safe_heading = htmlspecialchars(strip_tags($heading), ENT_QUOTES, 'UTF-8');
$safe_message = htmlspecialchars(strip_tags($message), ENT_QUOTES, 'UTF-8');
$safe_code = htmlspecialchars(strip_tags($code), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $safe_heading; ?></title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: #f8fafc;
            color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
        }
        .shell {
            width: min(720px, 100%);
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.10);
        }
        .top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 20px 22px;
            background: #0f172a;
            color: #ffffff;
        }
        .brand {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .02em;
        }
        .code {
            padding: 6px 10px;
            border: 1px solid rgba(255,255,255,.22);
            border-radius: 999px;
            background: rgba(255,255,255,.08);
            color: #cbd5e1;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 12px;
            font-weight: 700;
        }
        .content { padding: 28px; }
        .eyebrow {
            margin-bottom: 8px;
            color: #dc2626;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        h1 {
            margin: 0 0 10px;
            font-size: clamp(24px, 4vw, 34px);
            line-height: 1.12;
            letter-spacing: -.025em;
        }
        .message {
            margin: 0;
            color: #475569;
            font-size: 15px;
            line-height: 1.6;
        }
        .section {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid #e2e8f0;
        }
        .section-title {
            margin-bottom: 8px;
            color: #64748b;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        ul {
            margin: 0;
            padding-left: 20px;
            color: #475569;
            line-height: 1.55;
        }
        li + li { margin-top: 5px; }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 24px;
        }
        button {
            min-height: 40px;
            padding: 0 16px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #0f172a;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }
        button.primary {
            border-color: #2563eb;
            background: #2563eb;
            color: #ffffff;
        }
        button:hover { filter: brightness(.98); }
        .note {
            margin-top: 18px;
            color: #94a3b8;
            font-size: 12px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <main class="shell">
        <div class="top">
            <div class="brand">Inventory Management System</div>
            <div class="code"><?php echo $safe_code !== '' ? 'HTTP ' . $safe_code : 'ERROR'; ?></div>
        </div>

        <div class="content">
            <div class="eyebrow">Request could not be completed</div>
            <h1 data-error-heading><?php echo $safe_heading; ?></h1>
            <p class="message app-error-message" data-error-message><?php echo $safe_message; ?></p>

            <?php if (!empty($causes)): ?>
                <section class="section">
                    <div class="section-title">Possible causes</div>
                    <ul>
                        <?php foreach ($causes as $cause): ?>
                            <li><?php echo htmlspecialchars(strip_tags((string) $cause), ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <?php if (!empty($steps)): ?>
                <section class="section">
                    <div class="section-title">What to do</div>
                    <ul>
                        <?php foreach ($steps as $step): ?>
                            <li><?php echo htmlspecialchars(strip_tags((string) $step), ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <div class="actions">
                <button type="button" class="primary" onclick="window.location.reload()">Try Again</button>
                <button type="button" onclick="history.length > 1 ? history.back() : window.location.assign('/')">Go Back</button>
            </div>

            <div class="note">
                Detailed server diagnostics are kept out of this page to avoid exposing sensitive application information.
            </div>
        </div>
    </main>
</body>
</html>
