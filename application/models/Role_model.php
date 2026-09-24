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
        $this->db->group_by(array('r.id', 'r.role_name', 'r.description', 'r.status'));
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
            $saved = $this->db->update('roles', $data, array('id' => (int) $id));
            return $saved ? (int) $id : FALSE;
        }

        if (!$this->db->insert('roles', $data)) {
            return FALSE;
        }

        return (int) $this->db->insert_id();
    }

    public function delete($id) {
        return $this->db->delete('roles', array('id' => (int) $id));
    }

    // Permissions now belong to modules. Always read module data through the modules table.
    public function get_permissions() {
        $this->db->select(
            'p.id, p.module_id, p.permission_name, p.permission_key, p.action, ' .
            'p.description, p.status, m.module_name, m.module_key, m.sort_order'
        );
        $this->db->from('permissions p');
        $this->db->join('modules m', 'm.id = p.module_id', 'inner');
        $this->db->where('p.status', 1);
        $this->db->where('m.status', 1);
        $this->db->order_by('m.sort_order', 'ASC');
        $this->db->order_by('m.module_name', 'ASC');
        $this->db->order_by('p.permission_name', 'ASC');

        return $this->db->get()->result();
    }

    public function get_role_permissions($role_id) {
        $this->db->select('permission_id');
        $this->db->where('role_id', (int) $role_id);
        $rows = $this->db->get('role_permissions')->result();

        return array_map(function ($row) {
            return (int) $row->permission_id;
        }, $rows);
    }

    public function get_role_permission_details($role_id) {
        $this->db->select(
            'p.id, p.permission_name, p.permission_key, p.action, p.description, ' .
            'm.module_name, m.module_key, m.sort_order'
        );
        $this->db->from('role_permissions rp');
        $this->db->join('permissions p', 'p.id = rp.permission_id', 'inner');
        $this->db->join('modules m', 'm.id = p.module_id', 'inner');
        $this->db->where('rp.role_id', (int) $role_id);
        $this->db->where('p.status', 1);
        $this->db->where('m.status', 1);
        $this->db->order_by('m.sort_order', 'ASC');
        $this->db->order_by('m.module_name', 'ASC');
        $this->db->order_by('p.permission_name', 'ASC');

        return $this->db->get()->result();
    }

    public function sync_permissions($role_id, $permission_ids) {
        $role_id = (int) $role_id;

        if ($role_id <= 0 || !$this->get_by_id($role_id)) {
            return FALSE;
        }

        $valid_permissions = array_map(function ($row) {
            return (int) $row->id;
        }, $this->get_permissions());

        $permission_ids = array_unique(array_map('intval', (array) $permission_ids));
        $permission_ids = array_values(array_intersect($permission_ids, $valid_permissions));

        $this->db->trans_begin();

        $this->db->delete('role_permissions', array('role_id' => $role_id));

        foreach ($permission_ids as $permission_id) {
            $this->db->insert('role_permissions', array(
                'role_id' => $role_id,
                'permission_id' => $permission_id
            ));

            if ($this->db->affected_rows() < 1) {
                $this->db->trans_rollback();
                return FALSE;
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        return TRUE;
    }

    public function has_users($role_id) {
        return $this->count_users($role_id) > 0;
    }

    public function count_users($role_id) {
        return $this->db
            ->where('role_id', (int) $role_id)
            ->count_all_results('users');
    }

    public function count_all() {
        return $this->db->count_all('roles');
    }

    public function get_datatable($start, $length, $search, $order_column, $order_dir) {
        $this->db->select('r.id, r.role_name, r.description, r.status, COUNT(DISTINCT u.id) AS user_count');
        $this->db->from('roles r');
        $this->db->join('users u', 'u.role_id = r.id', 'left');
        $this->apply_datatable_search($search);
        $this->db->group_by(array('r.id', 'r.role_name', 'r.description', 'r.status'));

        if ($order_column) {
            $this->db->order_by($order_column, $order_dir);
        }

        $this->db->limit((int) $length, (int) $start);
        return $this->db->get()->result();
    }

    public function count_datatable_filtered($search) {
        $this->db->from('roles r');
        $this->apply_datatable_search($search);
        return $this->db->count_all_results();
    }

    private function apply_datatable_search($search) {
        if ($search === '') {
            return;
        }

        $this->db->group_start();
        $this->db->like('r.role_name', $search);
        $this->db->or_like('r.description', $search);

        if (strcasecmp($search, 'active') === 0) {
            $this->db->or_where('r.status', 1);
        } elseif (strcasecmp($search, 'inactive') === 0) {
            $this->db->or_where('r.status', 0);
        }

        $this->db->group_end();
    }
}
