<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Controller {

    // Setup ni sa Users controller; CodeIgniter mo-run ani automatically, while route mapping makita sa application/config/routes.php.
    public function __construct() {
        parent::__construct();
        $this->load->library(array('session', 'form_validation'));
        $this->load->helper(array('form', 'url'));
        $this->load->library(array('Datatable_service', 'User_service'));

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('User_model');
    }

    // Mao ni ang index flow sa Users; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function index() {
        $this->require_permission('users.view');

        $data['page_title'] = 'User Management';
        $this->load->view('templates/header', $data);
        $this->load->view('users/index', $data);
        $this->load->view('templates/footer');
    }

    // Mao ni ang add flow sa Users; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function add() {
        $this->require_permission('users.create');
        $this->user_form();
    }

    // Mao ni ang view flow sa Users; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function view($id) {
        $this->require_permission('users.view');

        $data['user'] = $this->User_model->get_by_id($id);
        if (!$data['user']) {
            show_404();
        }

        $this->load->view('modal/users/details', $data);
    }

    // Mao ni ang edit flow sa Users; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function edit($id) {
        $this->require_permission('users.edit');

        $user = $this->User_model->get_by_id($id);
        if (!$user) {
            show_404();
        }

        $this->user_form((int) $id, $user);
    }

    // Mao ni ang delete flow sa Users; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function delete($id) {
        $this->require_permission('users.delete');

        $id = (int) $id;
        $user = $this->User_model->get_by_id($id);
        if (!$user) {
            show_404();
        }

        $execute = $this->input->method(TRUE) === 'POST';
        $result = $this->user_service->delete(
            $id,
            (int) $this->session->userdata('user_id'),
            $execute
        );

        if (!$execute || !$result['success']) {
            $this->load->view('modal/users/delete', array(
                'user' => $user,
                'delete_error' => $result['success'] ? '' : $result['message']
            ));
            return;
        }

        $this->session->set_flashdata('success', 'User deleted successfully.');
        redirect('users');
    }

    // Mao ni ang role search flow sa Users; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function role_search() {
        $this->require_user_form_permission();

        if ($this->input->method(TRUE) !== 'GET') {
            show_error('Invalid request method.', 405, 'Method Not Allowed');
        }

        $items = $this->user_service->role_options(
            trim((string) $this->input->get('q', TRUE)),
            20
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('items' => $items)));
    }

    // Mao ni ang datatable flow sa Users; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
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

    // Internal helper ni para user form; tawagon ra sulod application/controllers/Users.php, so ari ra pud pangitaa ang caller if mag-trace ka.
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

        $result = $this->user_service->save(
            $id,
            array(
                'first_name' => $this->input->post('first_name', TRUE),
                'middle_name' => $this->input->post('middle_name', TRUE),
                'last_name' => $this->input->post('last_name', TRUE),
                'username' => $this->input->post('username', TRUE),
                'role_id' => $this->input->post('role_id', TRUE),
                'status' => $this->input->post('status', TRUE),
                'password' => $id !== NULL ? $this->input->post('password', FALSE) : ''
            ),
            $user,
            (int) $this->session->userdata('user_id')
        );

        if (!$result['success']) {
            $this->render_user_form($id, $user, $result['message']);
            return;
        }

        if (!empty($result['self_deactivated'])) {
            $this->session->sess_destroy();
            redirect('login');
            return;
        }

        if ($result['temporary_password'] !== NULL) {
            if ($this->input->is_ajax_request()) {
                $this->load->view('modal/users/created', array(
                    'username' => $result['username'],
                    'temporary_password' => $result['temporary_password']
                ));
                return;
            }

            $this->session->set_flashdata('temporary_password', $result['temporary_password']);
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

    // Internal helper ni para render user form; tawagon ra sulod application/controllers/Users.php, so ari ra pud pangitaa ang caller if mag-trace ka.
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

    // Internal helper ni para require user form permission; tawagon ra sulod application/controllers/Users.php, so ari ra pud pangitaa ang caller if mag-trace ka.
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

    // Internal helper ni para require permission; tawagon ra sulod application/controllers/Users.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function require_permission($permission_key) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_key)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
