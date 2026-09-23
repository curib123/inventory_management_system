<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    // user authentication controller to handle login and logout functionality
    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->library('Auth_service');
        $this->load->library('session');
        $this->load->helper('url');
    }
    // user login function to check if user is logged in and redirect to dashboard if logged in
    public function index() {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }

        $this->login();
    }
    // user login function to authenticate users and set session data
    public function login() {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }

        $this->load->helper(array('form'));
        $this->load->library('form_validation');
        $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[3]|max_length[50]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]|max_length[255]');

        if ($this->input->post()) {
            if ($this->form_validation->run() === FALSE) {
                $data['error'] = validation_errors();
                $this->load->view('auth/login', $data);
                return;
            }

            $username = $this->input->post('username');
            $password = $this->input->post('password');

            $session_data = $this->auth_service->authenticate($this->User_model, $username, $password);

            if ($session_data) {
                $this->session->set_userdata($session_data);
                redirect('dashboard');
            } else {
                $data['error'] = 'Invalid username or password.';
                $this->load->view('auth/login', $data);
                return;
            }
        }

        $this->load->view('auth/login');
    }
    // user logout function to destroy session data and redirect to login page
    public function logout() {
        $this->session->sess_destroy();
        redirect('login');
    }
}
