<h2>Roles and Permissions</h2>
<a class="btn btn-success" href="<?php echo site_url('roles/add'); ?>">Add Role</a>

<table>
    <thead>
        <tr>
            <th>Role</th>
            <th>Description</th>
            <th>Status</th>
            <th>Users</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($roles)): foreach ($roles as $role): ?>
            <tr>
                <td><?php echo html_escape($role->role_name); ?></td>
                <td><?php echo html_escape($role->description); ?></td>
                <td><?php echo $role->status ? 'Active' : 'Inactive'; ?></td>
                <td><?php echo (int) $role->user_count; ?></td>
                <td>
                    <a class="btn" href="<?php echo site_url('roles/edit/' . $role->id); ?>">Edit</a>
                    <?php if ((int) $role->user_count === 0): ?>
                        <a class="btn btn-danger" href="<?php echo site_url('roles/delete/' . $role->id); ?>" onclick="return confirm('Delete this role?');">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="5">No roles found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
