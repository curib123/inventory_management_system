<h2>Roles and Permissions</h2>
<button type="button" onclick="document.getElementById('role-form-add').showModal();">Add Role</button>
<?php $this->load->view('modal/roles/form', array('modal_id' => 'add', 'permissions' => $permissions, 'form_action' => site_url('roles/add'))); ?>

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
                    <button type="button" onclick="document.getElementById('role-form-<?php echo (int) $role->id; ?>').showModal();">Edit</button>
                    <button type="button" onclick="document.getElementById('role-details-<?php echo (int) $role->id; ?>').showModal();">Details</button>
                    <?php if ((int) $role->user_count === 0): ?>
                        <button type="button" onclick="document.getElementById('role-delete-<?php echo (int) $role->id; ?>').showModal();">Delete</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php $this->load->view('modal/roles/form', array('modal_id' => $role->id, 'role' => $role, 'permissions' => $permissions, 'selected_permissions' => $this->Role_model->get_role_permissions($role->id), 'form_action' => site_url('roles/edit/' . $role->id))); ?>
            <?php $this->load->view('modal/roles/details', array('role' => $role)); ?>
            <?php $this->load->view('modal/roles/delete', array('role' => $role)); ?>
        <?php endforeach; else: ?>
            <tr><td colspan="5">No roles found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
