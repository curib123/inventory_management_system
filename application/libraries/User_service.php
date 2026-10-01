<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User_service {

    private $CI;

    // Setup ni sa User_service; gi-load ni sa application/controllers/Users.php ug Auth.php para account business rules naa ra diri.
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model(array('User_model', 'Role_model', 'Activity_log_model'));


    }

   
// Business flow ni para create or update user; application/controllers/Users.php ang caller,
// including role validity ug temporary-password rules.
public function save($id, $input, $current_user = NULL, $current_session_user_id = 0) {

    $id = $id === NULL ? NULL : (int) $id;

    $role_id = (int) (isset($input['role_id']) ? $input['role_id'] : 0);
    $username = trim(
        (string) (isset($input['username']) ? $input['username'] : '')
    );


    $requested_status =
        isset($input['status']) && (int) $input['status'] === 0
            ? 0
            : 1;

    // Determine whether this is CREATE or UPDATE
    $is_new_user = ($id === NULL);

    $role = $this->CI->User_model->get_role_by_id($role_id);

    $preserves_existing_role =
        $id !== NULL &&
        $current_user &&
        (int) $current_user->role_id === $role_id;

    if (
        !$role ||
        (
            !(int) $role->status &&
            (!$preserves_existing_role || $requested_status === 1)
        )
    ) {
        return array(
            'success' => FALSE,
            'message' => 'The selected role is invalid or inactive. Active user accounts require an active role.'
        );
    }

    if ($this->CI->User_model->username_exists($username, $id)) {
        return array(
            'success' => FALSE,
            'message' => 'That username is already in use.'
        );
    }

    $middle_name = trim(
        (string) (
            isset($input['middle_name'])
                ? $input['middle_name']
                : ''
        )
    );

    $data = array(
        'first_name' => trim((string) $input['first_name']),
        'middle_name' => $middle_name === '' ? NULL : $middle_name,
        'last_name' => trim((string) $input['last_name']),
        'username' => $username,
        'role_id' => $role_id,
        'status' => $requested_status
    );

    $old_profile_image = $current_user && isset($current_user->profile_image)
        ? (string) $current_user->profile_image
        : '';
    $image_upload = $this->upload_profile_image();

    if (!$image_upload['success']) {
        return array('success' => FALSE, 'message' => $image_upload['message']);
    }

    if ($image_upload['filename'] !== NULL) {
        $data['profile_image'] = $image_upload['filename'];
    }

    $temporary_password = NULL;
    $password_reset = FALSE;

    if ($is_new_user) {

        // CREATE
        $temporary_password = $this->generate_temporary_password();

        $data['password'] = password_hash(
            $temporary_password,
            PASSWORD_DEFAULT
        );

        $data['must_change_password'] = 1;

    } else {

        // UPDATE
        $password = isset($input['password'])
            ? (string) $input['password']
            : '';

        if ($password !== '') {
            $password_reset = TRUE;
            $data['password'] = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $data['must_change_password'] = 1;
        }
    }

    // Save user
    $guard_admin_capability = $is_new_user ||
        !$current_user ||
        (int) $current_user->role_id !== $role_id ||
        (int) $current_user->status !== $requested_status;

    if ($guard_admin_capability) {
        $this->CI->db->trans_begin();
        $this->CI->Role_model->lock_admin_invariant();
    }

    if (!$this->CI->User_model->save($data, $id)) {
        if ($image_upload['filename'] !== NULL) {
            $this->delete_profile_image($image_upload['filename']);
        }

        if ($guard_admin_capability) {
            $this->CI->db->trans_rollback();
        }

        return array(
            'success' => FALSE,
            'message' => 'The user could not be saved.'
        );
    }

    if ($guard_admin_capability) {
        if ($this->CI->Role_model->count_admin_capable_users() < 1) {
            $this->CI->db->trans_rollback();
            if ($image_upload['filename'] !== NULL) {
                $this->delete_profile_image($image_upload['filename']);
            }
            return array(
                'success' => FALSE,
                'message' => 'This change would leave no active administrator able to view and manage roles. Keep at least one active administrator with both role-management permissions.'
            );
        }

        if ($this->CI->db->trans_status() === FALSE) {
            $this->CI->db->trans_rollback();
            if ($image_upload['filename'] !== NULL) {
                $this->delete_profile_image($image_upload['filename']);
            }
            return array('success' => FALSE, 'message' => 'The user could not be saved.');
        }

        $this->CI->db->trans_commit();
    }

    if ($image_upload['filename'] !== NULL && $old_profile_image !== '') {
        $this->delete_profile_image($old_profile_image);
    }


    $action = $is_new_user
        ? 'user_created'
        : 'user_updated';

    $description = $is_new_user
        ? 'Created user: ' . $username
        : 'Updated user: ' . $username;

    $this->CI->Activity_log_model->insert_activity_log(array(
        'user_id'     => (int) $current_session_user_id,
        'action'      => $action,
        'description' => $description,
        'ip_address'  => $this->CI->input->ip_address()
    ));

    return array(
        'success' => TRUE,
        'username' => $username,
        'temporary_password' => $temporary_password,
        'profile_image' => $image_upload['filename'] !== NULL ? $image_upload['filename'] : $old_profile_image,
        'password_reset' => $password_reset,
        'self_deactivated' =>
            !$is_new_user &&
            $id === (int) $current_session_user_id &&
            $requested_status === 0
    );
}
    private function upload_profile_image() {
        if (!isset($_FILES['profile_image']) || !is_array($_FILES['profile_image'])) {
            return array('success' => TRUE, 'filename' => NULL);
        }

        $file_error = isset($_FILES['profile_image']['error'])
            ? (int) $_FILES['profile_image']['error']
            : UPLOAD_ERR_NO_FILE;

        if ($file_error === UPLOAD_ERR_NO_FILE) {
            return array('success' => TRUE, 'filename' => NULL);
        }

        if ($file_error !== UPLOAD_ERR_OK) {
            return array('success' => FALSE, 'message' => 'The profile image upload failed. Please choose a smaller image and try again.');
        }

        $upload_directory = FCPATH . 'assets/image/profiles/';

        if (!is_dir($upload_directory) && !@mkdir($upload_directory, 0755, TRUE) && !is_dir($upload_directory)) {
            return array('success' => FALSE, 'message' => 'Profile image storage is unavailable.');
        }

        if (!is_writable($upload_directory)) {
            return array('success' => FALSE, 'message' => 'Profile image storage is not writable.');
        }

        $this->CI->load->library('upload');
        $this->CI->upload->initialize(array(
            'upload_path' => $upload_directory,
            'allowed_types' => 'jpg|jpeg|png|gif|webp',
            'max_size' => 2048,
            'file_ext_tolower' => TRUE,
            'encrypt_name' => TRUE,
            'detect_mime' => TRUE,
            'mod_mime_fix' => TRUE
        ), TRUE);

        if (!$this->CI->upload->do_upload('profile_image')) {
            return array(
                'success' => FALSE,
                'message' => trim(strip_tags($this->CI->upload->display_errors('', '')))
            );
        }

        $upload_data = $this->CI->upload->data();
        $filename = isset($upload_data['file_name']) ? basename((string) $upload_data['file_name']) : '';

        if (!preg_match('/\A[a-f0-9]{32}\.(?:jpg|jpeg|png|gif|webp)\z/i', $filename)) {
            $this->delete_profile_image($filename);
            return array('success' => FALSE, 'message' => 'The uploaded profile image has an invalid filename.');
        }

        if (empty($upload_data['full_path']) || !$this->crop_profile_image($upload_data['full_path'])) {
            $this->delete_profile_image($filename);
            return array('success' => FALSE, 'message' => 'The image could not be cropped. Please choose another image.');
        }

        return array('success' => TRUE, 'filename' => $filename);
    }

    private function crop_profile_image($image_path) {
        if (!function_exists('imagecreatefromstring') || !function_exists('imagecopyresampled')) {
            return FALSE;
        }

        $image_info = @getimagesize($image_path);
        $allowed_mimes = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');

        if (!$image_info || !in_array($image_info['mime'], $allowed_mimes, TRUE)) {
            return FALSE;
        }

        $source = @imagecreatefromstring(@file_get_contents($image_path));

        if (!$source) {
            return FALSE;
        }

        if ($image_info['mime'] === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($image_path);
            $orientation = isset($exif['Orientation']) ? (int) $exif['Orientation'] : 1;
            $rotation = $orientation === 3 ? 180 : ($orientation === 6 ? -90 : ($orientation === 8 ? 90 : 0));

            if ($rotation !== 0) {
                $oriented = @imagerotate($source, $rotation, 0);

                if ($oriented !== FALSE) {
                    imagedestroy($source);
                    $source = $oriented;
                }
            }
        }

        $source_width = imagesx($source);
        $source_height = imagesy($source);
        $crop_size = min($source_width, $source_height);
        $source_x = (int) floor(($source_width - $crop_size) / 2);
        $source_y = (int) floor(($source_height - $crop_size) / 2);
        $canvas = imagecreatetruecolor(512, 512);

        if ($image_info['mime'] === 'image/jpeg') {
            $background = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $background);
        } else {
            imagealphablending($canvas, FALSE);
            imagesavealpha($canvas, TRUE);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefill($canvas, 0, 0, $transparent);
        }

        $copied = imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            $source_x,
            $source_y,
            512,
            512,
            $crop_size,
            $crop_size
        );
        $written = FALSE;

        if ($copied) {
            switch ($image_info['mime']) {
                case 'image/jpeg':
                    $written = imagejpeg($canvas, $image_path, 85);
                    break;
                case 'image/png':
                    $written = imagepng($canvas, $image_path, 6);
                    break;
                case 'image/gif':
                    $written = imagegif($canvas, $image_path);
                    break;
                case 'image/webp':
                    $written = function_exists('imagewebp') && imagewebp($canvas, $image_path, 85);
                    break;
            }
        }

        imagedestroy($source);
        imagedestroy($canvas);

        return (bool) $written;
    }

    private function delete_profile_image($filename) {
        $filename = basename((string) $filename);

        if (!preg_match('/\A[a-f0-9]{32}\.(?:jpg|jpeg|png|gif|webp)\z/i', $filename)) {
            return;
        }

        $image_path = FCPATH . 'assets/image/profiles/' . $filename;

        if (is_file($image_path)) {
            @unlink($image_path);
        }
    }

    public function session_identity($user_id) {
            $user = $this->CI->User_model->get_by_id((int) $user_id);

            if (!$user || !(int) $user->status || !(int) $user->role_status) {
                return FALSE;
            }

            return array(
                'first_name' => (string) $user->first_name,
                'last_name' => (string) $user->last_name,
                'username' => (string) $user->username,
                'role_id' => (int) $user->role_id,
                'role_name' => (string) $user->role_name,
                'auth_version' => (int) $user->auth_version,
                'profile_image' => isset($user->profile_image) ? (string) $user->profile_image : '',
                'must_change_password' => !empty($user->must_change_password)
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

        $this->CI->Activity_log_model->insert_activity_log(array( 'user_id' => (int) $current_session_user_id, 'action' => 'user_deleted', 'description' => 'Deleted user: ' . $user->username, 'ip_address' => $this->CI->input->ip_address() ));

        return array('success' => TRUE, 'user' => $user);
    }

    // Business flow ni para change password; application/controllers/Auth.php ang caller, with current-password ug password-reuse rules centralized diri.
    public function change_password($user_id, $current_password, $new_password) {
        $user_id = (int) $user_id;
        $current_password = (string) $current_password;
        $new_password = (string) $new_password;

        $password_hash = $this->CI->User_model->get_password_hash($user_id);

        if (!$password_hash || !password_verify($current_password, $password_hash)) {
            return array('success' => FALSE, 'message' => 'The current password is incorrect.');
        }

        if (hash_equals($current_password, $new_password)) {
            return array(
                'success' => FALSE,
                'message' => 'Choose a new password that is different from the current password.'
            );
        }

        if (!$this->CI->User_model->update_password_hash(
            $user_id,
            password_hash($new_password, PASSWORD_DEFAULT),
            FALSE
        )) {
            return array('success' => FALSE, 'message' => 'The password could not be updated. Please try again.');
        }

         $this->CI->Activity_log_model->insert_activity_log(array( 'user_id' => (int) $user_id, 'action' => 'password_updated', 'description' => 'Updated password ', 'ip_address' => $this->CI->input->ip_address() ));


        return array(
            'success' => TRUE,
            'auth_version' => $this->CI->User_model->get_auth_version($user_id)
        );
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
