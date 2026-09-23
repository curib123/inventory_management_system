<dialog id="supplier-details-<?php echo (int) $supplier->id; ?>">
    <h3>Supplier Details</h3>
    <p>Name: <?php echo html_escape($supplier->supplier_name); ?></p>
    <p>Contact Person: <?php echo html_escape($supplier->contact_person); ?></p>
    <p>Phone: <?php echo html_escape($supplier->phone); ?></p>
    <p>Address: <?php echo html_escape($supplier->address); ?></p>
    <button type="button" onclick="this.closest('dialog').close();">Close</button>
</dialog>
