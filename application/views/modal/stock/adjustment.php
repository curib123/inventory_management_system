<?php
$adjustment_confirmation = array(
    'title' => 'Confirm stock adjustment?',
    'message' => 'Review the product, system stock, physical count, variance, and reason before applying this correction.',
    'impact' => 'This replaces the recorded stock with the verified physical count and creates a permanent adjustment history entry.',
    'assist' => 'Use Stock Adjustment only for verified reconciliation differences. Normal receiving and releasing must use Stock In or Stock Out.',
    'label' => 'Apply Adjustment',
    'variant' => 'warning',
    'icon' => 'bi-sliders'
);

echo form_open(current_url(), ui_modal_form_attributes($adjustment_confirmation));

$selected_supplier = set_value('supplier_filter');
$selected_product = set_value('product_id');
$selected_product_record = !empty($products) ? reset($products) : NULL;
$system_stock = $selected_product_record ? (int) $selected_product_record->stock : NULL;
$product_unit = $selected_product_record && !empty($selected_product_record->unit)
    ? $selected_product_record->unit
    : 'unit';
$actual_value = set_value('actual_stock');
$variance = ($system_stock !== NULL && $actual_value !== '' && is_numeric($actual_value))
    ? ((int) $actual_value - $system_stock)
    : NULL;
?>

<?php
$this->load->view('components/modal/header', array(
    'modal_title' => $page_title,
    'modal_subtitle' => 'Find the counted product, compare system stock with the physical count, then record the reason for the difference.',
    'modal_icon' => 'bi-sliders',
    'modal_variant' => 'warning',
    'modal_eyebrow' => 'Inventory reconciliation'
));
?>

