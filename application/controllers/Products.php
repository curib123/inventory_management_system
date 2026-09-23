<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends CI_Controller {

    // products controller to handle product management functionality
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->helper(array('form', 'url'));

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('Product_model');
        $this->load->model('Supplier_model');
        $this->load->model('User_model');
    }
    // Function to check if the user has the required permission
    public function index() {
        $this->require_permission('manage_products');

        $limit = 10;
        $page = (int) $this->input->get('per_page', TRUE);
        $page = max(1, $page);
        $offset = ($page - 1) * $limit;

        $total_rows = $this->Product_model->count_all();
        $data['products'] = $this->Product_model->get_all($limit, $offset);
        $data['suppliers'] = $this->Supplier_model->get_all();
        $data['page_title'] = 'Products';
        $data['pagination'] = $this->paginate($total_rows, $limit, 'products');

        $this->load->view('templates/header', $data);
        $this->load->view('products/index', $data);
        $this->load->view('templates/footer');
    }
    // Function to check if the user has the required permission and add a new product
    public function add() {
        $this->require_permission('manage_products');

        $this->form_validation->set_rules('product_name', 'Product Name', 'required');
        $this->form_validation->set_rules('product_code', 'Product Code', 'required');
        $this->form_validation->set_rules('category_id', 'Category', 'required');

        if ($this->form_validation->run() === FALSE) {
            $data['suppliers'] = $this->Supplier_model->get_all();
            $data['page_title'] = 'Add Product';

            $this->load->view('templates/header', $data);
            $this->load->view('products/form', $data);
            $this->load->view('templates/footer');
            return;
        }

        $data = array(
            'supplier_id' => $this->input->post('supplier_id'),
            'category_id' => $this->input->post('category_id'),
            'product_code' => $this->input->post('product_code'),
            'product_name' => $this->input->post('product_name'),
            'unit' => $this->input->post('unit'),
            'cost_price' => $this->input->post('cost_price'),
            'selling_price' => $this->input->post('selling_price'),
            'reorder_level' => $this->input->post('reorder_level'),
            'status' => $this->input->post('status'),
        );

        $this->Product_model->save($data);
        redirect('products');
    }
    //edit product function to check if the user has the required permission and edit an existing product
    public function edit($id) {
        $this->require_permission('manage_products');

        $product = $this->Product_model->get_by_id($id);

        if (!$product) {
            redirect('products');
        }

        $this->form_validation->set_rules('product_name', 'Product Name', 'required');
        $this->form_validation->set_rules('product_code', 'Product Code', 'required');

        if ($this->form_validation->run() === FALSE) {
            $data['product'] = $product;
            $data['suppliers'] = $this->Supplier_model->get_all();
            $data['page_title'] = 'Edit Product';

            $this->load->view('templates/header', $data);
            $this->load->view('products/form', $data);
            $this->load->view('templates/footer');
            return;
        }

        $data = array(
            'supplier_id' => $this->input->post('supplier_id'),
            'category_id' => $this->input->post('category_id'),
            'product_code' => $this->input->post('product_code'),
            'product_name' => $this->input->post('product_name'),
            'unit' => $this->input->post('unit'),
            'cost_price' => $this->input->post('cost_price'),
            'selling_price' => $this->input->post('selling_price'),
            'reorder_level' => $this->input->post('reorder_level'),
            'status' => $this->input->post('status'),
        );

        $this->Product_model->save($data, $id);
        redirect('products');
    }
    //delete product function to check if the user has the required permission and delete an existing product
    public function delete($id) {
        $this->require_permission('manage_products');

        $this->Product_model->delete($id);
        redirect('products');
    }
    // Function to paginate the products list
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
        $config['num_tag_open'] = '<span style="margin:0 4px;">';
        $config['num_tag_close'] = '</span>';
        $config['cur_tag_open'] = '<strong style="margin:0 4px;">';
        $config['cur_tag_close'] = '</strong>';

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
