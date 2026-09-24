<?php
$close_label = isset($close_label) ? $close_label : 'Cancel';
$close_class = isset($close_class) ? $close_class : 'btn-outline-secondary';
$submit_label = isset($submit_label) ? $submit_label : '';
$submit_class = isset($submit_class) ? $submit_class : 'btn-primary';
$submit_icon = isset($submit_icon) ? $submit_icon : '';
?>
<div class="modal-footer">
    <button type="button" class="btn <?php echo html_escape($close_class); ?>" data-modal-close>
        <?php echo html_escape($close_label); ?>
    </button>

    <?php if ($submit_label !== ''): ?>
        <button type="submit" class="btn <?php echo html_escape($submit_class); ?>">
            <?php if ($submit_icon !== ''): ?><i class="bi <?php echo html_escape($submit_icon); ?> me-1"></i><?php endif; ?>
            <?php echo html_escape($submit_label); ?>
        </button>
    <?php endif; ?>
</div>
