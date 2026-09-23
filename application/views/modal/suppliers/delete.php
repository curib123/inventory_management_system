<dialog id="supplier-delete-<?php echo (int) $supplier->id; ?>">
    <h3>Delete Supplier</h3>
    <p>Delete <?php echo html_escape($supplier->supplier_name); ?>?</p>
    <a href="<?php echo site_url('suppliers/delete/' . $supplier->id); ?>">Delete</a>
    <button type="button" onclick="this.closest('dialog').close();">Cancel</button>
</dialog>
