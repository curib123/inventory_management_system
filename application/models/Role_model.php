<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends CI_Model {
    
    // roles model to handle role management functionality
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    // Function to get all roles with the count of users assigned to each role
    public function get_all() {
        $this->db->select('r.*, COUNT(DISTINCT u.id) AS user_count');
        $this->db->from('roles r');
        $this->db->join('users u', 'u.role_id = r.id', 'left');
        $this->db->group_by('r.id');
        $this->db->order_by('r.role_name', 'ASC');
        return $this->db->get()->result();
    }
    // Function to get a role by its ID
    public function get_by_id($id) {
        return $this->db->get_where('roles', array('id' => (int) $id))->row();
    }
    // Function to save a new role or update an existing role
    public function save($data, $id = NULL) {
        if ($id) {
            return $this->db->update('roles', $data, array('id' => (int) $id));
        }

        return $this->db->insert('roles', $data);
    }
    // Function to delete a role by its ID
    public function delete($id) {
        $this->db->where('id', (int) $id);
        return $this->db->delete('roles');
    }
    // Function to get all permissions
    public function get_permissions() {
        $this->db->where('status', 1);
        $this->db->order_by('module_name', 'ASC');
        $this->db->order_by('permission_name', 'ASC');
        return $this->db->get('permissions')->result();
    }
   // Function to get the permissions assigned to a specific role
    public function get_role_permissions($role_id) {
        $this->db->select('permission_id');
        $this->db->where('role_id', (int) $role_id);
        $rows = $this->db->get('role_permissions')->result();

        return array_map(function ($row) {
            return (int) $row->permission_id;
        }, $rows);
    }
    // Function to synchronize the permissions assigned to a specific role
    public function sync_permissions($role_id, $permission_ids) {
        $role_id = (int) $role_id;
        $permission_ids = array_unique(array_map('intval', (array) $permission_ids));

        $this->db->trans_start();
        $this->db->delete('role_permissions', array('role_id' => $role_id));

        foreach ($permission_ids as $permission_id) {
            if ($permission_id > 0) {
                $this->db->insert('role_permissions', array(
                    'role_id' => $role_id,
                    'permission_id' => $permission_id
                ));
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
    // Function to check if a role has any users assigned to it
    public function has_users($role_id) {
        return $this->db->where('role_id', (int) $role_id)
            ->count_all_results('users') > 0;
    }
}
