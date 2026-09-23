<h2>Users</h2>
<p><a href="<?php echo site_url('users/add'); ?>">Add User</a></p>

<table data-datatable-server data-source="<?php echo site_url('users/datatable'); ?>">
    <thead>
        <tr>
            <th>Username</th>
            <th>Role</th>
            <th>Status</th>
            <th>Created</th>
            <th data-orderable="false">Actions</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
