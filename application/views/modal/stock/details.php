<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Stock Transaction Details',
    'modal_subtitle' => 'Transaction information and recorded inventory items.',
    'modal_icon' => 'bi-receipt'
));
?>

<div class="app-modal-body">
    <dl class="row app-detail-list mb-4">
        <dt class="col-sm-4">Transaction</dt><dd class="col-sm-8"><?php echo html_escape($transaction->transaction_no); ?></dd>
        <dt class="col-sm-4">Type</dt><dd class="col-sm-8"><?php echo html_escape($transaction->type); ?></dd>
        <dt class="col-sm-4">Supplier / Scope</dt><dd class="col-sm-8"><?php echo html_escape($transaction->supplier_name ?: 'Unassigned Products'); ?></dd>
        <dt class="col-sm-4">Processed By</dt><dd class="col-sm-8"><?php echo html_escape($transaction->username); ?></dd>
        <dt class="col-sm-4">Date</dt><dd class="col-sm-8"><?php echo html_escape($transaction->created_at); ?></dd>
        <dt class="col-sm-4">Remarks</dt><dd class="col-sm-8"><?php echo html_escape($transaction->remarks ?: 'N/A'); ?></dd>
    </dl>

    <div class="table-responsive border rounded-3">
        <table class="table app-data-table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Product Code</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Cost Price</th>
                    <?php if ($transaction->type === 'adjustment'): ?>
                        <th>System Stock</th>
                        <th>Actual Stock</th>
                        <th>Difference</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo html_escape($item->product_code); ?></td>
                        <td><?php echo html_escape($item->product_name); ?></td>
                        <td><?php echo (int) $item->quantity; ?></td>
                        <td><?php echo html_escape($item->unit); ?></td>
                        <td><?php echo number_format((float) $item->cost_price, 2); ?></td>
                        <?php if ($transaction->type === 'adjustment'): ?>
                            <td><?php echo $item->system_stock !== NULL ? (int) $item->system_stock : 'N/A'; ?></td>
                            <td><?php echo $item->actual_stock !== NULL ? (int) $item->actual_stock : 'N/A'; ?></td>
                            <td><?php echo $item->adjustment_difference !== NULL ? (int) $item->adjustment_difference : 'N/A'; ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="<?php echo $transaction->type === 'adjustment' ? 8 : 5; ?>" class="text-center text-body-secondary py-4">No transaction items found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $this->load->view('components/modal/footer', array('close_label' => 'Close')); ?>
