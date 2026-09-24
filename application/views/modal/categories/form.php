<?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
<?php
$this->load->view('components/modal/header', array(
    'modal_title' => $page_title,
    'modal_subtitle' => 'Set the category name and availability.',
    'modal_icon' => 'bi-tags'
));
?>
<div class="modal-body">
    <?php $this->load->view('components/modal/messages'); ?>

    <div class="mb-3">
        <label for="category_name" class="form-label">Category Name</label>
        <input type="text" id="category_name" name="category_name" class="form-control" required maxlength="100" value="<?php echo html_escape(set_value('category_name', isset($category) && $category ? $category->category_name : '')); ?>">
    </div>

    <div>
        <label for="status" class="form-label">Status</label>
        <?php $selected_status = set_value('status', isset($category) && $category ? $category->status : 1); ?>
        <select id="status" name="status" class="form-select">
            <option value="1" <?php echo ((string) $selected_status === '1') ? 'selected' : ''; ?>>Active</option>
            <option value="0" <?php echo ((string) $selected_status === '0') ? 'selected' : ''; ?>>Inactive</option>
        </select>
    </div>
</div>
<?php
$this->load->view('components/modal/footer', array(
    'submit_label' => 'Save Category',
    'submit_icon' => 'bi-check-lg'
));
?>
<?php echo form_close(); ?>
