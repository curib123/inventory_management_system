<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/application/libraries/Stock_service.php';

class Issue009DatabaseStub {
    public function trans_begin() {}
    public function trans_rollback() {}
    public function trans_commit() {}
    public function trans_status() { return TRUE; }
}

class Issue009ProductModelStub {
    public function get_by_id($product_id) {
        return (object) array(
            'id' => $product_id,
            'supplier_id' => 2,
            'status' => 1
        );
    }
}

class Issue009SupplierModelStub {
    public function get_by_id($supplier_id) {
        return (object) array('id' => $supplier_id, 'status' => 1);
    }
}

class Issue009StockModelStub {
    public $adjustment;
    public $transaction_item;

    public function get_product_for_update($product_id) {
        return (object) array(
            'id' => $product_id,
            'supplier_id' => 2,
            'status' => 1,
            'stock' => 10,
            'cost_price' => 25.50
        );
    }

    public function insert_transaction($data) {
        return 314;
    }

    public function insert_adjustment($data) {
        $this->adjustment = $data;
        return TRUE;
    }

    public function insert_transaction_item($data) {
        $this->transaction_item = $data;
        return TRUE;
    }

    public function update_product_stock($product_id, $stock) {
        return TRUE;
    }
}

class Issue009ActivityLogModelStub {
    public function insert_activity_log($data) {
        return TRUE;
    }
}

class Issue009InputStub {
    public function ip_address() {
        return '127.0.0.1';
    }
}

class Issue009StockServiceDatabaseStub extends Issue009DatabaseStub {}

class StockAdjustmentTransactionLinkTest extends TestCase {
    public function testAdjustmentAndTransactionItemUseTheCreatedTransactionId() {
        $stock_model = new Issue009StockModelStub();
        $ci = (object) array(
            'db' => new Issue009StockServiceDatabaseStub(),
            'Product_model' => new Issue009ProductModelStub(),
            'Supplier_model' => new Issue009SupplierModelStub(),
            'Stock_model' => $stock_model,
            'Activity_log_model' => new Issue009ActivityLogModelStub(),
            'stock_rules' => new Stock_rules(),
            'input' => new Issue009InputStub()
        );

        $service = (new ReflectionClass(Stock_service::class))->newInstanceWithoutConstructor();
        $ci_property = new ReflectionProperty(Stock_service::class, 'CI');
        $ci_property->setAccessible(TRUE);
        $ci_property->setValue($service, $ci);

        $result = $service->create_adjustment('2', 9, 8, 'Physical count correction', 7);

        $this->assertTrue($result['success']);
        $this->assertSame(314, $stock_model->adjustment['transaction_id']);
        $this->assertSame(314, $stock_model->transaction_item['transaction_id']);
        $this->assertSame(9, $stock_model->transaction_item['product_id']);
    }
}
