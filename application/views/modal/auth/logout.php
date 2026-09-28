<?php echo form_open('logout', array('data-modal-form' => '1')); ?>
<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Log out?',
    'modal_icon' => 'bi-box-arrow-right',
    'modal_variant' => 'warning'
));
?>

<div class="app-modal-body">
    <p class="mb-0">You will need to sign in again to continue.</p>
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
