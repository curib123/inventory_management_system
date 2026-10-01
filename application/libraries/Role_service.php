<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Role_service {

    private $CI;

    // Setup ni sa Role_service; gi-load ni sa application/controllers/Roles.php para role ug permission business rules naa ra diri.
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model(array('Role_model','Activity_log_model'));
        $this->CI->config->load('permissions');
    }

    // Business flow ni para save role ug permissions; application/controllers/Roles.php ang caller, then transaction/dependency rules diri gi-centralize.
    public function save($id, $role_data, $permission_ids, $can_edit_role, $can_manage_permissions) {
        $id = $id === NULL ? NULL : (int) $id;
        $role_name = '';

        if (!$can_edit_role && !$can_manage_permissions) {
            return array('success' => FALSE, 'message' => 'You do not have permission to modify this role.');
        }

        if ($id !== NULL) {
            $current_role = $this->CI->Role_model->get_by_id($id);

            if (!$current_role) {
                return array('success' => FALSE, 'message' => 'Role not found.');
            }

            $role_name = trim((string) $current_role->role_name);
        }

        if ($can_edit_role) {
            $role_name = trim((string) (isset($role_data['role_name']) ? $role_data['role_name'] : ''));

            if ($this->CI->Role_model->name_exists($role_name, $id)) {
                return array('success' => FALSE, 'message' => 'That role name already exists.');
            }

            $role_data['role_name'] = $role_name;
        }

        $normalized_permissions = array();

        if ($can_manage_permissions) {
            $normalized_permissions = $this->normalize_permissions($permission_ids);

            if ($normalized_permissions === FALSE) {
                return array(
                    'success' => FALSE,
                    'message' => 'One or more selected permissions are invalid or have a missing dependency.'
                );
            }
        }

        $this->CI->db->trans_begin();
        $this->CI->Role_model->lock_admin_invariant();
        $role_id = $id;

        if ($can_edit_role) {
            $role_id = $this->CI->Role_model->save($role_data, $id);

            if ($role_id === FALSE) {
                $this->CI->db->trans_rollback();
                return array('success' => FALSE, 'message' => 'The role could not be saved.');
            }
        }

        if ($can_manage_permissions) {
            if (!$role_id || !$this->CI->Role_model->replace_permissions($role_id, $normalized_permissions)) {
                $this->CI->db->trans_rollback();
                return array('success' => FALSE, 'message' => 'The role permissions could not be saved.');
            }
        }

        if ($this->CI->db->trans_status() === FALSE) {
            $this->CI->db->trans_rollback();
            return array(
                'success' => FALSE,
                'message' => 'The role and permissions could not be saved. No changes were committed.'
            );
        }

        if ($this->CI->Role_model->count_admin_capable_users() < 1) {
            $this->CI->db->trans_rollback();
            return array(
                'success' => FALSE,
                'message' => 'This change would leave no active administrator able to view and manage roles. Keep at least one active administrator with both role-management permissions.'
            );
        }

        $this->CI->db->trans_commit();

           if (!$can_edit_role && $can_manage_permissions) { $message = 'Role permissions updated successfully.'; $action = 'role_permissions_updated'; $description = 'Updated permissions for role: ' . $role_name; } elseif ($id === NULL) { $message = 'Role created successfully.'; $action = 'role_created'; $description = 'Created role: ' . $role_name; } else { $message = 'Role changes saved successfully.'; $action = 'role_updated'; $description = 'Updated role: ' . $role_name; } 
  
          $user_id = (int) $this->CI->session->userdata('user_id'); 
          
          $this->CI->Activity_log_model->insert_activity_log(array( 'user_id' => $user_id, 'action' => $action, 'description' => $description, 'ip_address' => $this->CI->input->ip_address() ));
        return array('success' => TRUE, 'role_id' => (int) $role_id, 'message' => $message);
    }

    // Business flow ni para delete role; application/controllers/Roles.php ang caller, while assigned-user rule diri gi-enforce.
    
    public function delete($id, $execute = TRUE) {
    $id = (int) $id;

    $role = $this->CI->Role_model->get_by_id($id);

    if (!$role) {
        return array(
            'success' => FALSE,
            'not_found' => TRUE,
            'message' => 'Role not found.'
        );
    }

    if ($this->CI->Role_model->has_users($id)) {
        return array(
            'success' => FALSE,
            'message' => 'This role cannot be deleted while users are assigned to it.'
        );
    }

    // Used for confirmation/preview before actually deleting
    if (!$execute) {
        return array(
            'success' => TRUE,
            'role' => $role
        );
    }

    // Delete role
    if (!$this->CI->Role_model->delete($id)) {
        return array(
            'success' => FALSE,
            'message' => 'The role could not be deleted.'
        );
    }

    // Get logged-in user
    $user_id = (int) $this->CI->session->userdata('user_id');

    // Activity log
    $this->CI->Activity_log_model->insert_activity_log(array(
        'user_id'     => $user_id,
        'action'      => 'role_deleted',
        'description' => 'Deleted role: ' . $role->role_name,
        'ip_address'  => $this->CI->input->ip_address()
    ));

    return array(
        'success' => TRUE,
        'role' => $role
    );
   }

    // Internal helper ni para normalize permissions; tawagon ra sulod application/libraries/Role_service.php para validate IDs ug auto-add required dependencies.
    private function normalize_permissions($permission_ids) {
        $normalized = array();

        foreach ((array) $permission_ids as $permission_id) {
            if (!is_scalar($permission_id)) {
                return FALSE;
            }

            $permission_id = filter_var(
                $permission_id,
                FILTER_VALIDATE_INT,
                array('options' => array('min_range' => 1))
            );

            if ($permission_id === FALSE) {
                return FALSE;
            }

            $normalized[] = (int) $permission_id;
        }

        $normalized = array_values(array_unique($normalized));
        $permission_rows = $this->CI->Role_model->get_permissions();
        $valid_permissions = array();
        $permission_id_by_key = array();
        $permission_key_by_id = array();

        foreach ($permission_rows as $permission) {
            $permission_id = (int) $permission->id;
            $permission_key = (string) $permission->permission_key;
            $valid_permissions[] = $permission_id;
            $permission_id_by_key[$permission_key] = $permission_id;
            $permission_key_by_id[$permission_id] = $permission_key;
        }

        if (!empty(array_diff($normalized, $valid_permissions))) {
            return FALSE;
        }

        $dependencies = (array) $this->CI->config->item('permission_dependencies');

        foreach ($normalized as $permission_id) {
            if (!isset($permission_key_by_id[$permission_id])) {
                return FALSE;
            }

            $permission_key = $permission_key_by_id[$permission_id];

            foreach ((array) (isset($dependencies[$permission_key]) ? $dependencies[$permission_key] : array()) as $required_key) {
                if (!isset($permission_id_by_key[$required_key])) {
                    return FALSE;
                }

                $normalized[] = $permission_id_by_key[$required_key];
            }
        }

        return array_values(array_unique($normalized));
    }
    }
