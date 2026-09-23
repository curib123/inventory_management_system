<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_all() {
        $this->db->select('r.*, COUNT(DISTINCT u.id) AS user_count');
        $this->db->from('roles r');
        $this->db->join('users u', 'u.role_id = r.id', 'left');
        $this->db->group_by('r.id');
        $this->db->order_by('r.role_name', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_id($id) {
        return $this->db->get_where('roles', array('id' => (int) $id))->row();
    }

    public function name_exists($name, $exclude_id = NULL) {
        $this->db->where('role_name', trim($name));
        if ($exclude_id !== NULL) {
            $this->db->where('id !=', (int) $exclude_id);
        }
        return $this->db->count_all_results('roles') > 0;
    }

    public function save($data, $id = NULL) {
        if ($id !== NULL) {
            return $this->db->update('roles', $data, array('id' => (int) $id));
        }
        return $this->db->insert('roles', $data);
    }

    public function delete($id) {
        $this->db->where('id', (int) $id);
        return $this->db->delete('roles');
    }

    public function get_permissions() {
        $this->db->where('status', 1);
        $this->db->order_by('module_name', 'ASC');
        $this->db->order_by('permission_name', 'ASC');
        return $this->db->get('permissions')->result();
    }

    public function get_role_permissions($role_id) {
        $this->db->select('permission_id');
        $this->db->where('role_id', (int) $role_id);
        $rows = $this->db->get('role_permissions')->result();
        return array_map(function ($row) { return (int) $row->permission_id; }, $rows);
    }

    public function sync_permissions($role_id, $permission_ids) {
        $role_id = (int) $role_id;
        $valid_permissions = array_map(function ($row) { return (int) $row->id; }, $this->get_permissions());
        $permission_ids = array_unique(array_map('intval', (array) $permission_ids));
        $permission_ids = array_values(array_intersect($permission_ids, $valid_permissions));

        $this->db->trans_start();
        $this->db->delete('role_permissions', array('role_id' => $role_id));
        foreach ($permission_ids as $permission_id) {
            $this->db->insert('role_permissions', array('role_id' => $role_id, 'permission_id' => $permission_id));
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function has_users($role_id) {
        return $this->db->where('role_id', (int) $role_id)->count_all_results('users') > 0;
    }
}
