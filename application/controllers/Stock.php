<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Stock extends CI_Controller {

    // Setup ni sa Stock controller; CodeIgniter mo-run ani automatically, while route mapping makita sa application/config/routes.php.
    public function __construct() {
        parent::__construct();
        $this->load->library(array('session', 'form_validation'));
        $this->load->helper(array('form', 'url'));
        $this->load->library(array('Datatable_service', 'Stock_service'));

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('Stock_model');
        $this->load->model('Product_model');
        $this->load->model('Supplier_model');
        $this->load->model('User_model');
    }

    // Mao ni ang index flow sa Stock; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function index() {
        $this->history();
    }

    // Mao ni ang history flow sa Stock; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function history() {
        $this->require_permission('stock.history');

        $data['page_title'] = 'Stock Movement History';
        $this->load->view('templates/header', $data);
        $this->load->view('stock/history', $data);
        $this->load->view('templates/footer');
    }

    // Mao ni ang details flow sa Stock; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function details($id) {
        $this->require_permission('stock.history');

        $data['transaction'] = $this->Stock_model->get_transaction($id);
        if (!$data['transaction']) {
            show_404();
        }

        $data['items'] = $this->Stock_model->get_transaction_items($id);
        $this->load->view('modal/stock/details', $data);
    }

    // Mao ni ang stock in flow sa Stock; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function stock_in() {
        $this->require_permission('stock.stock_in');
        $this->transaction_form('stock_in');
    }

    // Mao ni ang stock out flow sa Stock; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function stock_out() {
        $this->require_permission('stock.stock_out');
        $this->transaction_form('stock_out');
    }

    // Mao ni ang supplier search flow sa Stock; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function supplier_search() {
        $mode = strtolower(trim((string) $this->input->get('mode', TRUE)));

        if (!in_array($mode, array('stock_in', 'stock_out', 'adjustment'), TRUE)) {
            $mode = 'stock_in';
        }

        if ($mode === 'stock_out') {
            $this->require_permission('stock.stock_out');
        } elseif ($mode === 'adjustment') {
            $this->require_permission('stock.adjust');
        } else {
            $this->require_permission('stock.stock_in');
        }

        if ($this->input->method(TRUE) !== 'GET') {
            show_error('Invalid request method.', 405, 'Method Not Allowed');
        }

        $items = $this->stock_service->supplier_options(
            trim((string) $this->input->get('q', TRUE)),
            20
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('items' => $items)));
    }

    // Mao ni ang adjustment products search flow sa Stock; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function adjustment_products_search() {
        $this->require_permission('stock.adjust');

        if ($this->input->method(TRUE) !== 'GET') {
            show_error('Invalid request method.', 405, 'Method Not Allowed');
        }

        $result = $this->stock_service->adjustment_product_options(
            trim((string) $this->input->get('q', TRUE)),
            trim((string) $this->input->get('supplier_id', TRUE)),
            20
        );

        if (!$result['success']) {
            show_error(
                $result['message'],
                isset($result['status']) ? (int) $result['status'] : 400,
                'Invalid Adjustment Product Search'
            );
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('items' => $result['items'])));
    }

    // Mao ni ang supplier products flow sa Stock; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function supplier_products($supplier_id) {
        $mode = strtolower(trim((string) $this->input->get('mode', TRUE)));
        $mode = $mode === 'stock_out' ? 'stock_out' : 'stock_in';

        $this->require_permission(
            $mode === 'stock_out' ? 'stock.stock_out' : 'stock.stock_in'
        );

        if ($this->input->method(TRUE) !== 'GET') {
            show_error('Invalid request method.', 405, 'Method Not Allowed');
        }

        $result = $this->stock_service->supplier_products($supplier_id, $mode);

        if (!$result['success']) {
            show_error(
                $result['message'],
                isset($result['status']) ? (int) $result['status'] : 400,
                'Supplier Products Error'
            );
        }

        $this->load->view('components/stock/product_quantity_list', array(
            'products' => $result['products'],
            'quantities' => array(),
            'mode' => $result['mode']
        ));
    }

    // Mao ni ang adjustment flow sa Stock; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function adjustment() {
        $this->require_permission('stock.adjust');

        $this->form_validation->set_rules('supplier_filter', 'Supplier', 'trim|required|max_length[32]');
        $this->form_validation->set_rules('product_id', 'Product', 'required|integer|greater_than[0]');
        $this->form_validation->set_rules(
            'actual_stock',
            'Actual Stock',
            'required|integer|greater_than_equal_to[0]|less_than_equal_to[2147483647]'
        );
        $this->form_validation->set_rules('reason', 'Reason', 'trim|required|max_length[255]');

        if ($this->form_validation->run() === FALSE) {
            $this->render_adjustment_form();
            return;
        }

        $result = $this->stock_service->create_adjustment(
            $this->input->post('supplier_filter', TRUE),
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

    // Mao ni ang adjustments flow sa Stock; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function adjustments() {
        $this->require_permission('stock.adjust');

        $data['page_title'] = 'Stock Adjustments';
        $this->load->view('templates/header', $data);
        $this->load->view('stock/adjustments', $data);
        $this->load->view('templates/footer');
    }

    // Mao ni ang low stock flow sa Stock; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function low_stock() {
        $this->require_permission('stock.view');

        $data['page_title'] = 'Low Stock Monitoring';
        $this->load->view('templates/header', $data);
        $this->load->view('stock/low_stock', $data);
        $this->load->view('templates/footer');
    }

    // Mao ni ang history datatable flow sa Stock; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function history_datatable() {
        $this->require_permission('stock.history');

        $columns = array('t.transaction_no', 't.type', 's.supplier_name', 'u.username', 't.created_at', NULL);
        $request = $this->datatable_service->request($this->input, $columns, 't.created_at', 'desc');
        $transactions = $this->Stock_model->get_transactions_datatable(
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir'],
            $request['filters']
        );

        $rows = array();
        foreach ($transactions as $transaction) {
            $rows[] = array(
                html_escape($transaction->transaction_no),
                html_escape($transaction->type),
                html_escape($transaction->supplier_name),
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
            $this->Stock_model->count_transactions_filtered($request['search'], $request['filters']),
            $rows
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    // Mao ni ang adjustments datatable flow sa Stock; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function adjustments_datatable() {
        $this->require_permission('stock.adjust');

        $columns = array('p.product_name', 'a.system_stock', 'a.actual_stock', 'a.difference', 'a.reason', 'u.username', 'a.created_at');
        $request = $this->datatable_service->request($this->input, $columns, 'a.created_at', 'desc');
        $adjustments = $this->Stock_model->get_adjustments_datatable(
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir'],
            $request['filters']
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
            $this->Stock_model->count_adjustments_filtered($request['search'], $request['filters']),
            $rows
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    // Mao ni ang low stock datatable flow sa Stock; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function low_stock_datatable() {
        $this->require_permission('stock.view');

        $columns = array('p.product_code', 'p.product_name', 'p.stock', 'p.reorder_level', 'p.unit');
        $request = $this->datatable_service->request($this->input, $columns, 'p.stock', 'asc');
        $products = $this->Stock_model->get_low_stock_datatable(
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir'],
            $request['filters']
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
            $this->Stock_model->count_low_stock_filtered($request['search'], $request['filters']),
            $rows
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    // Internal helper ni para transaction form; tawagon ra sulod application/controllers/Stock.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function transaction_form($type) {
        $this->form_validation->set_rules(
            'supplier_id',
            'Supplier',
            'required|integer|greater_than[0]'
        );

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

        $result = $this->stock_service->create_transaction(
            $type,
            $this->input->post('supplier_id', TRUE),
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

    // Internal helper ni para render transaction form; tawagon ra sulod application/controllers/Stock.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function render_transaction_form($type, $item_error = '') {
        $data['suppliers'] = array();
        $data['transaction_type'] = $type;
        $data['page_title'] = $type === 'stock_in' ? 'Stock In' : 'Stock Out';
        $data['item_error'] = $item_error;
        $data['supplier_products'] = array();
        $data['transaction_quantities'] = $this->posted_quantity_map();

        $supplier_id = (int) $this->input->post('supplier_id', TRUE);

        if ($supplier_id > 0) {
            $supplier = $this->Supplier_model->get_by_id($supplier_id);

            if ($supplier && (int) $supplier->status === 1) {
                $data['suppliers'] = array($supplier);
                $data['supplier_products'] = $this->Product_model->get_active_by_supplier($supplier_id, $type);
            }
        }

        $this->load->view('modal/stock/transaction_form', $data);
    }

    // Internal helper ni para render adjustment form; tawagon ra sulod application/controllers/Stock.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function render_adjustment_form($form_error = '') {
        $data['products'] = array();
        $data['suppliers'] = array();
        $data['page_title'] = 'Stock Adjustment';
        $data['form_error'] = $form_error;

        $supplier_scope = trim((string) $this->input->post('supplier_filter', TRUE));
        $product_id = (int) $this->input->post('product_id', TRUE);

        if ($supplier_scope !== '' && $supplier_scope !== 'unassigned' && ctype_digit($supplier_scope)) {
            $supplier = $this->Supplier_model->get_by_id((int) $supplier_scope);

            if ($supplier && (int) $supplier->status === 1) {
                $data['suppliers'] = array($supplier);
            }
        }

        if ($product_id > 0) {
            $product = $this->Product_model->get_by_id($product_id);

            if ($product && (int) $product->status === 1) {
                $data['products'] = array($product);
            }
        }

        $this->load->view('modal/stock/adjustment', $data);
    }

    // Internal helper ni para read transaction items; tawagon ra sulod application/controllers/Stock.php, so ari ra pud pangitaa ang caller if mag-trace ka.
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

            // Stock In and Stock Out render product rows ahead of submission.
            // Blank/zero quantity means that product is not part of this transaction.
            if ($quantity_is_blank) {
                continue;
            }

            if (!$product_id_is_valid || !$quantity_is_valid) {
                $error = $type === 'stock_in'
                    ? 'Stock-in product IDs and quantities must be positive whole numbers.'
                    : 'Stock-out product IDs and quantities must be positive whole numbers.';
                return array();
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

    // Internal helper ni para posted quantity map; tawagon ra sulod application/controllers/Stock.php, so ari ra pud pangitaa ang caller if mag-trace ka.
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

    // Internal helper ni para require permission; tawagon ra sulod application/controllers/Stock.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function require_permission($permission_key) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->authorization_service->has_permission($user_id, $permission_key)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
