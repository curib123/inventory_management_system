<?php
$adjustment_confirmation = array(
    'title' => 'Confirm stock adjustment?',
    'message' => 'Review the selected product, physical count, and reason before applying this correction.',
    'impact' => 'This changes the recorded stock to the actual count and creates a permanent adjustment history entry.',
    'assist' => 'Use adjustments only for verified physical-count corrections, not normal Stock In or Stock Out movement.',
    'label' => 'Apply Adjustment',
    'variant' => 'warning',
    'icon' => 'bi-sliders'
);

echo form_open(current_url(), ui_modal_form_attributes($adjustment_confirmation));
?>
<?php
$this->load->view('components/modal/header', array(
    'modal_title' => $page_title,
    'modal_subtitle' => 'Optionally filter by supplier, then select the exact product and record the physical count.',
    'modal_icon' => 'bi-sliders'
));
?>

<div class="modal-body">
    <?php if (validation_errors()): ?>
        <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
    <?php endif; ?>

    <?php if (!empty($form_error)): ?>
        <div class="alert alert-danger"><?php echo html_escape($form_error); ?></div>
    <?php endif; ?>

    <?php $selected_supplier = set_value('supplier_filter'); ?>
    <?php $selected_product = set_value('product_id'); ?>

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6">
            <label for="adjustment_supplier_filter" class="form-label">
                Supplier <span class="text-body-secondary">(Optional Filter)</span>
            </label>
            <select
                id="adjustment_supplier_filter"
                name="supplier_filter"
                class="form-select"
                data-searchable-select
                data-search-placeholder="Search suppliers..."
                data-adjustment-supplier
                data-product-target="#adjustment_product_id"
            >
                <option value="">All Suppliers</option>
                <?php foreach ($suppliers as $supplier): ?>
                    <option
                        value="<?php echo (int) $supplier->id; ?>"
                        <?php echo ((string) $selected_supplier === (string) $supplier->id) ? 'selected' : ''; ?>
                    >
                        <?php echo html_escape($supplier->supplier_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">
                Leave this blank to search every active product. Selecting a supplier limits the product list to that supplier.
            </div>
        </div>

        <div class="col-12 col-md-6">
            <label for="adjustment_product_id" class="form-label">Product</label>
            <select
                id="adjustment_product_id"
                name="product_id"
                class="form-select"
                required
                data-searchable-select
                data-search-placeholder="Search product code or name..."
                data-adjustment-product
            >
                <option value="">Select Product</option>
                <?php foreach ($products as $product): ?>
                    <option
                        value="<?php echo (int) $product->id; ?>"
                        data-supplier-id="<?php echo (int) $product->supplier_id; ?>"
                        <?php echo ((string) $selected_product === (string) $product->id) ? 'selected' : ''; ?>
                    >
                        <?php
                        echo html_escape(
                            $product->product_code . ' - ' .
                            $product->product_name .
                            ' (Current stock: ' . (int) $product->stock . ')'
                        );
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Search by product code or name, then select the physically counted item.</div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-4">
            <label for="actual_stock" class="form-label">Actual Stock</label>
            <input
                type="number"
                id="actual_stock"
                name="actual_stock"
                class="form-control"
                min="0"
                max="2147483647"
                step="1"
                required
                value="<?php echo html_escape(set_value('actual_stock')); ?>"
            >
            <div class="form-text">Enter the verified physical quantity, not the difference.</div>
        </div>

        <div class="col-12 col-md-8">
            <label for="reason" class="form-label">Reason</label>
            <input
                type="text"
                id="reason"
                name="reason"
                class="form-control"
                maxlength="255"
                required
                value="<?php echo html_escape(set_value('reason')); ?>"
            >
            <div class="form-text">State why the system quantity needs correction so the adjustment can be audited later.</div>
        </div>
    </div>

    <div class="mt-3">
        <?php
        $this->load->view('components/form/assist_note', array(
            'assist_title' => 'Use only for verified corrections',
            'assist_text' => 'A stock adjustment directly changes recorded inventory. Normal deliveries and releases should use Stock In or Stock Out instead.',
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
    'submit_icon' => 'bi-check-lg'
));
?>
<?php echo form_close(); ?>
