<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Product_service {

    private $CI;

    // Setup ni sa Product_service; gi-load ni sa application/controllers/Products.php para diri tanan product business rules.
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model(array('Product_model', 'Category_model', 'Supplier_model', 'Stock_model','Activity_log_model'));
    }

    // Business flow ni para save product; application/controllers/Products.php ang caller, while models query/persistence ra ang role.
  
// Business flow ni para create product.
public function create($input) {

    $code = trim((string) (
        isset($input['product_code'])
            ? $input['product_code']
            : ''
    ));

    $category_id = (int) (
        isset($input['category_id'])
            ? $input['category_id']
            : 0
    );

    $supplier_raw = isset($input['supplier_id'])
        ? $input['supplier_id']
        : NULL;

    $supplier_id = (
        $supplier_raw === '' ||
        $supplier_raw === NULL
    )
        ? NULL
        : (int) $supplier_raw;


    // Check duplicate product code
    if ($this->CI->Product_model->code_exists($code, NULL)) {
        return array(
            'success' => FALSE,
            'message' => 'That product code already exists.'
        );
    }


    // Validate category
    $category = $this->CI->Category_model->get_by_id($category_id);

    if (!$category || !(int) $category->status) {
        return array(
            'success' => FALSE,
            'message' => 'The selected category is invalid or inactive.'
        );
    }


    // Validate supplier
    if ($supplier_id !== NULL) {

        $supplier = $this->CI->Supplier_model->get_by_id($supplier_id);

        if (!$supplier || !(int) $supplier->status) {
            return array(
                'success' => FALSE,
                'message' => 'The selected supplier is invalid or inactive.'
            );
        }
    }


    $data = array(
        'supplier_id'   => $supplier_id,
        'category_id'   => $category_id,
        'product_code'  => $code,
        'product_name'  => trim((string) $input['product_name']),
        'unit'          => trim((string) $input['unit']),
        'cost_price'    => (float) $input['cost_price'],
        'selling_price' => (float) $input['selling_price'],
        'reorder_level' => (int) $input['reorder_level'],
        'status'        => isset($input['status']) &&
                           (int) $input['status'] === 0
                           ? 0
                           : 1
    );


    // Create product
    if (!$this->CI->Product_model->save($data, NULL)) {
        return array(
            'success' => FALSE,
            'message' => 'The product could not be created.'
        );
    }


    // Activity log
    $this->CI->Activity_log_model->insert_activity_log(array(
        'user_id'     => (int) $this->CI->session->userdata('user_id'),
        'action'      => 'product_created',
        'description' => 'Created product: ' . $data['product_name'],
        'ip_address'  => $this->CI->input->ip_address()
    ));


    return array(
        'success' => TRUE
    );
}

// Business flow ni para update product.
public function update($id, $input, $current_product = NULL) {

    $id = (int) $id;

    if ($id <= 0) {
        return array(
            'success' => FALSE,
            'message' => 'Invalid product.'
        );
    }


    // Get current product if controller did not provide it
    if (!$current_product) {
        $current_product = $this->CI->Product_model->get_by_id($id);
    }

    if (!$current_product) {
        return array(
            'success' => FALSE,
            'message' => 'Product not found.'
        );
    }


    $code = trim((string) (
        isset($input['product_code'])
            ? $input['product_code']
            : ''
    ));

    $category_id = (int) (
        isset($input['category_id'])
            ? $input['category_id']
            : 0
    );

    $supplier_raw = isset($input['supplier_id'])
        ? $input['supplier_id']
        : NULL;

    $supplier_id = (
        $supplier_raw === '' ||
        $supplier_raw === NULL
    )
        ? NULL
        : (int) $supplier_raw;


    // Check duplicate product code
    if ($this->CI->Product_model->code_exists($code, $id)) {
        return array(
            'success' => FALSE,
            'message' => 'That product code already exists.'
        );
    }


    // Validate category
    $uses_existing_category =
        (int) $current_product->category_id === $category_id;

    $category = $this->CI->Category_model->get_by_id($category_id);

    if (
        !$category ||
        (
            !(int) $category->status &&
            !$uses_existing_category
        )
    ) {
        return array(
            'success' => FALSE,
            'message' => 'The selected category is invalid or inactive.'
        );
    }


    // Validate supplier
    if ($supplier_id !== NULL) {

        $supplier = $this->CI->Supplier_model->get_by_id($supplier_id);

        $uses_existing_supplier =
            $current_product->supplier_id !== NULL &&
            (int) $current_product->supplier_id === $supplier_id;

        if (
            !$supplier ||
            (
                !(int) $supplier->status &&
                !$uses_existing_supplier
            )
        ) {
            return array(
                'success' => FALSE,
                'message' => 'The selected supplier is invalid or inactive.'
            );
        }
    }


    $data = array(
        'supplier_id'   => $supplier_id,
        'category_id'   => $category_id,
        'product_code'  => $code,
        'product_name'  => trim((string) $input['product_name']),
        'unit'          => trim((string) $input['unit']),
        'cost_price'    => (float) $input['cost_price'],
        'selling_price' => (float) $input['selling_price'],
        'reorder_level' => (int) $input['reorder_level'],
        'status'        => isset($input['status']) &&
                           (int) $input['status'] === 0
                           ? 0
                           : 1
    );


    // Update product
    if (!$this->CI->Product_model->save($data, $id)) {
        return array(
            'success' => FALSE,
            'message' => 'The product could not be updated.'
        );
    }


    // Activity log
    $this->CI->Activity_log_model->insert_activity_log(array(
        'user_id'     => (int) $this->CI->session->userdata('user_id'),
        'action'      => 'product_updated',
        'description' => 'Updated product: ' . $data['product_name'],
        'ip_address'  => $this->CI->input->ip_address()
    ));


    return array(
        'success' => TRUE
    );
}

