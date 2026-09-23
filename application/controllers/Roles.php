<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Roles extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(array('session', 'form_validation'));
        $this->load->helper(array('form', 'url'));
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
        $this->load->model('Role_model');
        $this->load->model('User_model');
    }

    public function index() {
        $this->require_permission('manage_users');
        $data['roles'] = $this->Role_model->get_all();
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

        if (!$this->Role_model->save($role_data, $id)) {
            show_error('The role could not be saved.', 500, 'Role Not Saved');
        }

        $role_id = $id !== NULL ? (int) $id : (int) $this->db->insert_id();
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
