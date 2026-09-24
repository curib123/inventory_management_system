<?php
$assist_variant = isset($assist_variant) ? preg_replace('/[^a-z0-9-]/i', '', (string) $assist_variant) : 'info';
$assist_icon = isset($assist_icon) ? preg_replace('/[^a-z0-9-]/i', '', (string) $assist_icon) : 'bi-info-circle';
$assist_title = isset($assist_title) ? (string) $assist_title : 'Before you continue';
$assist_text = isset($assist_text) ? (string) $assist_text : '';
?>
<div class="app-assist-note app-assist-note-<?php echo html_escape($assist_variant); ?>">
    <span class="app-assist-note-icon" aria-hidden="true">
        <i class="bi <?php echo html_escape($assist_icon); ?>"></i>
    </span>
    <div>
        <div class="app-assist-note-title"><?php echo html_escape($assist_title); ?></div>
        <?php if ($assist_text !== ''): ?>
            <div class="app-assist-note-text"><?php echo html_escape($assist_text); ?></div>
        <?php endif; ?>
    </div>
</div>
