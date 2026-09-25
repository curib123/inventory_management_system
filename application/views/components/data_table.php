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
                            $label = is_array($column) ? (isset($column['label']) ? $column['label'] : '') : $column;
                            $orderable = !is_array($column) || !isset($column['orderable']) || $column['orderable'] !== false;
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
