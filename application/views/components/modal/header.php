<?php
$modal_title = isset($modal_title) ? $modal_title : '';
$modal_subtitle = isset($modal_subtitle) ? $modal_subtitle : '';
$modal_icon = isset($modal_icon) ? $modal_icon : '';
$modal_variant = isset($modal_variant)
    ? preg_replace('/[^a-z0-9-]/i', '', (string) $modal_variant)
    : 'primary';
$modal_eyebrow = isset($modal_eyebrow) ? trim((string) $modal_eyebrow) : '';
?>
<div class="modal-header app-modal-header app-modal-header-<?php echo html_escape($modal_variant); ?>">
    <div class="app-modal-heading">
        <?php if ($modal_icon !== ''): ?>
            <span class="app-modal-icon app-modal-icon-<?php echo html_escape($modal_variant); ?>" aria-hidden="true">
                <i class="bi <?php echo html_escape($modal_icon); ?>"></i>
            </span>
        <?php endif; ?>

        <div class="app-modal-heading-copy">
            <?php if ($modal_eyebrow !== ''): ?>
                <div class="app-modal-eyebrow"><?php echo html_escape($modal_eyebrow); ?></div>
            <?php endif; ?>

            <h2 class="modal-title app-modal-title" id="action-modal-title"><?php echo html_escape($modal_title); ?></h2>

            <?php if ($modal_subtitle !== ''): ?>
                <p class="app-modal-subtitle"><?php echo html_escape($modal_subtitle); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <button
        type="button"
        class="btn-close app-modal-close"
        data-modal-close
        aria-label="Close"
        title="Close"
    ></button>
</div>
