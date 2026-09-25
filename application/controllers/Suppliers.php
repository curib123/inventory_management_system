<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Suppliers extends CI_Controller {

    // Setup ni sa Suppliers controller; CodeIgniter mo-run ani automatically, while route mapping makita sa application/config/routes.php.
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

    // Mao ni ang index flow sa Suppliers; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function index() {
        $this->require_permission('suppliers.view');

        $data['page_title'] = 'Suppliers';
        $this->load->view('templates/header', $data);
        $this->load->view('suppliers/index', $data);
        $this->load->view('templates/footer');
    }

    // Mao ni ang add flow sa Suppliers; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function add() {
        $this->require_permission('suppliers.create');
        $this->supplier_form();
    }

    // Mao ni ang view flow sa Suppliers; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function view($id) {
        $this->require_permission('suppliers.view');

        $data['supplier'] = $this->Supplier_model->get_by_id($id);
        if (!$data['supplier']) {
            show_404();
        }

        $this->load->view('modal/suppliers/details', $data);
    }

    // Mao ni ang edit flow sa Suppliers; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function edit($id) {
        $this->require_permission('suppliers.edit');

        $supplier = $this->Supplier_model->get_by_id($id);
        if (!$supplier) {
            show_404();
        }

        $this->supplier_form((int) $id, $supplier);
    }

    // Mao ni ang delete flow sa Suppliers; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function delete($id) {
        $this->require_permission('suppliers.delete');

        $supplier = $this->Supplier_model->get_by_id($id);
        if (!$supplier) {
            show_404();
        }

        $delete_error = '';

        if ($this->Supplier_model->has_dependencies($id)) {
            $delete_error =
                'This supplier is used by products or stock transaction history. ' .
                'Set the supplier to inactive instead of deleting it.';
        }

        if ($this->input->method(TRUE) !== 'POST') {
            $this->load->view('modal/suppliers/delete', array(
                'supplier' => $supplier,
                'delete_error' => $delete_error
            ));
            return;
        }

        if ($delete_error !== '') {
            $this->load->view('modal/suppliers/delete', array(
                'supplier' => $supplier,
                'delete_error' => $delete_error
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

        $this->session->set_flashdata('success', 'Supplier deleted successfully.');
        redirect('suppliers');
    }

    // Mao ni ang datatable flow sa Suppliers; route mapping naa sa application/config/routes.php, then related UI/data usage makita sa application/views/.
    public function datatable() {
        $this->require_permission('suppliers.view');

        $columns = array(
            's.supplier_name',
            's.contact_person',
            's.phone',
            's.address',
            's.status',
            NULL
        );

        $request = $this->datatable_service->request($this->input, $columns, 's.supplier_name', 'asc');
        $suppliers = $this->Supplier_model->get_datatable(
            $request['start'],
            $request['length'],
            $request['search'],
            $request['order_column'],
            $request['order_dir'],
            $request['filters']
        );

        $current_user_id = (int) $this->session->userdata('user_id');
        $can_edit = $this->User_model->has_permission($current_user_id, 'suppliers.edit');
        $can_delete = $this->User_model->has_permission($current_user_id, 'suppliers.delete');

        $rows = array();
        foreach ($suppliers as $supplier) {
            $id = (int) $supplier->id;
            $action_items = array(
                array(
                    'label' => 'View',
                    'url' => site_url('suppliers/view/' . $id),
                    'variant' => 'secondary',
                    'icon' => 'bi-eye'
                )
            );

            if ($can_edit) {
                $action_items[] = array(
                    'label' => 'Edit',
                    'url' => site_url('suppliers/edit/' . $id),
                    'variant' => 'primary',
                    'icon' => 'bi-pencil'
                );
            }

            if ($can_delete) {
                $action_items[] = array(
                    'label' => 'Delete',
                    'url' => site_url('suppliers/delete/' . $id),
                    'variant' => 'danger',
                    'icon' => 'bi-trash'
                );
            }

            $actions = ui_modal_action_group($action_items);
            $rows[] = array(
                html_escape($supplier->supplier_name),
                html_escape($supplier->contact_person),
                html_escape($supplier->phone),
                html_escape($supplier->address),
                $supplier->status ? 'Active' : 'Inactive',
                $actions
            );
        }

        $payload = $this->datatable_service->payload(
            $request['draw'],
            $this->Supplier_model->count_all(),
            $this->Supplier_model->count_datatable_filtered($request['search'], $request['filters']),
            $rows
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    // Internal helper ni para supplier form; tawagon ra sulod application/controllers/Suppliers.php, so ari ra pud pangitaa ang caller if mag-trace ka.
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

        $this->session->set_flashdata(
            'success',
            $id === NULL ? 'Supplier created successfully.' : 'Supplier changes saved successfully.'
        );
        redirect('suppliers');
    }

    // Internal helper ni para render supplier form; tawagon ra sulod application/controllers/Suppliers.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function render_supplier_form($id, $supplier, $form_error = '') {
        $data['supplier'] = $supplier;
        $data['page_title'] = $id === NULL ? 'Add Supplier' : 'Edit Supplier';
        $data['form_error'] = $form_error;

        $this->load->view('modal/suppliers/form', $data);
    }

    // Internal helper ni para require permission; tawagon ra sulod application/controllers/Suppliers.php, so ari ra pud pangitaa ang caller if mag-trace ka.
    private function require_permission($permission_key) {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !$this->User_model->has_permission($user_id, $permission_key)) {
            show_error('You do not have permission to access this page.', 403, 'Access Denied');
        }
    }
}
