<?php
$products = isset($products) ? (array) $products : array();
$quantities = isset($quantities) && is_array($quantities) ? $quantities : array();
$mode = isset($mode) && $mode === 'stock_out' ? 'stock_out' : 'stock_in';
$is_stock_out = $mode === 'stock_out';
?>

<?php if (!empty($products)): ?>
    <div class="app-stock-product-list" data-stock-product-list>
        <?php foreach ($products as $product): ?>
            <?php
            $product_id = (int) $product->id;
            $current_stock = (int) $product->stock;
            $is_low_stock = $current_stock <= (int) $product->reorder_level;
            $quantity = isset($quantities[$product_id]) ? (int) $quantities[$product_id] : '';
            $preview_quantity = $quantity === '' ? 0 : (int) $quantity;
            $new_stock = $is_stock_out
                ? $current_stock - $preview_quantity
                : $current_stock + $preview_quantity;
            $search_text = strtolower(
                trim(
                    (string) $product->product_code . ' ' .
                    (string) $product->product_name . ' ' .
                    (string) $product->unit
                )
            );
            ?>
            <div
                class="app-stock-product <?php echo $is_low_stock ? 'is-low-stock' : ''; ?>"
                data-stock-product-card
                data-stock-product-search="<?php echo html_escape($search_text); ?>"
            >
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="app-stock-product-name">
                            <?php echo html_escape($product->product_code . ' - ' . $product->product_name); ?>
                        </span>

                        <?php if ($current_stock <= 0 && $is_stock_out): ?>
                            <span class="badge text-bg-secondary">Out of stock</span>
                        <?php elseif ($is_low_stock): ?>
                            <span class="badge text-bg-warning">Low stock</span>
                        <?php endif; ?>
                    </div>

                    <div class="app-stock-product-meta">
                        <span>
                            Current:
                            <strong><?php echo $current_stock; ?></strong>
                            <?php echo html_escape($product->unit ?: 'unit'); ?>
                        </span>
                        <span>
                            Reorder:
                            <strong><?php echo (int) $product->reorder_level; ?></strong>
                        </span>
                        <span class="app-stock-preview">
                            New:
                            <strong
                                data-stock-new
                                data-current-stock="<?php echo $current_stock; ?>"
                                data-stock-direction="<?php echo $is_stock_out ? '-1' : '1'; ?>"
                            ><?php echo $new_stock; ?></strong>
                            <?php echo html_escape($product->unit ?: 'unit'); ?>
                        </span>
                    </div>
                </div>

                <div class="app-stock-product-quantity">
                    <input type="hidden" name="product_id[]" value="<?php echo $product_id; ?>">


                    <input
                        type="number"
                        id="<?php echo $mode; ?>_quantity_<?php echo $product_id; ?>"
                        name="quantity[]"
                        class="form-control"
                        data-stock-quantity
                        min="0"
                        <?php if ($is_stock_out): ?>max="<?php echo $current_stock; ?>"<?php endif; ?>
                        step="1"
                        inputmode="numeric"
                        placeholder="0"
                        value="<?php echo $quantity === '' ? '' : (int) $quantity; ?>"
                        <?php echo ($is_stock_out && $current_stock <= 0) ? 'readonly' : ''; ?>
                    >
                </div>
            </div>
        <?php endforeach; ?>

        <div class="app-stock-product-empty d-none" data-stock-filter-empty>
            <i class="bi bi-search d-block fs-3 mb-2"></i>
            No products match your search.
        </div>
    </div>
<?php else: ?>
    <div class="app-stock-product-empty">
        <i class="bi bi-box-seam d-block fs-3 mb-2"></i>
        <?php echo $is_stock_out ? 'No active products are available for Stock Out.' : 'No active products are assigned to this supplier.'; ?>
    </div>
<?php endif; ?>
