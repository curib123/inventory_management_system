<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Activity_logs extends CI_Controller {

    // Setup ni sa Activity Logs controller; users.view protects audit history with the existing administration permission.
    public function __construct() {
        parent::__construct();
        $this->load->library(array('session', 'Datatable_service'));
        $this->load->helper(array('url', 'html'));

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('Activity_log_model');
    }

    // Audit workspace page with filter metadata for the shared server-side table component.
    public function index() {
        $this->require_permission('users.view');

        $data['page_title'] = 'Activity Logs';
        $data['audit_users'] = $this->Activity_log_model->get_filter_users();
        $data['audit_actions'] = $this->Activity_log_model->get_filter_actions();

        $this->load->view('templates/header', $data);
        $this->load->view('activity_logs/index', $data);
        $this->load->view('templates/footer');
    }

    // Server-side Activity Logs DataTable endpoint.
    public function datatable() {
        $this->require_permission('users.view');

        $columns = array(
            'l.created_at',
            'u.username',
            'l.action',
            'l.description',
            'l.ip_address'
        );

        $request = $this->datatable_service->request(
            $this->input,
            $columns,
            'l.created_at',
            'desc'
        );

        $logs = $this->Activity_log_model->get_datatable(
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir'],
            $request['filters']
        );

        $rows = array();

        foreach ($logs as $log) {
            $display_name = trim((string) $log->first_name . ' ' . (string) $log->last_name);

            if ($display_name === '') {
                $display_name = (string) $log->username;
            } else {
                $display_name .= ' (' . (string) $log->username . ')';
            }

            $rows[] = array(
                html_escape($log->created_at),
                html_escape($display_name),
                html_escape($log->action),
                html_escape($log->description ?: 'No description'),
                html_escape($log->ip_address ?: 'N/A')
            );
        }

        $payload = $this->datatable_service->payload(
            $request['draw'],
            $this->Activity_log_model->count_all(),
            $this->Activity_log_model->count_datatable_filtered(
                $request['search'],
                $request['filters']
            ),
            $rows
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    // Existing administration permission ang guard para no database permission migration needed.
    private function require_permission($permission_key) {
        $user_id = $this->session->userdata('user_id');

        if (!$user_id || !$this->authorization_service->has_permission($user_id, $permission_key)) {
            show_error('You do not have permission to access activity logs.', 403, 'Access Denied');
        }
    }
}
