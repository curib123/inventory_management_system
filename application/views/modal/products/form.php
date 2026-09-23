<dialog id="product-form-<?php echo $modal_id; ?>">
    <?php $this->load->view('products/form', array(
        'product' => isset($product) ? $product : NULL,
        'suppliers' => $suppliers,
       'categories' => $categories,
        'form_action' => $form_action
    )); ?>
   
</dialog>
