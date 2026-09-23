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
        $this->require_permission('manage_users');

        $data['page_title'] = 'Roles and Permissions';
        $this->load->view('templates/header', $data);
        $this->load->view('roles/index', $data);
        $this->load->view('templates/footer');
    }

    public function add() {
        $this->require_permission('manage_users');
        $this->role_form();
    }

    public function view($id) {
        $this->require_permission('manage_users');

        $role = $this->Role_model->get_by_id($id);
        if (!$role) {
            show_404();
        }

        $this->load->view('modal/roles/details', array(
            'role' => $role,
            'user_count' => $this->Role_model->count_users($id)
        ));
    }

    public function edit($id) {
        $this->require_permission('manage_users');

        $role = $this->Role_model->get_by_id($id);
        if (!$role) {
            show_404();
        }

        $this->role_form((int) $id, $role);
    }

    public function delete($id) {
        $this->require_permission('manage_users');

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
        $this->require_permission('manage_users');

        $columns = array('r.role_name', 'r.description', 'r.status', 'user_count', NULL);
        $request = $this->datatable_service->request($this->input, $columns, 'r.role_name', 'asc');
        $roles = $this->Role_model->get_datatable(
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir']
        );

        $rows = array();
        foreach ($roles as $role) {
            $id = (int) $role->id;
            $actions = '<button type="button" data-modal-url="' . site_url('roles/view/' . $id) . '">View</button> ';
            $actions .= '<button type="button" data-modal-url="' . site_url('roles/edit/' . $id) . '">Edit</button> ';

            if ((int) $role->user_count === 0) {
                $actions .= '<button type="button" data-modal-url="' . site_url('roles/delete/' . $id) . '">Delete</button>';
            }

            $rows[] = array(
                html_escape($role->role_name),
                html_escape($role->description),
                $role->status ? 'Active' : 'Inactive',
                (int) $role->user_count,
                $actions
            );
        }

        $payload = $this->datatable_service->payload(
            $request['draw'],
            $this->Role_model->count_all(),
            $this->Role_model->count_datatable_filtered($request['search']),
            $rows
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    private function role_form($id = NULL, $role = NULL) {
        $this->form_validation->set_rules('role_name', 'Role Name', 'trim|required|alpha_dash|max_length[50]');
        $this->form_validation->set_rules('description', 'Description', 'trim|max_length[255]');

        if ($this->form_validation->run() === FALSE) {
            $this->render_role_form($id, $role);
            return;
        }

        $role_name = trim($this->input->post('role_name', TRUE));
        if ($this->Role_model->name_exists($role_name, $id)) {
            $this->render_role_form($id, $role, 'That role name already exists.');
            return;
        }

        $role_data = array(
            'role_name' => $role_name,
            'description' => trim($this->input->post('description', TRUE)),
            'status' => $this->input->post('status', TRUE) === '0' ? 0 : 1
        );

        $role_id = $this->Role_model->save($role_data, $id);
        if ($role_id === FALSE) {
            $this->render_role_form($id, $role, 'The role could not be saved.');
            return;
        }

        if (!$this->Role_model->sync_permissions($role_id, $this->input->post('permissions', TRUE))) {
            $this->render_role_form($id, $role, 'The role permissions could not be saved.');
            return;
        }

        redirect('roles');
    }

    private function render_role_form($id, $role, $form_error = '') {
        $data['role'] = $role;
        $data['permissions'] = $this->Role_model->get_permissions();
        $data['selected_permissions'] = $id === NULL ? array() : $this->Role_model->get_role_permissions($id);
        $data['page_title'] = $id === NULL ? 'Add Role' : 'Edit Role';
        $data['form_error'] = $form_error;

        $this->load->view('modal/roles/form', $data);
    }

    private function require_permission($permission_name) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_name)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
