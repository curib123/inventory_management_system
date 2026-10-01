<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Session_version_guard {
    public function enforce() {
        $CI =& get_instance();

        if (!$CI->session->userdata('logged_in')) {
            return;
        }

        $user_id = (int) $CI->session->userdata('user_id');
        $session_version = $CI->session->userdata('auth_version');
        $current_version = NULL;

        if ($user_id > 0) {
            try {
                $CI->load->model('User_model');
                $current_version = $CI->User_model->get_auth_version($user_id);
            } catch (Throwable $exception) {
                log_message('error', 'Unable to validate the authenticated session version.');
            }
        }

        if (!self::versions_match($session_version, $current_version)) {
            $CI->session->sess_destroy();
            redirect('login');
        }
    }

    public static function versions_match($session_version, $current_version) {
        return $session_version !== NULL &&
            $current_version !== NULL &&
            (int) $session_version > 0 &&
            (int) $session_version === (int) $current_version;
    }
}
