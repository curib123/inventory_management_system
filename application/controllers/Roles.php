<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Roles extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(array('session', 'form_validation'));
        $this->load->helper(array('form', 'url'));
        $this->load->library('Datatable_service');

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('Role_model');
        $this->load->model('User_model');
    }

    public function index() {
        $this->require_permission('roles.view');

        $data['page_title'] = 'Roles and Permissions';
        $this->load->view('templates/header', $data);
        $this->load->view('roles/index', $data);
        $this->load->view('templates/footer');
    }

    public function add() {
        $this->require_permission('roles.create');
        $this->role_form();
    }

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

    public function edit($id) {
        $this->require_any_permission(array('roles.edit', 'roles.permissions'));

        $role = $this->Role_model->get_by_id($id);

        if (!$role) {
            show_404();
        }

        $this->role_form((int) $id, $role);
    }

    public function delete($id) {
        $this->require_permission('roles.delete');

        $role = $this->Role_model->get_by_id($id);

        if (!$role) {
            show_404();
        }

        $delete_error = '';

        if ($this->Role_model->has_users($id)) {
            $delete_error = 'This role cannot be deleted while users are assigned to it.';
        }

        if ($this->input->method(TRUE) !== 'POST') {
            $this->load->view('modal/roles/delete', array(
                'role' => $role,
                'delete_error' => $delete_error
            ));
            return;
        }

        if ($delete_error !== '') {
            $this->load->view('modal/roles/delete', array(
                'role' => $role,
                'delete_error' => $delete_error
            ));
            return;
        }

        if (!$this->Role_model->delete($id)) {
            $this->load->view('modal/roles/delete', array(
                'role' => $role,
                'delete_error' => 'The role could not be deleted.'
            ));
            return;
        }

        redirect('roles');
    }

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
            $request['order_dir']
        );

        $current_user_id = (int) $this->session->userdata('user_id');
        $can_edit_role = $this->User_model->has_permission($current_user_id, 'roles.edit');
        $can_manage_permissions = $this->User_model->has_permission($current_user_id, 'roles.permissions');
        $can_delete_role = $this->User_model->has_permission($current_user_id, 'roles.delete');

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
            $this->Role_model->count_datatable_filtered($request['search']),
            $rows
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    private function role_form($id = NULL, $role = NULL) {
        $current_user_id = (int) $this->session->userdata('user_id');

        $can_edit_role = $id === NULL
            ? $this->User_model->has_permission($current_user_id, 'roles.create')
            : $this->User_model->has_permission($current_user_id, 'roles.edit');

        $can_manage_permissions = $this->User_model->has_permission(
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

        $role_id = $id;

        if ($can_edit_role) {
            $role_name = trim((string) $this->input->post('role_name', TRUE));

            if ($this->Role_model->name_exists($role_name, $id)) {
                $this->render_role_form(
                    $id,
                    $role,
                    'That role name already exists.',
                    $can_edit_role,
                    $can_manage_permissions
                );
                return;
            }

            $role_data = array(
                'role_name' => $role_name,
                'description' => trim((string) $this->input->post('description', TRUE)),
                'status' => $this->input->post('status', TRUE) === '0' ? 0 : 1
            );

            $role_id = $this->Role_model->save($role_data, $id);

            if ($role_id === FALSE) {
                $this->render_role_form(
                    $id,
                    $role,
                    'The role could not be saved.',
                    $can_edit_role,
                    $can_manage_permissions
                );
                return;
            }
        }

        if ($can_manage_permissions) {
            $permission_ids = $this->input->post('permissions', TRUE);

            if (!$this->Role_model->sync_permissions($role_id, $permission_ids)) {
                $fresh_role = $role_id ? $this->Role_model->get_by_id($role_id) : $role;

                $this->render_role_form(
                    $role_id,
                    $fresh_role,
                    'The role permissions could not be saved.',
                    $can_edit_role,
                    $can_manage_permissions
                );
                return;
            }
        }

        redirect('roles');
    }

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

    private function require_permission($permission_key) {
        $user_id = (int) $this->session->userdata('user_id');

        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_key)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }

    private function require_any_permission($permission_keys) {
        $user_id = (int) $this->session->userdata('user_id');

        if (
            !$user_id ||
            !$this->User_model->has_any_permission($user_id, $permission_keys)
        ) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
