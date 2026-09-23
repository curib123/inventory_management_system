<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Stock extends CI_Controller {

   // stock controller to handle stock management functionality
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

    // Function to check if the user has the required permission
    public function index() {
        $this->history();
    }
    // Function to check if the user has the required permission and display stock movement history
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
    // Function to check if the user has the required permission and display details of a specific stock transaction
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
    // Function to check if the user has the required permission and display the stock adjustment form
    public function stock_in() {
        $this->require_permission('manage_stock_in');
        $this->transaction_form('stock_in');
    }
    // Function to check if the user has the required permission and display the stock out form
    public function stock_out() {
        $this->require_permission('manage_stock_out');
        $this->transaction_form('stock_out');
    }
    // Function to check if the user has the required permission and display the stock adjustment form
    public function adjustment() {
        $this->require_permission('manage_adjustments');

        $this->form_validation->set_rules('product_id', 'Product', 'required|integer');
        $this->form_validation->set_rules('actual_stock', 'Actual Stock', 'required|integer');
        $this->form_validation->set_rules('reason', 'Reason', 'required|max_length[255]');

        if ($this->form_validation->run() === FALSE) {
            $data['products'] = $this->Product_model->get_all(10000, 0);
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
    // Function to check if the user has the required permission and display the stock adjustments history
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
    // Function to check if the user has the required permission and display details of a specific stock adjustment
    public function low_stock() {
        $this->require_permission('view_dashboard');

        $data['products'] = $this->Stock_model->get_low_stock_products();
        $data['page_title'] = 'Low Stock Monitoring';
        $this->load->view('templates/header', $data);
        $this->load->view('stock/low_stock', $data);
        $this->load->view('templates/footer');
    }
    // Function to check if the user has the required permission and display details of a specific stock adjustment
    private function transaction_form($type) {
        $this->form_validation->set_rules('product_id[]', 'Product', 'required|integer');
        $this->form_validation->set_rules('quantity[]', 'Quantity', 'required|integer|greater_than[0]');

        if ($this->form_validation->run() === FALSE) {
            $data['products'] = $this->Product_model->get_all(10000, 0);
            $data['suppliers'] = $this->Supplier_model->get_all();
            $data['transaction_type'] = $type;
            $data['page_title'] = $type === 'stock_in' ? 'Stock In' : 'Stock Out';
            $this->load->view('templates/header', $data);
            $this->load->view('stock/transaction_form', $data);
            $this->load->view('templates/footer');
            return;
        }

        $product_ids = (array) $this->input->post('product_id', TRUE);
        $quantities = (array) $this->input->post('quantity', TRUE);
        $items = array();
        foreach ($product_ids as $index => $product_id) {
            $items[] = array(
                'product_id' => $product_id,
                'quantity' => isset($quantities[$index]) ? $quantities[$index] : 0
            );
        }

        $result = $this->Stock_model->create_transaction(
            $type,
            $this->input->post('supplier_id', TRUE),
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
    // Function to check if the user has the required permission and display the stock adjustments history
    private function paginate($total_rows, $limit, $base_url) {
        $this->load->library('pagination');
        $config['base_url'] = site_url($base_url);
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $limit;
        $config['use_page_numbers'] = TRUE;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'per_page';
        $config['full_tag_open'] = '<div class="pagination" style="margin-top:20px;">';
        $config['full_tag_close'] = '</div>';
        $this->pagination->initialize($config);
        return $this->pagination->create_links();
    }
    // Function to check if the user has the required permission
    private function require_permission($permission_name) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_name)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
