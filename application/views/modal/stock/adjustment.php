<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
<?php
$this->load->view('components/modal/header', array(
    'modal_title' => $page_title,
    'modal_subtitle' => 'Record the physical stock count and the reason for the correction.',
    'modal_icon' => 'bi-sliders'
));
?>
<div class="modal-body">
    <?php $this->load->view('components/modal/messages'); ?>

    <div class="mb-3">
        <label for="product_id" class="form-label">Product</label>
        <?php $selected_product = set_value('product_id'); ?>
        <select id="product_id" name="product_id" class="form-select" required>
            <option value="">Select Product</option>
            <?php foreach ($products as $product): ?>
                <option value="<?php echo (int) $product->id; ?>" <?php echo ((string) $selected_product === (string) $product->id) ? 'selected' : ''; ?>><?php echo html_escape($product->product_code . ' - ' . $product->product_name); ?> (Current stock: <?php echo (int) $product->stock; ?>)</option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-4">
            <label for="actual_stock" class="form-label">Actual Stock</label>
            <input type="number" id="actual_stock" name="actual_stock" class="form-control" min="0" step="1" required value="<?php echo html_escape(set_value('actual_stock')); ?>">
        </div>

        <div class="col-12 col-md-8">
            <label for="reason" class="form-label">Reason</label>
            <input type="text" id="reason" name="reason" class="form-control" maxlength="255" required value="<?php echo html_escape(set_value('reason')); ?>">
        </div>
    </div>
</div>
<?php
$this->load->view('components/modal/footer', array(
    'submit_label' => 'Save Adjustment',
    'submit_icon' => 'bi-check-lg'
));
?>
<?php echo form_close(); ?>
