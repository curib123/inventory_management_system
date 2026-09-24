<?php
$modal_title = isset($modal_title) ? $modal_title : '';
$modal_subtitle = isset($modal_subtitle) ? $modal_subtitle : '';
$modal_icon = isset($modal_icon) ? $modal_icon : '';
?>
<div class="modal-header">
    <div class="d-flex align-items-center gap-3">
        <?php if ($modal_icon !== ''): ?>
            <span class="app-modal-icon">
                <i class="bi <?php echo html_escape($modal_icon); ?>"></i>
            </span>
        <?php endif; ?>
        <div>
            <h2 class="modal-title fs-5 mb-0"><?php echo html_escape($modal_title); ?></h2>
            <?php if ($modal_subtitle !== ''): ?>
                <p class="text-body-secondary small mb-0 mt-1"><?php echo html_escape($modal_subtitle); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <button type="button" class="btn-close" data-modal-close aria-label="Close"></button>
</div>
