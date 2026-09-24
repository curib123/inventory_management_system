<?php
$title = isset($title) ? $title : '';
$description = isset($description) ? $description : '';
$actions = isset($actions) && is_array($actions) ? $actions : array();
?>
<div class="app-page-header d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="h4 mb-1"><?php echo html_escape($title); ?></h2>
        <?php if ($description !== ''): ?>
            <p class="text-body-secondary mb-0"><?php echo html_escape($description); ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($actions)): ?>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($actions as $action): ?>
                <?php
                $label = isset($action['label']) ? $action['label'] : 'Action';
                $icon = isset($action['icon']) ? $action['icon'] : '';
                $class = isset($action['class']) ? $action['class'] : 'btn-primary';
                $url = isset($action['url']) ? $action['url'] : '#';
                $modal_url = isset($action['modal_url']) ? $action['modal_url'] : '';
                ?>
                <?php if ($modal_url !== ''): ?>
                    <button type="button" class="btn <?php echo html_escape($class); ?>" data-modal-url="<?php echo html_escape($modal_url); ?>">
                        <?php if ($icon !== ''): ?><i class="bi <?php echo html_escape($icon); ?> me-1"></i><?php endif; ?>
                        <?php echo html_escape($label); ?>
                    </button>
                <?php else: ?>
                    <a class="btn <?php echo html_escape($class); ?>" href="<?php echo html_escape($url); ?>">
                        <?php if ($icon !== ''): ?><i class="bi <?php echo html_escape($icon); ?> me-1"></i><?php endif; ?>
                        <?php echo html_escape($label); ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
