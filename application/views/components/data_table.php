<?php
$source = isset($source) ? $source : '';
$columns = isset($columns) && is_array($columns) ? $columns : array();
$table_id = isset($table_id) ? $table_id : '';
$card_class = isset($card_class) ? $card_class : '';
$filters = isset($filters) && is_array($filters) ? $filters : array();
$search_placeholder = isset($search_placeholder) && trim((string) $search_placeholder) !== ''
    ? trim((string) $search_placeholder)
    : 'Search records...';
?>
<div class="card app-table-card border-0 <?php echo html_escape($card_class); ?>">
    <div class="card-body p-0">
        <div class="app-table-toolbar">
            <div class="app-table-search-block">
                <label class="app-table-search-label" for="<?php echo html_escape($table_id); ?>-search">
                    Search
                </label>
                <div class="app-table-search-control">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input
                        id="<?php echo html_escape($table_id); ?>-search"
                        type="search"
                        class="form-control"
                        placeholder="<?php echo html_escape($search_placeholder); ?>"
                        autocomplete="off"
                        data-table-search
                        aria-label="<?php echo html_escape($search_placeholder); ?>"
                    >
                    <button
                        type="button"
                        class="btn app-table-search-clear d-none"
                        data-table-search-clear
                        aria-label="Clear search"
                        title="Clear search"
                    >
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <div class="app-table-filter-row">
                <div class="app-table-filter-group">
                    <?php foreach ($filters as $filter): ?>
                        <?php
                        $filter_name = isset($filter['name'])
                            ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $filter['name'])
                            : '';
                        $filter_label = isset($filter['label']) ? (string) $filter['label'] : 'Filter';
                        $filter_icon = isset($filter['icon'])
                            ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $filter['icon'])
                            : 'bi-funnel';
                        $filter_options = isset($filter['options']) && is_array($filter['options'])
                            ? $filter['options']
                            : array();
                        ?>
                        <?php if ($filter_name !== ''): ?>
                            <div class="app-table-filter">
                                <label for="<?php echo html_escape($table_id . '-filter-' . $filter_name); ?>">
                                    <i class="bi <?php echo html_escape($filter_icon); ?>" aria-hidden="true"></i>
                                    <?php echo html_escape($filter_label); ?>
                                </label>
                                <select
                                    id="<?php echo html_escape($table_id . '-filter-' . $filter_name); ?>"
                                    class="form-select form-select-sm"
                                    data-table-filter="<?php echo html_escape($filter_name); ?>"
                                >
                                    <?php foreach ($filter_options as $value => $label): ?>
                                        <option value="<?php echo html_escape((string) $value); ?>">
                                            <?php echo html_escape((string) $label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div class="app-table-toolbar-actions">
                    <span class="badge rounded-pill app-table-active-filter-badge d-none" data-table-filter-count>
                        0 active
                    </span>

                    <button type="button" class="btn btn-sm btn-outline-secondary d-none" data-table-reset>
                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive app-table-scroll">
            <table
                <?php if ($table_id !== ''): ?>id="<?php echo html_escape($table_id); ?>"<?php endif; ?>
                class="table app-data-table align-middle mb-0"
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
                            $render = is_array($column) && !empty($column['render'])
                                ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $column['render'])
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
                                <?php if ($render !== ''): ?>
                                    data-render="<?php echo html_escape($render); ?>"
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
