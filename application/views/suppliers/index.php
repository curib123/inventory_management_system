<h2>Suppliers</h2>
<p><a href="<?php echo site_url('suppliers/add'); ?>">Add Supplier</a></p>

<table>
    <thead>
        <tr><th>Name</th><th>Contact Person</th><th>Phone</th><th>Address</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <?php if (!empty($suppliers)): foreach ($suppliers as $supplier): ?>
            <tr>
                <td><?php echo html_escape($supplier->supplier_name); ?></td>
                <td><?php echo html_escape($supplier->contact_person); ?></td>
                <td><?php echo html_escape($supplier->phone); ?></td>
                <td><?php echo html_escape($supplier->address); ?></td>
                <td><?php echo $supplier->status ? 'Active' : 'Inactive'; ?></td>
                <td>
                    <a href="<?php echo site_url('suppliers/edit/' . (int) $supplier->id); ?>">Edit</a>
                    <?php echo form_open('suppliers/delete/' . (int) $supplier->id); ?>
                        <button type="submit">Delete</button>
                    <?php echo form_close(); ?>
                </td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="6">No suppliers found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
