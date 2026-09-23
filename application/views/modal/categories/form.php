<dialog id="category-form-<?php echo $modal_id; ?>">
    <?php $this->load->view('categories/form', array(
        'category' => isset($category) ? $category : NULL,
        'form_action' => $form_action
    )); ?>
</dialog>
