<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Product_service {

    private $CI;

    // Setup ni sa Product_service; gi-load ni sa application/controllers/Products.php para diri tanan product business rules.
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model(array('Product_model', 'Category_model', 'Supplier_model'));
    }

    // Business flow ni para save product; application/controllers/Products.php ang caller, while models query/persistence ra ang role.
    public function save($id, $input, $current_product = NULL) {
        $id = $id === NULL ? NULL : (int) $id;
        $code = trim((string) (isset($input['product_code']) ? $input['product_code'] : ''));
        $category_id = (int) (isset($input['category_id']) ? $input['category_id'] : 0);
        $supplier_raw = isset($input['supplier_id']) ? $input['supplier_id'] : NULL;
        $supplier_id = ($supplier_raw === '' || $supplier_raw === NULL) ? NULL : (int) $supplier_raw;

        if ($this->CI->Product_model->code_exists($code, $id)) {
            return array('success' => FALSE, 'message' => 'That product code already exists.');
        }

        $category = $this->CI->Category_model->get_by_id($category_id);
        $uses_existing_category =
            $id !== NULL &&
            $current_product &&
            (int) $current_product->category_id === $category_id;

        if (!$category || (!(int) $category->status && !$uses_existing_category)) {
            return array('success' => FALSE, 'message' => 'The selected category is invalid or inactive.');
        }

        if ($supplier_id !== NULL) {
            $supplier = $this->CI->Supplier_model->get_by_id($supplier_id);
            $uses_existing_supplier =
                $id !== NULL &&
                $current_product &&
                $current_product->supplier_id !== NULL &&
                (int) $current_product->supplier_id === $supplier_id;

            if (!$supplier || (!(int) $supplier->status && !$uses_existing_supplier)) {
                return array('success' => FALSE, 'message' => 'The selected supplier is invalid or inactive.');
            }
        }

        $data = array(
            'supplier_id' => $supplier_id,
            'category_id' => $category_id,
            'product_code' => $code,
            'product_name' => trim((string) $input['product_name']),
            'unit' => trim((string) $input['unit']),
            'cost_price' => (float) $input['cost_price'],
            'selling_price' => (float) $input['selling_price'],
            'reorder_level' => (int) $input['reorder_level'],
            'status' => isset($input['status']) && (int) $input['status'] === 0 ? 0 : 1
        );

        if (!$this->CI->Product_model->save($data, $id)) {
            return array('success' => FALSE, 'message' => 'The product could not be saved.');
        }

        return array('success' => TRUE);
    }

    // Business flow ni para delete product; application/controllers/Products.php ang caller, then transaction-history rule diri gi-enforce.
    public function delete($id) {
        $id = (int) $id;
        $product = $this->CI->Product_model->get_by_id($id);

        if (!$product) {
            return array('success' => FALSE, 'not_found' => TRUE, 'message' => 'Product not found.');
        }

        if ($this->CI->Product_model->has_transaction_history($id)) {
            return array(
                'success' => FALSE,
                'message' => 'Products with stock transaction history cannot be deleted. Set the product to inactive instead.'
            );
        }

        if (!$this->CI->Product_model->delete($id)) {
            return array('success' => FALSE, 'message' => 'The product could not be deleted.');
        }

        return array('success' => TRUE, 'product' => $product);
    }

    // Search option builder ni para categories; application/controllers/Products.php ang caller para controller dili na mag-format lookup data.
    public function category_options($query, $limit = 20) {
        $items = array();

        foreach ($this->CI->Category_model->search_active($query, $limit) as $category) {
            $items[] = array(
                'id' => (int) $category->id,
                'text' => (string) $category->category_name
            );
        }

        return $items;
    }

    // Search option builder ni para suppliers; application/controllers/Products.php ang caller, shared formatting delegated sa Supplier_service.
    public function supplier_options($query, $limit = 20) {
        $this->CI->load->library('Supplier_service');
        return $this->CI->supplier_service->search_options($query, $limit);
    }
}
