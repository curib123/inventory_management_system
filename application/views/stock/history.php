<h2>Stock Movement History</h2>
<a class="btn btn-success" href="<?php echo site_url('stock/in'); ?>">Stock In</a>
<a class="btn" href="<?php echo site_url('stock/out'); ?>">Stock Out</a>
<a class="btn" href="<?php echo site_url('stock/adjustment'); ?>">Adjustment</a>

<?php if ($this->session->flashdata('success')): ?><p><?php echo html_escape($this->session->flashdata('success')); ?></p><?php endif; ?>
<?php if ($this->session->flashdata('error')): ?><p><?php echo html_escape($this->session->flashdata('error')); ?></p><?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Transaction No.</th>
            <th>Type</th>
            <th>Supplier</th>
            <th>Processed By</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($transactions)): foreach ($transactions as $transaction): ?>
            <tr>
                <td><?php echo html_escape($transaction->transaction_no); ?></td>
                <td><?php echo html_escape($transaction->type); ?></td>
                <td><?php echo html_escape($transaction->supplier_name ?: 'N/A'); ?></td>
                <td><?php echo html_escape($transaction->username); ?></td>
                <td><?php echo html_escape($transaction->created_at); ?></td>
                <td><a class="btn" href="<?php echo site_url('stock/details/' . $transaction->id); ?>">Details</a></td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="6">No stock movements found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if (!empty($pagination)): ?><?php echo $pagination; ?><?php endif; ?>
