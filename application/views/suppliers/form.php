<h2><?php echo isset($supplier) ? 'Edit Supplier' : 'Add Supplier'; ?></h2>

<?php echo validation_errors(); ?>

<?php echo form_open(current_url()); ?>
    <div class="form-group">
        <label for="supplier_name">Supplier Name</label>
        <input type="text" id="supplier_name" name="supplier_name" autocomplete="off" value="<?php echo html_escape(isset($supplier) ? $supplier->supplier_name : ''); ?>" required maxlength="150">
    </div>

    <div class="form-group">
        <label for="contact_person">Contact Person</label>
        <input type="text" id="contact_person" name="contact_person" autocomplete="off" value="<?php echo html_escape(isset($supplier) ? $supplier->contact_person : ''); ?>" maxlength="100">
    </div>

    <div class="form-group">
        <label for="phone">Phone</label>
        <input type="tel" id="phone" name="phone" autocomplete="off" minlength="11" maxlength="11" value="<?php echo html_escape(isset($supplier) ? $supplier->phone : ''); ?>">
    </div>

    <div class="form-group">
        <label for="address">Address</label>
        <textarea id="address" name="address" autocomplete="off" rows="4"><?php echo html_escape(isset($supplier) ? $supplier->address : ''); ?></textarea>
    </div>

    <div class="form-actions">
        <button class="btn btn-success" type="submit">Save</button>
        <a class="btn" href="<?php echo site_url('suppliers'); ?>">Cancel</a>
    </div>
<?php echo form_close(); ?>
