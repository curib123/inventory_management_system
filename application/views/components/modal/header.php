<?php
$modal_title = isset($modal_title) ? $modal_title : '';
$modal_subtitle = isset($modal_subtitle) ? $modal_subtitle : '';
$modal_icon = isset($modal_icon) ? $modal_icon : '';
?>
<div class="modal-header">
    <div>
        <h2 class="modal-title fs-5 mb-0">
            <?php if ($modal_icon !== ''): ?><i class="bi <?php echo html_escape($modal_icon); ?> me-2"></i><?php endif; ?>
            <?php echo html_escape($modal_title); ?>
        </h2>
        <?php if ($modal_subtitle !== ''): ?>
            <p class="app-modal-subtitle"><?php echo html_escape($modal_subtitle); ?></p>
        <?php endif; ?>
    </div>
    <button type="button" class="btn-close" data-modal-close aria-label="Close"></button>
</div>
