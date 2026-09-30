<?php
$summary = isset($movement_summary) && is_array($movement_summary)
    ? $movement_summary
    : array('total' => 0, 'stock_in' => 0, 'stock_out' => 0, 'adjustment' => 0);
$sort = isset($movement_sort) && $movement_sort === 'asc' ? 'asc' : 'desc';
$movement_url = site_url('products/movement/' . (int) $product->id);
?>

<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Product Movement',
    'modal_subtitle' => 'Complete stock history for this product.',
    'modal_icon' => 'bi-clock-history',
    'modal_variant' => 'info'
));
?>

<div class="app-modal-body">

    <div class="app-movement-toolbar">
        <div>
            <div class="app-movement-toolbar-title">Movement timeline</div>
            <div class="app-movement-toolbar-note">Sorted by transaction date.</div>
        </div>

        <div class="btn-group btn-group-sm" role="group" aria-label="Sort product movement by date">
            <button
                type="button"
                class="btn <?php echo $sort === 'desc' ? 'btn-primary' : 'btn-outline-secondary'; ?>"
                data-modal-url="<?php echo html_escape($movement_url . '?sort=desc'); ?>"
            >
                <i class="bi bi-sort-down me-1"></i>Newest
            </button>
            <button
                type="button"
                class="btn <?php echo $sort === 'asc' ? 'btn-primary' : 'btn-outline-secondary'; ?>"
                data-modal-url="<?php echo html_escape($movement_url . '?sort=asc'); ?>"
            >
                <i class="bi bi-sort-up me-1"></i>Oldest
            </button>
        </div>
    </div>

    <?php if (empty($movements)): ?>
        <div class="app-movement-empty">
            <i class="bi bi-clock-history" aria-hidden="true"></i>
            <strong>No movement yet</strong>
            <span>This product has no Stock In, Stock Out, or adjustment history.</span>
        </div>
    <?php else: ?>
        <div class="app-movement-timeline">
            <?php $last_date_key = ''; ?>
            <?php foreach ($movements as $index => $movement): ?>
                <?php
                $timestamp = strtotime((string) $movement['created_at']);
                $date_key = $timestamp ? date('Y-m-d', $timestamp) : 'unknown';
                $date_label = $timestamp ? date('F j, Y', $timestamp) : 'Unknown date';
                $time_label = $timestamp ? date('g:i A', $timestamp) : '—';

                if ($movement['type'] === 'stock_in') {
                    $variant = 'success';
                    $icon = 'bi-box-arrow-in-down';
                } elseif ($movement['type'] === 'stock_out') {
                    $variant = 'danger';
                    $icon = 'bi-box-arrow-up';
                } else {
                    $variant = 'warning';
                    $icon = 'bi-sliders';
                }

                $signed_quantity = (int) $movement['signed_quantity'];
                $quantity_label = ($signed_quantity > 0 ? '+' : '') . number_format($signed_quantity);
                ?>

                <?php if ($date_key !== $last_date_key): ?>
                    <div class="app-movement-date">
                        <span><?php echo html_escape($date_label); ?></span>
                    </div>
                    <?php $last_date_key = $date_key; ?>
                <?php endif; ?>

                <article class="app-movement-step app-movement-step-<?php echo html_escape($variant); ?>">
                    <div class="app-movement-step-rail" aria-hidden="true">
                        <span class="app-movement-step-dot">
                            <i class="bi <?php echo html_escape($icon); ?>"></i>
                        </span>
                    </div>
                          <div class="app-movement-card">
                        <div class="app-movement-card-head">
                            <div>
                                <div class="app-movement-type-row">
                                    <span class="app-movement-type app-movement-type-<?php echo html_escape($variant); ?>">
                                        <?php echo html_escape($movement['type_label']); ?>
                                    </span>
                                    <span class="app-movement-reference">
                                        <?php echo html_escape($movement['transaction_no']); ?>
                                    </span>
                                </div>
                                <div class="app-movement-time"><?php echo html_escape($time_label); ?></div>
                            </div>

                            <div class="app-movement-quantity app-movement-quantity-<?php echo html_escape($variant); ?>">
                                <?php echo html_escape($quantity_label); ?>
                                <small><?php echo html_escape($product->unit ?: 'unit'); ?></small>
                            </div>
                        </div>

                        <dl class="app-movement-details">
                            <?php if ($movement['type'] === 'adjustment'): ?>
                                <div>
                                    <dt>System stock</dt>
                                    <dd><?php echo number_format((int) $movement['system_stock']); ?></dd>
                                </div>
                                <div>
                                    <dt>Actual stock</dt>
                                    <dd><?php echo number_format((int) $movement['actual_stock']); ?></dd>
                                </div>
                                <div>
                                    <dt>Difference</dt>
                                    <dd><?php echo html_escape($quantity_label); ?></dd>
                                </div>
                            <?php else: ?>
                                <div>
                                    <dt>Quantity</dt>
                                    <dd><?php echo number_format((int) $movement['quantity']); ?></dd>
                                </div>
                                <div>
                                    <dt>Cost price</dt>
                                    <dd>₱<?php echo number_format((float) $movement['cost_price'], 2); ?></dd>
                                </div>
                                <div>
                                    <dt>Supplier</dt>
                                    <dd><?php echo html_escape($movement['supplier_name'] ?: 'Unassigned Products'); ?></dd>
                                </div>
                            <?php endif; ?>

                            <div>
                                <dt>Processed by</dt>
                                <dd><?php echo html_escape($movement['username'] ?: 'Unknown user'); ?></dd>
                            </div>
                        </dl>

                        <?php if ($movement['remarks'] !== ''): ?>
                            <div class="app-movement-note">
                                <i class="bi bi-chat-left-text" aria-hidden="true"></i>
                                <span><?php echo html_escape($movement['remarks']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$this->load->view('components/modal/footer', array(
    'close_label' => 'Close'
));
?>
