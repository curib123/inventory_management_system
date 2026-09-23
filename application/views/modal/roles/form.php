<dialog id="role-form-<?php echo $modal_id; ?>">
    <?php $this->load->view('roles/form', array(
        'role' => isset($role) ? $role : NULL,
        'permissions' => $permissions,
        'selected_permissions' => isset($selected_permissions) ? $selected_permissions : array(),
        'page_title' => isset($role) ? 'Edit Role' : 'Add Role',
        'form_action' => $form_action
    )); ?>
    <button type="button" onclick="this.closest('dialog').close();">Close</button>
</dialog>
