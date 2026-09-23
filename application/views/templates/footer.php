    </main>

    <script src="https://cdn.datatables.net/v/dt/dt-3.1.1/datatables.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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

            var modal = document.getElementById('action-modal');
            var modalContent = document.getElementById('action-modal-content');

            if (!modal || !modalContent) {
                return;
            }

            async function loadModal(url) {
                modalContent.textContent = 'Loading...';

                if (!modal.open) {
                    modal.showModal();
                }

                try {
                    var response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    modalContent.innerHTML = await response.text();
                } catch (error) {
                    modalContent.textContent = 'Unable to load this action.';
                }
            }

            document.addEventListener('click', function (event) {
                var trigger = event.target.closest('[data-modal-url]');
                if (trigger) {
                    event.preventDefault();
                    loadModal(trigger.getAttribute('data-modal-url'));
                    return;
                }

                if (event.target.closest('[data-modal-close]')) {
                    event.preventDefault();
                    modal.close();
                    modalContent.innerHTML = '';
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
                    modalContent.textContent = 'Unable to complete this action.';
                }
            });
        });
    </script>
</body>
</html>
