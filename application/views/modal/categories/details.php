<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Category Details',
    'modal_subtitle' => 'Category identity, status, and product usage.',
    'modal_icon' => 'bi-tags'
));
?>

<div class="modal-body">
    <dl class="row app-detail-list mb-0">
        <dt class="col-sm-4">ID</dt><dd class="col-sm-8"><?php echo (int) $category->id; ?></dd>
        <dt class="col-sm-4">Name</dt><dd class="col-sm-8"><?php echo html_escape($category->category_name); ?></dd>
        <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><span class="badge <?php echo $category->status ? 'text-bg-success' : 'text-bg-secondary'; ?>"><?php echo $category->status ? 'Active' : 'Inactive'; ?></span></dd>
        <dt class="col-sm-4">Products</dt><dd class="col-sm-8"><?php echo (int) $product_count; ?></dd>
    </dl>
</div>

<?php $this->load->view('components/modal/footer', array('close_label' => 'Close')); ?>