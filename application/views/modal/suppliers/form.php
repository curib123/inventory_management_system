<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
    <div class="modal-header">
        <h2 class="modal-title fs-5"><?php echo html_escape($page_title); ?></h2>
        <button type="button" class="btn-close" data-modal-close aria-label="Close"></button>
    </div>

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
                <input type="text" id="supplier_name" name="supplier_name" class="form-control" required maxlength="150" value="<?php echo html_escape(set_value('supplier_name', isset($supplier) && $supplier ? $supplier->supplier_name : '')); ?>">
            </div>

            <div class="col-12 col-md-6">
                <label for="contact_person" class="form-label">Contact Person</label>
                <input type="text" id="contact_person" name="contact_person" class="form-control" maxlength="100" value="<?php echo html_escape(set_value('contact_person', isset($supplier) && $supplier ? $supplier->contact_person : '')); ?>">
            </div>

            <div class="col-12 col-md-6">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" id="phone" name="phone" class="form-control" maxlength="30" value="<?php echo html_escape(set_value('phone', isset($supplier) && $supplier ? $supplier->phone : '')); ?>">
            </div>

            <div class="col-12 col-md-6">
                <label for="status" class="form-label">Status</label>
                <?php $selected_status = set_value('status', isset($supplier) && $supplier ? $supplier->status : 1); ?>
                <select id="status" name="status" class="form-select">
                    <option value="1" <?php echo ((string) $selected_status === '1') ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo ((string) $selected_status === '0') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="col-12">
                <label for="address" class="form-label">Address</label>
                <textarea id="address" name="address" class="form-control" rows="4"><?php echo html_escape(set_value('address', isset($supplier) && $supplier ? $supplier->address : '')); ?></textarea>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
        <button type="submit" class="btn btn-primary">Save Supplier</button>
    </div>
<?php echo form_close(); ?>
