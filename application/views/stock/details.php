<h2>Stock Transaction Details</h2>
<p><strong>Transaction:</strong> <?php echo html_escape($transaction->transaction_no); ?></p>
<p><strong>Type:</strong> <?php echo html_escape($transaction->type); ?></p>
<p><strong>Supplier:</strong> <?php echo html_escape($transaction->supplier_name ?: 'N/A'); ?></p>
<p><strong>Processed By:</strong> <?php echo html_escape($transaction->username); ?></p>
<p><strong>Date:</strong> <?php echo html_escape($transaction->created_at); ?></p>
<p><strong>Remarks:</strong> <?php echo html_escape($transaction->remarks ?: 'N/A'); ?></p>

<table>
    <thead><tr><th>Product Code</th><th>Product</th><th>Quantity</th><th>Unit</th><th>Cost Price</th></tr></thead>
    <tbody>
        <?php if (!empty($items)): foreach ($items as $item): ?>
            <tr>
                <td><?php echo html_escape($item->product_code); ?></td>
                <td><?php echo html_escape($item->product_name); ?></td>
                <td><?php echo (int) $item->quantity; ?></td>
                <td><?php echo html_escape($item->unit); ?></td>
                <td><?php echo number_format((float) $item->cost_price, 2); ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="5">No transaction items found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<p><a href="<?php echo site_url('stock/history'); ?>">Back to History</a></p>
