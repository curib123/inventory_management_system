<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Stock extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(array('session', 'form_validation'));
        $this->load->helper(array('form', 'url'));
        $this->load->library('Datatable_service');

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('Stock_model');
        $this->load->model('Product_model');
        $this->load->model('Supplier_model');
        $this->load->model('User_model');
    }

    public function index() {
        $this->history();
    }

    public function history() {
        $this->require_permission('stock.history');

        $data['page_title'] = 'Stock Movement History';
        $this->load->view('templates/header', $data);
        $this->load->view('stock/history', $data);
        $this->load->view('templates/footer');
    }

    public function details($id) {
        $this->require_permission('stock.history');

        $data['transaction'] = $this->Stock_model->get_transaction($id);
        if (!$data['transaction']) {
            show_404();
        }

        $data['items'] = $this->Stock_model->get_transaction_items($id);
        $this->load->view('modal/stock/details', $data);
    }

    public function stock_in() {
        $this->require_permission('stock.stock_in');
        $this->transaction_form('stock_in');
    }

    public function stock_out() {
        $this->require_permission('stock.stock_out');
        $this->transaction_form('stock_out');
    }

    public function supplier_products($supplier_id) {
        $this->require_permission('stock.stock_in');

        if ($this->input->method(TRUE) !== 'GET') {
            show_error('Invalid request method.', 405, 'Method Not Allowed');
        }

        $supplier = $this->Supplier_model->get_by_id($supplier_id);

        if (!$supplier || !(int) $supplier->status) {
            show_error('Supplier not found or inactive.', 404, 'Supplier Not Found');
        }

        $this->load->view('components/stock/product_quantity_list', array(
            'products' => $this->Product_model->get_active_by_supplier($supplier_id),
            'quantities' => array()
        ));
    }

    public function adjustment() {
        $this->require_permission('stock.adjust');

        $this->form_validation->set_rules('product_id', 'Product', 'required|integer|greater_than[0]');
        $this->form_validation->set_rules('actual_stock', 'Actual Stock', 'required|integer|greater_than_equal_to[0]');
        $this->form_validation->set_rules('reason', 'Reason', 'trim|required|max_length[255]');

        if ($this->form_validation->run() === FALSE) {
            $this->render_adjustment_form();
            return;
        }

        $result = $this->Stock_model->create_adjustment(
            $this->input->post('product_id', TRUE),
            $this->input->post('actual_stock', TRUE),
            $this->input->post('reason', TRUE),
            $this->session->userdata('user_id')
        );

        if (!$result['success']) {
            $this->render_adjustment_form($result['message']);
            return;
        }

        $this->session->set_flashdata('success', 'Stock adjustment saved: ' . $result['transaction_no']);
        redirect('stock/adjustments');
    }

    public function adjustments() {
        $this->require_permission('stock.adjust');

        $data['page_title'] = 'Stock Adjustments';
        $this->load->view('templates/header', $data);
        $this->load->view('stock/adjustments', $data);
        $this->load->view('templates/footer');
    }

    public function low_stock() {
        $this->require_permission('stock.view');

        $data['page_title'] = 'Low Stock Monitoring';
        $this->load->view('templates/header', $data);
        $this->load->view('stock/low_stock', $data);
        $this->load->view('templates/footer');
    }

    public function history_datatable() {
        $this->require_permission('stock.history');

        $columns = array('t.transaction_no', 't.type', 's.supplier_name', 'u.username', 't.created_at', NULL);
        $request = $this->datatable_service->request($this->input, $columns, 't.created_at', 'desc');
        $transactions = $this->Stock_model->get_transactions_datatable(
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir']
        );

        $rows = array();
        foreach ($transactions as $transaction) {
            $rows[] = array(
                html_escape($transaction->transaction_no),
                html_escape($transaction->type),
                html_escape($transaction->supplier_name ?: 'N/A'),
                html_escape($transaction->username),
                html_escape($transaction->created_at),
                ui_modal_action_group(array(
                    array(
                        'label' => 'Details',
                        'url' => site_url('stock/details/' . (int) $transaction->id),
                        'variant' => 'secondary',
                        'icon' => 'bi-eye'
                    )
                ))
            );
        }

        $payload = $this->datatable_service->payload(
            $request['draw'],
            $this->Stock_model->count_transactions(),
            $this->Stock_model->count_transactions_filtered($request['search']),
            $rows
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    public function adjustments_datatable() {
        $this->require_permission('stock.adjust');

        $columns = array('p.product_name', 'a.system_stock', 'a.actual_stock', 'a.difference', 'a.reason', 'u.username', 'a.created_at');
        $request = $this->datatable_service->request($this->input, $columns, 'a.created_at', 'desc');
        $adjustments = $this->Stock_model->get_adjustments_datatable(
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir']
        );

        $rows = array();
        foreach ($adjustments as $adjustment) {
            $rows[] = array(
                html_escape($adjustment->product_code . ' - ' . $adjustment->product_name),
                (int) $adjustment->system_stock,
                (int) $adjustment->actual_stock,
                (int) $adjustment->difference,
                html_escape($adjustment->reason),
                html_escape($adjustment->username),
                html_escape($adjustment->created_at)
            );
        }

        $payload = $this->datatable_service->payload(
            $request['draw'],
            $this->Stock_model->count_adjustments(),
            $this->Stock_model->count_adjustments_filtered($request['search']),
            $rows
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    public function low_stock_datatable() {
        $this->require_permission('stock.view');

        $columns = array('p.product_code', 'p.product_name', 'p.stock', 'p.reorder_level', 'p.unit');
        $request = $this->datatable_service->request($this->input, $columns, 'p.stock', 'asc');
        $products = $this->Stock_model->get_low_stock_datatable(
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir']
        );

        $rows = array();
        foreach ($products as $product) {
            $rows[] = array(
                html_escape($product->product_code),
                html_escape($product->product_name),
                (int) $product->stock,
                (int) $product->reorder_level,
                html_escape($product->unit)
            );
        }

        $payload = $this->datatable_service->payload(
            $request['draw'],
            $this->Stock_model->count_low_stock_products(),
            $this->Stock_model->count_low_stock_filtered($request['search']),
            $rows
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    private function transaction_form($type) {
        if ($type === 'stock_in') {
            $this->form_validation->set_rules('supplier_id', 'Supplier', 'required|integer|greater_than[0]');
        }

        $this->form_validation->set_rules('remarks', 'Remarks', 'trim|max_length[255]');

        $is_post = $this->input->method(TRUE) === 'POST';
        $valid = $this->form_validation->run();
        $item_error = '';
        $items = $is_post ? $this->read_transaction_items($item_error, $type) : array();

        if ($item_error !== '') {
            $valid = FALSE;
        }

        if (!$is_post || !$valid) {
            $this->render_transaction_form($type, $item_error);
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
            $this->render_transaction_form($type, $result['message']);
            return;
        }

        $this->session->set_flashdata('success', 'Stock transaction saved: ' . $result['transaction_no']);
        redirect('stock/history');
    }

    private function render_transaction_form($type, $item_error = '') {
        $data['products'] = $type === 'stock_out'
            ? $this->Product_model->get_active()
            : array();

        $data['suppliers'] = $this->Supplier_model->get_active();
        $data['transaction_type'] = $type;
        $data['page_title'] = $type === 'stock_in' ? 'Stock In' : 'Stock Out';
        $data['item_error'] = $item_error;
        $data['stock_in_products'] = array();
        $data['stock_in_quantities'] = array();

        if ($type === 'stock_in') {
            $supplier_id = (int) $this->input->post('supplier_id', TRUE);

            if ($supplier_id > 0) {
                $supplier = $this->Supplier_model->get_by_id($supplier_id);

                if ($supplier && (int) $supplier->status === 1) {
                    $data['stock_in_products'] = $this->Product_model->get_active_by_supplier($supplier_id);
                    $data['stock_in_quantities'] = $this->posted_quantity_map();
                }
            }
        }

        $this->load->view('modal/stock/transaction_form', $data);
    }

    private function render_adjustment_form($form_error = '') {
        $data['products'] = $this->Product_model->get_active();
        $data['page_title'] = 'Stock Adjustment';
        $data['form_error'] = $form_error;

        $this->load->view('modal/stock/adjustment', $data);
    }

    private function read_transaction_items(&$error, $type) {
        $error = '';
        $items = array();
        $product_ids = (array) $this->input->post('product_id', TRUE);
        $quantities = (array) $this->input->post('quantity', TRUE);

        foreach ($product_ids as $index => $raw_product_id) {
            $raw_product_id = trim((string) $raw_product_id);
            $raw_quantity = isset($quantities[$index])
                ? trim((string) $quantities[$index])
                : '';

            $product_id_is_valid = ctype_digit($raw_product_id) && (int) $raw_product_id > 0;
            $quantity_is_blank = $raw_quantity === '' || $raw_quantity === '0';
            $quantity_is_valid = ctype_digit($raw_quantity) && (int) $raw_quantity > 0;

            if ($type === 'stock_in') {
                // Every supplier product is rendered. Blank/zero means not included.
                if ($quantity_is_blank) {
                    continue;
                }

                if (!$product_id_is_valid || !$quantity_is_valid) {
                    $error =
                        'Stock-in product IDs and quantities must be positive whole numbers.';
                    return array();
                }
            } else {
                $product_is_blank = $raw_product_id === '' || $raw_product_id === '0';

                if ($product_is_blank && $quantity_is_blank) {
                    continue;
                }

                if (!$product_id_is_valid || !$quantity_is_valid) {
                    $error =
                        'Every used stock-out row must contain a valid product and a positive whole-number quantity.';
                    return array();
                }
            }

            $items[] = array(
                'product_id' => (int) $raw_product_id,
                'quantity' => (int) $raw_quantity
            );
        }

        if (empty($items)) {
            $error = 'Add a quantity greater than zero for at least one product.';
        }

        return $items;
    }

    private function posted_quantity_map() {
        $map = array();
        $product_ids = (array) $this->input->post('product_id', TRUE);
        $quantities = (array) $this->input->post('quantity', TRUE);

        foreach ($product_ids as $index => $product_id) {
            $product_id = (int) $product_id;
            $quantity = isset($quantities[$index]) ? (int) $quantities[$index] : 0;

            if ($product_id > 0 && $quantity > 0) {
                $map[$product_id] = $quantity;
            }
        }

        return $map;
    }

    private function require_permission($permission_key) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_key)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
