<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Suppliers extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(array('session', 'form_validation'));
        $this->load->helper(array('form', 'url'));
        $this->load->library('Datatable_service');

        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('Supplier_model');
        $this->load->model('User_model');
    }

    public function index() {
        $this->require_permission('manage_suppliers');

        $data['page_title'] = 'Suppliers';
        $this->load->view('templates/header', $data);
        $this->load->view('suppliers/index', $data);
        $this->load->view('templates/footer');
    }

    public function add() {
        $this->require_permission('manage_suppliers');
        $this->supplier_form();
    }

    public function view($id) {
        $this->require_permission('manage_suppliers');

        $data['supplier'] = $this->Supplier_model->get_by_id($id);
        if (!$data['supplier']) {
            show_404();
        }

        $this->load->view('modal/suppliers/details', $data);
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

        $supplier = $this->Supplier_model->get_by_id($id);
        if (!$supplier) {
            show_404();
        }

        if ($this->input->method(TRUE) !== 'POST') {
            $this->load->view('modal/suppliers/delete', array(
                'supplier' => $supplier,
                'delete_error' => ''
            ));
            return;
        }

        if (!$this->Supplier_model->delete($id)) {
            $this->load->view('modal/suppliers/delete', array(
                'supplier' => $supplier,
                'delete_error' => 'The supplier could not be deleted.'
            ));
            return;
        }

        redirect('suppliers');
    }

    public function datatable() {
        $this->require_permission('manage_suppliers');

        $columns = array(
            's.supplier_name',
            's.contact_person',
            's.phone',
            's.address',
            's.status',
            's.created_at',
            's.updated_at',
            NULL
        );

        $request = $this->datatable_service->request($this->input, $columns, 's.supplier_name', 'asc');
        $suppliers = $this->Supplier_model->get_datatable(
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir']
        );

        $rows = array();
        foreach ($suppliers as $supplier) {
            $id = (int) $supplier->id;
            $actions = '<button type="button" data-modal-url="' . site_url('suppliers/view/' . $id) . '">View</button> ';
            $actions .= '<button type="button" data-modal-url="' . site_url('suppliers/edit/' . $id) . '">Edit</button> ';
            $actions .= '<button type="button" data-modal-url="' . site_url('suppliers/delete/' . $id) . '">Delete</button>';

            $rows[] = array(
                html_escape($supplier->supplier_name),
                html_escape($supplier->contact_person),
                html_escape($supplier->phone),
                html_escape($supplier->address),
                $supplier->status ? 'Active' : 'Inactive',
                html_escape($supplier->created_at),
                html_escape($supplier->updated_at),
                $actions
            );
        }

        $payload = $this->datatable_service->payload(
            $request['draw'],
            $this->Supplier_model->count_all(),
            $this->Supplier_model->count_datatable_filtered($request['search']),
            $rows
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    private function supplier_form($id = NULL, $supplier = NULL) {
        $this->form_validation->set_rules('supplier_name', 'Supplier Name', 'trim|required|max_length[150]');
        $this->form_validation->set_rules('contact_person', 'Contact Person', 'trim|max_length[100]');
        $this->form_validation->set_rules('phone', 'Phone', 'trim|max_length[30]');
        $this->form_validation->set_rules('address', 'Address', 'trim');

        if ($this->form_validation->run() === FALSE) {
            $this->render_supplier_form($id, $supplier);
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
            $this->render_supplier_form($id, $supplier, 'The supplier could not be saved.');
            return;
        }

        redirect('suppliers');
    }

    private function render_supplier_form($id, $supplier, $form_error = '') {
        $data['supplier'] = $supplier;
        $data['page_title'] = $id === NULL ? 'Add Supplier' : 'Edit Supplier';
        $data['form_error'] = $form_error;

        $this->load->view('modal/suppliers/form', $data);
    }

    private function require_permission($permission_name) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_name)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
