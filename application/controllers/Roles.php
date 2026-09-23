<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Roles extends CI_Controller {

    // roles controller to handle role management functionality
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
    // Function to check if the user has the required permission
    public function index() {
        $this->require_permission('manage_users');


        $data['roles'] = $this->Role_model->get_all();
        $data['permissions'] = $this->Role_model->get_permissions();
        $data['page_title'] = 'Roles and Permissions';

        $this->load->view('templates/header', $data);
        $this->load->view('roles/index', $data);
        $this->load->view('templates/footer');
    }
    // Function to check if the user has the required permission and add a new role
    public function add() {
        $this->require_permission('manage_users');
        $this->role_form();
    }
    // Function to check if the user has the required permission and edit an existing role
    public function edit($id) {
        $this->require_permission('manage_users');

        if (!$this->Role_model->get_by_id($id)) {
            redirect('roles');
        }

        $this->role_form((int) $id);
    }
   // Function to check if the user has the required permission and delete an existing role
    public function delete($id) {
        $this->require_permission('manage_users');

        $role = $this->Role_model->get_by_id($id);
        if (!$role || $this->Role_model->has_users($id)) {
            show_error('This role cannot be deleted while users are assigned to it.', 400, 'Role Not Deleted');
        }

        $this->Role_model->delete($id);
        redirect('roles');
    }
    // Function to handle the role form for adding and editing roles
    private function role_form($id = NULL) {
        $this->form_validation->set_rules('role_name', 'Role Name', 'required|alpha_dash|max_length[50]');
        $this->form_validation->set_rules('description', 'Description', 'max_length[255]');

        $role = $id ? $this->Role_model->get_by_id($id) : NULL;
        if ($this->form_validation->run() === FALSE) {
            $data['role'] = $role;
            $data['permissions'] = $this->Role_model->get_permissions();
            $data['selected_permissions'] = $id ? $this->Role_model->get_role_permissions($id) : array();
            $data['page_title'] = $id ? 'Edit Role' : 'Add Role';

            $this->load->view('templates/header', $data);
            $this->load->view('roles/form', $data);
            $this->load->view('templates/footer');
            return;
        }

        $role_data = array(
            'role_name' => trim($this->input->post('role_name', TRUE)),
            'description' => trim($this->input->post('description', TRUE)),
            'status' => $this->input->post('status', TRUE) ? 1 : 0
        );

        if (!$this->Role_model->save($role_data, $id)) {
            show_error('The role could not be saved. Check that the role name is unique.', 400, 'Role Not Saved');
        }

        $role_id = $id ? $id : $this->db->insert_id();
        $this->Role_model->sync_permissions($role_id, $this->input->post('permissions', TRUE));
        redirect('roles');
    }
   // Function to check if the user has the required permission
    private function require_permission($permission_name) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_name)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
