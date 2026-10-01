<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/system/core/Model.php';
require_once dirname(__DIR__) . '/application/models/Report_model.php';

class FakeReportQueryBuilder {
    public $selects = array();
    public $froms = array();
    public $joins = array();
    public $wheres = array();
    public $likes = array();
    public $orders = array();
    public $limitValue = null;
    public $offsetValue = null;
    public $groupStarts = 0;
    public $groupEnds = 0;

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

    public function where($key, $value = NULL, $escape = NULL) {
        $this->wheres[] = array($key, $value, $escape);
        return $this;
    }

    public function like($field, $match = '', $side = 'both') {
        $this->likes[] = array($field, $match, $side);
        return $this;
    }

    public function group_start() {
        $this->groupStarts++;
        return $this;
    }

    public function or_group_start() {
        $this->groupStarts++;
        return $this;
    }

    public function group_end() {
        $this->groupEnds++;
        return $this;
    }

    public function order_by($field, $direction = 'ASC') {
        $this->orders[] = array($field, $direction);
        return $this;
    }

    public function limit($limit, $offset = 0) {
        $this->limitValue = (int) $limit;
        $this->offsetValue = (int) $offset;
        return $this;
    }

    public function count_all_results() {
        return 0;
    }

    public function get() {
        return new class {
            public function result_array() {
                return array();
            }
        };
    }
}

class FakeReportLoader {
    private $owner;

    public function __construct($owner) {
        $this->owner = $owner;
    }

    public function database() {
        $this->owner->db = new FakeReportQueryBuilder();
        return $this->owner->db;
    }
}

class TestableReportModel extends Report_model {
    public function __construct() {
        $this->load = new FakeReportLoader($this);
        parent::__construct();
    }
}

class ReportModelDateFilterTest extends TestCase {
    public function testSnapshotReportsIgnoreDateRangeFilters() {
        $model = new TestableReportModel();

        $model->get_export_rows('inventory', '', array('period' => 'today'));
        $this->assertFalse($this->containsDateFilter($model->db->wheres, 'p.created_at'));

        $model = new TestableReportModel();
        $model->get_export_rows('low-stock', '', array('period' => 'today'));
        $this->assertFalse($this->containsDateFilter($model->db->wheres, 'p.created_at'));
    }

    public function testTransactionReportsKeepDateRangeFilters() {
        $model = new TestableReportModel();

        $model->get_export_rows('stock-in', '', array('period' => 'today'));
        $this->assertTrue($this->containsDateFilter($model->db->wheres, 't.created_at'));
    }

    public function testMovementReportJoinsAdjustmentDetailsByTransactionAndProduct() {
        $model = new TestableReportModel();

        $model->get_export_rows('movement');

        $this->assertContains(
            array('stock_adjustments a', 'a.transaction_id = t.id AND a.product_id = i.product_id', 'left'),
            $model->db->joins
        );
        $this->assertStringContainsString('a.system_stock, a.actual_stock, a.difference', implode(' ', $model->db->selects));
    }

    public function testMovementReportUsesProductAndCategorySnapshots() {
        $model = new TestableReportModel();

        $model->get_export_rows('movement', '', array('category' => 4));

        $selected_columns = implode(' ', $model->db->selects);
        $this->assertStringContainsString('i.product_code_snapshot AS product_code', $selected_columns);
        $this->assertStringContainsString('i.product_name_snapshot AS product_name', $selected_columns);
        $this->assertStringContainsString('i.category_name_snapshot AS category_name', $selected_columns);
        $this->assertContains(array('i.category_id_snapshot', 4, NULL), $model->db->wheres);
        $this->assertNotContains(array('products p', 'p.id = i.product_id', ''), $model->db->joins);
        $this->assertStringContainsString("WHEN t.type = 'stock_out' THEN -i.quantity", $selected_columns);
        $this->assertStringContainsString("WHEN t.type = 'adjustment' THEN COALESCE(a.difference, i.quantity)", $selected_columns);
    }

    public function testLegacyMovementReportUsesProductSnapshots() {
        $model = new TestableReportModel();

        $model->get_stock_movement_report();

        $selected_columns = implode(' ', $model->db->selects);
        $this->assertStringContainsString('i.product_code_snapshot AS product_code', $selected_columns);
        $this->assertStringContainsString('i.category_name_snapshot AS category_name', $selected_columns);
        $this->assertNotContains(array('products p', 'p.id = i.product_id', ''), $model->db->joins);
    }

    public function testExportChunkQueryUsesBoundedLimitAndCompositeCursor() {
        $model = new TestableReportModel();

        $model->get_export_rows_chunk('movement', '', array(), array(
            'created_at' => '2026-09-01 10:00:00',
            'transaction_id' => 22,
            'item_id' => 91
        ), 500);

        $this->assertSame(500, $model->db->limitValue);
        $this->assertContains(array('t.created_at <', '2026-09-01 10:00:00', NULL), $model->db->wheres);
        $this->assertContains(array('t.id <', 22, NULL), $model->db->wheres);
        $this->assertContains(array('i.id >', 91, NULL), $model->db->wheres);
        $this->assertSame(array('t.created_at', 'DESC'), $model->db->orders[0]);
        $this->assertSame(array('t.id', 'DESC'), $model->db->orders[1]);
        $this->assertSame(array('i.id', 'ASC'), $model->db->orders[2]);
    }

    private function containsDateFilter(array $clauses, $field) {
        foreach ($clauses as $clause) {
            if (is_array($clause) && isset($clause[0]) && strpos((string) $clause[0], $field) !== FALSE) {
                return TRUE;
            }
        }

        return FALSE;
    }
}
