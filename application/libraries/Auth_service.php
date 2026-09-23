<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_service {

    public function authenticate($user_model, $username, $password) {
        $user = $user_model->login($username, $password);

        if (!$user) {
            return FALSE;
        }

        return $this->session_data($user);
    }

    public function session_data($user) {
        return array(
            'user_id' => $user->id,
            'username' => $user->username,
            'role_id' => $user->role_id,
            'role_name' => $user->role_name,
            'logged_in' => TRUE
        );
    }
}
