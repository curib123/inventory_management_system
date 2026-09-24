<div class="modal-header">
    <h2 class="modal-title fs-5">Category Details</h2>
    <button type="button" class="btn-close" data-modal-close aria-label="Close"></button>
</div>

<div class="modal-body">
    <dl class="row mb-0">
        <dt class="col-sm-4">ID</dt><dd class="col-sm-8"><?php echo (int) $category->id; ?></dd>
        <dt class="col-sm-4">Name</dt><dd class="col-sm-8"><?php echo html_escape($category->category_name); ?></dd>
        <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><span class="badge <?php echo $category->status ? 'text-bg-success' : 'text-bg-secondary'; ?>"><?php echo $category->status ? 'Active' : 'Inactive'; ?></span></dd>
        <dt class="col-sm-4">Products</dt><dd class="col-sm-8"><?php echo (int) $product_count; ?></dd>
    </dl>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-modal-close>Close</button>
</div>
