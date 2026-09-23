<h2>User Management</h2>
<p><button type="button" data-modal-url="<?php echo site_url('users/add'); ?>">Add User</button></p>

<table data-datatable-server data-source="<?php echo site_url('users/datatable'); ?>">
    <thead>
        <tr>
            <th>First Name</th>
            <th>Middle Name</th>
            <th>Last Name</th>
            <th>Username</th>
            <th>Role</th>
            <th>Status</th>
            <th>Created</th>
            <th data-orderable="false">Actions</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<?php $this->load->view('modal/container'); ?>
