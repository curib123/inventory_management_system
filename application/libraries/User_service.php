<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User_service {

    private $CI;

    // Setup ni sa User_service; gi-load ni sa application/controllers/Users.php ug Auth.php para account business rules naa ra diri.
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('User_model');
    }

    // Business flow ni para create or update user; application/controllers/Users.php ang caller, including role validity ug temporary-password rules.
    public function save($id, $input, $current_user = NULL, $current_session_user_id = 0) {
        $id = $id === NULL ? NULL : (int) $id;
        $role_id = (int) (isset($input['role_id']) ? $input['role_id'] : 0);
        $username = trim((string) (isset($input['username']) ? $input['username'] : ''));
        $requested_status = isset($input['status']) && (int) $input['status'] === 0 ? 0 : 1;
        $role = $this->CI->User_model->get_role_by_id($role_id);
        $preserves_existing_role =
            $id !== NULL &&
            $current_user &&
            (int) $current_user->role_id === $role_id;

        if (
            !$role ||
            (!(int) $role->status && (!$preserves_existing_role || $requested_status === 1))
        ) {
            return array(
                'success' => FALSE,
                'message' => 'The selected role is invalid or inactive. Active user accounts require an active role.'
            );
        }

        if ($this->CI->User_model->username_exists($username, $id)) {
            return array('success' => FALSE, 'message' => 'That username is already in use.');
        }

        $middle_name = trim((string) (isset($input['middle_name']) ? $input['middle_name'] : ''));
        $data = array(
            'first_name' => trim((string) $input['first_name']),
            'middle_name' => $middle_name === '' ? NULL : $middle_name,
            'last_name' => trim((string) $input['last_name']),
            'username' => $username,
            'role_id' => $role_id,
            'status' => $requested_status
        );
        $temporary_password = NULL;

        if ($id === NULL) {
            $temporary_password = $this->generate_temporary_password();
            $data['password'] = password_hash($temporary_password, PASSWORD_DEFAULT);
            $data['must_change_password'] = 1;
        } else {
            $password = isset($input['password']) ? (string) $input['password'] : '';

            if ($password !== '') {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
                $data['must_change_password'] = 1;
            }
        }

        if (!$this->CI->User_model->save($data, $id)) {
            return array('success' => FALSE, 'message' => 'The user could not be saved.');
        }

        return array(
            'success' => TRUE,
            'username' => $username,
            'temporary_password' => $temporary_password,
            'self_deactivated' =>
                $id !== NULL &&
                $id === (int) $current_session_user_id &&
                $requested_status === 0
        );
    }

    // Business flow ni para delete user; application/controllers/Users.php ang caller, then self-delete ug history rules diri gi-check.
    public function delete($id, $current_session_user_id, $execute = TRUE) {
        $id = (int) $id;
        $user = $this->CI->User_model->get_by_id($id);

        if (!$user) {
            return array('success' => FALSE, 'not_found' => TRUE, 'message' => 'User not found.');
        }

        if ($id === (int) $current_session_user_id) {
            return array('success' => FALSE, 'message' => 'You cannot delete your own signed-in account.');
        }

        if ($this->CI->User_model->has_history($id)) {
            return array(
                'success' => FALSE,
                'message' => 'This user has transaction or activity history. Set the account to inactive instead of deleting it.'
            );
        }

        if (!$execute) {
            return array('success' => TRUE, 'user' => $user);
        }

        if (!$this->CI->User_model->delete($id)) {
            return array('success' => FALSE, 'message' => 'The user could not be deleted.');
        }

        return array('success' => TRUE, 'user' => $user);
    }

    // Business flow ni para change password; application/controllers/Auth.php ang caller, with current-password ug password-reuse rules centralized diri.
    public function change_password($user_id, $current_password, $new_password) {
        $user_id = (int) $user_id;
        $current_password = (string) $current_password;
        $new_password = (string) $new_password;

        if (!$this->CI->User_model->verify_password($user_id, $current_password)) {
            return array('success' => FALSE, 'message' => 'The current password is incorrect.');
        }

        if (hash_equals($current_password, $new_password)) {
            return array(
                'success' => FALSE,
                'message' => 'Choose a new password that is different from the current password.'
            );
        }

        if (!$this->CI->User_model->update_password($user_id, $new_password, FALSE)) {
            return array('success' => FALSE, 'message' => 'The password could not be updated. Please try again.');
        }

        return array('success' => TRUE);
    }

    // Search option builder ni para roles; application/controllers/Users.php ang caller para controller dili na mag-format role lookup data.
    public function role_options($query, $limit = 20) {
        $items = array();

        foreach ($this->CI->User_model->search_active_roles($query, $limit) as $role) {
            $items[] = array(
                'id' => (int) $role->id,
                'text' => (string) $role->role_name,
                'secondary' => (string) ($role->description ?: '')
            );
        }

        return $items;
    }

    // Internal helper ni para generate temporary password; tawagon ra sulod application/libraries/User_service.php para secure onboarding password creation.
    private function generate_temporary_password($length = 14) {
        $length = max(12, min(32, (int) $length));
        $groups = array(
            'ABCDEFGHJKLMNPQRSTUVWXYZ',
            'abcdefghijkmnopqrstuvwxyz',
            '23456789',
            '!@#$%*-_'
        );
        $all = implode('', $groups);
        $characters = array();

        foreach ($groups as $group) {
            $characters[] = $group[random_int(0, strlen($group) - 1)];
        }

        while (count($characters) < $length) {
            $characters[] = $all[random_int(0, strlen($all) - 1)];
        }

        for ($i = count($characters) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            $temp = $characters[$i];
            $characters[$i] = $characters[$j];
            $characters[$j] = $temp;
        }

        return implode('', $characters);
    }
}
