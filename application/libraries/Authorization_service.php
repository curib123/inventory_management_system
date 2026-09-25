<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Authorization_service {

    private $CI;

    // Setup ni sa Authorization_service; auto-loaded ni para controllers ug shared views one place ra mag-check permissions.
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('User_model');
    }

    // Business rule ni para one permission check; controllers ug application/views/templates/header.php ang callers, while User_model permission keys query/cache ra.
    public function has_permission($user_id, $permission_key) {
        return in_array(
            (string) $permission_key,
            $this->CI->User_model->get_user_permission_keys((int) $user_id),
            TRUE
        );
    }

    // Business rule ni para any-permission check; controllers ug shared navigation ang callers when one of several permissions can grant access.
    public function has_any_permission($user_id, $permission_keys) {
        $user_permissions = $this->CI->User_model->get_user_permission_keys((int) $user_id);

        foreach ((array) $permission_keys as $permission_key) {
            if (in_array((string) $permission_key, $user_permissions, TRUE)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    // Controller guard ni para one permission; application/controllers/ ang caller para unauthorized requests centralized ug consistent.
    public function require_permission($user_id, $permission_key) {
        if (!$user_id || !$this->has_permission($user_id, $permission_key)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }

    // Controller guard ni para any permission; application/controllers/Roles.php ug similar flows ang caller when multiple permissions can authorize one action.
    public function require_any_permission($user_id, $permission_keys) {
        if (!$user_id || !$this->has_any_permission($user_id, $permission_keys)) {
            show_error('You do not have permission to access this action.', 403, 'Access Denied');
        }
    }

    // Business routing rule ni para first authorized page; application/controllers/Auth.php ang caller after login para route priority naa ra one place.
    public function first_authorized_route($user_id) {
        $destinations = array(
            'dashboard.view' => 'dashboard',
            'products.view' => 'products',
            'categories.view' => 'categories',
            'suppliers.view' => 'suppliers',
            'stock.history' => 'stock',
            'stock.view' => 'stock/low-stock',
            'reports.view' => 'reports',
            'users.view' => 'users',
            'roles.view' => 'roles'
        );

        foreach ($destinations as $permission_key => $route) {
            if ($this->has_permission($user_id, $permission_key)) {
                return $route;
            }
        }

        return NULL;
    }
}
