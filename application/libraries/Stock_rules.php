<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_rules {

    const MAX_STOCK = 2147483647;

    public function validate_stock_value($stock) {
        $stock = filter_var(
            $stock,
            FILTER_VALIDATE_INT,
            array('options' => array(
                'min_range' => 0,
                'max_range' => self::MAX_STOCK
            ))
        );

        if ($stock === FALSE) {
            throw new InvalidArgumentException(
                'Stock must be a valid whole number between 0 and ' . self::MAX_STOCK . '.'
            );
        }

        return (int) $stock;
    }

    public function validate_quantity($quantity) {
        $quantity = filter_var(
            $quantity,
            FILTER_VALIDATE_INT,
            array('options' => array(
                'min_range' => 1,
                'max_range' => self::MAX_STOCK
            ))
        );

        if ($quantity === FALSE) {
            throw new InvalidArgumentException(
                'Quantity must be a valid whole number between 1 and ' . self::MAX_STOCK . '.'
            );
        }

        return (int) $quantity;
    }

    public function calculate_stock($current_stock, $quantity, $type) {
        if (!in_array($type, array('stock_in', 'stock_out'), TRUE)) {
            throw new InvalidArgumentException('Invalid stock transaction type.');
        }

        $current_stock = $this->validate_stock_value($current_stock);
        $quantity = $this->validate_quantity($quantity);

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
