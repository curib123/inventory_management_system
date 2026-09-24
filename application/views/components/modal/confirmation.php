<?php
$confirmation_variant = isset($confirmation_variant)
    ? preg_replace('/[^a-z0-9-]/i', '', (string) $confirmation_variant)
    : 'warning';
$confirmation_icon = isset($confirmation_icon)
    ? preg_replace('/[^a-z0-9-]/i', '', (string) $confirmation_icon)
    : 'bi-exclamation-triangle';
$confirmation_title = isset($confirmation_title) ? (string) $confirmation_title : 'Confirm this action';
$confirmation_message = isset($confirmation_message) ? (string) $confirmation_message : '';
$confirmation_items = isset($confirmation_items) && is_array($confirmation_items)
    ? $confirmation_items
    : array();
?>
<div class="app-confirmation-card app-confirmation-card-<?php echo html_escape($confirmation_variant); ?>">
    <div class="app-confirmation-card-icon" aria-hidden="true">
        <i class="bi <?php echo html_escape($confirmation_icon); ?>"></i>
    </div>
    <div class="min-w-0">
        <div class="app-confirmation-card-title"><?php echo html_escape($confirmation_title); ?></div>

        <?php if ($confirmation_message !== ''): ?>
            <p class="app-confirmation-card-message mb-0"><?php echo html_escape($confirmation_message); ?></p>
        <?php endif; ?>

        <?php if (!empty($confirmation_items)): ?>
            <ul class="app-confirmation-card-list">
                <?php foreach ($confirmation_items as $item): ?>
                    <li><?php echo html_escape((string) $item); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
