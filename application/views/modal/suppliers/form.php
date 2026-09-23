<dialog id="supplier-form-<?php echo $modal_id; ?>">
    <?php $this->load->view('suppliers/form', array(
        'supplier' => isset($supplier) ? $supplier : NULL,
        'form_action' => $form_action
    )); ?>
    <button type="button" onclick="this.closest('dialog').close();">Close</button>
</dialog>
