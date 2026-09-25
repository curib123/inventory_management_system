<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    // Setup ni sa Dashboard controller; CodeIgniter mo-run ani automatically, while route mapping makita sa application/config/routes.php.
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
    // Mao ni ang index flow sa Dashboard; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function index() {
        $this->require_permission('dashboard.view');

        $data['total_products'] = $this->Product_model->get_total_products();
        $data['total_stock'] = $this->Product_model->get_total_stock();
        $data['low_stock_items'] = $this->Stock_model->count_low_stock_products();
        $data['today_stock_in'] = $this->Stock_model->get_today_quantity('stock_in');
        $data['today_stock_out'] = $this->Stock_model->get_today_quantity('stock_out');
        $data['inventory_value'] = $this->Stock_model->get_inventory_value();
        $data['stock_health'] = $this->Stock_model->get_stock_health_summary();
        $data['stock_by_category'] = $this->Stock_model->get_stock_by_category();
        $data['monthly_movement'] = $this->normalize_monthly_movement(
            $this->Stock_model->get_monthly_movement_summary()
        );
        $data['page_title'] = 'Dashboard';

        $this->load->view('templates/header', $data);
        $this->load->view('dashboard/index', $data);
        $this->load->view('templates/footer');
    }
    // Internal helper ni para normalize monthly movement; tawagon ra sulod application/controllers/Dashboard.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function normalize_monthly_movement($rows) {
        $by_month = array();

        foreach ((array) $rows as $row) {
            if (!isset($row->month)) {
                continue;
            }

            $by_month[(string) $row->month] = array(
                'stock_in' => isset($row->stock_in) ? (int) $row->stock_in : 0,
                'stock_out' => isset($row->stock_out) ? (int) $row->stock_out : 0
            );
        }

        $series = array();

        for ($months_ago = 11; $months_ago >= 0; $months_ago--) {
            $timestamp = strtotime(
                'first day of -' . $months_ago . ' month'
            );
            $key = date('Y-m', $timestamp);
            $movement = isset($by_month[$key])
                ? $by_month[$key]
                : array('stock_in' => 0, 'stock_out' => 0);

            $series[] = array(
                'month' => $key,
                'label' => date('M Y', $timestamp),
                'stock_in' => (int) $movement['stock_in'],
                'stock_out' => (int) $movement['stock_out']
            );
        }

        return $series;
    }

    // Internal helper ni para require permission; tawagon ra sulod application/controllers/Dashboard.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function require_permission($permission_key) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_key)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
