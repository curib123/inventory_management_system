<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    // users model to handle user management functionality
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    // Function to authenticate a user based on username and password
    public function login($username, $password) {
        $this->db->select('u.*, r.role_name');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->where('u.username', $username);
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
    // Function to get all users with their role names
    public function get_all() {
        $this->db->select('u.*, r.role_name');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->order_by('u.id', 'DESC');
        return $this->db->get()->result();
    }
    // Function to get a user by its ID with its role name
    public function get_by_id($id) {
        $this->db->select('u.*, r.role_name');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->where('u.id', $id);
        return $this->db->get()->row();
    }
    // Function to save a new user or update an existing user
    public function save($data, $id = NULL) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->update('users', $data);
        }

        return $this->db->insert('users', $data);
    }
    // Function to delete a user by its ID
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('users');
    }
    // Function to get the total number of users
    public function get_user_permissions($user_id) {
        $this->db->select('p.permission_name, p.module_name');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->join('role_permissions rp', 'rp.role_id = r.id', 'left');
        $this->db->join('permissions p', 'p.id = rp.permission_id', 'left');
        $this->db->where('u.id', $user_id);
        $this->db->group_by('p.id');
        return $this->db->get()->result_array();
    }
    // Function to check if a user has a specific permission
    public function has_permission($user_id, $permission_name) {
        $this->db->select('COUNT(*) AS total');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id', 'left');
        $this->db->join('role_permissions rp', 'rp.role_id = r.id', 'left');
        $this->db->join('permissions p', 'p.id = rp.permission_id', 'left');
        $this->db->where('u.id', $user_id);
        $this->db->where('p.permission_name', $permission_name);
        $query = $this->db->get();
        $result = $query->row();

        return ($result && $result->total > 0);
    }
    // Function to create a default admin user if it doesn't exist
    public function create_default_admin() {
        $this->db->where('role_name', 'admin');
        $role_query = $this->db->get('roles');

        if ($role_query->num_rows() === 0) {
            $this->db->insert('roles', array(
                'role_name' => 'admin',
                'description' => 'Full system access',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ));
        }

        $role = $this->db->get_where('roles', array('role_name' => 'admin'))->row();

        $this->db->where('username', 'admin');
        $query = $this->db->get('users');

        if ($query->num_rows() === 0) {
            $data = array(
                'username' => 'admin',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role_id' => $role->id,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s')
            );

            return $this->db->insert('users', $data);
        }

        return TRUE;
    }
}
