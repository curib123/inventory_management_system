<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    // Setup ni sa Auth controller; CodeIgniter mo-run ani automatically, while route mapping makita sa application/config/routes.php.
    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->library(array('Auth_service', 'User_service', 'session', 'form_validation'));
        $this->load->helper(array('url', 'form'));
    }

    // Mao ni ang index flow sa Auth; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function index() {
        if ($this->session->userdata('logged_in')) {
            $this->redirect_to_authorized_page();
            return;
        }

        $this->login();
    }

    // Mao ni ang login flow sa Auth; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function login() {
        if ($this->User_model->count_all() === 0) {
            redirect('setup');
            return;
        }

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

    // Mao ni ang change password flow sa Auth; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function change_password() {
        if (!$this->session->userdata('logged_in')) {
            show_error('Your session is no longer active.', 401, 'Session Expired');
        }

        $user_id = (int) $this->session->userdata('user_id');

        if ($this->input->method(TRUE) === 'GET') {
            $this->load->view('modal/auth/change_password', array(
                'password_change_required' => (bool) $this->session->userdata('must_change_password')
            ));
            return;
        }

        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Invalid request method.', 405, 'Method Not Allowed');
        }

        $action = trim((string) $this->input->post('password_action', TRUE));

        if ($action === 'later') {
            $this->session->set_userdata('password_change_deferred', TRUE);
            $this->output
                ->set_header('X-Modal-Close: 1')
                ->set_output('');
            return;
        }

        $this->form_validation->set_rules(
            'current_password',
            'Current Password',
            'required|min_length[8]|max_length[255]'
        );
        $this->form_validation->set_rules(
            'new_password',
            'New Password',
            'required|min_length[8]|max_length[255]'
        );
        $this->form_validation->set_rules(
            'confirm_password',
            'Confirm Password',
            'required|matches[new_password]'
        );

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('modal/auth/change_password', array(
                'password_change_required' => (bool) $this->session->userdata('must_change_password')
            ));
            return;
        }

        $result = $this->user_service->change_password(
            $user_id,
            (string) $this->input->post('current_password', FALSE),
            (string) $this->input->post('new_password', FALSE)
        );

        if (!$result['success']) {
            $this->load->view('modal/auth/change_password', array(
                'password_change_required' => (bool) $this->session->userdata('must_change_password'),
                'password_error' => $result['message']
            ));
            return;
        }

        $this->session->set_userdata(array(
            'must_change_password' => FALSE,
            'password_change_deferred' => FALSE
        ));
        $this->session->set_flashdata('success', 'Password changed successfully.');

        $this->output
            ->set_header('X-Modal-Close: 1')
            ->set_header('X-Page-Reload: 1')
            ->set_output('');
    }

    // Mao ni ang logout confirm flow sa Auth; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function logout_confirm() {
        if (!$this->session->userdata('logged_in')) {
            show_error('Your session is no longer active.', 401, 'Session Expired');
        }

        if ($this->input->method(TRUE) !== 'GET') {
            show_error('Invalid request method.', 405, 'Method Not Allowed');
        }

        $this->load->view('modal/auth/logout');
    }

    // Mao ni ang logout flow sa Auth; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function logout() {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Invalid request method.', 405, 'Method Not Allowed');
        }

        $this->session->sess_destroy();
        redirect('login');
    }

    // Internal helper ni para redirect to authorized page; tawagon ra sulod application/controllers/Auth.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function redirect_to_authorized_page() {
        $user_id = (int) $this->session->userdata('user_id');

        $route = $this->authorization_service->first_authorized_route($user_id);

        if ($route !== NULL) {
            redirect($route);
            return;
        }

        show_error(
            'Your account is active, but its role has no page-view permission assigned.',
            403,
            'No Access Assigned'
        );
    }
}
