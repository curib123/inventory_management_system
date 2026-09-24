<?php
$close_label = isset($close_label) ? $close_label : 'Cancel';
$show_submit = isset($show_submit) ? (bool) $show_submit : TRUE;
$submit_label = isset($submit_label) ? $submit_label : 'Save';
$submit_variant = isset($submit_variant) ? $submit_variant : 'primary';
$submit_icon = isset($submit_icon) ? $submit_icon : 'bi-check-lg';
?>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-modal-close><?php echo html_escape($close_label); ?></button>
    <?php if ($show_submit): ?>
        <button type="submit" class="btn btn-<?php echo html_escape($submit_variant); ?>">
            <?php if ($submit_icon !== ''): ?><i class="bi <?php echo html_escape($submit_icon); ?> me-1"></i><?php endif; ?>
            <?php echo html_escape($submit_label); ?>
        </button>
    <?php endif; ?>
</div>