public function save($id, $input, $current_product = NULL) {

    if ($id === NULL) {
        return $this->create($input);
    }

    return $this->update($id, $input, $current_product);
}


    // Business flow ni para delete product; application/controllers/Products.php ang caller, then transaction-history rule diri gi-enforce.
    public function delete($id, $execute = TRUE) {
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

        if (!$execute) {
            return array('success' => TRUE, 'product' => $product);
        }

        if (!$this->CI->Product_model->delete($id)) {
            return array('success' => FALSE, 'message' => 'The product could not be deleted.');
        }

          // Activity log
    $this->CI->Activity_log_model->insert_activity_log(array(
        'user_id'     => (int) $this->CI->session->userdata('user_id'),
        'action'      => 'product_deleted',
        'description' => 'Deleted product: ' . $product->product_name,
        'ip_address'  => $this->CI->input->ip_address()
    ));

        return array('success' => TRUE, 'product' => $product);
    }

    // Business flow ni para product movement history; controller mohatag product ID ug sort, service mo-combine Stock In/Out + adjustments into one audit timeline.
    public function movement_history($id, $sort = 'desc') {
        $id = (int) $id;
        $product = $this->CI->Product_model->get_by_id($id);

        if (!$product) {
            return array(
                'success' => FALSE,
                'not_found' => TRUE,
                'product' => NULL,
                'movements' => array(),
                'summary' => array()
            );
        }

        $movements = array();

        foreach ($this->CI->Stock_model->get_product_transaction_movements($id) as $row) {
            $type = isset($row['type']) ? (string) $row['type'] : '';
            $quantity = isset($row['quantity']) ? (int) $row['quantity'] : 0;

            $movements[] = array(
                'source_id' => isset($row['source_id']) ? (int) $row['source_id'] : 0,
                'type' => $type,
                'type_label' => $type === 'stock_in' ? 'Stock In' : 'Stock Out',
                'transaction_no' => isset($row['transaction_no']) ? (string) $row['transaction_no'] : '',
                'quantity' => $quantity,
                'signed_quantity' => $type === 'stock_out' ? -$quantity : $quantity,
                'cost_price' => isset($row['cost_price']) ? (float) $row['cost_price'] : 0.0,
                'supplier_name' => isset($row['supplier_name']) ? (string) $row['supplier_name'] : 'Unassigned Products',
                'username' => isset($row['username']) ? (string) $row['username'] : '',
                'remarks' => isset($row['remarks']) ? trim((string) $row['remarks']) : '',
                'system_stock' => NULL,
                'actual_stock' => NULL,
                'difference' => NULL,
                'created_at' => isset($row['created_at']) ? (string) $row['created_at'] : ''
            );
        }

        $sort = strtolower((string) $sort) === 'asc' ? 'asc' : 'desc';

        usort($movements, function ($left, $right) use ($sort) {
            $left_time = strtotime(isset($left['created_at']) ? $left['created_at'] : '') ?: 0;
            $right_time = strtotime(isset($right['created_at']) ? $right['created_at'] : '') ?: 0;

            if ($left_time === $right_time) {
                $left_id = isset($left['source_id']) ? (int) $left['source_id'] : 0;
                $right_id = isset($right['source_id']) ? (int) $right['source_id'] : 0;
                $comparison = $left_id <=> $right_id;
            } else {
                $comparison = $left_time <=> $right_time;
            }

            return $sort === 'asc' ? $comparison : -$comparison;
        });

        $summary = array(
            'total' => count($movements),
            'stock_in' => 0,
            'stock_out' => 0,
            'adjustment' => 0
        );

        foreach ($movements as $movement) {
            if (isset($summary[$movement['type']])) {
                $summary[$movement['type']]++;
            }
        }

        return array(
            'success' => TRUE,
            'product' => $product,
            'movements' => $movements,
            'summary' => $summary,
            'sort' => $sort
        );
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
