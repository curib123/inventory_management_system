<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_rules {

    public function calculate_stock($current_stock, $quantity, $type) {
        $current_stock = (int) $current_stock;
        $quantity = (int) $quantity;

        if (!in_array($type, array('stock_in', 'stock_out'), TRUE)) {
            throw new InvalidArgumentException('Invalid stock transaction type.');
        }

        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero.');
        }

        if ($type === 'stock_out' && $quantity > $current_stock) {
            throw new UnderflowException('Insufficient stock.');
        }

        return $type === 'stock_in'
            ? $current_stock + $quantity
            : $current_stock - $quantity;
    }
}
