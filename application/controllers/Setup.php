<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Setup extends CI_Controller {

    // Setup ni sa Setup controller; CodeIgniter mo-run ani automatically, while route mapping makita sa application/config/routes.php.
    public function __construct() {
        parent::__construct();

        $this->load->library(array('session', 'form_validation', 'Setup_service'));
        $this->load->helper(array('url', 'form'));
        $this->load->model(array('User_model', 'Role_model'));
    }

    // Mao ni ang index flow sa Setup; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function index() {
        $setup_status = $this->setup_service->status();

        if (!empty($setup_status['configured'])) {
            redirect('login');
            return;
        }

        if (empty($setup_status['ready'])) {
            show_error(
                $setup_status['message'],
                500,
                'Setup Error'
            );
        }

        $this->form_validation->set_rules(
            'first_name',
            'First Name',
            'trim|required|max_length[100]'
        );
        $this->form_validation->set_rules(
            'middle_name',
            'Middle Name',
            'trim|max_length[100]'
        );
        $this->form_validation->set_rules(
            'last_name',
            'Last Name',
            'trim|required|max_length[100]'
        );
        $this->form_validation->set_rules(
            'username',
            'Username',
            'trim|required|min_length[3]|max_length[50]|alpha_dash'
        );
        $this->form_validation->set_rules(
            'password',
            'Password',
            'required|min_length[12]|max_length[255]'
        );
        $this->form_validation->set_rules(
            'password_confirm',
            'Confirm Password',
            'required|matches[password]'
        );

        if ($this->input->method(TRUE) === 'POST') {
            if ($this->form_validation->run() === FALSE) {
                $this->load->view('auth/setup');
                return;
            }

            $result = $this->setup_service->create_initial_admin(array(
                'first_name' => $this->input->post('first_name', TRUE),
                'middle_name' => $this->input->post('middle_name', TRUE),
                'last_name' => $this->input->post('last_name', TRUE),
                'username' => $this->input->post('username', TRUE),
                'password' => $this->input->post('password', FALSE)
            ));

            if (!$result['success']) {
                if (!empty($result['configured'])) {
                    redirect('login');
                    return;
                }

                $data['error'] = $result['message'];
                $this->load->view('auth/setup', $data);
                return;
            }

            $this->session->set_flashdata(
                'success',
                'Administrator account created. Sign in with your new credentials.'
            );

            redirect('login');
            return;
        }

        $this->load->view('auth/setup');
    }
}