<div class="modal-body">
    <?php if (validation_errors()): ?>
        <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
    <?php endif; ?>

    <?php if (!empty($form_error)): ?>
        <div class="alert alert-danger"><?php echo html_escape($form_error); ?></div>
    <?php endif; ?>

    <div class="app-adjustment-flow">
        <section class="app-adjustment-section">
            <div class="app-adjustment-section-heading">
                <span class="app-adjustment-step">1</span>
                <div>
                    <h3>Find the product</h3>
                    <p>Supplier is optional and only narrows the product search. It is not saved as the source of the adjustment.</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-5">
                    <label for="adjustment_supplier_filter" class="form-label">
                        Supplier <span class="text-body-secondary">(Optional)</span>
                    </label>
                    <select
                        id="adjustment_supplier_filter"
                        name="supplier_filter"
                        class="form-select"
                        data-adjustment-supplier
                        data-searchable-select
                        data-search-placeholder="Search supplier name, contact, or phone..."
                        data-search-url="<?php echo site_url('stock/suppliers/search'); ?>"
                        data-search-mode="adjustment"
                        data-product-target="#adjustment_product_id"
                    >
                        <option value="">All suppliers</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option
                                value="<?php echo (int) $supplier->id; ?>"
                                <?php echo ((string) $selected_supplier === (string) $supplier->id) ? 'selected' : ''; ?>
                            >
                                <?php echo html_escape($supplier->supplier_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Use this only when you know which supplier owns the product.</div>
                </div>

                <div class="col-12 col-md-7">
                    <label for="adjustment_product_id" class="form-label">Product</label>
                    <select
                        id="adjustment_product_id"
                        name="product_id"
                        class="form-select"
                        required
                        data-adjustment-product
                        data-searchable-select
                        data-search-placeholder="Search product code, name, unit, or supplier..."
                        data-search-url="<?php echo site_url('stock/adjustment/products/search'); ?>"
                        data-search-dependent="#adjustment_supplier_filter"
                        data-search-dependent-param="supplier_id"
                    >
                        <option value="">Search then select a product</option>
                        <?php foreach ($products as $product): ?>
                            <option
                                value="<?php echo (int) $product->id; ?>"
                                data-supplier-id="<?php echo (int) $product->supplier_id; ?>"
                                data-current-stock="<?php echo (int) $product->stock; ?>"
                                data-unit="<?php echo html_escape($product->unit ?: 'unit'); ?>"
                                data-supplier-name="<?php echo html_escape($product->supplier_name ?: 'No supplier'); ?>"
                                <?php echo ((string) $selected_product === (string) $product->id) ? 'selected' : ''; ?>
                            >
                                <?php
                                echo html_escape(
                                    $product->product_code . ' - ' .
                                    $product->product_name .
                                    ' — System stock: ' . (int) $product->stock . ' ' .
                                    ($product->unit ?: 'unit')
                                );
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Searches active products on the server, so the selector stays fast even with a large catalog.</div>
                </div>
            </div>
        </section>

        <section class="app-adjustment-section">
            <div class="app-adjustment-section-heading">
                <span class="app-adjustment-step">2</span>
                <div>
                    <h3>Reconcile the count</h3>
                    <p>Enter the verified physical quantity. The system calculates the variance automatically.</p>
                </div>
            </div>

            <div
                class="app-adjustment-snapshot <?php echo $selected_product_record ? '' : 'is-empty'; ?>"
                data-adjustment-snapshot
            >
                <div class="app-adjustment-metric">
                    <span class="app-adjustment-metric-label">System stock</span>
                    <strong data-adjustment-system-stock>
                        <?php echo $system_stock !== NULL ? $system_stock : '—'; ?>
                    </strong>
                    <span data-adjustment-unit><?php echo html_escape($selected_product_record ? $product_unit : ''); ?></span>
                </div>

                <div class="app-adjustment-arrow" aria-hidden="true">
                    <i class="bi bi-arrow-right"></i>
                </div>

                <div class="app-adjustment-metric">
                    <label for="actual_stock" class="app-adjustment-metric-label">Actual physical stock</label>
                    <div class="app-adjustment-actual-wrap">
                        <input
                            type="number"
                            id="actual_stock"
                            name="actual_stock"
                            class="form-control"
                            min="0"
                            max="2147483647"
                            step="1"
                            required
                            data-adjustment-actual
                            value="<?php echo html_escape($actual_value); ?>"
                            <?php echo $selected_product_record ? '' : 'disabled'; ?>
                        >
                        <span data-adjustment-unit-copy><?php echo html_escape($selected_product_record ? $product_unit : ''); ?></span>
                    </div>
                </div>

                <div class="app-adjustment-arrow" aria-hidden="true">
                    <i class="bi bi-chevron-right"></i>
                </div>

                <div class="app-adjustment-metric app-adjustment-variance">
                    <span class="app-adjustment-metric-label">Variance</span>
                    <strong
                        data-adjustment-variance
                        class="<?php
                        echo $variance === NULL
                            ? ''
                            : ($variance > 0 ? 'text-success' : ($variance < 0 ? 'text-danger' : 'text-body-secondary'));
                        ?>"
                    >
                        <?php
                        if ($variance === NULL) {
                            echo '—';
                        } elseif ($variance > 0) {
                            echo '+' . $variance;
                        } else {
                            echo $variance;
                        }
                        ?>
                    </strong>
                    <span data-adjustment-variance-label>
                        <?php
                        if ($variance === NULL) {
                            echo 'Select a product';
                        } elseif ($variance > 0) {
                            echo 'Physical count is higher';
                        } elseif ($variance < 0) {
                            echo 'Physical count is lower';
                        } else {
                            echo 'No difference';
                        }
                        ?>
                    </span>
                </div>
            </div>

            <div class="form-text mt-2">
                Example: system stock 10 and physical count 8 gives a variance of -2. Saving sets the product stock to 8.
            </div>
        </section>

        <section class="app-adjustment-section">
            <div class="app-adjustment-section-heading">
                <span class="app-adjustment-step">3</span>
                <div>
                    <h3>Document the reason</h3>
                    <p>Every adjustment needs an audit-friendly explanation.</p>
                </div>
            </div>

            <label for="reason" class="form-label">Reason</label>
            <input
                type="text"
                id="reason"
                name="reason"
                class="form-control"
                maxlength="255"
                required
                placeholder="Example: Physical count after cycle count found 2 damaged units"
                value="<?php echo html_escape(set_value('reason')); ?>"
            >
            <div class="form-text">Use a specific business reason such as damage, shrinkage, counting correction, or data-entry correction.</div>
        </section>
    </div>

    <div class="mt-3">
        <?php
        $this->load->view('components/form/assist_note', array(
            'assist_title' => 'Adjustment is for reconciliation only',
            'assist_text' => 'Do not use this to receive or release normal inventory. Use Stock In for receipts and Stock Out for releases so movement history remains accurate.',
            'assist_variant' => 'warning',
            'assist_icon' => 'bi-exclamation-triangle'
        ));
        ?>
    </div>
</div>

<?php
$this->load->view('components/modal/footer', array(
    'close_label' => 'Cancel',
    'submit_label' => 'Review Adjustment',
    'submit_class' => 'btn-warning',
    'submit_icon' => 'bi-check-lg',
    'footer_note' => 'The adjustment is not saved until you confirm the review step.'
));
?>

<?php echo form_close(); ?>
