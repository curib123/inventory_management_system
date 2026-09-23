<h2><?php echo html_escape($report_title); ?></h2>

<p>
    <a href="<?php echo site_url('reports/inventory'); ?>">Inventory</a> |
    <a href="<?php echo site_url('reports/stock-in'); ?>">Stock In</a> |
    <a href="<?php echo site_url('reports/stock-out'); ?>">Stock Out</a> |
    <a href="<?php echo site_url('reports/movement'); ?>">Movement</a> |
    <a href="<?php echo site_url('reports/low-stock'); ?>">Low Stock</a> |
    <a href="<?php echo site_url('reports/valuation'); ?>">Valuation</a>
</p>

<p>
    Export:
    <a href="<?php echo site_url('reports/export/' . $report_key . '/csv'); ?>">CSV</a> |
    <a href="<?php echo site_url('reports/export/' . $report_key . '/xlsx'); ?>">Excel</a> |
    <a href="<?php echo site_url('reports/export/' . $report_key . '/pdf'); ?>">PDF</a>
</p>

<table>
    <thead>
        <tr>
            <?php if (!empty($rows)): foreach (array_keys($rows[0]) as $header): ?>
                <th><?php echo html_escape(ucwords(str_replace('_', ' ', $header))); ?></th>
            <?php endforeach; endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($rows)): foreach ($rows as $row): ?>
            <tr><?php foreach ($row as $value): ?><td><?php echo html_escape($value); ?></td><?php endforeach; ?></tr>
        <?php endforeach; else: ?>
            <tr><td>No report data found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
