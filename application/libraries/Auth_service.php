
<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_service {

    private $user_model;
    private $CI;

    // Setup sa Auth_service.
    // CodeIgniter mo-inject sa real User_model, while tests pwede mo-pass mock.
    public function __construct($dependencies = array()) {

        if (isset($dependencies['user_model'])) {
            $this->user_model = $dependencies['user_model'];

            // If a CI instance is explicitly provided, use it.
            if (isset($dependencies['CI'])) {
                $this->CI = $dependencies['CI'];
            }

            return;
        }

        if (function_exists('get_instance')) {
            $this->CI =& get_instance();

            $this->CI->load->model(array(
                'User_model',
                'Activity_log_model'
            ));

            $this->user_model = $this->CI->User_model;
        }
    }

    // Shared service para authenticate.
    public function authenticate($username, $password) {

        if (!$this->user_model) {
            return FALSE;
        }

        $user = $this->user_model->find_active_by_username($username);

        if (
            !$user ||
            !password_verify(
                (string) $password,
                (string) $user->password
            )
        ) {
            return FALSE;
        }

        // Log successful login.
        $this->CI->Activity_log_model->insert_activity_log(array(
            'user_id' => $user->id,
            'action' => 'login_created',
            'description' => 'Login Session',
            'ip_address' => $this->CI->input->ip_address()
        ));

        return $this->session_data($user);
    }

    // Business check para initial setup.
    public function has_users() {
        return $this->user_model &&
               $this->user_model->count_all() > 0;
    }

    // Shared service para session data.
    public function session_data($user) {
        return array(
            'user_id' => $user->id,
            'first_name' => isset($user->first_name)
                ? $user->first_name
                : '',
            'last_name' => isset($user->last_name)
                ? $user->last_name
                : '',
            'username' => $user->username,
            'role_id' => $user->role_id,
            'role_name' => $user->role_name,
            'must_change_password' => !empty($user->must_change_password),
            'password_change_deferred' => FALSE,
            'logged_in' => TRUE
        );
    }
}

