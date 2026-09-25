<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_service {

    // Shared service ni para authenticate; main caller/integration pangitaa sa application/controllers/Auth.php, so didto tan-awa if mangita ka asa ni gigamit.
    public function authenticate($user_model, $username, $password) {
        $user = $user_model->login($username, $password);

        if (!$user) {
            return FALSE;
        }

        return $this->session_data($user);
    }

    // Shared service ni para session data; main caller/integration pangitaa sa application/controllers/Auth.php, so didto tan-awa if mangita ka asa ni gigamit.
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
