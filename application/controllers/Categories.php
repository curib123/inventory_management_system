<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(array('session', 'form_validation'));
        $this->load->helper(array('form', 'url'));
        $this->load->library('Datatable_service');

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('Category_model');
        $this->load->model('User_model');
    }

    public function index() {
        $this->require_permission('categories.view');

        $data['page_title'] = 'Categories';
        $this->load->view('templates/header', $data);
        $this->load->view('categories/index', $data);
        $this->load->view('templates/footer');
    }

    public function add() {
        $this->require_permission('categories.create');
        $this->category_form();
    }

    public function view($id) {
        $this->require_permission('categories.view');

        $category = $this->Category_model->get_by_id($id);
        if (!$category) {
            show_404();
        }

        $this->load->view('modal/categories/details', array(
            'category' => $category,
            'product_count' => $this->Category_model->count_products($id)
        ));
    }

    public function edit($id) {
        $this->require_permission('categories.edit');

        $category = $this->Category_model->get_by_id($id);
        if (!$category) {
            show_404();
        }

        $this->category_form((int) $id, $category);
    }

    public function delete($id) {
        $this->require_permission('categories.delete');

        $category = $this->Category_model->get_by_id($id);
        if (!$category) {
            show_404();
        }

        $delete_error = '';
        if ($this->Category_model->count_products($id) > 0) {
            $delete_error = 'This category is assigned to products. Reassign those products before deleting the category.';
        }

        if ($this->input->method(TRUE) !== 'POST') {
            $this->load->view('modal/categories/delete', array(
                'category' => $category,
                'delete_error' => $delete_error
            ));
            return;
        }

        if ($delete_error !== '') {
            $this->load->view('modal/categories/delete', array(
                'category' => $category,
                'delete_error' => $delete_error
            ));
            return;
        }

        if (!$this->Category_model->delete($id)) {
            $this->load->view('modal/categories/delete', array(
                'category' => $category,
                'delete_error' => 'The category could not be deleted.'
            ));
            return;
        }

        redirect('categories');
    }

    public function datatable() {
        $this->require_permission('categories.view');

        $columns = array('c.id', 'c.category_name', 'c.status', 'product_count', NULL);
        $request = $this->datatable_service->request($this->input, $columns, 'c.category_name', 'asc');
        $categories = $this->Category_model->get_datatable(
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir']
        );

        $current_user_id = (int) $this->session->userdata('user_id');
        $can_edit = $this->User_model->has_permission($current_user_id, 'categories.edit');
        $can_delete = $this->User_model->has_permission($current_user_id, 'categories.delete');

        $rows = array();
        foreach ($categories as $category) {
            $id = (int) $category->id;
            $action_items = array(
                array(
                    'label' => 'View',
                    'url' => site_url('categories/view/' . $id),
                    'variant' => 'secondary',
                    'icon' => 'bi-eye'
                )
            );

            if ($can_edit) {
                $action_items[] = array(
                    'label' => 'Edit',
                    'url' => site_url('categories/edit/' . $id),
                    'variant' => 'primary',
                    'icon' => 'bi-pencil'
                );
            }

            if ($can_delete) {
                $action_items[] = array(
                    'label' => 'Delete',
                    'url' => site_url('categories/delete/' . $id),
                    'variant' => 'danger',
                    'icon' => 'bi-trash'
                );
            }

            $actions = ui_modal_action_group($action_items);
            $rows[] = array(
                $id,
                html_escape($category->category_name),
                $category->status ? 'Active' : 'Inactive',
                (int) $category->product_count,
                $actions
            );
        }

        $payload = $this->datatable_service->payload(
            $request['draw'],
            $this->Category_model->count_all(),
            $this->Category_model->count_datatable_filtered($request['search']),
            $rows
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    private function category_form($id = NULL, $category = NULL) {
        $this->form_validation->set_rules('category_name', 'Category Name', 'trim|required|max_length[100]');

        if ($this->form_validation->run() === FALSE) {
            $this->render_category_form($id, $category);
            return;
        }

        $name = trim($this->input->post('category_name', TRUE));
        if ($this->Category_model->name_exists($name, $id)) {
            $this->render_category_form($id, $category, 'That category name already exists.');
            return;
        }

        $data = array(
            'category_name' => $name,
            'status' => $this->input->post('status', TRUE) === '0' ? 0 : 1
        );

        if (!$this->Category_model->save($data, $id)) {
            $this->render_category_form($id, $category, 'The category could not be saved.');
            return;
        }

        redirect('categories');
    }

    private function render_category_form($id, $category, $form_error = '') {
        $data['category'] = $category;
        $data['page_title'] = $id === NULL ? 'Add Category' : 'Edit Category';
        $data['form_error'] = $form_error;

        $this->load->view('modal/categories/form', $data);
    }

    private function require_permission($permission_key) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_key)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
