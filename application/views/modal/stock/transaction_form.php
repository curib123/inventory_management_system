<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
<?php
$this->load->view('components/modal/header', array(
    'modal_title' => $page_title,
    'modal_subtitle' => $transaction_type === 'stock_in'
        ? 'Select one supplier, then enter quantities for any of that supplier\'s products.'
        : 'Record inventory leaving stock.',
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

    <?php if ($transaction_type === 'stock_in'): ?>
        <?php $selected_supplier = set_value('supplier_id'); ?>

        <div class="mb-4">
            <label for="stock_in_supplier_id" class="form-label">Supplier</label>
            <select
                id="stock_in_supplier_id"
                name="supplier_id"
                class="form-select"
                required
                data-stock-in-supplier
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
                One Stock In transaction belongs to one supplier. Only products assigned to that supplier are shown.
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <div>
                <h3 class="h6 mb-1">Supplier Products</h3>
                <p class="small text-body-secondary mb-0">Low-stock products appear first.</p>
            </div>
        </div>

        <div data-stock-in-products>
            <?php if ($selected_supplier !== ''): ?>
                <?php
                $this->load->view('components/stock/product_quantity_list', array(
                    'products' => $stock_in_products,
                    'quantities' => $stock_in_quantities
                ));
                ?>
            <?php else: ?>
                <div class="app-stock-product-empty">
                    <i class="bi bi-truck d-block fs-3 mb-2"></i>
                    Select a supplier to load its products.
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <h3 class="h6 mb-3">Items</h3>

        <div class="vstack gap-3">
            <?php for ($row = 0; $row < 5; $row++): ?>
                <div class="border rounded-3 p-3">
                    <div class="fw-semibold mb-2">Item <?php echo $row + 1; ?></div>
                    <div class="row g-2">
                        <div class="col-12 col-md-8">
                            <label for="product_id_<?php echo $row; ?>" class="form-label">Product</label>
                            <select id="product_id_<?php echo $row; ?>" name="product_id[]" class="form-select">
                                <option value="">Select Product</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo (int) $product->id; ?>">
                                        <?php echo html_escape($product->product_code . ' - ' . $product->product_name); ?>
                                        - Stock: <?php echo (int) $product->stock; ?>
                                        <?php echo html_escape($product->unit ?: ''); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="quantity_<?php echo $row; ?>" class="form-label">Quantity</label>
                            <input
                                type="number"
                                id="quantity_<?php echo $row; ?>"
                                name="quantity[]"
                                class="form-control"
                                min="1"
                                step="1"
                            >
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

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
    </div>
</div>

<?php
$this->load->view('components/modal/footer', array(
    'close_label' => 'Cancel',
    'submit_label' => 'Save Transaction',
    'submit_icon' => 'bi-check-lg'
));
?>
<?php echo form_close(); ?>