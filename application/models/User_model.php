<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function login($username, $password) {
        $this->db->where('username', $username);
        $this->db->where('status', 1);
        $query = $this->db->get('users');

        if ($query->num_rows() === 1) {
            $user = $query->row();
            if (password_verify($password, $user->password)) {
                return $user;
            }
        }

        return FALSE;
    }

    public function create_default_admin() {
        $this->db->where('username', 'admin');
        $query = $this->db->get('users');

        if ($query->num_rows() === 0) {
            $data = array(
                'username' => 'admin',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s')
            );

            return $this->db->insert('users', $data);
        }

        return TRUE;
    }
}
