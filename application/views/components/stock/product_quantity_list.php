<?php
$products = isset($products) ? (array) $products : array();
$quantities = isset($quantities) && is_array($quantities) ? $quantities : array();
?>

<?php if (!empty($products)): ?>
    <div class="app-stock-product-list">
        <?php foreach ($products as $product): ?>
            <?php
            $product_id = (int) $product->id;
            $is_low_stock = (int) $product->stock <= (int) $product->reorder_level;
            $quantity = isset($quantities[$product_id]) ? (int) $quantities[$product_id] : '';
            $preview_quantity = $quantity === '' ? 0 : (int) $quantity;
            $new_stock = (int) $product->stock + $preview_quantity;
            ?>
            <div class="app-stock-product <?php echo $is_low_stock ? 'is-low-stock' : ''; ?>">
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="app-stock-product-name">
                            <?php echo html_escape($product->product_code . ' - ' . $product->product_name); ?>
                        </span>

                        <?php if ($is_low_stock): ?>
                            <span class="badge text-bg-warning">Low stock priority</span>
                        <?php endif; ?>
                    </div>

                    <div class="app-stock-product-meta">
                        <span>
                            Current:
                            <strong><?php echo (int) $product->stock; ?></strong>
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
                                data-current-stock="<?php echo (int) $product->stock; ?>"
                            ><?php echo $new_stock; ?></strong>
                            <?php echo html_escape($product->unit ?: 'unit'); ?>
                        </span>
                    </div>
                </div>

                <div class="app-stock-product-quantity">
                    <input type="hidden" name="product_id[]" value="<?php echo $product_id; ?>">

                    <label for="stock_in_quantity_<?php echo $product_id; ?>" class="form-label small mb-1">
                        Stock In Qty
                    </label>

                    <input
                        type="number"
                        id="stock_in_quantity_<?php echo $product_id; ?>"
                        name="quantity[]"
                        class="form-control"
                        data-stock-quantity
                        min="0"
                        step="1"
                        inputmode="numeric"
                        placeholder="0"
                        value="<?php echo $quantity === '' ? '' : (int) $quantity; ?>"
                    >
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="app-stock-product-empty">
        <i class="bi bi-box-seam d-block fs-3 mb-2"></i>
        No active products are assigned to this supplier.
    </div>
<?php endif; ?>
