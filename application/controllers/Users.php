<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(array('session', 'form_validation'));
        $this->load->helper(array('form', 'url'));
        $this->load->library('Datatable_service');

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('User_model');
        $this->require_permission('manage_users');
    }

    public function index() {
        $data['page_title'] = 'User Management';
        $this->load->view('templates/header', $data);
        $this->load->view('users/index', $data);
        $this->load->view('templates/footer');
    }

    public function add() {
        $this->user_form();
    }

    public function view($id) {
        $data['user'] = $this->User_model->get_by_id($id);
        if (!$data['user']) {
            show_404();
        }

        $data['page_title'] = 'View User';
        $this->load->view('templates/header', $data);
        $this->load->view('users/view', $data);
        $this->load->view('templates/footer');
    }

    public function edit($id) {
        $user = $this->User_model->get_by_id($id);
        if (!$user) {
            show_404();
        }
        $this->user_form((int) $id, $user);
    }

    public function delete($id) {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Invalid request method.', 405, 'Method Not Allowed');
        }

        $id = (int) $id;
        if ($id === (int) $this->session->userdata('user_id')) {
            show_error('You cannot delete your own signed-in account.', 400, 'User Not Deleted');
        }

        $user = $this->User_model->get_by_id($id);
        if (!$user) {
            show_404();
        }

        if ($this->User_model->has_history($id)) {
            show_error('This user has transaction or activity history. Set the account to inactive instead of deleting it.', 400, 'User Not Deleted');
        }

        if (!$this->User_model->delete($id)) {
            show_error('The user could not be deleted.', 500, 'User Not Deleted');
        }

        redirect('users');
    }

    public function datatable() {
        $columns = array(
            'u.first_name',
            'u.middle_name',
            'u.last_name',
            'u.username',
            'r.role_name',
            'u.status',
            'u.created_at',
            NULL
        );

        $request = $this->datatable_service->request($this->input, $columns, 'u.last_name', 'asc');
        $users = $this->User_model->get_datatable(
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir']
        );

        $rows = array();
        $current_user_id = (int) $this->session->userdata('user_id');

        foreach ($users as $user) {
            $actions = '<a href="' . site_url('users/view/' . (int) $user->id) . '">View</a> ';
            $actions .= '<a href="' . site_url('users/edit/' . (int) $user->id) . '">Edit</a>';

            if ((int) $user->id !== $current_user_id) {
                $actions .= form_open('users/delete/' . (int) $user->id);
                $actions .= '<button type="submit">Delete</button>';
                $actions .= form_close();
            }

            $rows[] = array(
                html_escape($user->first_name),
                html_escape($user->middle_name ?: ''),
                html_escape($user->last_name),
                html_escape($user->username),
                html_escape($user->role_name ?: 'N/A'),
                $user->status ? 'Active' : 'Inactive',
                html_escape($user->created_at),
                $actions
            );
        }

        $payload = $this->datatable_service->payload(
            $request['draw'],
            $this->User_model->count_all(),
            $this->User_model->count_datatable_filtered($request['search']),
            $rows
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    private function user_form($id = NULL, $user = NULL) {
        $this->form_validation->set_rules('first_name', 'First Name', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('middle_name', 'Middle Name', 'trim|max_length[100]');
        $this->form_validation->set_rules('last_name', 'Last Name', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[3]|max_length[50]|alpha_dash');
        $this->form_validation->set_rules('role_id', 'Role', 'required|integer|greater_than[0]');

        if ($id === NULL) {
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|max_length[255]');
        } else {
            $this->form_validation->set_rules('password', 'Password', 'min_length[8]|max_length[255]');
        }

        if ($this->form_validation->run() === FALSE) {
            $data['user'] = $user;
            $data['roles'] = $this->User_model->get_active_roles();
            $data['page_title'] = $id === NULL ? 'Add User' : 'Edit User';
            $this->load->view('templates/header', $data);
            $this->load->view('users/form', $data);
            $this->load->view('templates/footer');
            return;
        }

        $username = trim($this->input->post('username', TRUE));
        $role_id = (int) $this->input->post('role_id', TRUE);
        $roles = $this->User_model->get_active_roles();
        $role_ids = array_map(function ($role) {
            return (int) $role->id;
        }, $roles);

        if (!in_array($role_id, $role_ids, TRUE)) {
            show_error('The selected role is invalid or inactive.', 400, 'User Not Saved');
        }

        if ($this->User_model->username_exists($username, $id)) {
            show_error('That username is already in use.', 400, 'User Not Saved');
        }

        $middle_name = trim((string) $this->input->post('middle_name', TRUE));

        $data = array(
            'first_name' => trim((string) $this->input->post('first_name', TRUE)),
            'middle_name' => $middle_name === '' ? NULL : $middle_name,
            'last_name' => trim((string) $this->input->post('last_name', TRUE)),
            'username' => $username,
            'role_id' => $role_id,
            'status' => $this->input->post('status', TRUE) === '0' ? 0 : 1
        );

        $password = (string) $this->input->post('password', FALSE);
        if ($password !== '') {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if (!$this->User_model->save($data, $id)) {
            show_error('The user could not be saved.', 500, 'User Not Saved');
        }

        if ($id !== NULL &&
            (int) $id === (int) $this->session->userdata('user_id') &&
            $data['status'] === 0) {
            $this->session->sess_destroy();
            redirect('login');
        }

        redirect('users');
    }

    private function require_permission($permission_name) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_name)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
