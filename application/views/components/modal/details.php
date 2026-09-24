<?php
$detail_title = isset($detail_title) ? $detail_title : 'Details';
$detail_subtitle = isset($detail_subtitle) ? $detail_subtitle : '';
$detail_icon = isset($detail_icon) ? $detail_icon : 'bi-info-circle';
$detail_rows = isset($detail_rows) && is_array($detail_rows) ? $detail_rows : array();

$this->load->view('components/modal/header', array(
    'modal_title' => $detail_title,
    'modal_subtitle' => $detail_subtitle,
    'modal_icon' => $detail_icon
));
?>
<div class="modal-body">
    <dl class="app-detail-list row mb-0">
        <?php foreach ($detail_rows as $row): ?>
            <dt class="col-sm-4"><?php echo html_escape($row['label']); ?></dt>
            <dd class="col-sm-8"><?php echo html_escape((string) $row['value']); ?></dd>
        <?php endforeach; ?>
    </dl>
</div>
<?php
$this->load->view('components/modal/footer', array(
    'close_label' => 'Close',
    'show_submit' => FALSE
));
?>
