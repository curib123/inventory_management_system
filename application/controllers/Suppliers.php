<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Suppliers extends CI_Controller {

    // suppliers controller to handle supplier management functionality
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper(array('form', 'url'));

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('Supplier_model');
        $this->load->model('User_model');
    }
    // Function to check if the user has the required permission
    public function index() {
        $this->require_permission('manage_suppliers');

        $data['suppliers'] = $this->Supplier_model->get_all();
        $data['page_title'] = 'Suppliers';

        $this->load->view('templates/header', $data);
        $this->load->view('suppliers/index', $data);
        $this->load->view('templates/footer');
    }
    // Function to check if the user has the required permission and add a new supplier
    public function add() {
        $this->require_permission('manage_suppliers');

        $this->form_validation->set_rules('supplier_name', 'Supplier Name', 'required|max_length[150]');
        $this->form_validation->set_rules('contact_person', 'Contact Person', 'max_length[100]');
        $this->form_validation->set_rules('phone', 'Phone', 'max_length[30]');
        $this->form_validation->set_rules('address', 'Address');

        if ($this->form_validation->run() === FALSE) {
            $data['page_title'] = 'Add Supplier';
            $this->load->view('templates/header', $data);
            $this->load->view('suppliers/form', $data);
            $this->load->view('templates/footer');
            return;
        }

        $data = array(
            'supplier_name' => $this->input->post('supplier_name'),
            'contact_person' => $this->input->post('contact_person'),
            'phone' => $this->input->post('phone'),
            'address' => $this->input->post('address')
        );

        $this->Supplier_model->save($data);
        redirect('suppliers');
    }
    // Function to check if the user has the required permission and edit an existing supplier
    public function edit($id) {
        $this->require_permission('manage_suppliers');

        $supplier = $this->Supplier_model->get_by_id($id);

        if (!$supplier) {
            redirect('suppliers');
        }

        $this->form_validation->set_rules('supplier_name', 'Supplier Name', 'required|max_length[150]');
        $this->form_validation->set_rules('contact_person', 'Contact Person', 'max_length[100]');
        $this->form_validation->set_rules('phone', 'Phone', 'max_length[30]');
        $this->form_validation->set_rules('address', 'Address');

        if ($this->form_validation->run() === FALSE) {
            $data['supplier'] = $supplier;
            $data['page_title'] = 'Edit Supplier';
            $this->load->view('templates/header', $data);
            $this->load->view('suppliers/form', $data);
            $this->load->view('templates/footer');
            return;
        }

        $data = array(
            'supplier_name' => $this->input->post('supplier_name'),
            'contact_person' => $this->input->post('contact_person'),
            'phone' => $this->input->post('phone'),
            'address' => $this->input->post('address')
        );

        $this->Supplier_model->save($data, $id);
        redirect('suppliers');
    }
    // Function to check if the user has the required permission and delete an existing supplier
    public function delete($id) {
        $this->require_permission('manage_suppliers');

        $this->Supplier_model->delete($id);
        redirect('suppliers');
    }
    // Function to check if the user has the required permission
    private function require_permission($permission_name) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_name)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
