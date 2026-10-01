<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/system/core/Model.php';
require_once dirname(__DIR__) . '/application/models/Stock_model.php';

class Issue013StockDetailsDatabaseStub {
    public $selects = array();
    public $froms = array();
    public $joins = array();
    public $wheres = array();

    public function select($sql, $escape = NULL) {
        $this->selects[] = $sql;
        return $this;
    }

    public function from($table) {
        $this->froms[] = $table;
        return $this;
    }

    public function join($table, $condition, $type = '') {
        $this->joins[] = array($table, $condition, $type);
        return $this;
    }

    public function where($field, $value = NULL) {
        $this->wheres[] = array($field, $value);
        return $this;
    }

    public function get() {
        return new class {
            public function result() {
                return array();
            }
        };
    }
}

class Issue013TestStockModel extends Stock_model {
    public $fake_db;

    public function __get($key) {
        if ($key === 'db') {
            return $this->fake_db;
        }

        return parent::__get($key);
    }
}

class StockTransactionDetailsQueryTest extends TestCase {
    public function testDetailsQueryUsesSnapshotsAndAdjustmentLinkWithoutCurrentProductJoin() {
        $model = (new ReflectionClass(Issue013TestStockModel::class))->newInstanceWithoutConstructor();
        $model->fake_db = new Issue013StockDetailsDatabaseStub();

        $model->get_transaction_items(42);

        $select = implode(' ', $model->fake_db->selects);
        $this->assertStringContainsString('i.product_code_snapshot AS product_code', $select);
        $this->assertStringContainsString('i.product_name_snapshot AS product_name', $select);
        $this->assertStringContainsString('i.category_name_snapshot AS category_name', $select);
        $this->assertStringContainsString('i.unit_snapshot AS unit', $select);
        $this->assertStringNotContainsString('p.product_code', $select);
        $this->assertNotContains('products p', $model->fake_db->froms);
        $this->assertNotContains(array('products p', 'p.id = i.product_id', ''), $model->fake_db->joins);
        $this->assertContains(
            array('stock_adjustments a', 'a.transaction_id = i.transaction_id AND a.product_id = i.product_id', 'left'),
            $model->fake_db->joins
        );
        $this->assertSame(array('i.transaction_id', 42), $model->fake_db->wheres[0]);
    }
}
