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

<table data-datatable-server data-source="<?php echo site_url('reports/datatable/' . $report_key); ?>">
    <thead>
        <tr>
            <?php foreach ($columns as $label): ?>
                <th><?php echo html_escape($label); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody></tbody>
</table>
