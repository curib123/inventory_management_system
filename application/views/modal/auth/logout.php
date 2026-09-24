<?php echo form_open('logout', array('data-modal-form' => '1')); ?>
<?php
$this->load->view('components/modal/header', array(
    'modal_title' => 'Confirm Logout',
    'modal_subtitle' => 'End the current signed-in session.',
    'modal_icon' => 'bi-box-arrow-right'
));
?>

<div class="modal-body">
    <?php
    $this->load->view('components/modal/confirmation', array(
        'confirmation_variant' => 'warning',
        'confirmation_icon' => 'bi-box-arrow-right',
        'confirmation_title' => 'Log out now?',
        'confirmation_message' => 'Your current session will end and you will need to sign in again to continue.',
        'confirmation_items' => array(
            'Make sure any unfinished form work has already been saved.'
        )
    ));
    ?>
</div>

<?php
$this->load->view('components/modal/footer', array(
    'close_label' => 'Stay Signed In',
    'submit_label' => 'Logout',
    'submit_class' => 'btn-danger',
    'submit_icon' => 'bi-box-arrow-right'
));
?>
<?php echo form_close(); ?>
