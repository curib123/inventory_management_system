<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/application/libraries/Product_service.php';

function &get_instance() {
    return $GLOBALS['ci'];
}

class ProductMovementHistoryTest extends TestCase {
    protected function setUp(): void {
        $GLOBALS['ci'] = new class {
            public $Product_model;
            public $Stock_model;
            public $load;

            public function __construct() {
                $this->load = new class {
                    public function model($names = array()) {
                    }
                };

                $this->Product_model = new class {
                    public function get_by_id($id) {
                        return (object) array(
                            'id' => (int) $id,
                            'product_name' => 'Widget',
                            'unit' => 'pcs'
                        );
                    }
                };

                $this->Stock_model = new class {
                    public function get_product_transaction_movements($product_id) {
                        return array(
                            array(
                                'source_id' => 101,
                                'type' => 'stock_in',
                                'transaction_no' => 'IN-101',
                                'quantity' => 10,
                                'cost_price' => 2.50,
                                'supplier_name' => 'Acme',
                                'username' => 'alice',
                                'remarks' => 'restock',
                                'created_at' => '2024-01-01 09:00:00'
                            ),
                            array(
                                'source_id' => 102,
                                'type' => 'stock_out',
                                'transaction_no' => 'OUT-102',
                                'quantity' => 3,
                                'cost_price' => 3.00,
                                'supplier_name' => 'Acme',
                                'username' => 'bob',
                                'remarks' => 'sale',
                                'created_at' => '2024-01-02 11:00:00'
                            ),
                            array(
                                'source_id' => 901,
                                'type' => 'adjustment',
                                'transaction_no' => 'ADJ-901',
                                'quantity' => 5,
                                'cost_price' => 0,
                                'supplier_name' => 'Acme',
                                'username' => 'charlie',
                                'remarks' => 'duplicate adjustment row',
                                'created_at' => '2024-01-03 12:00:00'
                            )
                        );
                    }

                    public function get_product_adjustments($product_id) {
                        return array(
                            array(
                                'id' => 901,
                                'system_stock' => 7,
                                'actual_stock' => 12,
                                'difference' => 5,
                                'reason' => 'counted up',
                                'created_at' => '2024-01-03 12:00:00',
                                'username' => 'charlie'
                            ),
                            (object) array(
                                'id' => 902,
                                'system_stock' => 12,
                                'actual_stock' => 4,
                                'difference' => -8,
                                'reason' => 'counted down',
                                'created_at' => '2024-01-04 14:00:00',
                                'username' => 'dana'
                            )
                        );
                    }
                };
            }
        };
    }

    public function testMovementHistoryLabelsAdjustmentsAndSkipsDuplicateAdjustmentRows() {
        $service = new Product_service();

        $result = $service->movement_history(1, 'desc');

        $this->assertTrue($result['success']);
        $this->assertSame(4, $result['summary']['total']);
        $this->assertSame(1, $result['summary']['stock_in']);
        $this->assertSame(1, $result['summary']['stock_out']);
        $this->assertSame(2, $result['summary']['adjustment']);

        $adjustments = array_values(array_filter($result['movements'], function ($movement) {
            return $movement['type'] === 'adjustment';
        }));

        $this->assertCount(2, $adjustments);
        $this->assertSame('Adjustment', $adjustments[0]['type_label']);
        $this->assertSame(-8, $adjustments[0]['signed_quantity']);
        $this->assertSame(12, $adjustments[0]['system_stock']);
        $this->assertSame(4, $adjustments[0]['actual_stock']);
        $this->assertSame(-8, $adjustments[0]['difference']);
        $this->assertSame('counted down', $adjustments[0]['remarks']);

        $this->assertSame('Adjustment', $adjustments[1]['type_label']);
        $this->assertSame(5, $adjustments[1]['signed_quantity']);
        $this->assertSame(7, $adjustments[1]['system_stock']);
        $this->assertSame(12, $adjustments[1]['actual_stock']);
        $this->assertSame(5, $adjustments[1]['difference']);
        $this->assertSame('counted up', $adjustments[1]['remarks']);
    }
}
