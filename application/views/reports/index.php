<h2><?php echo html_escape($report_title); ?></h2>

<div>
    <a class="btn" href="<?php echo site_url('reports/export/' . $report_key . '/csv'); ?>">CSV</a>
    <a class="btn btn-success" href="<?php echo site_url('reports/export/' . $report_key . '/xlsx'); ?>">Excel</a>
    <a class="btn" href="<?php echo site_url('reports/export/' . $report_key . '/pdf'); ?>">PDF</a>
</div>

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
            <tr>
                <?php foreach ($row as $value): ?>
                    <td><?php echo html_escape($value); ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; else: ?>
            <tr><td>No report data found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
