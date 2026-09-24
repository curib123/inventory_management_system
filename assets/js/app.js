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

    async function loadSupplierProducts(select) {
        var container = document.querySelector('[data-stock-in-products]');

        if (!container) {
            return;
        }

        var supplierId = select.value;
        var baseUrl = select.getAttribute('data-products-url');

        if (!supplierId) {
            container.innerHTML =
                '<div class="app-stock-product-empty">' +
                    '<i class="bi bi-truck d-block fs-3 mb-2"></i>' +
                    'Select a supplier to load its products.' +
                '</div>';
            return;
        }

        container.innerHTML =
            '<div class="app-stock-product-empty">' +
                '<div class="spinner-border spinner-border-sm me-2" role="status">' +
                    '<span class="visually-hidden">Loading...</span>' +
                '</div>' +
                'Loading supplier products...' +
            '</div>';

        try {
            var response = await fetch(
                baseUrl.replace(/\/$/, '') + '/' + encodeURIComponent(supplierId),
                {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            );

            if (!response.ok) {
                throw new Error('Unable to load supplier products.');
            }

            container.innerHTML = await response.text();
        } catch (error) {
            container.innerHTML =
                '<div class="alert alert-danger mb-0">' +
                    'Unable to load products for this supplier.' +
                '</div>';
        }
    }

    document.addEventListener('change', function (event) {
        var supplierSelect = event.target.closest('[data-stock-in-supplier]');

        if (supplierSelect) {
            loadSupplierProducts(supplierSelect);
        }
    });

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

        var submitButton = form.querySelector('button[type="submit"]');

        if (submitButton) {
            submitButton.disabled = true;
        }

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
