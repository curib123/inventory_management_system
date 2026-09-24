<?php
$title = isset($title) ? $title : '';
$subtitle = isset($subtitle) ? $subtitle : '';
$actions = isset($actions) && is_array($actions) ? $actions : array();
$columns = isset($columns) && is_array($columns) ? $columns : array();
$data_source = isset($data_source) ? $data_source : '';
?>

<div class="app-page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h2 class="app-page-title h4"><?php echo html_escape($title); ?></h2>
        <?php if ($subtitle !== ''): ?>
            <p class="app-page-subtitle"><?php echo html_escape($subtitle); ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($actions)): ?>
        <div class="app-page-actions">
            <?php foreach ($actions as $action): ?>
                <?php
                $label = isset($action['label']) ? $action['label'] : 'Action';
                $url = isset($action['url']) ? $action['url'] : '#';
                $icon = isset($action['icon']) ? $action['icon'] : '';
                $variant = isset($action['variant']) ? $action['variant'] : 'outline-secondary';
                $mode = isset($action['mode']) ? $action['mode'] : 'link';
                ?>
                <?php if ($mode === 'modal'): ?>
                    <button type="button" class="btn btn-<?php echo html_escape($variant); ?>" data-modal-url="<?php echo html_escape($url); ?>">
                        <?php if ($icon !== ''): ?><i class="bi <?php echo html_escape($icon); ?> me-1"></i><?php endif; ?>
                        <?php echo html_escape($label); ?>
                    </button>
                <?php else: ?>
                    <a class="btn btn-<?php echo html_escape($variant); ?>" href="<?php echo html_escape($url); ?>">
                        <?php if ($icon !== ''): ?><i class="bi <?php echo html_escape($icon); ?> me-1"></i><?php endif; ?>
                        <?php echo html_escape($label); ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="app-table-card card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="app-table table table-striped table-hover align-middle" data-datatable-server data-source="<?php echo html_escape($data_source); ?>">
                <thead class="table-light">
                    <tr>
                        <?php foreach ($columns as $column): ?>
                            <?php
                            $label = is_array($column) ? $column['label'] : $column;
                            $orderable = is_array($column) && array_key_exists('orderable', $column)
                                ? (bool) $column['orderable']
                                : TRUE;
                            ?>
                            <th<?php echo $orderable ? '' : ' data-orderable="false"'; ?>>
                                <?php echo html_escape($label); ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
