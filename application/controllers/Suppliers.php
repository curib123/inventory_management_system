<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Suppliers extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(array('session', 'form_validation'));
        $this->load->helper(array('form', 'url'));
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
        $this->load->model('Supplier_model');
        $this->load->model('User_model');
    }

    public function index() {
        $this->require_permission('manage_suppliers');
        $data['suppliers'] = $this->Supplier_model->get_all();
        $data['page_title'] = 'Suppliers';
        $this->load->view('templates/header', $data);
        $this->load->view('suppliers/index', $data);
        $this->load->view('templates/footer');
    }

    public function add() {
        $this->require_permission('manage_suppliers');
        $this->supplier_form();
    }

    public function edit($id) {
        $this->require_permission('manage_suppliers');
        $supplier = $this->Supplier_model->get_by_id($id);
        if (!$supplier) {
            show_404();
        }
        $this->supplier_form((int) $id, $supplier);
    }

    public function delete($id) {
        $this->require_permission('manage_suppliers');
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Invalid request method.', 405, 'Method Not Allowed');
        }
        if (!$this->Supplier_model->get_by_id($id)) {
            show_404();
        }
        if (!$this->Supplier_model->delete($id)) {
            show_error('The supplier could not be deleted.', 500, 'Supplier Not Deleted');
        }
        redirect('suppliers');
    }

    private function supplier_form($id = NULL, $supplier = NULL) {
        $this->form_validation->set_rules('supplier_name', 'Supplier Name', 'trim|required|max_length[150]');
        $this->form_validation->set_rules('contact_person', 'Contact Person', 'trim|max_length[100]');
        $this->form_validation->set_rules('phone', 'Phone', 'trim|max_length[30]');
        $this->form_validation->set_rules('address', 'Address', 'trim');

        if ($this->form_validation->run() === FALSE) {
            $data['supplier'] = $supplier;
            $data['page_title'] = $id === NULL ? 'Add Supplier' : 'Edit Supplier';
            $this->load->view('templates/header', $data);
            $this->load->view('suppliers/form', $data);
            $this->load->view('templates/footer');
            return;
        }

        $data = array(
            'supplier_name' => trim($this->input->post('supplier_name', TRUE)),
            'contact_person' => trim($this->input->post('contact_person', TRUE)),
            'phone' => trim($this->input->post('phone', TRUE)),
            'address' => trim($this->input->post('address', TRUE)),
            'status' => $this->input->post('status', TRUE) === '0' ? 0 : 1
        );
        if (!$this->Supplier_model->save($data, $id)) {
            show_error('The supplier could not be saved.', 500, 'Supplier Not Saved');
        }
        redirect('suppliers');
    }

    private function require_permission($permission_name) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_name)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
