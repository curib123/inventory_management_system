<h2>Suppliers</h2>
<button type="button" onclick="document.getElementById('supplier-form-add').showModal();">Add Supplier</button>
<?php $this->load->view('modal/suppliers/form', array('modal_id' => 'add', 'form_action' => site_url('suppliers/add'))); ?>

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
                    <button type="button" onclick="document.getElementById('supplier-form-<?php echo (int) $supplier->id; ?>').showModal();">Edit</button>
                    <button type="button" onclick="document.getElementById('supplier-details-<?php echo (int) $supplier->id; ?>').showModal();">Details</button>
                    <button type="button" onclick="document.getElementById('supplier-delete-<?php echo (int) $supplier->id; ?>').showModal();">Delete</button>
                </td>
            </tr>
            <?php $this->load->view('modal/suppliers/form', array('modal_id' => $supplier->id, 'supplier' => $supplier, 'form_action' => site_url('suppliers/edit/' . $supplier->id))); ?>
            <?php $this->load->view('modal/suppliers/details', array('supplier' => $supplier)); ?>
            <?php $this->load->view('modal/suppliers/delete', array('supplier' => $supplier)); ?>
        <?php endforeach; else: ?>
            <tr>
                <td colspan="6">No suppliers found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
