<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(array('session', 'form_validation'));
        $this->load->helper(array('form', 'url'));

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('Product_model');
        $this->load->model('Supplier_model');
        $this->load->model('Category_model');
        $this->load->model('User_model');
    }

    public function index() {
        $this->require_permission('manage_products');
        $data['products'] = $this->Product_model->get_all();
        $data['page_title'] = 'Products';
        $this->load->view('templates/header', $data);
        $this->load->view('products/index', $data);
        $this->load->view('templates/footer');
    }

    public function add() {
        $this->require_permission('manage_products');
        $this->product_form();
    }

    public function edit($id) {
        $this->require_permission('manage_products');
        $product = $this->Product_model->get_by_id($id);
        if (!$product) {
            show_404();
        }
        $this->product_form((int) $id, $product);
    }

    public function delete($id) {
        $this->require_permission('manage_products');
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Invalid request method.', 405, 'Method Not Allowed');
        }

        $product = $this->Product_model->get_by_id($id);
        if (!$product) {
            show_404();
        }
        if ($this->Product_model->has_transaction_history($id)) {
            show_error('Products with stock transaction history cannot be deleted. Set the product to inactive instead.', 400, 'Product Not Deleted');
        }
        if (!$this->Product_model->delete($id)) {
            show_error('The product could not be deleted.', 500, 'Product Not Deleted');
        }
        redirect('products');
    }

    private function product_form($id = NULL, $product = NULL) {
        $this->form_validation->set_rules('product_name', 'Product Name', 'trim|required|max_length[150]');
        $this->form_validation->set_rules('product_code', 'Product Code', 'trim|required|max_length[50]');
        $this->form_validation->set_rules('category_id', 'Category', 'required|integer|greater_than[0]');
        $this->form_validation->set_rules('supplier_id', 'Supplier', 'integer|greater_than[0]');
        $this->form_validation->set_rules('unit', 'Unit', 'trim|max_length[50]');
        $this->form_validation->set_rules('cost_price', 'Cost Price', 'required|numeric|greater_than_equal_to[0]');
        $this->form_validation->set_rules('selling_price', 'Selling Price', 'required|numeric|greater_than_equal_to[0]');
        $this->form_validation->set_rules('reorder_level', 'Reorder Level', 'required|integer|greater_than_equal_to[0]');

        if ($this->form_validation->run() === FALSE) {
            $data['product'] = $product;
            $data['suppliers'] = $this->Supplier_model->get_all();
            $data['categories'] = $this->Category_model->get_all(10000, 0);
            $data['page_title'] = $id === NULL ? 'Add Product' : 'Edit Product';
            $this->load->view('templates/header', $data);
            $this->load->view('products/form', $data);
            $this->load->view('templates/footer');
            return;
        }

        $code = trim($this->input->post('product_code', TRUE));
        $category_id = (int) $this->input->post('category_id', TRUE);
        $supplier_raw = $this->input->post('supplier_id', TRUE);
        $supplier_id = ($supplier_raw === '' || $supplier_raw === NULL) ? NULL : (int) $supplier_raw;

        if ($this->Product_model->code_exists($code, $id)) {
            show_error('That product code already exists.', 400, 'Product Not Saved');
        }
        if (!$this->Category_model->get_by_id($category_id)) {
            show_error('The selected category does not exist.', 400, 'Product Not Saved');
        }
        if ($supplier_id !== NULL && !$this->Supplier_model->get_by_id($supplier_id)) {
            show_error('The selected supplier does not exist.', 400, 'Product Not Saved');
        }

        $data = array(
            'supplier_id' => $supplier_id,
            'category_id' => $category_id,
            'product_code' => $code,
            'product_name' => trim($this->input->post('product_name', TRUE)),
            'unit' => trim($this->input->post('unit', TRUE)),
            'cost_price' => (float) $this->input->post('cost_price', TRUE),
            'selling_price' => (float) $this->input->post('selling_price', TRUE),
            'reorder_level' => (int) $this->input->post('reorder_level', TRUE),
            'status' => $this->input->post('status', TRUE) === '0' ? 0 : 1
        );

        if (!$this->Product_model->save($data, $id)) {
            show_error('The product could not be saved.', 500, 'Product Not Saved');
        }
        redirect('products');
    }

    private function require_permission($permission_name) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_name)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
