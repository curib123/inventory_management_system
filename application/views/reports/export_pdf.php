<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo html_escape($report_title); ?></title>
    <style>
        @page {
            margin: 24px 28px 42px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111827;
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 8px;
            line-height: 1.35;
        }

        h1 {
            margin: 0 0 5px;
            font-size: 15px;
            font-weight: bold;
        }

        .report-meta {
            margin: 0 0 12px;
            color: #4b5563;
            font-size: 7px;
        }

        .report-meta div {
            margin-bottom: 2px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        .data-table thead {
            display: table-header-group;
        }

        .data-table th,
        .data-table td {
            padding: 5px 4px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .data-table th {
            background: #f3f4f6;
            color: #111827;
            font-size: 6.7px;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
        }

        .data-table td {
            color: #1f2937;
            font-size: 7px;
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

        .empty-row {
            padding: 16px !important;
            text-align: center;
        }

        .footer {
            position: fixed;
            right: 28px;
            bottom: -26px;
            left: 28px;
            padding-top: 4px;
            border-top: 1px solid #d1d5db;
            color: #6b7280;
            font-size: 6.2px;
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
    <h1><?php echo html_escape($report_title); ?></h1>

    <div class="report-meta">
        <div><strong>System:</strong> <?php echo html_escape($report_meta['system_name']); ?></div>
        <div><strong>Generated:</strong> <?php echo html_escape($report_meta['generated_at']); ?></div>
        <div><strong>Prepared by:</strong> <?php echo html_escape($report_meta['prepared_by'] ?: 'System User'); ?></div>
        <div><strong>Records:</strong> <?php echo number_format((int) $report_meta['record_count']); ?></div>
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
                                <?php echo html_escape($value); ?>
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

    <div class="footer">
        <span class="footer-left">
            <?php echo html_escape($report_meta['system_name']); ?> — <?php echo html_escape($report_title); ?>
        </span>
        <span class="footer-right">Data report</span>
    </div>
</body>
</html>
