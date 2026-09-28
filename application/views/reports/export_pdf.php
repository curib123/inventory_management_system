<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo html_escape($report_title); ?></title>
    <style>
        @page {
            margin: 28px 30px 44px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111111;
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 8px;
            line-height: 1.3;
        }

        h1 {
            margin: 0 0 4px;
            font-size: 15px;
            font-weight: bold;
        }

        .report-subtitle {
            margin: 0 0 10px;
            color: #444444;
            font-size: 7px;
        }

        .section-title {
            margin: 10px 0 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table,
        .summary-table {
            margin-bottom: 10px;
        }

        th,
        td {
            padding: 4px 5px;
            border: 1px solid #777777;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        th {
            background: #eeeeee;
            color: #111111;
            font-size: 6.8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .info-table th,
        .summary-table th {
            width: 20%;
            white-space: nowrap;
        }

        .data-table thead {
            display: table-header-group;
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
            padding: 14px;
            text-align: center;
        }

        .report-note {
            margin-top: 8px;
            color: #444444;
            font-size: 6.5px;
        }

        .footer {
            position: fixed;
            right: 30px;
            bottom: -27px;
            left: 30px;
            padding-top: 4px;
            border-top: 1px solid #999999;
            color: #555555;
            font-size: 6px;
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
    <p class="report-subtitle">Inventory report generated from the current system data and selected report filters.</p>

    <div class="section-title">Report information</div>
    <table class="info-table" aria-label="Report information">
        <tbody>
            <tr>
                <th>System</th>
                <td><?php echo html_escape($report_meta['system_name']); ?></td>
                <th>Generated</th>
                <td><?php echo html_escape($report_meta['generated_at']); ?></td>
            </tr>
            <tr>
                <th>Prepared by</th>
                <td><?php echo html_escape($report_meta['prepared_by'] ?: 'System User'); ?></td>
                <th>Record count</th>
                <td><?php echo number_format((int) $report_meta['record_count']); ?></td>
            </tr>
        </tbody>
    </table>

    <?php if (!empty($report_meta['summary'])): ?>
        <div class="section-title">Summary</div>
        <table class="summary-table" aria-label="Report summary">
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report_meta['summary'] as $label => $value): ?>
                    <tr>
                        <td><?php echo html_escape($label); ?></td>
                        <td><?php echo html_escape($value); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="section-title">Data</div>
    <table class="data-table" aria-label="<?php echo html_escape($report_title); ?> data">
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

    <p class="report-note">
        This PDF intentionally contains text and table data only. Charts, graphs, canvases, images, icons, and other visual report elements are excluded from print output.
    </p>

    <div class="footer">
        <span class="footer-left">
            <?php echo html_escape($report_meta['system_name']); ?> — <?php echo html_escape($report_title); ?>
        </span>
        <span class="footer-right">Internal business report</span>
    </div>
</body>
</html>
