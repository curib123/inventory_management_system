<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_service {

    private $CI;

    // Setup ni sa Stock_service; gi-load ni sa application/controllers/Stock.php para stock business rules, calculations, ug transaction orchestration naa ra diri.
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model(array('Stock_model', 'Product_model', 'Supplier_model'));
        $this->CI->load->library(array('Stock_rules', 'Supplier_service'));
    }

    // Business flow ni para create stock transaction; application/controllers/Stock.php ang caller, supporting supplier-owned or unassigned product scopes.
    public function create_transaction($type, $supplier_scope, $remarks, $user_id, $items) {
        if (!in_array($type, array('stock_in', 'stock_out'), TRUE) || empty($items)) {
            return array('success' => FALSE, 'message' => 'A valid stock transaction with at least one item is required.');
        }

        $supplier_scope = trim((string) $supplier_scope);
        $is_unassigned = $supplier_scope === 'unassigned';
        $transaction_supplier_id = NULL;

        if (!$is_unassigned) {
            $supplier_id = filter_var(
                $supplier_scope,
                FILTER_VALIDATE_INT,
                array('options' => array('min_range' => 1, 'max_range' => Stock_rules::MAX_STOCK))
            );

            if ($supplier_id === FALSE) {
                return array(
                    'success' => FALSE,
                    'message' => 'Select a valid supplier or Unassigned Products for the stock transaction.'
                );
            }

            $supplier_id = (int) $supplier_id;
            $supplier = $this->CI->Supplier_model->get_by_id($supplier_id);

            if (!$supplier || !(int) $supplier->status) {
                return array('success' => FALSE, 'message' => 'The selected supplier is invalid or inactive.');
            }

            $transaction_supplier_id = $supplier_id;
        }

        $normalized = $this->normalize_items($items);

        if ($normalized === FALSE || empty($normalized)) {
            return array(
                'success' => FALSE,
                'message' => 'Every stock item must contain a valid product and a positive whole-number quantity.'
            );
        }

        $this->CI->db->trans_begin();

        //mao ni pattern para mo create ug transaction number ex. ( type +   date (ymdhis) + subtring lenght )  
        $transaction_no = strtoupper($type) . '-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -6));
        $transaction_id = $this->CI->Stock_model->insert_transaction(array(
            'transaction_no' => $transaction_no,
            'type' => $type,
            'supplier_id' => $transaction_supplier_id,
            'remarks' => trim((string) $remarks),
            'created_by' => (int) $user_id
        ));

        if ($transaction_id <= 0) {
            $this->CI->db->trans_rollback();
            return array('success' => FALSE, 'message' => 'Unable to create the stock transaction.');
        }

        foreach ($normalized as $product_id => $quantity) {
            $product = $this->CI->Stock_model->get_product_for_update($product_id);

            if (!$product || !(int) $product->status) {
                $this->CI->db->trans_rollback();
                return array('success' => FALSE, 'message' => 'One of the selected products is invalid or inactive.');
            }

            if ($is_unassigned) {
                if ($product->supplier_id !== NULL) {
                    $this->CI->db->trans_rollback();
                    return array(
                        'success' => FALSE,
                        'message' => $product->product_name . ' is assigned to a supplier and cannot be processed under Unassigned Products.'
                    );
                }
            } elseif ((int) $product->supplier_id !== $transaction_supplier_id) {
                $this->CI->db->trans_rollback();
                return array(
                    'success' => FALSE,
                    'message' => $product->product_name . ' is not assigned to the selected supplier.'
                );
            }

            try {
                $new_stock = $this->CI->stock_rules->calculate_stock($product->stock, $quantity, $type);
            } catch (UnderflowException $exception) {
                $this->CI->db->trans_rollback();
                return array('success' => FALSE, 'message' => 'Insufficient stock for ' . $product->product_name . '.');
            } catch (InvalidArgumentException $exception) {
                $this->CI->db->trans_rollback();
                return array('success' => FALSE, 'message' => $exception->getMessage());
            }

            if (!$this->CI->Stock_model->insert_transaction_item(array(
                'transaction_id' => $transaction_id,
                'product_id' => $product_id,
                'quantity' => $quantity,
                'cost_price' => $product->cost_price
            ))) {
                $this->CI->db->trans_rollback();
                return array('success' => FALSE, 'message' => 'A stock transaction item could not be saved.');
            }

            if (!$this->CI->Stock_model->update_product_stock($product_id, $new_stock)) {
                $this->CI->db->trans_rollback();
                return array('success' => FALSE, 'message' => 'Product stock could not be updated.');
            }
        }

        $this->CI->Stock_model->insert_activity_log(array(
            'user_id' => (int) $user_id,
            'action' => $type,
            'description' => 'Processed ' . $transaction_no,
            'ip_address' => $this->CI->input->ip_address()
        ));

        if ($this->CI->db->trans_status() === FALSE) {
            $this->CI->db->trans_rollback();
            return array('success' => FALSE, 'message' => 'The stock transaction could not be completed.');
        }

        $this->CI->db->trans_commit();
        return array('success' => TRUE, 'transaction_no' => $transaction_no);
    }

    // Business flow ni para create adjustment; application/controllers/Stock.php ang caller, with supplier scope, reconciliation, ug no-op rules all centralized diri.
    public function create_adjustment($supplier_scope, $product_id, $actual_stock, $reason, $user_id) {
        $product_id = filter_var(
            $product_id,
            FILTER_VALIDATE_INT,
            array('options' => array('min_range' => 1, 'max_range' => Stock_rules::MAX_STOCK))
        );

        if ($product_id === FALSE) {
            return array('success' => FALSE, 'message' => 'A valid product is required for the stock adjustment.');
        }

        try {
            $actual_stock = $this->CI->stock_rules->validate_stock_value($actual_stock);
        } catch (InvalidArgumentException $exception) {
            return array('success' => FALSE, 'message' => $exception->getMessage());
        }

        $product_id = (int) $product_id;
        $reason = trim((string) $reason);
        $product = $this->CI->Product_model->get_by_id($product_id);

        if (!$product || !(int) $product->status) {
            return array('success' => FALSE, 'message' => 'The selected product is invalid or inactive.');
        }

        $scope_result = $this->validate_adjustment_scope($supplier_scope, $product);

        if (!$scope_result['success']) {
            return $scope_result;
        }

        if ($reason === '') {
            return array('success' => FALSE, 'message' => 'A reason is required for the stock adjustment.');
        }

        $this->CI->db->trans_begin();
        $locked_product = $this->CI->Stock_model->get_product_for_update($product_id);

        if (!$locked_product || !(int) $locked_product->status) {
            $this->CI->db->trans_rollback();
            return array('success' => FALSE, 'message' => 'An active product is required for the stock adjustment.');
        }

        $difference = $actual_stock - (int) $locked_product->stock;

        if ($difference === 0) {
            $this->CI->db->trans_rollback();
            return array('success' => FALSE, 'message' => 'Actual stock is already equal to system stock. No adjustment is needed.');
        }

        $transaction_no = 'ADJUSTMENT-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -6));
        $transaction_id = $this->CI->Stock_model->insert_transaction(array(
            'transaction_no' => $transaction_no,
            'type' => 'adjustment',
            'supplier_id' => $supplier_scope,
            'remarks' => $reason,
            'created_by' => (int) $user_id
        ));

        if ($transaction_id <= 0) {
            $this->CI->db->trans_rollback();
            return array('success' => FALSE, 'message' => 'Unable to create the adjustment transaction.');
        }

        $this->CI->Stock_model->insert_adjustment(array(
            'product_id' => $product_id,
            'system_stock' => $locked_product->stock,
            'actual_stock' => $actual_stock,
            'difference' => $difference,
            'reason' => $reason,
            'created_by' => (int) $user_id
        ));

        $this->CI->Stock_model->insert_transaction_item(array(
            'transaction_id' => $transaction_id,
            'product_id' => $product_id,
            'quantity' => abs($difference),
            'cost_price' => $locked_product->cost_price
        ));

        $this->CI->Stock_model->update_product_stock($product_id, $actual_stock);
        $this->CI->Stock_model->insert_activity_log(array(
            'user_id' => (int) $user_id,
            'action' => 'stock_adjustment',
            'description' => 'Processed ' . $transaction_no,
            'ip_address' => $this->CI->input->ip_address()
        ));

        if ($this->CI->db->trans_status() === FALSE) {
            $this->CI->db->trans_rollback();
            return array('success' => FALSE, 'message' => 'The stock adjustment could not be completed.');
        }

        $this->CI->db->trans_commit();
        return array('success' => TRUE, 'transaction_no' => $transaction_no);
    }

    // Search option builder ni para stock suppliers; application/controllers/Stock.php ang caller, then shared supplier formatting gikan Supplier_service.
    public function supplier_options($query, $limit = 20) {
        return $this->CI->supplier_service->search_options($query, $limit);
    }

    // Business lookup ni para stock scope products; application/controllers/Stock.php ang caller, supporting supplier IDs or Unassigned Products.
    public function supplier_products($supplier_scope, $mode) {
        $supplier_scope = trim((string) $supplier_scope);
        $mode = $mode === 'stock_out' ? 'stock_out' : 'stock_in';

        if ($supplier_scope === 'unassigned') {
            return array(
                'success' => TRUE,
                'supplier' => NULL,
                'scope' => 'unassigned',
                'scope_label' => 'Unassigned Products',
                'products' => $this->CI->Product_model->get_active_by_supplier('unassigned', $mode),
                'mode' => $mode
            );
        }

        if (!ctype_digit($supplier_scope) || (int) $supplier_scope <= 0) {
            return array('success' => FALSE, 'status' => 400, 'message' => 'Invalid supplier or product scope.');
        }

        $supplier_id = (int) $supplier_scope;
        $supplier = $this->CI->Supplier_model->get_by_id($supplier_id);

        if (!$supplier || !(int) $supplier->status) {
            return array('success' => FALSE, 'status' => 404, 'message' => 'Supplier not found or inactive.');
        }

        return array(
            'success' => TRUE,
            'supplier' => $supplier,
            'scope' => (string) $supplier_id,
            'scope_label' => (string) $supplier->supplier_name,
            'products' => $this->CI->Product_model->get_active_by_supplier($supplier_id, $mode),
            'mode' => $mode
        );
    }

    // Search option builder ni para adjustment products; application/controllers/Stock.php ang caller, with supplier-scope validation before query.
    public function adjustment_product_options($query, $supplier_scope, $limit = 20) {
        $supplier_scope = trim((string) $supplier_scope);

        if ($supplier_scope === '') {
            return array('success' => TRUE, 'items' => array());
        }

        if ($supplier_scope !== 'unassigned') {
            if (!ctype_digit($supplier_scope) || (int) $supplier_scope <= 0) {
                return array('success' => FALSE, 'status' => 400, 'message' => 'Invalid supplier selection.');
            }

            $supplier = $this->CI->Supplier_model->get_by_id((int) $supplier_scope);

            if (!$supplier || !(int) $supplier->status) {
                return array('success' => FALSE, 'status' => 404, 'message' => 'Supplier not found or inactive.');
            }
        }

        $items = array();

        foreach ($this->CI->Product_model->search_active($query, $supplier_scope, $limit) as $product) {
            $secondary = array();

            if (!empty($product->supplier_name)) {
                $secondary[] = $product->supplier_name;
            }

            $secondary[] = 'System stock: ' . (int) $product->stock . ' ' . ($product->unit ?: 'unit');
            $items[] = array(
                'id' => (int) $product->id,
                'text' => (string) ($product->product_code . ' - ' . $product->product_name),
                'secondary' => implode(' • ', $secondary),
                'current_stock' => (int) $product->stock,
                'unit' => (string) ($product->unit ?: 'unit'),
                'supplier_id' => $product->supplier_id !== NULL ? (int) $product->supplier_id : 0,
                'supplier_name' => (string) ($product->supplier_name ?: 'No supplier')
            );
        }

        return array('success' => TRUE, 'items' => $items);
    }

    // Internal helper ni para adjustment supplier scope; tawagon ra sulod Stock_service para selected product sakto jud sa chosen supplier scope.
    private function validate_adjustment_scope($supplier_scope, $product) {
        $supplier_scope = trim((string) $supplier_scope);

        if ($supplier_scope === 'unassigned') {
            return $product->supplier_id === NULL
                ? array('success' => TRUE)
                : array('success' => FALSE, 'message' => 'The selected product is not an unassigned product.');
        }

        if (!ctype_digit($supplier_scope) || (int) $supplier_scope <= 0) {
            return array('success' => FALSE, 'message' => 'Select a valid supplier before choosing a product.');
        }

        $supplier_id = (int) $supplier_scope;
        $supplier = $this->CI->Supplier_model->get_by_id($supplier_id);

        if (!$supplier || !(int) $supplier->status || (int) $product->supplier_id !== $supplier_id) {
            return array('success' => FALSE, 'message' => 'The selected product does not belong to the selected supplier.');
        }

        return array('success' => TRUE);
    }

    // Internal helper ni para normalize transaction items; tawagon ra sulod Stock_service para duplicate products ma-combine ug quantities ma-validate safely.
    private function normalize_items($items) {
        $normalized = array();

        foreach ((array) $items as $item) {
            if (!is_array($item) ||
                !isset($item['product_id'], $item['quantity']) ||
                !is_scalar($item['product_id']) ||
                !is_scalar($item['quantity'])) {
                return FALSE;
            }

            $product_id = filter_var(
                $item['product_id'],
                FILTER_VALIDATE_INT,
                array('options' => array('min_range' => 1, 'max_range' => Stock_rules::MAX_STOCK))
            );
            $quantity = filter_var(
                $item['quantity'],
                FILTER_VALIDATE_INT,
                array('options' => array('min_range' => 1, 'max_range' => Stock_rules::MAX_STOCK))
            );

            if ($product_id === FALSE || $quantity === FALSE) {
                return FALSE;
            }

            $product_id = (int) $product_id;
            $quantity = (int) $quantity;

            if (!isset($normalized[$product_id])) {
                $normalized[$product_id] = 0;
            }

            if ($normalized[$product_id] > Stock_rules::MAX_STOCK - $quantity) {
                return FALSE;
            }

            $normalized[$product_id] += $quantity;
        }

        return $normalized;
    }
}
