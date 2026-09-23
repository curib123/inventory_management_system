<dialog id="application-alert">
    <h3><?php echo html_escape($alert_title); ?></h3>
    <p><?php echo html_escape($alert_message); ?></p>
    <button type="button" onclick="this.closest('dialog').close();">Close</button>
</dialog>
<script>
    document.getElementById('application-alert').showModal();
</script>
