<?php
$this->load->view('components/modal/header', array(
    'modal_title' => $alert_title,
    'modal_icon' => 'bi-info-circle'
));
?>

<div class="modal-body">
    <p class="mb-0"><?php echo html_escape($alert_message); ?></p>
</div>

<?php $this->load->view('components/modal/footer', array('close_label' => 'Close')); ?>