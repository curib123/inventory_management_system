<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->library(array('Auth_service', 'session', 'form_validation'));
        $this->load->helper(array('url', 'form'));
    }

    public function index() {
        if ($this->session->userdata('logged_in')) {
            $this->redirect_to_authorized_page();
            return;
        }

        $this->login();
    }

    public function login() {
        if ($this->session->userdata('logged_in')) {
            $this->redirect_to_authorized_page();
            return;
        }

        $this->form_validation->set_rules(
            'username',
            'Username',
            'trim|required|min_length[3]|max_length[50]'
        );
        $this->form_validation->set_rules(
            'password',
            'Password',
            'required|min_length[8]|max_length[255]'
        );

        if ($this->input->method(TRUE) === 'POST') {
            if ($this->form_validation->run() === FALSE) {
                $data['error'] = validation_errors();
                $this->load->view('auth/login', $data);
                return;
            }

            $session_data = $this->auth_service->authenticate(
                $this->User_model,
                trim($this->input->post('username', TRUE)),
                (string) $this->input->post('password', FALSE)
            );

            if ($session_data) {
                $this->session->sess_regenerate(TRUE);
                $this->session->set_userdata($session_data);
                $this->redirect_to_authorized_page();
                return;
            }

            $data['error'] = 'Invalid username or password.';
            $this->load->view('auth/login', $data);
            return;
        }

        $this->load->view('auth/login');
    }

    public function logout() {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Invalid request method.', 405, 'Method Not Allowed');
        }

        $this->session->sess_destroy();
        redirect('login');
    }

    private function redirect_to_authorized_page() {
        $user_id = (int) $this->session->userdata('user_id');

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
            if ($this->User_model->has_permission($user_id, $permission_key)) {
                redirect($route);
                return;
            }
        }

        show_error(
            'Your account is active, but its role has no page-view permission assigned.',
            403,
            'No Access Assigned'
        );
    }
}
