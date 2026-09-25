<?php
$product_is_edit = isset($product) && $product;

$product_confirmation = $product_is_edit
    ? array(
        'title' => 'Save product changes?',
        'message' => 'Review the product information before updating this inventory record.',
        'impact' => 'Supplier, unit, pricing, reorder level, and status changes can affect stock workflows and reports.',
        'assist' => 'Confirm that the product identity, supplier, unit, prices, reorder level, and status are correct.',
        'label' => 'Save Changes',
        'variant' => 'primary',
        'icon' => 'bi-check2-circle'
    )
    : array();

echo form_open(
    current_url(),
    ui_modal_form_attributes($product_confirmation)
);
?>

<?php
$this->load->view('components/modal/header', array(
    'modal_title' => $page_title,
    'modal_subtitle' => 'Maintain product identity, pricing, supplier, and reorder settings.',
    'modal_icon' => 'bi-box-seam'
));
?>

<div class="modal-body">
    <?php if (validation_errors()): ?>
        <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
    <?php endif; ?>

    <?php if (!empty($form_error)): ?>
        <div class="alert alert-danger"><?php echo html_escape($form_error); ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label for="product_name" class="form-label">Product Name</label>
            <input type="text" id="product_name" name="product_name" class="form-control" required maxlength="150" value="<?php echo html_escape(set_value('product_name', $product_is_edit ? $product->product_name : '')); ?>" autocomplete="off">
        </div>

        <div class="col-12 col-md-6">
            <label for="product_code" class="form-label">Product Code</label>
            <input type="text" id="product_code" name="product_code" class="form-control" required maxlength="50" value="<?php echo html_escape(set_value('product_code', $product_is_edit ? $product->product_code : '')); ?>" autocomplete="off">
            <div class="form-text">Use a stable business code because this appears in stock history and reports.</div>
        </div>

        <div class="col-12 col-md-6">
            <label for="category_id" class="form-label">Category</label>
            <?php $selected_category = set_value('category_id', $product_is_edit ? $product->category_id : ''); ?>
            <select id="category_id" name="category_id" class="form-select" required >
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo (int) $category->id; ?>" <?php echo ((string) $selected_category === (string) $category->id) ? 'selected' : ''; ?> autocomplete="off">
                        <?php echo html_escape($category->category_name . ((int) $category->status === 1 ? '' : ' (Inactive)')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12 col-md-6">
            <label for="supplier_id" class="form-label">Supplier</label>
            <?php $selected_supplier = set_value('supplier_id', $product_is_edit ? $product->supplier_id : ''); ?>
            <select
                id="supplier_id"
                name="supplier_id"
                class="form-select"
                data-searchable-select
                data-search-placeholder="Search supplier by name..."
            >
                <option value="">No Supplier</option>
                <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?php echo (int) $supplier->id; ?>" <?php echo ((string) $selected_supplier === (string) $supplier->id) ? 'selected' : ''; ?> autocomplete="off">
                        <?php echo html_escape($supplier->supplier_name . ((int) $supplier->status === 1 ? '' : ' (Inactive)')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Stock In and Stock Out use this relationship to show the correct supplier products.</div>
        </div>

        <div class="col-12 col-md-4">
            <label for="unit" class="form-label">Unit</label>
            <?php $selected_unit = set_value('unit', $product_is_edit ? $product->unit : 'pcs'); ?>
            <select id="unit" name="unit" class="form-select" required>
                <option value="">Select Unit</option>
                <?php foreach ($product_units as $unit_value => $unit_label): ?>
                    <option value="<?php echo html_escape($unit_value); ?>" <?php echo ((string) $selected_unit === (string) $unit_value) ? 'selected' : ''; ?> autocomplete="off">
                        <?php echo html_escape($unit_label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Controls how inventory quantities are described, for example pcs, box, kg, or liter.</div>
        </div>

        <div class="col-12 col-md-4">
            <label for="cost_price" class="form-label">Cost Price</label>
            <input type="number" id="cost_price" name="cost_price" class="form-control" min="0" step="0.01" required value="<?php echo html_escape(set_value('cost_price', $product_is_edit ? $product->cost_price : '0.00')); ?>" autocomplete="off">
        </div>

        <div class="col-12 col-md-4">
            <label for="selling_price" class="form-label">Selling Price</label>
            <input type="number" id="selling_price" name="selling_price" class="form-control" min="0" step="0.01" required value="<?php echo html_escape(set_value('selling_price', $product_is_edit ? $product->selling_price : '0.00')); ?>" autocomplete="off">
        </div>

        <div class="col-12 col-md-6">
            <label for="reorder_level" class="form-label">Reorder Level</label>
            <input type="number" id="reorder_level" name="reorder_level" class="form-control" min="0" step="1" required value="<?php echo html_escape(set_value('reorder_level', $product_is_edit ? $product->reorder_level : '0')); ?>" autocomplete="off">
            <div class="form-text">Low-stock monitoring uses this value to identify products that need replenishment.</div>
        </div>

        <div class="col-12 col-md-6">
            <label for="status" class="form-label">Status</label>
            <?php $selected_status = set_value('status', $product_is_edit ? $product->status : 1); ?>
            <select id="status" name="status" class="form-select">
                <option value="1" <?php echo ((string) $selected_status === '1') ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo ((string) $selected_status === '0') ? 'selected' : ''; ?>>Inactive</option>
            </select>
            <div class="form-text">Inactive products remain in historical records but are not available for normal stock operations.</div>
        </div>
    </div>

    <?php if ($product_is_edit): ?>
        <div class="mt-3">
            <?php
            $this->load->view('components/form/assist_note', array(
                'assist_title' => 'Review before saving',
                'assist_text' => 'Changes are applied immediately after confirmation and may affect stock entry, low-stock monitoring, and reports.',
                'assist_variant' => 'info',
                'assist_icon' => 'bi-info-circle'
            ));
            ?>
        </div>
    <?php endif; ?>
</div>

<?php
$this->load->view('components/modal/footer', array(
    'close_label' => 'Cancel',
    'submit_label' => $product_is_edit ? 'Save Changes' : 'Create Product',
    'submit_icon' => 'bi-check-lg'
));
?>
<?php echo form_close(); ?>
