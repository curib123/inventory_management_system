<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_rules {

    const MAX_STOCK = 2147483647;

    public function calculate_stock($current_stock, $quantity, $type) {
        if (!in_array($type, array('stock_in', 'stock_out'), TRUE)) {
            throw new InvalidArgumentException('Invalid stock transaction type.');
        }

        $current_stock = filter_var(
            $current_stock,
            FILTER_VALIDATE_INT,
            array('options' => array(
                'min_range' => 0,
                'max_range' => self::MAX_STOCK
            ))
        );
        $quantity = filter_var(
            $quantity,
            FILTER_VALIDATE_INT,
            array('options' => array(
                'min_range' => 1,
                'max_range' => self::MAX_STOCK
            ))
        );

        if ($current_stock === FALSE) {
            throw new InvalidArgumentException(
                'Current stock must be a valid non-negative whole number.'
            );
        }

        if ($quantity === FALSE) {
            throw new InvalidArgumentException(
                'Quantity must be a valid whole number greater than zero.'
            );
        }

        if ($type === 'stock_out' && $quantity > $current_stock) {
            throw new UnderflowException('Insufficient stock.');
        }

        if ($type === 'stock_in' && $quantity > self::MAX_STOCK - $current_stock) {
            throw new InvalidArgumentException(
                'Resulting stock exceeds the supported inventory limit.'
            );
        }

        return $type === 'stock_in'
            ? $current_stock + $quantity
            : $current_stock - $quantity;
    }
}
