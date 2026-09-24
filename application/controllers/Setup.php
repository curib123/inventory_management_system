<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Setup extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->library(array('session', 'form_validation'));
        $this->load->helper(array('url', 'form'));
        $this->load->model(array('User_model', 'Role_model'));
    }

    public function index() {
        if ($this->User_model->count_all() > 0) {
            redirect('login');
            return;
        }

        $admin_role = $this->Role_model->get_by_name('admin');

        if (!$admin_role || !(int) $admin_role->status) {
            show_error(
                'The admin role is missing or inactive. Import the current database schema before initial setup.',
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

            $username = trim((string) $this->input->post('username', TRUE));

            if ($this->User_model->username_exists($username)) {
                $data['error'] = 'That username already exists.';
                $this->load->view('auth/setup', $data);
                return;
            }

            $saved = $this->User_model->save(array(
                'first_name' => trim((string) $this->input->post('first_name', TRUE)),
                'middle_name' => trim((string) $this->input->post('middle_name', TRUE)),
                'last_name' => trim((string) $this->input->post('last_name', TRUE)),
                'username' => $username,
                'password' => password_hash(
                    (string) $this->input->post('password', FALSE),
                    PASSWORD_DEFAULT
                ),
                'role_id' => (int) $admin_role->id,
                'status' => 1
            ));

            if (!$saved) {
                $data['error'] = 'The administrator account could not be created.';
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
