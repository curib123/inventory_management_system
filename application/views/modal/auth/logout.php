<?php echo form_open('logout', array('data-modal-form' => '1')); ?>
<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Confirm Logout',
    'modal_subtitle' => 'End the current signed-in session.',
    'modal_icon' => 'bi-box-arrow-right'
));
?>

<div class="modal-body">
    <p class="mb-0">Are you sure you want to log out?</p>
</div>

<?php
$this->load->view('components/modal/footer', array(
    'close_label' => 'Cancel',
    'submit_label' => 'Logout',
    'submit_class' => 'btn-danger',
    'submit_icon' => 'bi-box-arrow-right'
));
?>
<?php echo form_close(); ?>