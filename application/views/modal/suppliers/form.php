<?php
$supplier_is_edit = isset($supplier) && $supplier;
$supplier_confirmation = $supplier_is_edit
    ? array(
        'title' => 'Save supplier changes?',
        'message' => 'Review the supplier details before updating this record.',
        'impact' => 'Supplier changes can affect product assignments and Stock In workflows.',
        'assist' => 'Confirm the supplier identity, contact details, address, and status.',
        'label' => 'Save Changes',
        'variant' => 'primary',
        'icon' => 'bi-check2-circle'
    )
    : array();

echo form_open(current_url(), ui_modal_form_attributes($supplier_confirmation));
?>

<?php
$this->load->view('components/modal/header', array(
    'modal_title' => $page_title,
    'modal_subtitle' => 'Maintain supplier identity, contact information, and status.',
    'modal_icon' => 'bi-truck'
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
            <label for="supplier_name" class="form-label">Supplier Name</label>
            <input type="text" id="supplier_name" name="supplier_name" class="form-control" required maxlength="150" value="<?php echo html_escape(set_value('supplier_name', $supplier_is_edit ? $supplier->supplier_name : '')); ?>">
        </div>

        <div class="col-12 col-md-6">
            <label for="contact_person" class="form-label">Contact Person</label>
            <input type="text" id="contact_person" name="contact_person" class="form-control" maxlength="100" value="<?php echo html_escape(set_value('contact_person', $supplier_is_edit ? $supplier->contact_person : '')); ?>">
        </div>

        <div class="col-12 col-md-6">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" id="phone" name="phone" class="form-control" maxlength="13" value="<?php echo html_escape(set_value('phone', $supplier_is_edit ? $supplier->phone : '')); ?>">
        </div>

        <div class="col-12 col-md-6">
            <label for="status" class="form-label">Status</label>
            <?php $selected_status = set_value('status', $supplier_is_edit ? $supplier->status : 1); ?>
            <select id="status" name="status" class="form-select">
                <option value="1" <?php echo ((string) $selected_status === '1') ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo ((string) $selected_status === '0') ? 'selected' : ''; ?>>Inactive</option>
            </select>
            <div class="form-text">Inactive suppliers are excluded from new Stock In supplier selection.</div>
        </div>

        <div class="col-12">
            <label for="address" class="form-label">Address</label>
            <textarea id="address" name="address" class="form-control" rows="4"><?php echo html_escape(set_value('address', $supplier_is_edit ? $supplier->address : '')); ?></textarea>
        </div>
    </div>

    <?php if ($supplier_is_edit): ?>
        <div class="mt-3">
            <?php
            $this->load->view('components/form/assist_note', array(
                'assist_title' => 'Check linked inventory',
                'assist_text' => 'Changing supplier status or identity can affect which products appear in Stock In for this supplier.',
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
    'submit_label' => $supplier_is_edit ? 'Save Changes' : 'Create Supplier',
    'submit_icon' => 'bi-check-lg'
));
?>
<?php echo form_close(); ?>
