<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Supplier_service {

    private $CI;

    // Setup ni sa Supplier_service; gi-load ni sa application/controllers/Suppliers.php ug Stock.php para supplier business rules naa ra diri.
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model(array('Supplier_model','Activity_log_model'));
    }

    // Business flow ni para save supplier; application/controllers/Suppliers.php ang caller, while Supplier_model persistence/query ra.
    public function save($id, $data) {
        $payload = array(
            'supplier_name' => trim((string) (isset($data['supplier_name']) ? $data['supplier_name'] : '')),
            'contact_person' => trim((string) (isset($data['contact_person']) ? $data['contact_person'] : '')),
            'phone' => trim((string) (isset($data['phone']) ? $data['phone'] : '')),
            'address' => trim((string) (isset($data['address']) ? $data['address'] : '')),
            'status' => isset($data['status']) && (int) $data['status'] === 0 ? 0 : 1
        );

        if ($payload['supplier_name'] === '') {
            return array('success' => FALSE, 'message' => 'Supplier name is required.');
        }

        if (!$this->CI->Supplier_model->save($payload, $id === NULL ? NULL : (int) $id)) {
            return array('success' => FALSE, 'message' => 'The supplier could not be saved.');
        }

         // Get logged-in user's ID
    $user_id = (int) $this->CI->session->userdata('user_id');

    // Determine action
    $action = $id === NULL
        ? 'supplier_created'
        : 'supplier_updated';

    $description = $id === NULL
        ? 'Created supplier: ' . $payload['supplier_name']
        : 'Updated supplier: ' . $payload['supplier_name'];

    // Activity log
    $this->CI->Activity_log_model->insert_activity_log(array(
        'user_id'     => $user_id,
        'action'      => $action,
        'description' => $description,
        'ip_address'  => $this->CI->input->ip_address()
    ));

        return array('success' => TRUE);
    }

    // Business flow ni para delete supplier; application/controllers/Suppliers.php ang caller, then dependency rules diri gi-check before delete.
    public function delete($id, $execute = TRUE) {
        $id = (int) $id;
        $supplier = $this->CI->Supplier_model->get_by_id($id);

        if (!$supplier) {
            return array('success' => FALSE, 'not_found' => TRUE, 'message' => 'Supplier not found.');
        }

        if ($this->CI->Supplier_model->has_dependencies($id)) {
            return array(
                'success' => FALSE,
                'message' => 'This supplier is used by products or stock transaction history. Set the supplier to inactive instead of deleting it.'
            );
        }

        if (!$execute) {
            return array('success' => TRUE, 'supplier' => $supplier);
        }

        if (!$this->CI->Supplier_model->delete($id)) {
            return array('success' => FALSE, 'message' => 'The supplier could not be deleted.');
        }

        
         // Get logged-in user's ID
    $user_id = (int) $this->CI->session->userdata('user_id');

    // Activity log
    $this->CI->Activity_log_model->insert_activity_log(array(
        'user_id'     => $user_id,
        'action'      => 'supplier_deleted',
        'description' => 'Deleted Supplier  : ' . $supplier->supplier_name ,
        'ip_address'  => $this->CI->input->ip_address()
    ));


        return array('success' => TRUE, 'supplier' => $supplier);
    }

    // Search option builder ni para suppliers; gigamit sa application/controllers/Products.php ug Stock.php para controller dili na mag-format domain lookup data.
    public function search_options($query, $limit = 20) {
        $items = array();

        foreach ($this->CI->Supplier_model->search_active($query, $limit) as $supplier) {
            $secondary = array();

            if (!empty($supplier->contact_person)) {
                $secondary[] = $supplier->contact_person;
            }

            if (!empty($supplier->phone)) {
                $secondary[] = $supplier->phone;
            }

            $items[] = array(
                'id' => (int) $supplier->id,
                'text' => (string) $supplier->supplier_name,
                'secondary' => implode(' • ', $secondary)
            );
        }

        return $items;
    }

    public function count_product($supplier_id){
        return $this->CI->Supplier_model->count_products($supplier_id);
       
    }
    public function get_supplier_products($supplier_id){
        return $this->CI->Supplier_model->get_supplier_products($supplier_id);
    }
}
