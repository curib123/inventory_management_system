<?php
$close_label = isset($close_label) ? $close_label : 'Cancel';
$close_class = isset($close_class) ? $close_class : 'btn-outline-secondary';
$close_icon = isset($close_icon) ? $close_icon : '';
$submit_label = isset($submit_label) ? $submit_label : '';
$submit_class = isset($submit_class) ? $submit_class : 'btn-primary';
$submit_icon = isset($submit_icon) ? $submit_icon : '';
$footer_note = isset($footer_note) ? trim((string) $footer_note) : '';
?>
<div class="modal-footer app-modal-footer">
    <?php if ($footer_note !== ''): ?>
        <div class="app-modal-footer-note">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            <span><?php echo html_escape($footer_note); ?></span>
        </div>
    <?php endif; ?>

    <div class="app-modal-footer-actions">
        <button type="button" class="btn <?php echo html_escape($close_class); ?>" data-modal-close>
            <?php if ($close_icon !== ''): ?>
                <i class="bi <?php echo html_escape($close_icon); ?> me-1"></i>
            <?php endif; ?>
            <?php echo html_escape($close_label); ?>
        </button>

        <?php if ($submit_label !== ''): ?>
            <button type="submit" class="btn <?php echo html_escape($submit_class); ?>">
                <?php if ($submit_icon !== ''): ?>
                    <i class="bi <?php echo html_escape($submit_icon); ?> me-1"></i>
                <?php endif; ?>
                <?php echo html_escape($submit_label); ?>
            </button>
        <?php endif; ?>
    </div>
</div>
