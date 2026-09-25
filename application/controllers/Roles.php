<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Roles extends CI_Controller {

    // Setup ni sa Roles controller; CodeIgniter mo-run ani automatically, while route mapping makita sa application/config/routes.php.
    public function __construct() {
        parent::__construct();
        $this->load->library(array('session', 'form_validation'));
        $this->load->helper(array('form', 'url'));
        $this->load->library(array('Datatable_service', 'Role_service'));

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('Role_model');
    }

    // Mao ni ang index flow sa Roles; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function index() {
        $this->require_permission('roles.view');

        $data['page_title'] = 'Roles and Permissions';
        $this->load->view('templates/header', $data);
        $this->load->view('roles/index', $data);
        $this->load->view('templates/footer');
    }

    // Mao ni ang add flow sa Roles; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function add() {
        $this->require_permission('roles.create');
        $this->role_form();
    }

    // Mao ni ang view flow sa Roles; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function view($id) {
        $this->require_permission('roles.view');

        $role = $this->Role_model->get_by_id($id);

        if (!$role) {
            show_404();
        }

        $this->load->view('modal/roles/details', array(
            'role' => $role,
            'user_count' => $this->Role_model->count_users($id),
            'permissions' => $this->Role_model->get_role_permission_details($id)
        ));
    }

    // Mao ni ang edit flow sa Roles; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function edit($id) {
        $this->require_any_permission(array('roles.edit', 'roles.permissions'));

        $role = $this->Role_model->get_by_id($id);

        if (!$role) {
            show_404();
        }

        $this->role_form((int) $id, $role);
    }

    // Mao ni ang delete flow sa Roles; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function delete($id) {
        $this->require_permission('roles.delete');

        $role = $this->Role_model->get_by_id($id);

        if (!$role) {
            show_404();
        }

        $execute = $this->input->method(TRUE) === 'POST';
        $result = $this->role_service->delete($id, $execute);

        if (!$execute || !$result['success']) {
            $this->load->view('modal/roles/delete', array(
                'role' => $role,
                'delete_error' => $result['success'] ? '' : $result['message']
            ));
            return;
        }

        $this->session->set_flashdata('success', 'Role deleted successfully.');
        redirect('roles');
    }

    // Mao ni ang datatable flow sa Roles; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function datatable() {
        $this->require_permission('roles.view');

        $columns = array(
            'r.role_name',
            'r.description',
            'r.status',
            'user_count',
            'permission_count',
            NULL
        );
        $request = $this->datatable_service->request($this->input, $columns, 'r.role_name', 'asc');
        $roles = $this->Role_model->get_datatable(
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir'],
            $request['filters']
        );

        $current_user_id = (int) $this->session->userdata('user_id');
        $can_edit_role = $this->authorization_service->has_permission($current_user_id, 'roles.edit');
        $can_manage_permissions = $this->authorization_service->has_permission($current_user_id, 'roles.permissions');
        $can_delete_role = $this->authorization_service->has_permission($current_user_id, 'roles.delete');

        $rows = array();

        foreach ($roles as $role) {
            $id = (int) $role->id;
            $action_items = array(
                array(
                    'label' => 'View',
                    'url' => site_url('roles/view/' . $id),
                    'variant' => 'secondary',
                    'icon' => 'bi-eye'
                )
            );

            if ($can_edit_role || $can_manage_permissions) {
                $action_items[] = array(
                    'label' => $can_edit_role ? 'Edit' : 'Permissions',
                    'url' => site_url('roles/edit/' . $id),
                    'variant' => 'primary',
                    'icon' => $can_edit_role ? 'bi-pencil' : 'bi-shield-check'
                );
            }

            if ($can_delete_role && (int) $role->user_count === 0) {
                $action_items[] = array(
                    'label' => 'Delete',
                    'url' => site_url('roles/delete/' . $id),
                    'variant' => 'danger',
                    'icon' => 'bi-trash'
                );
            }

            $rows[] = array(
                html_escape($role->role_name),
                html_escape($role->description),
                $role->status ? 'Active' : 'Inactive',
                (int) $role->user_count,
                (int) $role->permission_count,
                ui_modal_action_group($action_items)
            );
        }

        $payload = $this->datatable_service->payload(
            $request['draw'],
            $this->Role_model->count_all(),
            $this->Role_model->count_datatable_filtered($request['search'], $request['filters']),
            $rows
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    // Internal helper ni para role form; tawagon ra sulod application/controllers/Roles.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function role_form($id = NULL, $role = NULL) {
        $current_user_id = (int) $this->session->userdata('user_id');

        $can_edit_role = $id === NULL
            ? $this->authorization_service->has_permission($current_user_id, 'roles.create')
            : $this->authorization_service->has_permission($current_user_id, 'roles.edit');

        $can_manage_permissions = $this->authorization_service->has_permission(
            $current_user_id,
            'roles.permissions'
        );

        if (!$can_edit_role && !$can_manage_permissions) {
            show_error('You do not have permission to modify this role.', 403, 'Access Denied');
        }

        if ($can_edit_role) {
            $this->form_validation->set_rules(
                'role_name',
                'Role Name',
                'trim|required|alpha_dash|max_length[50]'
            );
            $this->form_validation->set_rules(
                'description',
                'Description',
                'trim|max_length[255]'
            );
        }

        $is_post = $this->input->method(TRUE) === 'POST';

        if ($is_post && $can_edit_role && $this->form_validation->run() === FALSE) {
            $this->render_role_form(
                $id,
                $role,
                '',
                $can_edit_role,
                $can_manage_permissions
            );
            return;
        }

        if (!$is_post) {
            $this->render_role_form(
                $id,
                $role,
                '',
                $can_edit_role,
                $can_manage_permissions
            );
            return;
        }

        $role_data = NULL;

        if ($can_edit_role) {
            $role_data = array(
                'role_name' => trim((string) $this->input->post('role_name', TRUE)),
                'description' => trim((string) $this->input->post('description', TRUE)),
                'status' => $this->input->post('status', TRUE) === '0' ? 0 : 1
            );
        }

        $result = $this->role_service->save(
            $id,
            $role_data,
            $can_manage_permissions ? $this->input->post('permissions', TRUE) : array(),
            $can_edit_role,
            $can_manage_permissions
        );

        if (!$result['success']) {
            $this->render_role_form(
                $id,
                $role,
                $result['message'],
                $can_edit_role,
                $can_manage_permissions
            );
            return;
        }

        $this->session->set_flashdata('success', $result['message']);
        redirect('roles');
    }

    // Internal helper ni para render role form; tawagon ra sulod application/controllers/Roles.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function render_role_form(
        $id,
        $role,
        $form_error = '',
        $can_edit_role = TRUE,
        $can_manage_permissions = FALSE
    ) {
        $data['role'] = $role;
        $data['permissions'] = $this->Role_model->get_permissions();
        $data['selected_permissions'] = $id === NULL
            ? array()
            : $this->Role_model->get_role_permissions($id);
        if ($id === NULL) {
            $data['page_title'] = 'Add Role';
        } elseif (!$can_edit_role && $can_manage_permissions) {
            $data['page_title'] = 'Manage Role Permissions';
        } else {
            $data['page_title'] = 'Edit Role';
        }

        $data['form_error'] = $form_error;
        $data['can_edit_role'] = (bool) $can_edit_role;
        $data['can_manage_permissions'] = (bool) $can_manage_permissions;

        $this->load->view('modal/roles/form', $data);
    }

    // Internal helper ni para require permission; tawagon ra sulod application/controllers/Roles.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function require_permission($permission_key) {
        $user_id = (int) $this->session->userdata('user_id');

        if (!$user_id || !$this->authorization_service->has_permission($user_id, $permission_key)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }

    // Internal helper ni para require any permission; tawagon ra sulod application/controllers/Roles.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function require_any_permission($permission_keys) {
        $user_id = (int) $this->session->userdata('user_id');

        if (
            !$user_id ||
            !$this->authorization_service->has_any_permission($user_id, $permission_keys)
        ) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
