<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(array('session', 'form_validation'));
        $this->load->helper(array('form', 'url'));
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
        $this->load->model('Category_model');
        $this->load->model('User_model');
    }

    public function index() {
        $this->require_permission('manage_products');
        $limit = 10;
        $page = max(1, (int) $this->input->get('per_page', TRUE));
        $data['categories'] = $this->Category_model->get_all($limit, ($page - 1) * $limit);
        $data['page_title'] = 'Categories';
        $data['pagination'] = $this->paginate($this->Category_model->count_all(), $limit, 'categories');
        $this->load->view('templates/header', $data);
        $this->load->view('categories/index', $data);
        $this->load->view('templates/footer');
    }

    public function add() {
        $this->require_permission('manage_products');
        $this->category_form();
    }

    public function edit($id) {
        $this->require_permission('manage_products');
        $category = $this->Category_model->get_by_id($id);
        if (!$category) {
            show_404();
        }
        $this->category_form((int) $id, $category);
    }

    public function delete($id) {
        $this->require_permission('manage_products');
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Invalid request method.', 405, 'Method Not Allowed');
        }
        $category = $this->Category_model->get_by_id($id);
        if (!$category) {
            show_404();
        }
        if ($this->Category_model->count_products($id) > 0) {
            show_error('This category is assigned to products. Reassign those products before deleting the category.', 400, 'Category Not Deleted');
        }
        if (!$this->Category_model->delete($id)) {
            show_error('The category could not be deleted.', 500, 'Category Not Deleted');
        }
        redirect('categories');
    }

    private function category_form($id = NULL, $category = NULL) {
        $this->form_validation->set_rules('category_name', 'Category Name', 'trim|required|max_length[100]');
        if ($this->form_validation->run() === FALSE) {
            $data['category'] = $category;
            $data['page_title'] = $id === NULL ? 'Add Category' : 'Edit Category';
            $this->load->view('templates/header', $data);
            $this->load->view('categories/form', $data);
            $this->load->view('templates/footer');
            return;
        }

        $name = trim($this->input->post('category_name', TRUE));
        if ($this->Category_model->name_exists($name, $id)) {
            show_error('That category name already exists.', 400, 'Category Not Saved');
        }

        $data = array(
            'category_name' => $name,
            'status' => $this->input->post('status', TRUE) === '0' ? 0 : 1
        );
        if (!$this->Category_model->save($data, $id)) {
            show_error('The category could not be saved.', 500, 'Category Not Saved');
        }
        redirect('categories');
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
