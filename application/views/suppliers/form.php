<h2><?php echo isset($supplier) ? 'Edit Supplier' : 'Add Supplier'; ?></h2>

<?php echo form_open(current_url()); ?>
    <div class="form-group">
        <label>Supplier Name</label>
        <input type="text" name="supplier_name" value="<?php echo isset($supplier) ? $supplier->supplier_name : ''; ?>" required>
    </div>

    <div class="form-group">
        <label>Contact Person</label>
        <input type="text" name="contact_person" value="<?php echo isset($supplier) ? $supplier->contact_person : ''; ?>">
    </div>

    <div class="form-group">
        <label>Phone</label>
        <input type="text" name="phone" value="<?php echo isset($supplier) ? $supplier->phone : ''; ?>">
    </div>

    <div class="form-group">
        <label>Address</label>
        <textarea name="address" rows="4"><?php echo isset($supplier) ? $supplier->address : ''; ?></textarea>
    </div>

    <div class="form-actions">
        <button class="btn btn-success" type="submit">Save</button>
        <a class="btn" href="<?php echo site_url('suppliers'); ?>">Cancel</a>
    </div>
<?php echo form_close(); ?>
