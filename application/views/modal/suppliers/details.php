<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Supplier Details',
    'modal_subtitle' => 'Supplier contact information and availability status.',
    'modal_icon' => 'bi-truck'
));
?>

<div class="modal-body">
    <dl class="row app-detail-list mb-0">
        <dt class="col-sm-4">Name</dt><dd class="col-sm-8"><?php echo html_escape($supplier->supplier_name); ?></dd>
        <dt class="col-sm-4">Contact Person</dt><dd class="col-sm-8"><?php echo html_escape($supplier->contact_person ?: 'N/A'); ?></dd>
        <dt class="col-sm-4">Phone</dt><dd class="col-sm-8"><?php echo html_escape($supplier->phone ?: 'N/A'); ?></dd>
        <dt class="col-sm-4">Address</dt><dd class="col-sm-8"><?php echo html_escape($supplier->address ?: 'N/A'); ?></dd>
        <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><span class="badge <?php echo $supplier->status ? 'text-bg-success' : 'text-bg-secondary'; ?>"><?php echo $supplier->status ? 'Active' : 'Inactive'; ?></span></dd>
    </dl>
</div>

<?php $this->load->view('components/modal/footer', array('close_label' => 'Close')); ?>