<h2>Suppliers</h2>
<p><button type="button" data-modal-url="<?php echo site_url('suppliers/add'); ?>">Add Supplier</button></p>

<table data-datatable-server data-source="<?php echo site_url('suppliers/datatable'); ?>">
    <thead>
        <tr>
            <th>Name</th>
            <th>Contact Person</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Status</th>
            <th>Created</th>
            <th>Updated</th>
            <th data-orderable="false">Actions</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<?php $this->load->view('modal/container'); ?>
