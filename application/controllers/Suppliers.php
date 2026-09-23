<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Suppliers extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('Supplier_model');
        $this->load->helper('form');
    }

    public function index() {
        $data['suppliers'] = $this->Supplier_model->get_all();
        $data['page_title'] = 'Suppliers';

        $this->load->view('templates/header', $data);
        $this->load->view('suppliers/index', $data);
        $this->load->view('templates/footer');
    }

    public function add() {
        $this->form_validation->set_rules('supplier_name', 'Supplier Name', 'required');

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

    public function edit($id) {
        $supplier = $this->Supplier_model->get_by_id($id);

        if (!$supplier) {
            redirect('suppliers');
        }

        $this->form_validation->set_rules('supplier_name', 'Supplier Name', 'required');

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

    public function delete($id) {
        $this->Supplier_model->delete($id);
        redirect('suppliers');
    }
}
