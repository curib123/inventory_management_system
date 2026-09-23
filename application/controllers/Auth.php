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
            redirect('dashboard');
        }
        $this->login();
    }

    public function login() {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }

        $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[3]|max_length[50]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]|max_length[255]');

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
                redirect('dashboard');
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
}
