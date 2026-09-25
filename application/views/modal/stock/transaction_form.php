
<?php
$stock_confirmation = $transaction_type === 'stock_in'
    ? array(
        'title' => 'Confirm Stock In?',
        'message' => 'Review the supplier and entered quantities before adding stock.',
        'impact' => 'This transaction increases inventory quantities and becomes part of permanent stock history.',
        'assist' => 'Make sure the supplier is correct and only the intended products have quantities greater than zero.',
        'label' => 'Confirm Stock In',
        'variant' => 'primary',
        'icon' => 'bi-box-arrow-in-down'
    )
    : array(
        'title' => 'Confirm Stock Out?',
        'message' => 'Review the supplier and entered quantities before removing stock.',
        'impact' => 'This transaction reduces inventory and becomes part of permanent stock history.',
        'assist' => 'Make sure the supplier is correct and only the intended products have quantities greater than zero.',
        'label' => 'Confirm Stock Out',
        'variant' => 'warning',
        'icon' => 'bi-box-arrow-up'
    );

echo form_open(current_url(), ui_modal_form_attributes($stock_confirmation));
?>

<?php
$this->load->view('components/modal/header', array(
    'modal_title' => $page_title,
    'modal_subtitle' => $transaction_type === 'stock_in'
        ? 'Select one supplier, then enter quantities for any of that supplier\'s products.'
        : 'Select one supplier, then enter quantities for products leaving that supplier\'s stock.',
    'modal_icon' => $transaction_type === 'stock_in'
        ? 'bi-box-arrow-in-down'
        : 'bi-box-arrow-up'
));
?>

<div class="modal-body">

    <?php if (validation_errors()): ?>
        <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
    <?php endif; ?>

    <?php if (!empty($item_error)): ?>
        <div class="alert alert-danger"><?php echo html_escape($item_error); ?></div>
    <?php endif; ?>


    <!-- Supplier selection for BOTH Stock In and Stock Out -->
    <?php $selected_supplier = set_value('supplier_id'); ?>

    <div class="mb-4">
        <label for="stock_supplier_id" class="form-label">Supplier</label>

        <select
            id="stock_supplier_id"
            name="supplier_id"
            class="form-select"
            required
            data-stock-supplier
            data-searchable-select
            data-search-placeholder="Search supplier by name..."
            data-stock-mode="<?php echo html_escape($transaction_type); ?>"
            data-products-url="<?php echo site_url('stock/supplier-products'); ?>"
        >
            <option value="">Select Supplier</option>

            <?php foreach ($suppliers as $supplier): ?>
                <?php if ((int) $supplier->status === 1): ?>
                    <option
                        value="<?php echo (int) $supplier->id; ?>"
                        <?php echo ((string) $selected_supplier === (string) $supplier->id) ? 'selected' : ''; ?>
                    >
                        <?php echo html_escape($supplier->supplier_name); ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <div class="form-text">
            Search and select one supplier. All active products assigned to that supplier will be loaded below.
        </div>
    </div>


    <!-- Products -->
    <div class="app-stock-products-heading mb-3">
        <div class="app-stock-products-heading-copy">
            <h3 class="h6 mb-1">
                <?php echo $transaction_type === 'stock_in'
                    ? 'Supplier Products'
                    : 'Products to Release'; ?>
            </h3>

            <p class="small text-body-secondary mb-0">
                <?php echo $transaction_type === 'stock_in'
                    ? 'All active supplier products are shown. Low-stock items appear first; enter quantities only for items being received.'
                    : 'All active supplier products are shown. Enter quantities only for items being released; stock cannot go below zero.'; ?>
            </p>
        </div>

        <div class="app-stock-product-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input
                type="search"
                class="form-control form-control-sm"
                placeholder="Search product code, name, or unit..."
                data-stock-product-filter
                autocomplete="off"
                aria-label="Search supplier products"
            >
        </div>
    </div>


    <div data-stock-in-products>

        <?php if ($selected_supplier !== ''): ?>

            <?php
            $this->load->view('components/stock/product_quantity_list', array(
                'products' => $supplier_products,
                'quantities' => $transaction_quantities,
                'mode' => $transaction_type
            ));
            ?>

        <?php else: ?>

            <div class="app-stock-product-empty">
                <i class="bi bi-truck d-block fs-3 mb-2"></i>

                Select a supplier to load its products.
            </div>

        <?php endif; ?>

    </div>


    <!-- Remarks -->
    <div class="mt-3">
        <label for="remarks" class="form-label">Remarks</label>

        <input
            type="text"
            id="remarks"
            name="remarks"
            class="form-control"
            maxlength="255"
            value="<?php echo html_escape(set_value('remarks')); ?>"
        >

        <div class="form-text">
            Add a short business reason or reference when it helps explain the movement later.
        </div>
    </div>


    <!-- Assist note -->
    <div class="mt-3">
        <?php
        $this->load->view('components/form/assist_note', array(
            'assist_title' => 'Inventory transaction',
            'assist_text' => $transaction_type === 'stock_in'
                ? 'Confirm the supplier and quantities carefully. Saving increases stock and creates a permanent transaction record.'
                : 'Confirm the supplier and quantities carefully. Saving reduces stock and creates a permanent transaction record.',
            'assist_variant' => $transaction_type === 'stock_in'
                ? 'info'
                : 'warning',
            'assist_icon' => $transaction_type === 'stock_in'
                ? 'bi-info-circle'
                : 'bi-exclamation-triangle'
        ));
        ?>
    </div>

</div>


<?php
$this->load->view('components/modal/footer', array(
    'close_label' => 'Cancel',
    'submit_label' => $transaction_type === 'stock_in'
        ? 'Review Stock In'
        : 'Review Stock Out',
    'submit_icon' => 'bi-check-lg'
));
?>

<?php echo form_close(); ?>
