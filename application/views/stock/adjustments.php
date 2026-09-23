<h2>Stock Adjustments</h2>
<a class="btn btn-success" href="<?php echo site_url('stock/adjustment'); ?>">New Adjustment</a>

<table>
    <thead>
        <tr>
            <th>Product</th>
            <th>System Stock</th>
            <th>Actual Stock</th>
            <th>Difference</th>
            <th>Reason</th>
            <th>Processed By</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($adjustments)): foreach ($adjustments as $adjustment): ?>
            <tr>
                <td><?php echo html_escape($adjustment->product_code . ' - ' . $adjustment->product_name); ?></td>
                <td><?php echo (int) $adjustment->system_stock; ?></td>
                <td><?php echo (int) $adjustment->actual_stock; ?></td>
                <td><?php echo (int) $adjustment->difference; ?></td>
                <td><?php echo html_escape($adjustment->reason); ?></td>
                <td><?php echo html_escape($adjustment->username); ?></td>
                <td><?php echo html_escape($adjustment->created_at); ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="7">No adjustments found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if (!empty($pagination)): ?><?php echo $pagination; ?><?php endif; ?>
