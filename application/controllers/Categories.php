<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends CI_Controller {
  
   // categories controller to handle category management functionality
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->helper(array('form', 'url'));

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('Category_model');
        $this->load->model('User_model');
    }
    // Function to check if the user has the required permission
    public function index() {
        $this->require_permission('manage_products');

        $limit = 10;
        $page = (int) $this->input->get('per_page', TRUE);
        $page = max(1, $page);
        $offset = ($page - 1) * $limit;

        $total_rows = $this->Category_model->count_all();
        $data['categories'] = $this->Category_model->get_all($limit, $offset);
        $data['page_title'] = 'Categories';
        $data['pagination'] = $this->paginate($total_rows, $limit, 'categories');

        $this->load->view('templates/header', $data);
        $this->load->view('categories/index', $data);
        $this->load->view('templates/footer');
    }
    // Function to check if the user has the required permission and add a new category
    public function add() {
        $this->require_permission('manage_products');

        $this->form_validation->set_rules('category_name', 'Category Name', 'required');

        if ($this->form_validation->run() === FALSE) {
            $data['page_title'] = 'Add Category';
            $this->load->view('templates/header', $data);
            $this->load->view('categories/form', $data);
            $this->load->view('templates/footer');
            return;
        }

        $data = array(
            'category_name' => $this->input->post('category_name'),
            'status' => $this->input->post('status')
        );

        $this->Category_model->save($data);
        redirect('categories');
    }
    // Function to check if the user has the required permission and edit an existing category
    public function edit($id) {
        $this->require_permission('manage_products');

        $category = $this->Category_model->get_by_id($id);

        if (!$category) {
            redirect('categories');
        }

        $this->form_validation->set_rules('category_name', 'Category Name', 'required');

        if ($this->form_validation->run() === FALSE) {
            $data['category'] = $category;
            $data['page_title'] = 'Edit Category';
            $this->load->view('templates/header', $data);
            $this->load->view('categories/form', $data);
            $this->load->view('templates/footer');
            return;
        }

        $data = array(
            'category_name' => $this->input->post('category_name'),
            'status' => $this->input->post('status')
        );

        $this->Category_model->save($data, $id);
        redirect('categories');
    }
    // Function to check if the user has the required permission and delete an existing category
    public function delete($id) {
        $this->require_permission('manage_products');

        $this->Category_model->delete($id);
        redirect('categories');
    }
    // Function to paginate the categories list
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
