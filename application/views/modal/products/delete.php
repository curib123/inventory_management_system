<div class="container-fluid bg-light roounded d-flex justify-content-center align-items-center">
  <div class="container">
    <div class="bg-danger-subtle text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
     <i class="bi bi-trash3 fs-3"></i>
    </div>
    <h2 class="text-dark p-2">Delete Product</h2>
      <p class="text-muted">Product: <?php echo html_escape($product->product_name); ?></p>
<?php if (!empty($delete_error)): ?>
    <p><?php echo html_escape($delete_error); ?></p>
    <button type="button" data-modal-close>Close</button>
<?php else: ?>
    <p class="text-danger ">Are you sure you want to delete this product?</p>
    <?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
       <div class="container justify-content-end align-items-center">
          <button type="submit">Delete Product</button>
        <button type="button" data-modal-close>Cancel</button>
       </div>
    <?php echo form_close(); ?>
<?php endif; ?>
  </div>
</div>