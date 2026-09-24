<?php
$delete_title = isset($delete_title) ? $delete_title : 'Delete';
$delete_icon = isset($delete_icon) ? $delete_icon : 'bi-trash';
$delete_name = isset($delete_name) ? $delete_name : '';
$delete_message = isset($delete_message) ? $delete_message : 'Are you sure you want to delete this record?';
$delete_submit_label = isset($delete_submit_label) ? $delete_submit_label : 'Delete';

$this->load->view('components/modal/header', array(
    'modal_title' => $delete_title,
    'modal_subtitle' => $delete_name !== '' ? $delete_name : '',
    'modal_icon' => $delete_icon
));
?>
<div class="modal-body">
    <?php if (!empty($delete_error)): ?>
        <div class="alert alert-warning mb-0" role="alert">
            <?php echo html_escape($delete_error); ?>
        </div>
    <?php else: ?>
        <div class="alert alert-danger mb-0" role="alert">
            <?php echo html_escape($delete_message); ?>
        </div>
    <?php endif; ?>
</div>

<?php if (empty($delete_error)): ?>
    <?php echo form_open(current_url(), array('data-modal-form' => '1')); ?>
        <?php
        $this->load->view('components/modal/footer', array(
            'close_label' => 'Cancel',
            'submit_label' => $delete_submit_label,
            'submit_variant' => 'danger',
            'submit_icon' => 'bi-trash'
        ));
        ?>
    <?php echo form_close(); ?>
<?php else: ?>
    <?php
    $this->load->view('components/modal/footer', array(
        'close_label' => 'Close',
        'show_submit' => FALSE
    ));
    ?>
<?php endif; ?>
