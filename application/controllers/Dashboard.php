<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    // Dashboard controller ni bai; diri gi-handle ang overview data para one place ra.
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('Product_model');
        $this->load->model('Stock_model');
        $this->load->model('User_model');
    }
    // Mao ni ang main dashboard load: check access first, then kuhaon ang summary data.
    public function index() {
        $this->require_permission('dashboard.view');

        $data['total_products'] = $this->Product_model->get_total_products();
        $data['total_stock'] = $this->Product_model->get_total_stock();
        $data['low_stock_items'] = $this->Stock_model->count_low_stock_products();
        $data['today_stock_in'] = $this->Stock_model->get_today_quantity('stock_in');
        $data['today_stock_out'] = $this->Stock_model->get_today_quantity('stock_out');
        $data['inventory_value'] = $this->Stock_model->get_inventory_value();
        $data['stock_by_category'] = $this->Stock_model->get_stock_by_category();
        $data['monthly_movement'] = $this->Stock_model->get_monthly_movement_summary();
        $data['page_title'] = 'Dashboard';

        $this->load->view('templates/header', $data);
        $this->load->view('dashboard/index', $data);
        $this->load->view('templates/footer');
    }
    // Simple permission guard ni para dili maka-sulod ang user if walay required access.
    private function require_permission($permission_name) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_name)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
