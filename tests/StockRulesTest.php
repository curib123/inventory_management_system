<?php

use PHPUnit\Framework\TestCase;

class StockRulesTest extends TestCase {

    public function testStockInAddsQuantityToCurrentStock() {
        $rules = new Stock_rules();

        $this->assertSame(125, $rules->calculate_stock(100, 25, 'stock_in'));
    }

    public function testStockOutSubtractsQuantityFromCurrentStock() {
        $rules = new Stock_rules();

        $this->assertSame(75, $rules->calculate_stock(100, 25, 'stock_out'));
    }

    public function testStockOutRejectsInsufficientInventory() {
        $this->expectException(UnderflowException::class);

        (new Stock_rules())->calculate_stock(10, 11, 'stock_out');
    }

    public function testZeroAndNegativeQuantitiesAreRejected() {
        $rules = new Stock_rules();

        $this->expectException(InvalidArgumentException::class);
        $rules->calculate_stock(10, 0, 'stock_in');
    }

    public function testUnknownTransactionTypeIsRejected() {
        $this->expectException(InvalidArgumentException::class);

        (new Stock_rules())->calculate_stock(10, 1, 'adjustment');
    }
}
