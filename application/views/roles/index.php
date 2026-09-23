<h2>Roles and Permissions</h2>
<p><button type="button" data-modal-url="<?php echo site_url('roles/add'); ?>">Add Role</button></p>

<table data-datatable-server data-source="<?php echo site_url('roles/datatable'); ?>">
    <thead>
        <tr>
            <th>Role</th>
            <th>Description</th>
            <th>Status</th>
            <th>Users</th>
            <th data-orderable="false">Actions</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<?php $this->load->view('modal/container'); ?>
