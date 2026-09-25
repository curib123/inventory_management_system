        </main>

<?php if ($this->session->userdata('user_id')): ?>
    </div>
</div>

<?php $this->load->view('modal/container'); ?>

<?php if (
    $this->session->userdata('must_change_password') &&
    !$this->session->userdata('password_change_deferred')
): ?>
    <div
        class="d-none"
        data-password-change-prompt-url="<?php echo html_escape(site_url('account/change-password')); ?>"
        aria-hidden="true"
    ></div>
<?php endif; ?>
<?php endif; ?>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous">
</script>

<script src="https://cdn.datatables.net/v/bs5/dt-3.1.1/datatables.min.js"></script>
<script src="<?php echo base_url('assets/js/app.js'); ?>"></script>

</body>
</html>
