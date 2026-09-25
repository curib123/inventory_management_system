<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo html_escape($report_title); ?></title>
    <style>
        @page {
            margin: 26px 28px 46px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #0f172a;
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 8.4px;
            line-height: 1.35;
        }

        .report-shell {
            width: 100%;
        }

        .hero {
            margin-bottom: 12px;
            border: 1px solid #dbe3ee;
            border-radius: 8px;
            overflow: hidden;
            background: #ffffff;
        }

        .hero-brand {
            padding: 9px 13px;
            background: #0f172a;
            color: #ffffff;
        }

        .hero-brand-label {
            color: #93c5fd;
            font-size: 6.5px;
            font-weight: bold;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .hero-brand-name {
            margin-top: 2px;
            font-size: 10px;
            font-weight: bold;
        }

        .hero-content {
            padding: 11px 13px 12px;
            border-bottom: 3px solid #2563eb;
        }

        .hero-title {
            margin: 0;
            color: #0f172a;
            font-size: 18px;
            line-height: 1.15;
        }

        .hero-subtitle {
            margin-top: 4px;
            color: #64748b;
            font-size: 7.4px;
        }

        .meta-table {
            width: 100%;
            margin-top: 9px;
            border-collapse: collapse;
        }

        .meta-cell {
            width: 33.333%;
            padding-right: 12px;
            vertical-align: top;
        }

        .meta-label {
            color: #94a3b8;
            font-size: 6px;
            font-weight: bold;
            letter-spacing: 0.7px;
            text-transform: uppercase;
        }

        .meta-value {
            margin-top: 2px;
            color: #334155;
            font-size: 7.4px;
            font-weight: bold;
        }

        .summary-table {
            width: 100%;
            margin: 0 0 12px;
            border-collapse: separate;
            border-spacing: 6px 0;
            table-layout: fixed;
        }

        .summary-card {
            padding: 8px 9px;
            border: 1px solid #dbe3ee;
            border-radius: 6px;
            background: #f8fafc;
            vertical-align: top;
        }

        .summary-label {
            color: #64748b;
            font-size: 6px;
            font-weight: bold;
            letter-spacing: 0.65px;
            text-transform: uppercase;
        }

        .summary-value {
            margin-top: 3px;
            color: #0f172a;
            font-size: 12px;
            font-weight: bold;
        }

        .section-head {
            margin: 0 0 6px;
        }

        .section-title {
            font-size: 8px;
            font-weight: bold;
            color: #0f172a;
        }

        .section-note {
            float: right;
            color: #64748b;
            font-size: 6.5px;
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
            padding: 6.5px 5.5px;
            border: 1px solid #1e40af;
            background: #1d4ed8;
            color: #ffffff;
            font-size: 6.4px;
            font-weight: bold;
            line-height: 1.2;
            text-align: left;
            text-transform: uppercase;
            vertical-align: middle;
        }

        .data-table td {
            padding: 5.5px;
            border: 1px solid #e2e8f0;
            color: #334155;
            font-size: 7px;
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

        .cell-number,
        .cell-money {
            text-align: right;
            white-space: nowrap;
        }

        .cell-date {
            white-space: nowrap;
        }

        .movement {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 8px;
            font-size: 6.2px;
            font-weight: bold;
            white-space: nowrap;
        }

        .movement-in {
            border: 1px solid #a7f3d0;
            background: #ecfdf5;
            color: #047857;
        }

        .movement-out {
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #b91c1c;
        }

        .movement-adjustment {
            border: 1px solid #fde68a;
            background: #fffbeb;
            color: #b45309;
        }

        .movement-default {
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .empty-row {
            padding: 18px !important;
            color: #64748b !important;
            text-align: center;
            font-style: italic;
        }

        .report-note {
            margin-top: 9px;
            padding: 7px 9px;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            background: #f8fafc;
            color: #64748b;
            font-size: 6.4px;
        }

        .footer {
            position: fixed;
            right: 28px;
            bottom: -29px;
            left: 28px;
            padding-top: 5px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 6.3px;
        }

        .footer-left {
            float: left;
        }

        .footer-right {
            float: right;
        }
    </style>
</head>
<body>
    <div class="report-shell">
        <div class="hero">
            <div class="hero-brand">
                <div class="hero-brand-label">Inventory Intelligence</div>
                <div class="hero-brand-name"><?php echo html_escape($report_meta['system_name']); ?></div>
            </div>

            <div class="hero-content">
                <h1 class="hero-title"><?php echo html_escape($report_title); ?></h1>
                <div class="hero-subtitle">
                    Enterprise inventory report prepared for operational review and record keeping.
                </div>

                <table class="meta-table" aria-label="Report metadata">
                    <tr>
                        <td class="meta-cell">
                            <div class="meta-label">Generated</div>
                            <div class="meta-value"><?php echo html_escape($report_meta['generated_at']); ?></div>
                        </td>
                        <td class="meta-cell">
                            <div class="meta-label">Prepared by</div>
                            <div class="meta-value"><?php echo html_escape($report_meta['prepared_by'] ?: 'System User'); ?></div>
                        </td>
                        <td class="meta-cell">
                            <div class="meta-label">Record count</div>
                            <div class="meta-value"><?php echo number_format((int) $report_meta['record_count']); ?></div>
                        </td>
                    </tr>
                </table>
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

        <div class="section-head">
            <span class="section-title">Report Data</span>
            <span class="section-note"><?php echo number_format((int) $report_meta['record_count']); ?> total record<?php echo (int) $report_meta['record_count'] === 1 ? '' : 's'; ?></span>
        </div>

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
                                <?php
                                $value = isset($row[$field]) ? $row[$field] : '—';
                                $cell_class = '';

                                if (in_array($field, array('stock', 'quantity', 'reorder_level', 'shortage'), TRUE)) {
                                    $cell_class = 'cell-number';
                                } elseif (in_array($field, array('cost_price', 'inventory_value'), TRUE)) {
                                    $cell_class = 'cell-money';
                                } elseif ($field === 'created_at') {
                                    $cell_class = 'cell-date';
                                }
                                ?>
                                <td class="<?php echo html_escape($cell_class); ?>">
                                    <?php if ($field === 'type'): ?>
                                        <?php
                                        $normalized_type = strtolower(str_replace(' ', '_', (string) $value));
                                        $movement_class = 'movement-default';

                                        if ($normalized_type === 'stock_in') {
                                            $movement_class = 'movement-in';
                                        } elseif ($normalized_type === 'stock_out') {
                                            $movement_class = 'movement-out';
                                        } elseif ($normalized_type === 'adjustment') {
                                            $movement_class = 'movement-adjustment';
                                        }
                                        ?>
                                        <span class="movement <?php echo html_escape($movement_class); ?>">
                                            <?php echo html_escape($value); ?>
                                        </span>
                                    <?php else: ?>
                                        <?php echo html_escape($value); ?>
                                    <?php endif; ?>
                                </td>
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
            Values reflect the system data available at the generation time shown above. Use the Excel export when further sorting, filtering, or spreadsheet analysis is required.
        </div>
    </div>

    <div class="footer">
        <span class="footer-left">
            <?php echo html_escape($report_meta['system_name']); ?> &mdash; <?php echo html_escape($report_title); ?>
        </span>
        <span class="footer-right">
            Internal business report
        </span>
    </div>
</body>
</html>
