<dialog id="product-form-<?php echo $modal_id; ?>">
    <?php $this->load->view('products/form', array(
        'product' => isset($product) ? $product : NULL,
        'suppliers' => $suppliers,
        'form_action' => $form_action
    )); ?>
    <button type="button" onclick="this.closest('dialog').close();">Close</button>
</dialog>
