<h2><?php echo html_escape($page_title); ?></h2>
<?php echo validation_errors(); ?>
<?php echo form_open(current_url()); ?>
    <p><label for="supplier_name">Supplier Name</label><br><input type="text" id="supplier_name" name="supplier_name" required maxlength="150" value="<?php echo html_escape(set_value('supplier_name', isset($supplier) ? $supplier->supplier_name : '')); ?>"></p>
    <p><label for="contact_person">Contact Person</label><br><input type="text" id="contact_person" name="contact_person" maxlength="100" value="<?php echo html_escape(set_value('contact_person', isset($supplier) ? $supplier->contact_person : '')); ?>"></p>
    <p><label for="phone">Phone</label><br><input type="text" id="phone" name="phone" maxlength="30" value="<?php echo html_escape(set_value('phone', isset($supplier) ? $supplier->phone : '')); ?>"></p>
    <p><label for="address">Address</label><br><textarea id="address" name="address" rows="4"><?php echo html_escape(set_value('address', isset($supplier) ? $supplier->address : '')); ?></textarea></p>
    <p>
        <label for="status">Status</label><br>
        <?php $selected_status = set_value('status', isset($supplier) ? $supplier->status : 1); ?>
        <select id="status" name="status">
            <option value="1" <?php echo ((string) $selected_status === '1') ? 'selected' : ''; ?>>Active</option>
            <option value="0" <?php echo ((string) $selected_status === '0') ? 'selected' : ''; ?>>Inactive</option>
        </select>
    </p>
    <button type="submit">Save Supplier</button>
    <a href="<?php echo site_url('suppliers'); ?>">Cancel</a>
<?php echo form_close(); ?>
