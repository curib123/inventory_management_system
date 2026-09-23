<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function login($username, $password) {
        $this->db->select('u.*, r.role_name');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->where('u.username', trim($username));
        $this->db->where('u.status', 1);
        $query = $this->db->get();

        if ($query->num_rows() === 1) {
            $user = $query->row();
            if (password_verify($password, $user->password)) {
                return $user;
            }
        }

        return FALSE;
    }

    public function get_all() {
        $this->db->select('u.id, u.username, u.role_id, u.status, u.created_at, u.updated_at, r.role_name');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->order_by('u.username', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_id($id) {
        $this->db->select('u.id, u.username, u.password, u.role_id, u.status, u.created_at, u.updated_at, r.role_name');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->where('u.id', (int) $id);
        return $this->db->get()->row();
    }

    public function get_active_roles() {
        $this->db->where('status', 1);
        $this->db->order_by('role_name', 'ASC');
        return $this->db->get('roles')->result();
    }

    public function username_exists($username, $exclude_id = NULL) {
        $this->db->where('username', trim($username));
        if ($exclude_id !== NULL) {
            $this->db->where('id !=', (int) $exclude_id);
        }
        return $this->db->count_all_results('users') > 0;
    }

    public function save($data, $id = NULL) {
        if ($id !== NULL) {
            $this->db->where('id', (int) $id);
            return $this->db->update('users', $data);
        }
        return $this->db->insert('users', $data);
    }

    public function has_history($id) {
        $id = (int) $id;
        $tables = array('stock_transactions' => 'created_by', 'stock_adjustments' => 'created_by', 'activity_logs' => 'user_id');
        foreach ($tables as $table => $column) {
            if ($this->db->where($column, $id)->count_all_results($table) > 0) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function delete($id) {
        $this->db->where('id', (int) $id);
        return $this->db->delete('users');
    }

    public function get_user_permissions($user_id) {
        $this->db->select('p.permission_name, p.module_name');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->join('role_permissions rp', 'rp.role_id = r.id', 'left');
        $this->db->join('permissions p', 'p.id = rp.permission_id', 'left');
        $this->db->where('u.id', (int) $user_id);
        $this->db->where('u.status', 1);
        $this->db->where('r.status', 1);
        $this->db->where('p.status', 1);
        $this->db->group_by('p.id');
        return $this->db->get()->result_array();
    }

    public function has_permission($user_id, $permission_name) {
        $this->db->select('COUNT(*) AS total');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id', 'inner');
        $this->db->join('role_permissions rp', 'rp.role_id = r.id', 'inner');
        $this->db->join('permissions p', 'p.id = rp.permission_id', 'inner');
        $this->db->where('u.id', (int) $user_id);
        $this->db->where('u.status', 1);
        $this->db->where('r.status', 1);
        $this->db->where('p.status', 1);
        $this->db->where('p.permission_name', $permission_name);
        $result = $this->db->get()->row();
        return $result && (int) $result->total > 0;
    }
}
