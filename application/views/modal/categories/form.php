<?php
$category_is_edit = isset($category) && $category;
$category_confirmation = $category_is_edit
    ? array(
        'title' => 'Save category changes?',
        'message' => 'Confirm the category name and status before updating it.',
        'impact' => 'Category changes appear anywhere linked products are grouped or reported.',
        'assist' => 'Check the category name and whether it should remain active.',
        'label' => 'Save Changes',
        'variant' => 'primary',
        'icon' => 'bi-check2-circle'
    )
    : array();

echo form_open(current_url(), ui_modal_form_attributes($category_confirmation));
?>

<?php
$this->load->view('components/modal/header', array(
    'modal_title' => $page_title,
    'modal_subtitle' => 'Maintain the category name and availability status.',
    'modal_icon' => 'bi-tags'
));
?>

<div class="modal-body">
    <?php if (validation_errors()): ?>
        <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
    <?php endif; ?>

    <?php if (!empty($form_error)): ?>
        <div class="alert alert-danger"><?php echo html_escape($form_error); ?></div>
    <?php endif; ?>

    <div class="mb-3">
        <label for="category_name" class="form-label">Category Name</label>
        <input type="text" id="category_name" name="category_name" class="form-control" required maxlength="100" value="<?php echo html_escape(set_value('category_name', $category_is_edit ? $category->category_name : '')); ?>">
        <div class="form-text">Use a clear business name that staff can recognize in product lists and reports.</div>
    </div>

    <div>
        <label for="status" class="form-label">Status</label>
        <?php $selected_status = set_value('status', $category_is_edit ? $category->status : 1); ?>
        <select id="status" name="status" class="form-select">
            <option value="1" <?php echo ((string) $selected_status === '1') ? 'selected' : ''; ?>>Active</option>
            <option value="0" <?php echo ((string) $selected_status === '0') ? 'selected' : ''; ?>>Inactive</option>
        </select>
        <div class="form-text">Inactive categories stay available for historical data but should not be used for new product assignments.</div>
    </div>

    <?php if ($category_is_edit): ?>
        <div class="mt-3">
            <?php
            $this->load->view('components/form/assist_note', array(
                'assist_title' => 'Before saving',
                'assist_text' => 'Confirm that linked products should continue using this category name and status.',
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
    'submit_label' => $category_is_edit ? 'Save Changes' : 'Create Category',
    'submit_icon' => 'bi-check-lg'
));
?>
<?php echo form_close(); ?>
