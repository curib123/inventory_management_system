<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends CI_Controller {

    // Setup ni sa Products controller; CodeIgniter mo-run ani automatically, while route mapping makita sa application/config/routes.php.
    public function __construct() {
        parent::__construct();
        $this->load->library(array('session', 'form_validation'));
        $this->load->helper(array('form', 'url'));
        $this->load->library(array('Datatable_service', 'Product_service'));

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('Product_model');
        $this->load->model('Supplier_model');
        $this->load->model('Category_model');
        $this->load->model('User_model');
        $this->config->load('inventory');
    }

    // Mao ni ang index flow sa Products; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function index() {
        $this->require_permission('products.view');

        $data['page_title'] = 'Products';
        $this->load->view('templates/header', $data);
        $this->load->view('products/index', $data);
        $this->load->view('templates/footer');
    }

    // Mao ni ang add flow sa Products; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function add() {
        $this->require_permission('products.create');
        $this->product_form();
    }

    // Mao ni ang view flow sa Products; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function view($id) {
        $this->require_permission('products.view');

        $data['product'] = $this->Product_model->get_by_id($id);
        if (!$data['product']) {
            show_404();
        }

        $this->load->view('modal/products/details', $data);
    }

    // Mao ni ang edit flow sa Products; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function edit($id) {
        $this->require_permission('products.edit');

        $product = $this->Product_model->get_by_id($id);
        if (!$product) {
            show_404();
        }

        $this->product_form((int) $id, $product);
    }

    // Mao ni ang delete flow sa Products; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function delete($id) {
        $this->require_permission('products.delete');

        $product = $this->Product_model->get_by_id($id);
        if (!$product) {
            show_404();
        }

        $execute = $this->input->method(TRUE) === 'POST';
        $result = $this->product_service->delete($id, $execute);

        if (!$execute || !$result['success']) {
            $this->load->view('modal/products/delete', array(
                'product' => $product,
                'delete_error' => $result['success'] ? '' : $result['message']
            ));
            return;
        }

        $this->session->set_flashdata('success', 'Product deleted successfully.');
        redirect('products');
    }

    // Mao ni ang category search flow sa Products; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function category_search() {
        $this->require_product_form_permission();

        if ($this->input->method(TRUE) !== 'GET') {
            show_error('Invalid request method.', 405, 'Method Not Allowed');
        }

        $items = $this->product_service->category_options(
            trim((string) $this->input->get('q', TRUE)),
            20
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('items' => $items)));
    }

    // Mao ni ang supplier search flow sa Products; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function supplier_search() {
        $this->require_product_form_permission();

        if ($this->input->method(TRUE) !== 'GET') {
            show_error('Invalid request method.', 405, 'Method Not Allowed');
        }

        $items = $this->product_service->supplier_options(
            trim((string) $this->input->get('q', TRUE)),
            20
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('items' => $items)));
    }

    // Mao ni ang datatable flow sa Products; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function datatable() {
        $this->require_permission('products.view');

        $columns = array(
            'p.id',
            'p.product_code',
            'p.product_name',
            'c.category_name',
            's.supplier_name',
            'p.stock',
            'p.selling_price',
            'p.status',
            NULL
        );

        $request = $this->datatable_service->request($this->input, $columns, 'p.product_name', 'asc');
        $products = $this->Product_model->get_datatable(
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir'],
            $request['filters']
        );

        $current_user_id = (int) $this->session->userdata('user_id');
        $can_edit = $this->authorization_service->has_permission($current_user_id, 'products.edit');
        $can_delete = $this->authorization_service->has_permission($current_user_id, 'products.delete');

        $rows = array();
        foreach ($products as $product) {
            $id = (int) $product->id;
            $action_items = array(
                array(
                    'label' => 'View',
                    'url' => site_url('products/view/' . $id),
                    'variant' => 'secondary',
                    'icon' => 'bi-eye'
                )
            );

            if ($can_edit) {
                $action_items[] = array(
                    'label' => 'Edit',
                    'url' => site_url('products/edit/' . $id),
                    'variant' => 'primary',
                    'icon' => 'bi-pencil'
                );
            }

            if ($can_delete) {
                $action_items[] = array(
                    'label' => 'Delete',
                    'url' => site_url('products/delete/' . $id),
                    'variant' => 'danger',
                    'icon' => 'bi-trash'
                );
            }

            $actions = ui_modal_action_group($action_items);
            $rows[] = array(
                $id,
                html_escape($product->product_code),
                html_escape($product->product_name),
                html_escape($product->category_name ?: 'N/A'),
                html_escape($product->supplier_name ?: 'N/A'),
                (int) $product->stock,
                number_format((float) $product->selling_price, 2),
                $product->status ? 'Active' : 'Inactive',
                $actions
            );
        }

        $payload = $this->datatable_service->payload(
            $request['draw'],
            $this->Product_model->count_all(),
            $this->Product_model->count_datatable_filtered($request['search'], $request['filters']),
            $rows
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    // Internal helper ni para product form; tawagon ra sulod application/controllers/Products.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function product_form($id = NULL, $product = NULL) {
        $this->form_validation->set_rules('product_name', 'Product Name', 'trim|required|max_length[150]');
        $this->form_validation->set_rules('product_code', 'Product Code', 'trim|required|max_length[50]');
        $this->form_validation->set_rules('category_id', 'Category', 'required|integer|greater_than[0]');
        $this->form_validation->set_rules('supplier_id', 'Supplier', 'integer|greater_than[0]');
        $unit_options = (array) $this->config->item('product_units');
        $allowed_units = array_keys($unit_options);

        if (
            $product &&
            !empty($product->unit) &&
            !in_array($product->unit, $allowed_units, TRUE)
        ) {
            $allowed_units[] = $product->unit;
        }

        $this->form_validation->set_rules(
            'unit',
            'Unit',
            'required|in_list[' . implode(',', $allowed_units) . ']'
        );
        $this->form_validation->set_rules(
            'cost_price',
            'Cost Price',
            'required|numeric|greater_than_equal_to[0]|less_than_equal_to[9999999999.99]'
        );
        $this->form_validation->set_rules(
            'selling_price',
            'Selling Price',
            'required|numeric|greater_than_equal_to[0]|less_than_equal_to[9999999999.99]'
        );
        $this->form_validation->set_rules(
            'reorder_level',
            'Reorder Level',
            'required|integer|greater_than_equal_to[0]|less_than_equal_to[2147483647]'
        );

        if ($this->form_validation->run() === FALSE) {
            $this->render_product_form($id, $product);
            return;
        }

        $result = $this->product_service->save($id, array(
            'supplier_id' => $this->input->post('supplier_id', TRUE),
            'category_id' => $this->input->post('category_id', TRUE),
            'product_code' => $this->input->post('product_code', TRUE),
            'product_name' => $this->input->post('product_name', TRUE),
            'unit' => $this->input->post('unit', TRUE),
            'cost_price' => $this->input->post('cost_price', TRUE),
            'selling_price' => $this->input->post('selling_price', TRUE),
            'reorder_level' => $this->input->post('reorder_level', TRUE),
            'status' => $this->input->post('status', TRUE)
        ), $product);

        if (!$result['success']) {
            $this->render_product_form($id, $product, $result['message']);
            return;
        }

        $this->session->set_flashdata(
            'success',
            $id === NULL ? 'Product created successfully.' : 'Product changes saved successfully.'
        );
        redirect('products');
    }

    // Internal helper ni para render product form; tawagon ra sulod application/controllers/Products.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function render_product_form($id, $product, $form_error = '') {
        $data['product'] = $product;
        $data['suppliers'] = array();
        $data['categories'] = array();

        $is_post = $this->input->method(TRUE) === 'POST';
        $selected_category_id = $is_post
            ? (int) $this->input->post('category_id', TRUE)
            : ($product ? (int) $product->category_id : 0);
        $selected_supplier_raw = $is_post
            ? $this->input->post('supplier_id', TRUE)
            : ($product ? $product->supplier_id : NULL);
        $selected_supplier_id = ($selected_supplier_raw === '' || $selected_supplier_raw === NULL)
            ? 0
            : (int) $selected_supplier_raw;

        if ($selected_category_id > 0) {
            $selected_category = $this->Category_model->get_by_id($selected_category_id);

            if ($selected_category) {
                $data['categories'][] = $selected_category;
            }
        }

        if ($selected_supplier_id > 0) {
            $selected_supplier = $this->Supplier_model->get_by_id($selected_supplier_id);

            if ($selected_supplier) {
                $data['suppliers'][] = $selected_supplier;
            }
        }

        $data['page_title'] = $id === NULL ? 'Add Product' : 'Edit Product';
        $data['form_error'] = $form_error;
        $data['product_units'] = (array) $this->config->item('product_units');

        if (
            $product &&
            !empty($product->unit) &&
            !isset($data['product_units'][$product->unit])
        ) {
            $data['product_units'][$product->unit] = $product->unit . ' (existing value)';
        }

        $this->load->view('modal/products/form', $data);
    }

    // Internal helper ni para require product form permission; tawagon ra sulod application/controllers/Products.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function require_product_form_permission() {
        $user_id = (int) $this->session->userdata('user_id');

        if (
            $user_id <= 0 ||
            !$this->authorization_service->has_any_permission(
                $user_id,
                array('products.create', 'products.edit')
            )
        ) {
            show_error('You do not have permission to manage product relationships.', 403, 'Access Denied');
        }
    }

    // Internal helper ni para require permission; tawagon ra sulod application/controllers/Products.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function require_permission($permission_key) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->authorization_service->has_permission($user_id, $permission_key)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
