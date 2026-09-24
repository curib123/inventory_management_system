
<div class="container-fluid rounded shadow-sm p-4 bg-light">
   <h2 class="text-dark "><?php echo html_escape($alert_title); ?></h2>
    <p class="text-muted p-2"><?php echo html_escape($alert_message); ?></p>
    <button type="button" onclick="this.closest('dialog').close();">Close</button>
</div>
<script>
    document.getElementById('application-alert').showModal();
</script>
