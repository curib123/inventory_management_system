<dialog id="category-form-<?php echo $modal_id; ?>">
    <?php $this->load->view('categories/form', array(
        'category' => isset($category) ? $category : NULL,
        'form_action' => $form_action
    )); ?>
    <button type="button" onclick="this.closest('dialog').close();">Close</button>
</dialog>
