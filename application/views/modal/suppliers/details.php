<div class="modal-header">
    <h2 class="modal-title fs-5">Supplier Details</h2>
    <button type="button" class="btn-close" data-modal-close aria-label="Close"></button>
</div>

<div class="modal-body">
    <dl class="row mb-0">
        <dt class="col-sm-4">ID</dt><dd class="col-sm-8"><?php echo (int) $supplier->id; ?></dd>
        <dt class="col-sm-4">Name</dt><dd class="col-sm-8"><?php echo html_escape($supplier->supplier_name); ?></dd>
        <dt class="col-sm-4">Contact Person</dt><dd class="col-sm-8"><?php echo html_escape($supplier->contact_person ?: 'N/A'); ?></dd>
        <dt class="col-sm-4">Phone</dt><dd class="col-sm-8"><?php echo html_escape($supplier->phone ?: 'N/A'); ?></dd>
        <dt class="col-sm-4">Address</dt><dd class="col-sm-8"><?php echo html_escape($supplier->address ?: 'N/A'); ?></dd>
        <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><span class="badge <?php echo $supplier->status ? 'text-bg-success' : 'text-bg-secondary'; ?>"><?php echo $supplier->status ? 'Active' : 'Inactive'; ?></span></dd>
    </dl>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-modal-close>Close</button>
</div>
