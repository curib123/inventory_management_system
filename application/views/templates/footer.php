        </main>

<?php if ($this->session->userdata('user_id')): ?>
    </div>
</div>
<?php endif; ?>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous">
</script>

<script src="https://cdn.datatables.net/v/bs5/dt-3.1.1/datatables.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var sidebar = document.getElementById('sidebar');
        var sidebarToggle = document.getElementById('sidebar-toggle');
        var sidebarBackdrop = document.getElementById('sidebar-backdrop');

        function setSidebarOpen(open) {
            if (!sidebar) {
                return;
            }

            sidebar.classList.toggle('show', open);

            if (sidebarBackdrop) {
                sidebarBackdrop.classList.toggle('show', open);
            }

            if (sidebarToggle) {
                sidebarToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            }
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function () {
                setSidebarOpen(!sidebar.classList.contains('show'));
            });
        }

        if (sidebarBackdrop) {
            sidebarBackdrop.addEventListener('click', function () {
                setSidebarOpen(false);
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && sidebar && sidebar.classList.contains('show')) {
                setSidebarOpen(false);
            }
        });

        document.querySelectorAll('table[data-datatable-server]').forEach(function (table) {
            var source = table.getAttribute('data-source');

            if (!source) {
                return;
            }

            var nonOrderable = [];

            table.querySelectorAll('thead th').forEach(function (th, index) {
                if (th.getAttribute('data-orderable') === 'false') {
                    nonOrderable.push(index);
                }
            });

            var options = {
                processing: true,
                serverSide: true,
                ajax: {
                    url: source,
                    type: 'GET'
                },
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                searchDelay: 300,
                order: []
            };

            if (nonOrderable.length > 0) {
                options.columnDefs = [{
                    targets: nonOrderable,
                    orderable: false,
                    searchable: false
                }];
            }

            table._dataTable = new DataTable(table, options);
        });

        var modalElement = document.getElementById('action-modal');
        var modalContent = document.getElementById('action-modal-content');
        var modalInstance = modalElement ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;

        async function loadModal(url) {
            if (!modalElement || !modalContent || !modalInstance) {
                window.location.href = url;
                return;
            }

            modalContent.innerHTML =
                '<div class="modal-body text-center py-5">' +
                    '<div class="spinner-border" role="status">' +
                        '<span class="visually-hidden">Loading...</span>' +
                    '</div>' +
                '</div>';

            modalInstance.show();

            try {
                var response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                modalContent.innerHTML = await response.text();
            } catch (error) {
                modalContent.innerHTML =
                    '<div class="modal-body">' +
                        '<div class="alert alert-danger mb-0">Unable to load this action.</div>' +
                    '</div>';
            }
        }

        document.addEventListener('click', function (event) {
            var trigger = event.target.closest('[data-modal-url]');

            if (trigger) {
                event.preventDefault();
                loadModal(trigger.getAttribute('data-modal-url'));
                return;
            }

            if (event.target.closest('[data-modal-close]') && modalInstance) {
                event.preventDefault();
                modalInstance.hide();
            }
        });

        document.addEventListener('submit', async function (event) {
            var form = event.target.closest('form[data-modal-form]');

            if (!form) {
                return;
            }

            event.preventDefault();

            try {
                var response = await fetch(form.action, {
                    method: form.method || 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }

                modalContent.innerHTML = await response.text();
            } catch (error) {
                modalContent.innerHTML =
                    '<div class="modal-body">' +
                        '<div class="alert alert-danger mb-0">Unable to complete this action.</div>' +
                    '</div>';
            }
        });

        if (modalElement) {
            modalElement.addEventListener('hidden.bs.modal', function () {
                if (modalContent) {
                    modalContent.innerHTML = '';
                }
            });
        }
    });
</script>

</body>
</html>
