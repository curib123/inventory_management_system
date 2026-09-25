<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Setup_service {

    private $CI;

    // Setup ni sa Setup_service; gi-load ni sa application/controllers/Setup.php para initial-admin business rules naa ra diri.
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model(array('User_model', 'Role_model'));
    }

    // Business status ni para initial setup; application/controllers/Setup.php ang caller para setup-once ug admin-role prerequisite centralized.
    public function status() {
        if ($this->CI->User_model->count_all() > 0) {
            return array('configured' => TRUE, 'ready' => FALSE);
        }

        $admin_role = $this->CI->Role_model->get_by_name('admin');

        if (!$admin_role || !(int) $admin_role->status) {
            return array(
                'configured' => FALSE,
                'ready' => FALSE,
                'message' => 'The admin role is missing or inactive. Import the current database schema before initial setup.'
            );
        }

        return array('configured' => FALSE, 'ready' => TRUE, 'admin_role' => $admin_role);
    }

    // Business flow ni para create initial admin; application/controllers/Setup.php ang caller, while model row persistence ra ang trabaho.
    public function create_initial_admin($input) {
        $status = $this->status();

        if (!empty($status['configured'])) {
            return array('success' => FALSE, 'configured' => TRUE, 'message' => 'Initial setup is already complete.');
        }

        if (empty($status['ready'])) {
            return array('success' => FALSE, 'message' => $status['message']);
        }

        $username = trim((string) $input['username']);

        if ($this->CI->User_model->username_exists($username)) {
            return array('success' => FALSE, 'message' => 'That username already exists.');
        }

        $middle_name = trim((string) (isset($input['middle_name']) ? $input['middle_name'] : ''));
        $saved = $this->CI->User_model->save(array(
            'first_name' => trim((string) $input['first_name']),
            'middle_name' => $middle_name === '' ? NULL : $middle_name,
            'last_name' => trim((string) $input['last_name']),
            'username' => $username,
            'password' => password_hash((string) $input['password'], PASSWORD_DEFAULT),
            'must_change_password' => 0,
            'role_id' => (int) $status['admin_role']->id,
            'status' => 1
        ));

        return $saved
            ? array('success' => TRUE)
            : array('success' => FALSE, 'message' => 'The administrator account could not be created.');
    }
}
