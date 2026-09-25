<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    private $permission_key_cache = array();

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
        $this->db->where('r.status', 1);
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
        $this->db->select('u.id, u.first_name, u.middle_name, u.last_name, u.username, u.role_id, u.status, u.created_at, u.updated_at, r.role_name');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->order_by('u.last_name', 'ASC');
        $this->db->order_by('u.first_name', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_id($id) {
        $this->db->select('u.id, u.first_name, u.middle_name, u.last_name, u.username, u.role_id, u.status, u.created_at, u.updated_at, r.role_name');
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

    public function get_role_by_id($id) {
        return $this->db->get_where('roles', array('id' => (int) $id))->row();
    }

    public function search_active_roles($query = '', $limit = 20) {
        $query = trim((string) $query);
        $limit = max(1, min(50, (int) $limit));

        $this->db->select('id, role_name, description');
        $this->db->from('roles');
        $this->db->where('status', 1);

        if ($query !== '') {
            $this->db->group_start();
            $this->db->like('role_name', $query);
            $this->db->or_like('description', $query);
            $this->db->group_end();
        }

        $this->db->order_by('role_name', 'ASC');
        $this->db->limit($limit);

        return $this->db->get()->result();
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
            $saved = $this->db->update('users', $data);

            if ($saved) {
                unset($this->permission_key_cache[(int) $id]);
            }

            return $saved;
        }

        return $this->db->insert('users', $data);
    }

    public function has_history($id) {
        $id = (int) $id;
        $tables = array(
            'stock_transactions' => 'created_by',
            'stock_adjustments' => 'created_by',
            'activity_logs' => 'user_id'
        );

        foreach ($tables as $table => $column) {
            if ($this->db->where($column, $id)->count_all_results($table) > 0) {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function delete($id) {
        unset($this->permission_key_cache[(int) $id]);
        return $this->db->delete('users', array('id' => (int) $id));
    }

    public function get_user_permissions($user_id) {
        $this->db->distinct();
        $this->db->select(
            'p.id, p.permission_name, p.permission_key, p.action, p.description, ' .
            'm.module_name, m.module_key, m.sort_order'
        );
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id', 'inner');
        $this->db->join('role_permissions rp', 'rp.role_id = r.id', 'inner');
        $this->db->join('permissions p', 'p.id = rp.permission_id', 'inner');
        $this->db->join('modules m', 'm.id = p.module_id', 'inner');
        $this->db->where('u.id', (int) $user_id);
        $this->db->where('u.status', 1);
        $this->db->where('r.status', 1);
        $this->db->where('p.status', 1);
        $this->db->where('m.status', 1);
        $this->db->order_by('m.sort_order', 'ASC');
        $this->db->order_by('p.permission_name', 'ASC');

        return $this->db->get()->result_array();
    }

    public function get_user_permission_keys($user_id) {
        $user_id = (int) $user_id;

        if ($user_id <= 0) {
            return array();
        }

        if (isset($this->permission_key_cache[$user_id])) {
            return $this->permission_key_cache[$user_id];
        }

        $permissions = $this->get_user_permissions($user_id);
        $keys = array();

        foreach ($permissions as $permission) {
            if (!empty($permission['permission_key'])) {
                $keys[] = $permission['permission_key'];
            }
        }

        $this->permission_key_cache[$user_id] = array_values(array_unique($keys));
        return $this->permission_key_cache[$user_id];
    }

    public function has_permission($user_id, $permission_key) {
        return in_array(
            (string) $permission_key,
            $this->get_user_permission_keys($user_id),
            TRUE
        );
    }

    public function verify_password($user_id, $password) {
        $row = $this->db
            ->select('password')
            ->where('id', (int) $user_id)
            ->where('status', 1)
            ->get('users')
            ->row();

        return $row && password_verify((string) $password, $row->password);
    }

    public function update_password($user_id, $password) {
        unset($this->permission_key_cache[(int) $user_id]);

        return $this->db->update(
            'users',
            array(
                'password' => password_hash((string) $password, PASSWORD_DEFAULT)
            ),
            array('id' => (int) $user_id)
        );
    }

    public function has_any_permission($user_id, $permission_keys) {
        $user_permissions = $this->get_user_permission_keys($user_id);

        foreach ((array) $permission_keys as $permission_key) {
            if (in_array((string) $permission_key, $user_permissions, TRUE)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function count_all() {
        return $this->db->count_all('users');
    }

    public function get_datatable($start, $length, $search, $order_column, $order_dir, $filters = array()) {
        $this->build_datatable_query($search, $filters);
        $this->db->select('u.id, u.first_name, u.middle_name, u.last_name, u.username, u.status, u.created_at, u.updated_at, r.role_name');

        if ($order_column) {
            $this->db->order_by($order_column, $order_dir);
        }

        $this->db->order_by('u.id', 'ASC');
        $this->db->limit((int) $length, (int) $start);
        return $this->db->get()->result();
    }

    public function count_datatable_filtered($search, $filters = array()) {
        $this->build_datatable_query($search, $filters);
        return $this->db->count_all_results();
    }

    private function build_datatable_query($search, $filters = array()) {
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');

        $status = isset($filters['status']) ? strtolower((string) $filters['status']) : '';
        if ($status === 'active') {
            $this->db->where('u.status', 1);
        } elseif ($status === 'inactive') {
            $this->db->where('u.status', 0);
        }

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('u.first_name', $search);
            $this->db->or_like('u.middle_name', $search);
            $this->db->or_like('u.last_name', $search);
            $this->db->or_like('u.username', $search);
            $this->db->or_like('r.role_name', $search);
            $this->db->or_like('u.created_at', $search);
            $this->db->or_like('u.updated_at', $search);

            if (strcasecmp($search, 'active') === 0) {
                $this->db->or_where('u.status', 1);
            } elseif (strcasecmp($search, 'inactive') === 0) {
                $this->db->or_where('u.status', 0);
            }

            $this->db->group_end();
        }
    }
}
