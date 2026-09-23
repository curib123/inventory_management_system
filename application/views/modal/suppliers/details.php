<h2>Supplier Details</h2>
<dl>
    <dt>ID</dt><dd><?php echo (int) $supplier->id; ?></dd>
    <dt>Name</dt><dd><?php echo html_escape($supplier->supplier_name); ?></dd>
    <dt>Contact Person</dt><dd><?php echo html_escape($supplier->contact_person ?: 'N/A'); ?></dd>
    <dt>Phone</dt><dd><?php echo html_escape($supplier->phone ?: 'N/A'); ?></dd>
    <dt>Address</dt><dd><?php echo html_escape($supplier->address ?: 'N/A'); ?></dd>
    <dt>Status</dt><dd><?php echo $supplier->status ? 'Active' : 'Inactive'; ?></dd>
</dl>
<button type="button" data-modal-close>Close</button>
