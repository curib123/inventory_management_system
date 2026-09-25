<?php
$alert_variant = isset($alert_variant)
    ? preg_replace('/[^a-z0-9-]/i', '', (string) $alert_variant)
    : 'info';
$alert_icon = isset($alert_icon)
    ? preg_replace('/[^a-z0-9-]/i', '', (string) $alert_icon)
    : ($alert_variant === 'danger' ? 'bi-exclamation-triangle' : 'bi-info-circle');

$this->load->view('components/modal/header', array(
    'modal_title' => $alert_title,
    'modal_subtitle' => 'System information requiring your attention.',
    'modal_icon' => $alert_icon,
    'modal_variant' => $alert_variant,
    'modal_eyebrow' => 'System message'
));
?>

<div class="modal-body">
    <div class="app-modal-message-card">
        <span class="app-modal-message-card-icon" aria-hidden="true">
            <i class="bi <?php echo html_escape($alert_icon); ?>"></i>
        </span>
        <div class="min-w-0">
            <div class="fw-semibold mb-1"><?php echo html_escape($alert_title); ?></div>
            <p class="mb-0"><?php echo html_escape($alert_message); ?></p>
        </div>
    </div>
</div>

<?php
$this->load->view('components/modal/footer', array(
    'close_label' => 'Close',
    'close_icon' => 'bi-x-lg'
));
?>
