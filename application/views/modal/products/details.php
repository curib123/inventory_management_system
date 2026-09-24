<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Product Details',
    'modal_subtitle' => 'Product information, stock, pricing, and supplier assignment.',
    'modal_icon' => 'bi-box-seam'
));
?>

<div class="modal-body">
    <dl class="row app-detail-list mb-0">
        <dt class="col-sm-4">ID</dt><dd class="col-sm-8"><?php echo (int) $product->id; ?></dd>
        <dt class="col-sm-4">Code</dt><dd class="col-sm-8"><?php echo html_escape($product->product_code); ?></dd>
        <dt class="col-sm-4">Name</dt><dd class="col-sm-8"><?php echo html_escape($product->product_name); ?></dd>
        <dt class="col-sm-4">Category</dt><dd class="col-sm-8"><?php echo html_escape($product->category_name ?: 'N/A'); ?></dd>
        <dt class="col-sm-4">Supplier</dt><dd class="col-sm-8"><?php echo html_escape($product->supplier_name ?: 'N/A'); ?></dd>
        <dt class="col-sm-4">Unit</dt><dd class="col-sm-8"><?php echo html_escape($product->unit); ?></dd>
        <dt class="col-sm-4">Stock</dt><dd class="col-sm-8"><?php echo (int) $product->stock; ?></dd>
        <dt class="col-sm-4">Cost Price</dt><dd class="col-sm-8"><?php echo number_format((float) $product->cost_price, 2); ?></dd>
        <dt class="col-sm-4">Selling Price</dt><dd class="col-sm-8"><?php echo number_format((float) $product->selling_price, 2); ?></dd>
        <dt class="col-sm-4">Reorder Level</dt><dd class="col-sm-8"><?php echo (int) $product->reorder_level; ?></dd>
        <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><span class="badge <?php echo $product->status ? 'text-bg-success' : 'text-bg-secondary'; ?>"><?php echo $product->status ? 'Active' : 'Inactive'; ?></span></dd>
    </dl>
</div>

<?php $this->load->view('components/modal/footer', array('close_label' => 'Close')); ?>