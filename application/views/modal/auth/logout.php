<dialog id="logout-confirmation">
    <h3>Confirm Logout</h3>
    <p>Are you sure you want to log out?</p>
    <a href="<?php echo site_url('logout'); ?>">Logout</a>
    <button type="button" onclick="this.closest('dialog').close();">Cancel</button>
</dialog>
