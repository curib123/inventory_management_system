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
    }

    public function index() {
        $this->require_permission('users.view');

        $data['page_title'] = 'User Management';
        $this->load->view('templates/header', $data);
        $this->load->view('users/index', $data);
        $this->load->view('templates/footer');
    }

    public function add() {
        $this->require_permission('users.create');
        $this->user_form();
    }

    public function view($id) {
        $this->require_permission('users.view');

        $data['user'] = $this->User_model->get_by_id($id);
        if (!$data['user']) {
            show_404();
        }

        $this->load->view('modal/users/details', $data);
    }

    public function edit($id) {
        $this->require_permission('users.edit');

        $user = $this->User_model->get_by_id($id);
        if (!$user) {
            show_404();
        }

        $this->user_form((int) $id, $user);
    }

    public function delete($id) {
        $this->require_permission('users.delete');

        $id = (int) $id;
        $user = $this->User_model->get_by_id($id);
        if (!$user) {
            show_404();
        }

        $delete_error = '';
        if ($id === (int) $this->session->userdata('user_id')) {
            $delete_error = 'You cannot delete your own signed-in account.';
        } elseif ($this->User_model->has_history($id)) {
            $delete_error = 'This user has transaction or activity history. Set the account to inactive instead of deleting it.';
        }

        if ($this->input->method(TRUE) !== 'POST') {
            $this->load->view('modal/users/delete', array(
                'user' => $user,
                'delete_error' => $delete_error
            ));
            return;
        }

        if ($delete_error !== '') {
            $this->load->view('modal/users/delete', array(
                'user' => $user,
                'delete_error' => $delete_error
            ));
            return;
        }

        if (!$this->User_model->delete($id)) {
            $this->load->view('modal/users/delete', array(
                'user' => $user,
                'delete_error' => 'The user could not be deleted.'
            ));
            return;
        }

        $this->session->set_flashdata('success', 'User deleted successfully.');
        redirect('users');
    }

    public function role_search() {
        $this->require_user_form_permission();

        if ($this->input->method(TRUE) !== 'GET') {
            show_error('Invalid request method.', 405, 'Method Not Allowed');
        }

        $query = trim((string) $this->input->get('q', TRUE));
        $roles = $this->User_model->search_active_roles($query, 20);
        $items = array();

        foreach ($roles as $role) {
            $items[] = array(
                'id' => (int) $role->id,
                'text' => (string) $role->role_name,
                'secondary' => (string) ($role->description ?: '')
            );
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('items' => $items)));
    }

    public function datatable() {
        $this->require_permission('users.view');

        $columns = array(
            'u.first_name',
            'u.middle_name',
            'u.last_name',
            'u.username',
            'r.role_name',
            'u.status',
            'u.created_at',
            'u.updated_at',
            NULL
        );

        $request = $this->datatable_service->request($this->input, $columns, 'u.last_name', 'asc');
        $users = $this->User_model->get_datatable(
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir'],
            $request['filters']
        );

        $rows = array();

        $current_user_id = (int) $this->session->userdata('user_id');

        foreach ($users as $user) {
            $id = (int) $user->id;
            $action_items = array(
                array('label' => 'View', 'url' => site_url('users/view/' . $id), 'variant' => 'secondary', 'icon' => 'bi-eye')
            );

            if ($this->User_model->has_permission($current_user_id, 'users.edit')) {
                $action_items[] = array('label' => 'Edit', 'url' => site_url('users/edit/' . $id), 'variant' => 'primary', 'icon' => 'bi-pencil');
            }

            if (
                $id !== $current_user_id &&
                $this->User_model->has_permission($current_user_id, 'users.delete')
            ) {
                $action_items[] = array('label' => 'Delete', 'url' => site_url('users/delete/' . $id), 'variant' => 'danger', 'icon' => 'bi-trash');
            }

            $actions = ui_modal_action_group($action_items);

            $rows[] = array(
                html_escape($user->first_name),
                html_escape($user->middle_name ?: ''),
                html_escape($user->last_name),
                html_escape($user->username),
                html_escape($user->role_name ?: 'N/A'),
                $user->status ? 'Active' : 'Inactive',
                html_escape($user->created_at),
                html_escape($user->updated_at),
                $actions
            );
        }

        $payload = $this->datatable_service->payload(
            $request['draw'],
            $this->User_model->count_all(),
            $this->User_model->count_datatable_filtered($request['search'], $request['filters']),
            $rows
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    private function user_form($id = NULL, $user = NULL) {
        $this->form_validation->set_rules('first_name', 'First Name', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('middle_name', 'Middle Name', 'trim|max_length[100]');
        $this->form_validation->set_rules('last_name', 'Last Name', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[3]|max_length[50]|alpha_dash');
        $this->form_validation->set_rules('role_id', 'Role', 'required|integer|greater_than[0]');

        if ($id !== NULL) {
            $this->form_validation->set_rules('password', 'Reset Password', 'min_length[8]|max_length[255]');
        }

        if ($this->form_validation->run() === FALSE) {
            $this->render_user_form($id, $user);
            return;
        }

        $username = trim($this->input->post('username', TRUE));
        $role_id = (int) $this->input->post('role_id', TRUE);
        $requested_status = $this->input->post('status', TRUE) === '0' ? 0 : 1;
        $role = $this->User_model->get_role_by_id($role_id);
        $preserves_existing_role =
            $id !== NULL &&
            $user &&
            (int) $user->role_id === $role_id;

        if (
            !$role ||
            (!(int) $role->status && (!$preserves_existing_role || $requested_status === 1))
        ) {
            $this->render_user_form(
                $id,
                $user,
                'The selected role is invalid or inactive. Active user accounts require an active role.'
            );
            return;
        }

        if ($this->User_model->username_exists($username, $id)) {
            $this->render_user_form($id, $user, 'That username is already in use.');
            return;
        }

        $middle_name = trim((string) $this->input->post('middle_name', TRUE));

        $data = array(
            'first_name' => trim((string) $this->input->post('first_name', TRUE)),
            'middle_name' => $middle_name === '' ? NULL : $middle_name,
            'last_name' => trim((string) $this->input->post('last_name', TRUE)),
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
            $password = (string) $this->input->post('password', FALSE);

            if ($password !== '') {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
                $data['must_change_password'] = 1;
            }
        }

        if (!$this->User_model->save($data, $id)) {
            $this->render_user_form($id, $user, 'The user could not be saved.');
            return;
        }

        if ($id !== NULL &&
            (int) $id === (int) $this->session->userdata('user_id') &&
            $data['status'] === 0) {
            $this->session->sess_destroy();
            redirect('login');
        }

        if ($temporary_password !== NULL) {
            if ($this->input->is_ajax_request()) {
                $this->load->view('modal/users/created', array(
                    'username' => $username,
                    'temporary_password' => $temporary_password
                ));
                return;
            }

            $this->session->set_flashdata('temporary_password', $temporary_password);
            $this->session->set_flashdata(
                'success',
                'User account created successfully. Copy the temporary password below and share it securely with the user.'
            );
            redirect('users');
            return;
        }

        $this->session->set_flashdata('success', 'User changes saved successfully.');
        redirect('users');
    }

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

    private function render_user_form($id, $user, $form_error = '') {
        $data['user'] = $user;
        $data['roles'] = array();

        $is_post = $this->input->method(TRUE) === 'POST';
        $selected_role_id = $is_post
            ? (int) $this->input->post('role_id', TRUE)
            : ($user ? (int) $user->role_id : 0);

        if ($selected_role_id > 0) {
            $selected_role = $this->User_model->get_role_by_id($selected_role_id);

            if ($selected_role) {
                $data['roles'][] = $selected_role;
            }
        }

        $data['page_title'] = $id === NULL ? 'Add User' : 'Edit User';
        $data['form_error'] = $form_error;

        $this->load->view('modal/users/form', $data);
    }

    private function require_user_form_permission() {
        $user_id = (int) $this->session->userdata('user_id');

        if (
            $user_id <= 0 ||
            !$this->User_model->has_any_permission(
                $user_id,
                array('users.create', 'users.edit')
            )
        ) {
            show_error('You do not have permission to manage user roles.', 403, 'Access Denied');
        }
    }

    private function require_permission($permission_key) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_key)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
