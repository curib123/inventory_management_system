<?php
$source = isset($source) ? $source : '';
$columns = isset($columns) && is_array($columns) ? $columns : array();
$table_id = isset($table_id) ? $table_id : '';
$card_class = isset($card_class) ? $card_class : '';
?>
<div class="card app-table-card shadow-sm border-0 <?php echo html_escape($card_class); ?>">
    <div class="card-body">
        <div class="table-responsive">
            <table
                <?php if ($table_id !== ''): ?>id="<?php echo html_escape($table_id); ?>"<?php endif; ?>
                class="table app-data-table table-hover align-middle mb-0"
                data-datatable-server
                data-source="<?php echo html_escape($source); ?>"
            >
                <thead>
                    <tr>
                        <?php foreach ($columns as $column): ?>
                            <?php
                            $label = is_array($column)
                                ? (isset($column['label']) ? $column['label'] : '')
                                : $column;
                            $orderable = !is_array($column) ||
                                !isset($column['orderable']) ||
                                $column['orderable'] !== false;
                            $visible = !is_array($column) ||
                                !isset($column['visible']) ||
                                $column['visible'] !== false;
                            $column_class = is_array($column) && !empty($column['class'])
                                ? preg_replace('/[^a-zA-Z0-9_\-\s]/', '', (string) $column['class'])
                                : '';

                            if (
                                $column_class === '' &&
                                preg_match('/^actions?$/i', trim((string) $label))
                            ) {
                                $column_class = 'text-end text-nowrap';
                            }
                            ?>
                            <th
                                <?php echo $orderable ? '' : 'data-orderable="false"'; ?>
                                <?php echo $visible ? '' : 'data-visible="false"'; ?>
                                <?php if ($column_class !== ''): ?>
                                    class="<?php echo html_escape($column_class); ?>"
                                    data-column-class="<?php echo html_escape($column_class); ?>"
                                <?php endif; ?>
                            >
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
