<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_rules {

    public function calculate_stock($current_stock, $quantity, $type) {
        if (!in_array($type, array('stock_in', 'stock_out'), TRUE)) {
            throw new InvalidArgumentException('Invalid stock transaction type.');
        }

        $current_stock = filter_var(
            $current_stock,
            FILTER_VALIDATE_INT,
            array('options' => array('min_range' => 0))
        );
        $quantity = filter_var(
            $quantity,
            FILTER_VALIDATE_INT,
            array('options' => array('min_range' => 1))
        );

        if ($current_stock === FALSE) {
            throw new InvalidArgumentException(
                'Current stock must be a non-negative whole number.'
            );
        }

        if ($quantity === FALSE) {
            throw new InvalidArgumentException(
                'Quantity must be a whole number greater than zero.'
            );
        }

        if ($type === 'stock_out' && $quantity > $current_stock) {
            throw new UnderflowException('Insufficient stock.');
        }

        return $type === 'stock_in'
            ? $current_stock + $quantity
            : $current_stock - $quantity;
    }
}
