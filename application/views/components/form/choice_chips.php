<?php
$chip_name = isset($chip_name) ? $chip_name : 'choices[]';
$chip_items = isset($chip_items) && is_array($chip_items) ? $chip_items : array();
$chip_selected = isset($chip_selected) && is_array($chip_selected) ? $chip_selected : array();
$chip_disabled = !empty($chip_disabled);
$chip_id_prefix = isset($chip_id_prefix) ? $chip_id_prefix : 'choice';
?>
<div class="app-choice-chips">
    <?php foreach ($chip_items as $chip): ?>
        <?php
        $value = isset($chip['value']) ? $chip['value'] : '';
        $label = isset($chip['label']) ? $chip['label'] : $value;
        $code = isset($chip['code']) ? $chip['code'] : '';
        $description = isset($chip['description']) ? $chip['description'] : '';
        $id = $chip_id_prefix . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) $value);
        $checked = in_array((string) $value, array_map('strval', $chip_selected), TRUE);
        ?>
        <input
            class="app-choice-input visually-hidden"
            type="checkbox"
            id="<?php echo html_escape($id); ?>"
            name="<?php echo html_escape($chip_name); ?>"
            value="<?php echo html_escape($value); ?>"
            <?php echo $checked ? 'checked' : ''; ?>
            <?php echo $chip_disabled ? 'disabled' : ''; ?>
        >
        <label class="app-choice-chip" for="<?php echo html_escape($id); ?>">
            <span class="app-choice-chip-label"><?php echo html_escape($label); ?></span>
            <?php if ($code !== ''): ?>
                <code class="app-choice-chip-code"><?php echo html_escape($code); ?></code>
            <?php endif; ?>
            <?php if ($description !== ''): ?>
                <span class="app-choice-chip-description"><?php echo html_escape($description); ?></span>
            <?php endif; ?>
        </label>
    <?php endforeach; ?>
</div>
