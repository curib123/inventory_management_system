
<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_service {

    private $user_model;
    private $activity_log_model;
    private $login_attempt_model;
    private $CI;
    private $ip_address;

    // Setup sa Auth_service.
    // CodeIgniter mo-inject sa real User_model, while tests pwede mo-pass mock.
    public function __construct($dependencies = array()) {
        $this->user_model = null;
        $this->activity_log_model = null;
        $this->login_attempt_model = null;
        $this->CI = null;
        $this->ip_address = '';

        if (isset($dependencies['user_model'])) {
            $this->user_model = $dependencies['user_model'];
        }

        if (isset($dependencies['activity_log_model'])) {
            $this->activity_log_model = $dependencies['activity_log_model'];
        }

        if (isset($dependencies['login_attempt_model'])) {
            $this->login_attempt_model = $dependencies['login_attempt_model'];
        }

        if (isset($dependencies['ip_address'])) {
            $this->ip_address = (string) $dependencies['ip_address'];
        }

        if (isset($dependencies['CI'])) {
            $this->CI = $dependencies['CI'];
        }

        if ($this->CI === null && function_exists('get_instance')) {
            $this->CI =& get_instance();

            $this->CI->load->model(array(
                'User_model',
                'Activity_log_model',
                'Login_attempt_model'
            ));

            if (empty($this->user_model)) {
                $this->user_model = $this->CI->User_model;
            }

            if (empty($this->activity_log_model)) {
                $this->activity_log_model = $this->CI->Activity_log_model;
            }

            if (empty($this->login_attempt_model)) {
                $this->login_attempt_model = $this->CI->Login_attempt_model;
            }
        }

        if ($this->activity_log_model === null && isset($this->CI->Activity_log_model)) {
            $this->activity_log_model = $this->CI->Activity_log_model;
        }

        if ($this->login_attempt_model === null && isset($this->CI->Login_attempt_model)) {
            $this->login_attempt_model = $this->CI->Login_attempt_model;
        }

        if ($this->ip_address === '' && isset($this->CI->input)) {
            $this->ip_address = $this->CI->input->ip_address();
        }
    }

    // Shared service para authenticate.
    public function authenticate($username, $password) {

        if (!$this->user_model) {
            return FALSE;
        }

        $username = strtolower(trim((string) $username));

        if ($this->login_attempt_model && method_exists($this->login_attempt_model, 'is_locked')) {
            try {
                if ($this->login_attempt_model->is_locked($username, $this->ip_address)) {
                    return FALSE;
                }
            } catch (Throwable $exception) {
                if (function_exists('log_message')) {
                    log_message('error', 'Login rate limiter check failed; rejecting authentication.');
                }

                return FALSE;
            }
        }

        $user = $this->user_model->find_active_by_username($username);

        if (
            !$user ||
            !password_verify(
                (string) $password,
                (string) $user->password
            )
        ) {
            if ($this->login_attempt_model && method_exists($this->login_attempt_model, 'record_failure')) {
                try {
                    if ($this->login_attempt_model->record_failure($username, $this->ip_address) && function_exists('log_message')) {
                        log_message(
                            'error',
                            'Login rate limit reached for account ' . hash('sha256', $username) .
                            ' from IP ' . $this->ip_address
                        );
                    }
                } catch (Throwable $exception) {
                    if (function_exists('log_message')) {
                        log_message('error', 'Login rate limiter update failed; rejecting authentication.');
                    }
                }
            }

            return FALSE;
        }

        if ($this->login_attempt_model && method_exists($this->login_attempt_model, 'clear_attempts')) {
            try {
                $this->login_attempt_model->clear_attempts($username, $this->ip_address);
            } catch (Throwable $exception) {
                if (function_exists('log_message')) {
                    log_message('error', 'Unable to clear successful login rate-limit counters.');
                }
            }
        }

        if ($this->activity_log_model && method_exists($this->activity_log_model, 'insert_activity_log')) {
            $this->activity_log_model->insert_activity_log(array(
                'user_id' => $user->id,
                'action' => 'login_created',
                'description' => 'Login Session',
                'ip_address' => $this->ip_address
            ));
        }

        return $this->session_data($user);
    }

    public function login_lockout_status($username) {
        if (!$this->login_attempt_model || !method_exists($this->login_attempt_model, 'lockout_status')) {
            return array('locked_until' => NULL, 'remaining_seconds' => 0);
        }

        try {
            $status = $this->login_attempt_model->lockout_status(
                strtolower(trim((string) $username)),
                $this->ip_address
            );
        } catch (Throwable $exception) {
            if (function_exists('log_message')) {
                log_message('error', 'Unable to retrieve login lockout status.');
            }

            return array('locked_until' => NULL, 'remaining_seconds' => 0);
        }

        return array(
            'locked_until' => isset($status['locked_until']) ? $status['locked_until'] : NULL,
            'remaining_seconds' => max(0, (int) (isset($status['remaining_seconds']) ? $status['remaining_seconds'] : 0))
        );
    }

    // Audit an explicit logout before the controller destroys the session.
    public function logout($user_id) {
        $user_id = (int) $user_id;

        if ($user_id <= 0 || !$this->activity_log_model || !method_exists($this->activity_log_model, 'insert_activity_log')) {
            return FALSE;
        }

        try {
            return $this->activity_log_model->insert_activity_log(array(
                'user_id' => $user_id,
                'action' => 'user_logout',
                'description' => 'Logout Session',
                'ip_address' => $this->ip_address
            ));
        } catch (Throwable $exception) {
            if (function_exists('log_message')) {
                log_message('error', 'Unable to record logout activity: ' . $exception->getMessage());
            }

            return FALSE;
        }
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
            'profile_image' => isset($user->profile_image) ? (string) $user->profile_image : '',
            'role_id' => $user->role_id,
            'role_name' => $user->role_name,
            'auth_version' => isset($user->auth_version) ? (int) $user->auth_version : 1,
            'must_change_password' => !empty($user->must_change_password),
            'password_change_deferred' => FALSE,
            'logged_in' => TRUE
        );
    }
}

