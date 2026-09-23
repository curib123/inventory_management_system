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
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Invalid request method.', 405, 'Method Not Allowed');
        }
        $role = $this->Role_model->get_by_id($id);
        if (!$role) {
            show_404();
        }
        if ($this->Role_model->has_users($id)) {
            show_error('This role cannot be deleted while users are assigned to it.', 400, 'Role Not Deleted');
        }
        if (!$this->Role_model->delete($id)) {
            show_error('The role could not be deleted.', 500, 'Role Not Deleted');
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
            $actions = '<a href="' . site_url('roles/edit/' . (int) $role->id) . '">Edit</a>';
            if ((int) $role->user_count === 0) {
                $actions .= form_open('roles/delete/' . (int) $role->id);
                $actions .= '<button type="submit">Delete</button>';
                $actions .= form_close();
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
            $data['role'] = $role;
            $data['permissions'] = $this->Role_model->get_permissions();
            $data['selected_permissions'] = $id === NULL ? array() : $this->Role_model->get_role_permissions($id);
            $data['page_title'] = $id === NULL ? 'Add Role' : 'Edit Role';
            $this->load->view('templates/header', $data);
            $this->load->view('roles/form', $data);
            $this->load->view('templates/footer');
            return;
        }

        $role_name = trim($this->input->post('role_name', TRUE));
        if ($this->Role_model->name_exists($role_name, $id)) {
            show_error('That role name already exists.', 400, 'Role Not Saved');
        }

        $role_data = array(
            'role_name' => $role_name,
            'description' => trim($this->input->post('description', TRUE)),
            'status' => $this->input->post('status', TRUE) === '0' ? 0 : 1
        );

        $role_id = $this->Role_model->save($role_data, $id);
        if ($role_id === FALSE) {
            show_error('The role could not be saved.', 500, 'Role Not Saved');
        }

        if (!$this->Role_model->sync_permissions($role_id, $this->input->post('permissions', TRUE))) {
            show_error('The role permissions could not be saved.', 500, 'Permissions Not Saved');
        }
        redirect('roles');
    }

    private function require_permission($permission_name) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_name)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
