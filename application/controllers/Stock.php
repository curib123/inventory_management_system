<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Stock extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(array('session', 'form_validation'));
        $this->load->helper(array('form', 'url'));
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
        $this->load->model('Stock_model');
        $this->load->model('Product_model');
        $this->load->model('Supplier_model');
        $this->load->model('User_model');
    }

    public function index() { $this->history(); }

    public function history() {
        $this->require_permission('view_reports');
        $limit = 20;
        $page = max(1, (int) $this->input->get('per_page', TRUE));
        $data['transactions'] = $this->Stock_model->get_transactions($limit, ($page - 1) * $limit);
        $data['pagination'] = $this->paginate($this->Stock_model->count_transactions(), $limit, 'stock/history');
        $data['page_title'] = 'Stock Movement History';
        $this->load->view('templates/header', $data);
        $this->load->view('stock/history', $data);
        $this->load->view('templates/footer');
    }

    public function details($id) {
        $this->require_permission('view_reports');
        $data['transaction'] = $this->Stock_model->get_transaction($id);
        if (!$data['transaction']) {
            show_404();
        }
        $data['items'] = $this->Stock_model->get_transaction_items($id);
        $data['page_title'] = 'Stock Transaction Details';
        $this->load->view('templates/header', $data);
        $this->load->view('stock/details', $data);
        $this->load->view('templates/footer');
    }

    public function stock_in() {
        $this->require_permission('manage_stock_in');
        $this->transaction_form('stock_in');
    }

    public function stock_out() {
        $this->require_permission('manage_stock_out');
        $this->transaction_form('stock_out');
    }

    public function adjustment() {
        $this->require_permission('manage_adjustments');
        $this->form_validation->set_rules('product_id', 'Product', 'required|integer|greater_than[0]');
        $this->form_validation->set_rules('actual_stock', 'Actual Stock', 'required|integer|greater_than_equal_to[0]');
        $this->form_validation->set_rules('reason', 'Reason', 'trim|required|max_length[255]');

        if ($this->form_validation->run() === FALSE) {
            $data['products'] = $this->Product_model->get_active();
            $data['page_title'] = 'Stock Adjustment';
            $this->load->view('templates/header', $data);
            $this->load->view('stock/adjustment', $data);
            $this->load->view('templates/footer');
            return;
        }

        $result = $this->Stock_model->create_adjustment(
            $this->input->post('product_id', TRUE),
            $this->input->post('actual_stock', TRUE),
            $this->input->post('reason', TRUE),
            $this->session->userdata('user_id')
        );

        if (!$result['success']) {
            $this->session->set_flashdata('error', $result['message']);
            redirect('stock/adjustment');
        }
        $this->session->set_flashdata('success', 'Stock adjustment saved: ' . $result['transaction_no']);
        redirect('stock/adjustments');
    }

    public function adjustments() {
        $this->require_permission('manage_adjustments');
        $limit = 20;
        $page = max(1, (int) $this->input->get('per_page', TRUE));
        $data['adjustments'] = $this->Stock_model->get_adjustments($limit, ($page - 1) * $limit);
        $data['pagination'] = $this->paginate($this->Stock_model->count_adjustments(), $limit, 'stock/adjustments');
        $data['page_title'] = 'Stock Adjustments';
        $this->load->view('templates/header', $data);
        $this->load->view('stock/adjustments', $data);
        $this->load->view('templates/footer');
    }

    public function low_stock() {
        $this->require_permission('view_dashboard');
        $data['products'] = $this->Stock_model->get_low_stock_products();
        $data['page_title'] = 'Low Stock Monitoring';
        $this->load->view('templates/header', $data);
        $this->load->view('stock/low_stock', $data);
        $this->load->view('templates/footer');
    }

    private function transaction_form($type) {
        if ($type === 'stock_in') {
            $this->form_validation->set_rules('supplier_id', 'Supplier', 'required|integer|greater_than[0]');
        }
        $this->form_validation->set_rules('remarks', 'Remarks', 'trim|max_length[255]');

        $is_post = $this->input->method(TRUE) === 'POST';
        $valid = $this->form_validation->run();
        $items = array();
        if ($is_post) {
            $product_ids = (array) $this->input->post('product_id', TRUE);
            $quantities = (array) $this->input->post('quantity', TRUE);
            foreach ($product_ids as $index => $product_id) {
                $product_id = (int) $product_id;
                $quantity = isset($quantities[$index]) ? (int) $quantities[$index] : 0;
                if ($product_id === 0 && $quantity === 0) {
                    continue;
                }
                if ($product_id <= 0 || $quantity <= 0) {
                    $valid = FALSE;
                    $data['item_error'] = 'Every used item row must contain a product and a quantity greater than zero.';
                    break;
                }
                $items[] = array('product_id' => $product_id, 'quantity' => $quantity);
            }
            if (empty($items)) {
                $valid = FALSE;
                $data['item_error'] = 'Add at least one product and quantity.';
            }
        }

        if (!$is_post || !$valid) {
            $data['products'] = $this->Product_model->get_active();
            $data['suppliers'] = $this->Supplier_model->get_all();
            $data['transaction_type'] = $type;
            $data['page_title'] = $type === 'stock_in' ? 'Stock In' : 'Stock Out';
            $this->load->view('templates/header', $data);
            $this->load->view('stock/transaction_form', $data);
            $this->load->view('templates/footer');
            return;
        }

        $result = $this->Stock_model->create_transaction(
            $type,
            $type === 'stock_in' ? $this->input->post('supplier_id', TRUE) : NULL,
            $this->input->post('remarks', TRUE),
            $this->session->userdata('user_id'),
            $items
        );

        if (!$result['success']) {
            $this->session->set_flashdata('error', $result['message']);
            redirect('stock/' . ($type === 'stock_in' ? 'in' : 'out'));
        }
        $this->session->set_flashdata('success', 'Stock transaction saved: ' . $result['transaction_no']);
        redirect('stock/history');
    }

    private function paginate($total_rows, $limit, $base_url) {
        $this->load->library('pagination');
        $config['base_url'] = site_url($base_url);
        $config['total_rows'] = (int) $total_rows;
        $config['per_page'] = (int) $limit;
        $config['use_page_numbers'] = TRUE;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'per_page';
        $this->pagination->initialize($config);
        return $this->pagination->create_links();
    }

    private function require_permission($permission_name) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_name)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
