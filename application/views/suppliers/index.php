<h2>Suppliers</h2>
<a class="btn btn-success" href="<?php echo site_url('suppliers/add'); ?>">Add Supplier</a>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Contact Person</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($suppliers)): foreach ($suppliers as $supplier): ?>
            <tr>
                <td><?php echo $supplier->id; ?></td>
                <td><?php echo $supplier->supplier_name; ?></td>
                <td><?php echo $supplier->contact_person; ?></td>
                <td><?php echo $supplier->phone; ?></td>
                <td><?php echo $supplier->address; ?></td>
                <td>
                    <a class="btn" href="<?php echo site_url('suppliers/edit/' . $supplier->id); ?>">Edit</a>
                    <a class="btn btn-danger" href="<?php echo site_url('suppliers/delete/' . $supplier->id); ?>" onclick="return confirm('Delete this supplier?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; else: ?>
            <tr>
                <td colspan="6">No suppliers found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
