<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends CI_Model {

    // Setup ni sa Role_model; CodeIgniter mo-run ani when gi-load ang model, with main integration sa application/controllers/Roles.php ug application/controllers/Users.php.
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Data helper ni para get all; main caller/integration pangitaa sa application/controllers/Roles.php ug application/controllers/Users.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_all() {
        $this->db->select(
            'r.*, COUNT(DISTINCT u.id) AS user_count, ' .
            'COUNT(DISTINCT rp.permission_id) AS permission_count'
        );
        $this->db->from('roles r');
        $this->db->join('users u', 'u.role_id = r.id', 'left');
        $this->db->join('role_permissions rp', 'rp.role_id = r.id', 'left');
        $this->db->group_by(array('r.id', 'r.role_name', 'r.description', 'r.status'));
        $this->db->order_by('r.role_name', 'ASC');
        return $this->db->get()->result();
    }

    // Data helper ni para get by id; main caller/integration pangitaa sa application/controllers/Roles.php ug application/controllers/Users.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_by_id($id) {
        return $this->db->get_where('roles', array('id' => (int) $id))->row();
    }

    // Data helper ni para get by name; main caller/integration pangitaa sa application/controllers/Roles.php ug application/controllers/Users.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_by_name($name) {
        return $this->db->get_where(
            'roles',
            array('role_name' => trim((string) $name))
        )->row();
    }

    // Data helper ni para name exists; main caller/integration pangitaa sa application/controllers/Roles.php ug application/controllers/Users.php, so didto tan-awa ang business flow if mag-trace ka.
    public function name_exists($name, $exclude_id = NULL) {
        $this->db->where('role_name', trim($name));

        if ($exclude_id !== NULL) {
            $this->db->where('id !=', (int) $exclude_id);
        }

        return $this->db->count_all_results('roles') > 0;
    }

    // Data helper ni para save; main caller/integration pangitaa sa application/controllers/Roles.php ug application/controllers/Users.php, so didto tan-awa ang business flow if mag-trace ka.
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

    // Data helper ni para delete; main caller/integration pangitaa sa application/controllers/Roles.php ug application/controllers/Users.php, so didto tan-awa ang business flow if mag-trace ka.
    public function delete($id) {
        return $this->db->delete('roles', array('id' => (int) $id));
    }

    // Data helper ni para get permissions; main caller/integration pangitaa sa application/controllers/Roles.php ug application/controllers/Users.php, so didto tan-awa ang business flow if mag-trace ka.
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

    // Data helper ni para get role permissions; main caller/integration pangitaa sa application/controllers/Roles.php ug application/controllers/Users.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_role_permissions($role_id) {
        $this->db->select('permission_id');
        $this->db->where('role_id', (int) $role_id);
        $rows = $this->db->get('role_permissions')->result();

        return array_map(function ($row) {
            return (int) $row->permission_id;
        }, $rows);
    }

    // Data helper ni para get role permission details; main caller/integration pangitaa sa application/controllers/Roles.php ug application/controllers/Users.php, so didto tan-awa ang business flow if mag-trace ka.
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

    // Persistence helper ni para replace permissions; application/libraries/Role_service.php ang caller, while validation/dependency rules didto tanan.
    public function replace_permissions($role_id, $permission_ids) {
        $role_id = (int) $role_id;

        if (!$this->db->delete('role_permissions', array('role_id' => $role_id))) {
            return FALSE;
        }

        foreach ((array) $permission_ids as $permission_id) {
            if (!$this->db->insert('role_permissions', array(
                'role_id' => $role_id,
                'permission_id' => (int) $permission_id
            ))) {
                return FALSE;
            }
        }

        return TRUE;
    }

    // Data helper ni para has users; main caller/integration pangitaa sa application/controllers/Roles.php ug application/controllers/Users.php, so didto tan-awa ang business flow if mag-trace ka.
    public function has_users($role_id) {
        return $this->count_users($role_id) > 0;
    }

    // Data helper ni para count users; main caller/integration pangitaa sa application/controllers/Roles.php ug application/controllers/Users.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_users($role_id) {
        return $this->db
            ->where('role_id', (int) $role_id)
            ->count_all_results('users');
    }

    // Data helper ni para count all; main caller/integration pangitaa sa application/controllers/Roles.php ug application/controllers/Users.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_all() {
        return $this->db->count_all('roles');
    }

    // Data helper ni para get datatable; main caller/integration pangitaa sa application/controllers/Roles.php ug application/controllers/Users.php, so didto tan-awa ang business flow if mag-trace ka.
    public function get_datatable($start, $length, $search, $order_column, $order_dir, $filters = array()) {
        $this->db->select(
            'r.id, r.role_name, r.description, r.status, ' .
            'COUNT(DISTINCT u.id) AS user_count, ' .
            'COUNT(DISTINCT rp.permission_id) AS permission_count'
        );
        $this->db->from('roles r');
        $this->db->join('users u', 'u.role_id = r.id', 'left');
        $this->db->join('role_permissions rp', 'rp.role_id = r.id', 'left');
        $this->apply_datatable_search($search, $filters);
        $this->db->group_by(array('r.id', 'r.role_name', 'r.description', 'r.status'));

        if ($order_column) {
            $this->db->order_by($order_column, $order_dir);
        }

        $this->db->limit((int) $length, (int) $start);
        return $this->db->get()->result();
    }

    // Data helper ni para count datatable filtered; main caller/integration pangitaa sa application/controllers/Roles.php ug application/controllers/Users.php, so didto tan-awa ang business flow if mag-trace ka.
    public function count_datatable_filtered($search, $filters = array()) {
        $this->db->from('roles r');
        $this->apply_datatable_search($search, $filters);
        return $this->db->count_all_results();
    }

    // Data helper ni para apply datatable search; main caller/integration pangitaa sa application/controllers/Roles.php ug application/controllers/Users.php, so didto tan-awa ang business flow if mag-trace ka.
    private function apply_datatable_search($search, $filters = array()) {
        $status = isset($filters['status']) ? strtolower((string) $filters['status']) : '';
        if ($status === 'active') {
            $this->db->where('r.status', 1);
        } elseif ($status === 'inactive') {
            $this->db->where('r.status', 0);
        }

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
