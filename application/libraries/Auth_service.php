<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_service {

    private $user_model;

    // Setup ni sa Auth_service; CodeIgniter mo-inject sa real User_model, while tests pwede mo-pass mock para clean dependency boundary.
    public function __construct($dependencies = array()) {
        if (isset($dependencies['user_model'])) {
            $this->user_model = $dependencies['user_model'];
            return;
        }

        $CI =& get_instance();
        $CI->load->model('User_model');
        $this->user_model = $CI->User_model;
    }

    // Shared service ni para authenticate; application/controllers/Auth.php credentials ra ang ihatag, then lookup ug password verification diri tanan.
    public function authenticate($username, $password) {
        $user = $this->user_model->find_active_by_username($username);

        if (!$user || !password_verify((string) $password, (string) $user->password)) {
            return FALSE;
        }

        return $this->session_data($user);
    }

    // Business check ni para initial setup; application/controllers/Auth.php ang caller para login controller dili direct mo-query User_model.
    public function has_users() {
        return $this->user_model->count_all() > 0;
    }

    // Shared service ni para session data; application/controllers/Auth.php ang caller after successful credential verification.
    public function session_data($user) {
        return array(
            'user_id' => $user->id,
            'username' => $user->username,
            'role_id' => $user->role_id,
            'role_name' => $user->role_name,
            'must_change_password' => !empty($user->must_change_password),
            'password_change_deferred' => FALSE,
            'logged_in' => TRUE
        );
    }
}
