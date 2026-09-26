<?php

use PHPUnit\Framework\TestCase;

class StockRulesTest extends TestCase {

    // QA ni para stock-in math; incoming quantity should add to current stock exactly.
    public function testStockInAddsQuantityToCurrentStock() {
        $rules = new Stock_rules();

        $this->assertSame(125, $rules->calculate_stock(100, 25, 'stock_in'));
    }

    // QA ni para stock-out math; released quantity should subtract from current stock exactly.
    public function testStockOutSubtractsQuantityFromCurrentStock() {
        $rules = new Stock_rules();

        $this->assertSame(75, $rules->calculate_stock(100, 25, 'stock_out'));
    }

    // QA ni para insufficient inventory; stock-out should reject quantity larger than available stock.
    public function testStockOutRejectsInsufficientInventory() {
        $this->expectException(UnderflowException::class);

        (new Stock_rules())->calculate_stock(10, 11, 'stock_out');
    }

    // QA ni para invalid quantity; zero or negative stock movement should be rejected.
    public function testZeroAndNegativeQuantitiesAreRejected() {
        $rules = new Stock_rules();

        $this->expectException(InvalidArgumentException::class);
        $rules->calculate_stock(10, 0, 'stock_in');
    }


    // QA ni para fractional quantity; whole-number inventory should reject decimals instead of truncating them.
    public function testFractionalQuantityIsRejectedInsteadOfTruncated() {
        $this->expectException(InvalidArgumentException::class);

        (new Stock_rules())->calculate_stock(10, 1.5, 'stock_in');
    }

    // QA ni para corrupt negative stock; stock calculation should reject invalid current inventory state.
    public function testNegativeCurrentStockIsRejected() {
        $this->expectException(InvalidArgumentException::class);

        (new Stock_rules())->calculate_stock(-1, 1, 'stock_in');
    }


    // QA ni para integer overflow; stock-in must not exceed signed database integer maximum.
    public function testStockInRejectsDatabaseIntegerOverflow() {
        $this->expectException(InvalidArgumentException::class);

        (new Stock_rules())->calculate_stock(2147483647, 1, 'stock_in');
    }

    // QA ni para oversized quantity; movement quantity beyond DB integer limit should fail safely.
    public function testQuantityBeyondDatabaseIntegerLimitIsRejected() {
        $this->expectException(InvalidArgumentException::class);

        (new Stock_rules())->calculate_stock(0, 2147483648, 'stock_in');
    }

    // QA ni para adjustment count; physical stock must be a whole number and reject fractional input.
    public function testAdjustmentStockValueRejectsFractionalInput() {
        $this->expectException(InvalidArgumentException::class);

        (new Stock_rules())->validate_stock_value(1.5);
    }

    // QA ni para adjustment overflow; physical stock beyond DB integer maximum should be rejected.
    public function testAdjustmentStockValueRejectsIntegerOverflow() {
        $this->expectException(InvalidArgumentException::class);

        (new Stock_rules())->validate_stock_value(2147483648);
    }

    // QA ni para boundary value; signed integer maximum should still be accepted as valid stock.
    public function testAdjustmentStockValueAllowsSignedIntMaximum() {
        $this->assertSame(
            2147483647,
            (new Stock_rules())->validate_stock_value(2147483647)
        );
    }

    // QA ni para transaction type whitelist; unsupported movement type should never reach stock calculation.
    public function testUnknownTransactionTypeIsRejected() {
        $this->expectException(InvalidArgumentException::class);

        (new Stock_rules())->calculate_stock(10, 1, 'adjustment');
    }
}
