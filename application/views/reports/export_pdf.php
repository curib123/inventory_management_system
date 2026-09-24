<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo html_escape($report_title); ?></title>
    <style>
        @page {
            margin: 24px 24px 42px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #0f172a;
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 9px;
            line-height: 1.35;
        }

        .report-shell {
            width: 100%;
        }

        .report-header {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 14px;
            overflow: hidden;
        }

        .report-brand {
            background: #0f172a;
            color: #ffffff;
            padding: 11px 14px;
        }

        .report-brand-kicker {
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            color: #cbd5e1;
        }

        .report-brand-name {
            margin-top: 2px;
            font-size: 12px;
            font-weight: bold;
        }

        .report-heading {
            padding: 13px 14px 11px;
            background: #ffffff;
        }

        .report-title {
            margin: 0 0 4px;
            font-size: 18px;
            line-height: 1.15;
            color: #0f172a;
        }

        .report-meta {
            color: #64748b;
            font-size: 8px;
        }

        .summary-table {
            width: 100%;
            margin: 0 0 14px;
            border-collapse: separate;
            border-spacing: 6px 0;
            table-layout: fixed;
        }

        .summary-card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 9px;
            background: #f8fafc;
            vertical-align: top;
        }

        .summary-label {
            color: #64748b;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .summary-value {
            margin-top: 3px;
            color: #0f172a;
            font-size: 12px;
            font-weight: bold;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        .data-table thead {
            display: table-header-group;
        }

        .data-table th {
            padding: 7px 6px;
            border: 1px solid #1d4ed8;
            background: #2563eb;
            color: #ffffff;
            font-size: 7px;
            font-weight: bold;
            line-height: 1.2;
            text-align: left;
            vertical-align: middle;
        }

        .data-table td {
            padding: 6px;
            border: 1px solid #e2e8f0;
            color: #334155;
            font-size: 7.4px;
            line-height: 1.28;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .data-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .data-table tbody tr {
            page-break-inside: avoid;
        }

        .empty-row {
            padding: 18px !important;
            color: #64748b !important;
            text-align: center;
            font-style: italic;
        }

        .report-note {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 7px;
        }

        .footer {
            position: fixed;
            right: 24px;
            bottom: -25px;
            left: 24px;
            color: #64748b;
            font-size: 7px;
        }

        .footer-left {
            float: left;
        }
    </style>
</head>
<body>
    <div class="report-shell">
        <div class="report-header">
            <div class="report-brand">
                <div class="report-brand-kicker">Business Report</div>
                <div class="report-brand-name"><?php echo html_escape($report_meta['system_name']); ?></div>
            </div>

            <div class="report-heading">
                <h1 class="report-title"><?php echo html_escape($report_title); ?></h1>
                <div class="report-meta">
                    Generated <?php echo html_escape($report_meta['generated_at']); ?>
                    &nbsp;&bull;&nbsp;
                    Prepared by <?php echo html_escape($report_meta['prepared_by'] ?: 'System User'); ?>
                    &nbsp;&bull;&nbsp;
                    <?php echo number_format((int) $report_meta['record_count']); ?> record<?php echo (int) $report_meta['record_count'] === 1 ? '' : 's'; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($report_meta['summary'])): ?>
            <table class="summary-table" aria-label="Report summary">
                <tr>
                    <?php foreach ($report_meta['summary'] as $label => $value): ?>
                        <td class="summary-card">
                            <div class="summary-label"><?php echo html_escape($label); ?></div>
                            <div class="summary-value"><?php echo html_escape($value); ?></div>
                        </td>
                    <?php endforeach; ?>
                </tr>
            </table>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <?php foreach ($columns as $field => $label): ?>
                        <th><?php echo html_escape($label); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($rows)): ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($columns as $field => $label): ?>
                                <td><?php echo html_escape(isset($row[$field]) ? $row[$field] : '—'); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td class="empty-row" colspan="<?php echo max(1, count($columns)); ?>">
                            No report data found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="report-note">
            This report is formatted for A4 landscape printing. Column labels, report metadata, totals, and pagination are included for manager-ready review.
        </div>
    </div>

    <div class="footer">
        <span class="footer-left">
            <?php echo html_escape($report_meta['system_name']); ?> &mdash; <?php echo html_escape($report_title); ?>
        </span>
    </div>
</body>
</html>
