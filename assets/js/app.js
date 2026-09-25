document.addEventListener('DOMContentLoaded', function () {
    var sidebar = document.getElementById('sidebar');
    var sidebarToggle = document.getElementById('sidebar-toggle');
    var sidebarBackdrop = document.getElementById('sidebar-backdrop');
    var modalElement = document.getElementById('action-modal');
    var modalContent = document.getElementById('action-modal-content');
    var modalInstance = modalElement ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;

    function escapeHtml(value) {
        var node = document.createElement('div');
        node.textContent = value === null || value === undefined ? '' : String(value);
        return node.innerHTML;
    }

    function cleanText(value, fallback) {
        var text = String(value || '').replace(/\s+/g, ' ').trim();

        if (!text) {
            return fallback || '';
        }

        return text.length > 420 ? text.substring(0, 417) + '...' : text;
    }

    function statusProblem(status, context) {
        var action = context || 'complete this request';
        var problems = {
            0: {
                title: 'Connection problem',
                message: 'The system could not reach the server while trying to ' + action + '.',
                causes: [
                    'The device may be offline or the network connection may be unstable.',
                    'The local/server host may be stopped or unreachable.',
                    'A browser, proxy, firewall, or VPN may have interrupted the request.'
                ],
                steps: [
                    'Check the network connection, then try again.',
                    'If other pages also fail, verify that the application server is running.'
                ]
            },
            400: {
                title: 'Invalid request',
                message: 'The server rejected the request because some request data was not valid.',
                causes: [
                    'A required value may be missing or malformed.',
                    'The requested action or export format may not be supported.'
                ],
                steps: [
                    'Review the entered values and retry the action.',
                    'Refresh the page if the form has been open for a long time.'
                ]
            },
            401: {
                title: 'Session expired',
                message: 'Your login session is no longer valid.',
                causes: [
                    'The session may have expired after inactivity.',
                    'The server may have regenerated or cleared the session.'
                ],
                steps: [
                    'Sign in again, then retry the action.'
                ]
            },
            403: {
                title: 'Access denied',
                message: 'Your account does not have permission to perform this action.',
                causes: [
                    'Your role may not include the required permission.',
                    'Your role or permission assignment may have changed.'
                ],
                steps: [
                    'Ask an administrator to verify your assigned role and permissions.'
                ]
            },
            404: {
                title: 'Record or page not found',
                message: 'The requested resource is no longer available at this location.',
                causes: [
                    'The record may have been deleted by another user.',
                    'The link may be outdated or the route may have changed.'
                ],
                steps: [
                    'Refresh the current list and try opening the record again.'
                ]
            },
            409: {
                title: 'Data conflict',
                message: 'The action conflicts with the current state of the data.',
                causes: [
                    'Another user may have changed the record.',
                    'A related record or transaction may prevent this action.'
                ],
                steps: [
                    'Refresh the data and review related records before trying again.'
                ]
            },
            422: {
                title: 'Validation problem',
                message: 'Some submitted values did not pass validation.',
                causes: [
                    'A required field may be missing.',
                    'A value may be outside the allowed format or range.'
                ],
                steps: [
                    'Review the highlighted fields and correct the invalid values.'
                ]
            },
            500: {
                title: 'Server processing problem',
                message: 'The server could not finish the requested operation.',
                causes: [
                    'A database or server operation may have failed.',
                    'Required files or export dependencies may be unavailable.',
                    'Unexpected application data may have caused the operation to stop.'
                ],
                steps: [
                    'Retry once after refreshing the page.',
                    'If the problem continues, give the support reference to the administrator.'
                ]
            },
            503: {
                title: 'Service temporarily unavailable',
                message: 'The application service is temporarily unavailable.',
                causes: [
                    'The server may be restarting, overloaded, or under maintenance.',
                    'A required service such as the database may be unavailable.'
                ],
                steps: [
                    'Try again after the service is available.'
                ]
            }
        };

        return problems[status] || {
            title: 'Request failed',
            message: 'The system could not ' + action + '.',
            causes: [
                'The server returned an unexpected response.',
                'The request may have been interrupted or rejected.'
            ],
            steps: [
                'Refresh the page and try again.',
                'If the problem continues, contact the administrator with the status shown below.'
            ]
        };
    }

    function parseServerMessage(html) {
        if (!html) {
            return {};
        }

        try {
            var documentNode = new DOMParser().parseFromString(html, 'text/html');
            var heading = documentNode.querySelector('[data-error-heading], h1, h2, title');
            var message = documentNode.querySelector('[data-error-message], .app-error-message, main p, body p');

            return {
                title: heading ? cleanText(heading.textContent, '') : '',
                message: message ? cleanText(message.textContent, '') : ''
            };
        } catch (error) {
            return {};
        }
    }

    function responseProblem(response, html, context) {
        var problem = statusProblem(response ? response.status : 0, context);
        var server = parseServerMessage(html);
        var reference = response && response.headers
            ? response.headers.get('X-Error-Reference')
            : '';

        if (server.title && !/^error$/i.test(server.title)) {
            problem.title = server.title;
        }

        if (server.message) {
            problem.message = server.message;
        }

        problem.status = response ? response.status : 0;
        problem.reference = reference || '';

        return problem;
    }

    function problemBodyHtml(problem) {
        var causes = Array.isArray(problem.causes) ? problem.causes : [];
        var steps = Array.isArray(problem.steps) ? problem.steps : [];
        var status = problem.status
            ? '<span class="app-problem-code">HTTP ' + escapeHtml(problem.status) + '</span>'
            : '<span class="app-problem-code">NETWORK</span>';
        var reference = problem.reference
            ? '<div class="app-problem-reference"><span>Support reference</span><strong>' + escapeHtml(problem.reference) + '</strong></div>'
            : '';

        return '' +
            '<div class="app-problem-panel">' +
                '<div class="d-flex align-items-start justify-content-between gap-3 mb-3">' +
                    '<div>' +
                        '<div class="app-problem-eyebrow">What happened</div>' +
                        '<p class="mb-0">' + escapeHtml(problem.message || 'The requested action could not be completed.') + '</p>' +
                    '</div>' +
                    status +
                '</div>' +
                (causes.length
                    ? '<div class="app-problem-section">' +
                        '<div class="app-problem-section-title">Possible causes</div>' +
                        '<ul>' + causes.map(function (item) {
                            return '<li>' + escapeHtml(item) + '</li>';
                        }).join('') + '</ul>' +
                      '</div>'
                    : '') +
                (steps.length
                    ? '<div class="app-problem-section mb-0">' +
                        '<div class="app-problem-section-title">What to do</div>' +
                        '<ul>' + steps.map(function (item) {
                            return '<li>' + escapeHtml(item) + '</li>';
                        }).join('') + '</ul>' +
                      '</div>'
                    : '') +
                reference +
            '</div>';
    }

    function showProblem(problem) {
        if (!modalElement || !modalContent || !modalInstance) {
            window.alert((problem.title || 'Request failed') + '\n\n' + (problem.message || ''));
            return;
        }

        modalContent.innerHTML =
            '<div class="modal-header">' +
                '<div class="d-flex align-items-center gap-3">' +
                    '<span class="app-modal-icon app-modal-icon-danger">' +
                        '<i class="bi bi-exclamation-triangle"></i>' +
                    '</span>' +
                    '<div>' +
                        '<div class="text-body-secondary small fw-semibold text-uppercase">System message</div>' +
                        '<h2 class="modal-title fs-5 mb-0">' + escapeHtml(problem.title || 'Request failed') + '</h2>' +
                    '</div>' +
                '</div>' +
                '<button type="button" class="btn-close" data-modal-close aria-label="Close"></button>' +
            '</div>' +
            '<div class="modal-body">' +
                problemBodyHtml(problem) +
            '</div>' +
            '<div class="modal-footer">' +
                '<button type="button" class="btn btn-outline-secondary" data-modal-close>Close</button>' +
                '<button type="button" class="btn btn-primary" data-retry-page>' +
                    '<i class="bi bi-arrow-clockwise me-1"></i>Refresh Page' +
                '</button>' +
            '</div>';

        modalInstance.show();
    }

    function showInlineProblem(container, problem) {
        if (!container) {
            showProblem(problem);
            return;
        }

        var existing = container.querySelector('.app-inline-problem');

        if (existing) {
            existing.remove();
        }

        var wrapper = document.createElement('div');
        wrapper.className = 'app-inline-problem alert alert-danger mb-3';
        wrapper.setAttribute('role', 'alert');
        wrapper.innerHTML =
            '<div class="d-flex align-items-start gap-2 mb-2">' +
                '<i class="bi bi-exclamation-triangle-fill mt-1"></i>' +
                '<div>' +
                    '<div class="fw-semibold">' + escapeHtml(problem.title || 'Request failed') + '</div>' +
                    '<div class="small">' + escapeHtml(problem.message || '') + '</div>' +
                '</div>' +
            '</div>' +
            problemBodyHtml(problem);

        container.insertBefore(wrapper, container.firstChild);
        wrapper.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }

    function refreshSearchableSelect(select) {
        if (!select || !select._searchableInput) {
            return;
        }

        var query = select._searchableInput.value.trim().toLowerCase();

        Array.from(select.options).forEach(function (option, index) {
            if (index === 0 || option.value === '') {
                option.hidden = false;
                return;
            }

            var externallyHidden = option.getAttribute('data-filter-hidden') === '1';
            var searchableText = String(
                option.getAttribute('data-search-text') || option.textContent || ''
            ).toLowerCase();

            option.hidden = externallyHidden || (query !== '' && searchableText.indexOf(query) === -1);
        });
    }

    function initializeSearchableSelect(select) {
        if (!select || select.getAttribute('data-searchable-ready') === '1') {
            return;
        }

        select.setAttribute('data-searchable-ready', '1');

        var wrapper = document.createElement('div');
        wrapper.className = 'app-searchable-select';

        var search = document.createElement('input');
        search.type = 'search';
        search.className = 'form-control form-control-sm app-searchable-select-search';
        search.placeholder = select.getAttribute('data-search-placeholder') || 'Search options...';
        search.autocomplete = 'off';
        search.setAttribute('aria-label', search.placeholder);

        var parent = select.parentNode;
        parent.insertBefore(wrapper, select);
        wrapper.appendChild(search);
        wrapper.appendChild(select);

        select._searchableInput = search;
        select._refreshSearchable = function () {
            refreshSearchableSelect(select);
        };

        search.addEventListener('input', function () {
            refreshSearchableSelect(select);
        });

        select.addEventListener('change', function () {
            search.value = '';
            refreshSearchableSelect(select);
        });

        refreshSearchableSelect(select);
    }

    function initializeSearchableSelects(container) {
        if (!container) {
            return;
        }

        container.querySelectorAll('select[data-searchable-select]').forEach(function (select) {
            initializeSearchableSelect(select);
        });
    }

    function filterAdjustmentProducts(supplierSelect) {
        if (!supplierSelect) {
            return;
        }

        var targetSelector = supplierSelect.getAttribute('data-product-target');
        var productSelect = targetSelector ? document.querySelector(targetSelector) : null;

        if (!productSelect) {
            return;
        }

        var supplierId = supplierSelect.value;

        Array.from(productSelect.options).forEach(function (option) {
            if (option.value === '') {
                option.removeAttribute('data-filter-hidden');
                option.disabled = false;
                return;
            }

            var matches = supplierId === '' ||
                option.getAttribute('data-supplier-id') === supplierId;

            option.setAttribute('data-filter-hidden', matches ? '0' : '1');
            option.disabled = !matches;
        });

        var selectedOption = productSelect.options[productSelect.selectedIndex];

        if (
            selectedOption &&
            selectedOption.value !== '' &&
            selectedOption.getAttribute('data-filter-hidden') === '1'
        ) {
            productSelect.value = '';
        }

        if (typeof productSelect._refreshSearchable === 'function') {
            productSelect._refreshSearchable();
        }
    }

    function filterStockProducts(input) {
        var list = input ? input.closest('.modal-body') : null;

        if (!list) {
            return;
        }

        var query = input.value.trim().toLowerCase();
        var cards = list.querySelectorAll('[data-stock-product-card]');
        var visibleCount = 0;

        cards.forEach(function (card) {
            var text = String(card.getAttribute('data-stock-product-search') || '').toLowerCase();
            var matches = query === '' || text.indexOf(query) !== -1;

            card.classList.toggle('d-none', !matches);

            if (matches) {
                visibleCount++;
            }
        });

        var empty = list.querySelector('[data-stock-filter-empty]');

        if (empty) {
            empty.classList.toggle('d-none', visibleCount > 0);
        }
    }

    function enhanceFeedback(container) {
        if (!container) {
            return;
        }

        initializeSearchableSelects(container);

        container.querySelectorAll('[data-adjustment-supplier]').forEach(function (supplierSelect) {
            filterAdjustmentProducts(supplierSelect);
        });

        container.querySelectorAll('.alert-danger, .alert-warning').forEach(function (alert) {
            alert.classList.add('app-feedback-alert');
            alert.setAttribute('role', 'alert');

            if (!alert.querySelector('.app-feedback-label')) {
                var label = document.createElement('div');
                label.className = 'app-feedback-label fw-semibold mb-1';
                label.innerHTML = alert.classList.contains('alert-warning')
                    ? '<i class="bi bi-exclamation-triangle me-1"></i>Please review'
                    : '<i class="bi bi-exclamation-circle me-1"></i>Action needs attention';
                alert.insertBefore(label, alert.firstChild);
            }
        });

        var firstAlert = container.querySelector('.alert-danger, .alert-warning');

        if (firstAlert) {
            firstAlert.setAttribute('tabindex', '-1');
            firstAlert.focus({ preventScroll: true });
        }
    }

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

    function renderTableCell(renderType, value) {
        var raw = value === null || value === undefined ? '' : String(value);
        var normalized = raw.trim().toLowerCase();

        if (renderType === 'status') {
            var active = normalized === 'active' || normalized === '1' || normalized === 'enabled';
            return '<span class="badge rounded-pill app-table-badge ' +
                (active ? 'app-table-badge-success' : 'app-table-badge-secondary') + '">' +
                escapeHtml(raw || (active ? 'Active' : 'Inactive')) +
            '</span>';
        }

        if (renderType === 'movement') {
            var movementClass = 'app-table-badge-info';
            var movementLabel = raw.replace(/_/g, ' ');

            if (normalized === 'stock_in') {
                movementClass = 'app-table-badge-success';
                movementLabel = 'Stock In';
            } else if (normalized === 'stock_out') {
                movementClass = 'app-table-badge-danger';
                movementLabel = 'Stock Out';
            } else if (normalized === 'adjustment') {
                movementClass = 'app-table-badge-warning';
                movementLabel = 'Adjustment';
            }

            return '<span class="badge rounded-pill app-table-badge ' + movementClass + '">' +
                escapeHtml(movementLabel) +
            '</span>';
        }

        if (renderType === 'stock_alert') {
            var stock = Number(raw);
            var stockClass = stock <= 0 ? 'app-table-badge-danger' : 'app-table-badge-warning';
            return '<span class="app-table-number-badge ' + stockClass + '">' +
                escapeHtml(raw) +
            '</span>';
        }

        if (renderType === 'difference') {
            var difference = Number(raw);
            var differenceClass = difference > 0
                ? 'app-table-badge-success'
                : (difference < 0 ? 'app-table-badge-danger' : 'app-table-badge-secondary');
            var prefix = difference > 0 ? '+' : '';

            return '<span class="badge rounded-pill app-table-badge ' + differenceClass + '">' +
                escapeHtml(prefix + raw) +
            '</span>';
        }

        return raw;
    }

    if (window.DataTable && DataTable.ext) {
        DataTable.ext.errMode = 'none';
    }

    document.querySelectorAll('table[data-datatable-server]').forEach(function (table) {
        var source = table.getAttribute('data-source');
        var card = table.closest('.app-table-card');

        if (!source || !card) {
            return;
        }

        var columnDefinitions = [];

        table.querySelectorAll('thead th').forEach(function (th, index) {
            var definition = {
                targets: index
            };
            var configured = false;

            if (th.getAttribute('data-orderable') === 'false') {
                definition.orderable = false;
                configured = true;
            }

            if (th.getAttribute('data-visible') === 'false') {
                definition.visible = false;
                definition.searchable = false;
                configured = true;
            }

            var columnClass = th.getAttribute('data-column-class');

            if (columnClass) {
                definition.className = columnClass;
                configured = true;
            }

            var renderType = th.getAttribute('data-render');

            if (renderType) {
                definition.render = function (data, type) {
                    if (type !== 'display') {
                        return data;
                    }

                    return renderTableCell(renderType, data);
                };
                configured = true;
            }

            if (configured) {
                columnDefinitions.push(definition);
            }
        });

        var options = {
            processing: true,
            serverSide: true,
            ajax: {
                url: source,
                type: 'GET',
                data: function (payload) {
                    var filters = {};

                    card.querySelectorAll('[data-table-filter]').forEach(function (select) {
                        var name = select.getAttribute('data-table-filter');
                        var value = select.value;

                        if (name && value !== '') {
                            filters[name] = value;
                        }
                    });

                    payload.table_filters = filters;
                },
                error: function (xhr) {
                    var problem = responseProblem(
                        xhr ? {
                            status: xhr.status || 0,
                            headers: {
                                get: function (name) {
                                    return xhr.getResponseHeader ? xhr.getResponseHeader(name) : '';
                                }
                            }
                        } : null,
                        xhr && xhr.responseText ? xhr.responseText : '',
                        'load the table data'
                    );

                    showProblem(problem);
                }
            },
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            searchDelay: 300,
            order: [],
            layout: {
                topStart: null,
                topEnd: null,
                bottomStart: ['pageLength', 'info'],
                bottomEnd: 'paging'
            },
            language: {
                emptyTable: 'No records found.',
                info: 'Showing _START_ to _END_ of _TOTAL_ records',
                infoEmpty: 'No records to show',
                infoFiltered: '',
                lengthMenu: 'Rows per page _MENU_',
                paginate: {
                    previous: 'Previous',
                    next: 'Next'
                },
                processing: 'Loading records...',
                zeroRecords: 'No matching records found.'
            }
        };

        if (columnDefinitions.length > 0) {
            options.columnDefs = columnDefinitions;
        }

        var dataTable = new DataTable(table, options);
        table._dataTable = dataTable;

        var searchInput = card.querySelector('[data-table-search]');
        var searchClear = card.querySelector('[data-table-search-clear]');
        var resetButton = card.querySelector('[data-table-reset]');
        var filterCount = card.querySelector('[data-table-filter-count]');
        var searchTimer = null;

        function updateToolbarState() {
            var activeFilters = 0;

            card.querySelectorAll('[data-table-filter]').forEach(function (select) {
                if (select.value !== '') {
                    activeFilters++;
                }
            });

            var hasSearch = searchInput && searchInput.value.trim() !== '';

            if (filterCount) {
                filterCount.textContent = activeFilters + (activeFilters === 1 ? ' filter active' : ' filters active');
                filterCount.classList.toggle('d-none', activeFilters === 0);
            }

            if (resetButton) {
                resetButton.classList.toggle('d-none', activeFilters === 0 && !hasSearch);
            }

            if (searchClear) {
                searchClear.classList.toggle('d-none', !hasSearch);
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                window.clearTimeout(searchTimer);
                updateToolbarState();

                searchTimer = window.setTimeout(function () {
                    dataTable.search(searchInput.value.trim()).draw();
                }, 300);
            });
        }

        if (searchClear && searchInput) {
            searchClear.addEventListener('click', function () {
                searchInput.value = '';
                updateToolbarState();
                searchInput.focus();
                dataTable.search('').draw();
            });
        }

        card.querySelectorAll('[data-table-filter]').forEach(function (select) {
            select.addEventListener('change', function () {
                updateToolbarState();
                dataTable.ajax.reload(null, true);
            });
        });
        if (resetButton) {
            resetButton.addEventListener('click', function () {
                card.querySelectorAll('[data-table-filter]').forEach(function (select) {
                    select.value = '';
                });

                if (searchInput) {
                    searchInput.value = '';
                }

                updateToolbarState();
                dataTable.search('').draw();
            });
        }

        updateToolbarState();
    });

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
                '<div class="small text-body-secondary mt-3">Loading action...</div>' +
            '</div>';

        modalInstance.show();

        try {
            var response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.redirected) {
                window.location.href = response.url;
                return;
            }

            var html = await response.text();

            if (!response.ok) {
                showProblem(responseProblem(response, html, 'load this action'));
                return;
            }

            modalContent.innerHTML = html;
            enhanceFeedback(modalContent);
        } catch (error) {
            showProblem(statusProblem(0, 'load this action'));
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

            var html = await response.text();

            if (!response.ok) {
                showInlineProblem(
                    container,
                    responseProblem(response, html, 'load the supplier products')
                );
                return;
            }

            container.innerHTML = html;
            enhanceFeedback(container);
        } catch (error) {
            showInlineProblem(
                container,
                statusProblem(0, 'load the supplier products')
            );
        }
    }

    function updateStockPreview(input) {
        var product = input.closest('.app-stock-product');

        if (!product) {
            return;
        }

        var preview = product.querySelector('[data-stock-new]');

        if (!preview) {
            return;
        }

        var current = parseInt(preview.getAttribute('data-current-stock'), 10) || 0;
        var direction = parseInt(preview.getAttribute('data-stock-direction'), 10) || 1;
        var quantity = /^\d+$/.test(input.value.trim())
            ? parseInt(input.value, 10)
            : 0;
        var nextStock = current + (direction * quantity);

        preview.textContent = String(nextStock);
        preview.classList.toggle('text-danger', nextStock < 0);
        input.classList.toggle('is-invalid', nextStock < 0);
    }

    var confirmationStates = new WeakMap();

    function confirmationVariantClass(variant) {
        return ['primary', 'success', 'warning', 'danger'].indexOf(variant) !== -1
            ? variant
            : 'primary';
    }

    function showFormConfirmation(form, submitButton) {
        if (!form || confirmationStates.has(form)) {
            return;
        }

        var fragment = document.createDocumentFragment();

        while (form.firstChild) {
            fragment.appendChild(form.firstChild);
        }

        var variant = confirmationVariantClass(
            form.getAttribute('data-confirm-variant') || 'primary'
        );
        var icon = form.getAttribute('data-confirm-icon') || 'bi-check2-circle';
        var title = form.getAttribute('data-confirm-title') || 'Confirm changes';
        var message = form.getAttribute('data-confirm-message') || 'Review the information before continuing.';
        var label = form.getAttribute('data-confirm-label') || 'Confirm';
        var assist = form.getAttribute('data-confirm-assist') || '';
        var impact = form.getAttribute('data-confirm-impact') || '';

        var state = {
            fragment: fragment,
            submitButton: submitButton || null
        };

        confirmationStates.set(form, state);

        var stage = document.createElement('div');
        stage.className = 'app-confirmation-stage';
        stage.innerHTML =
            '<div class="modal-header">' +
                '<div class="d-flex align-items-center gap-3">' +
                    '<span class="app-modal-icon app-modal-icon-' + escapeHtml(variant) + '">' +
                        '<i class="bi ' + escapeHtml(icon) + '"></i>' +
                    '</span>' +
                    '<div>' +
                        '<div class="text-body-secondary small fw-semibold text-uppercase">Confirmation</div>' +
                        '<h2 class="modal-title fs-5 mb-0">' + escapeHtml(title) + '</h2>' +
                    '</div>' +
                '</div>' +
                '<button type="button" class="btn-close" data-confirm-cancel aria-label="Go back"></button>' +
            '</div>' +
            '<div class="modal-body">' +
                '<div class="app-confirmation-review">' +
                    '<div class="app-confirmation-review-icon app-confirmation-review-icon-' + escapeHtml(variant) + '">' +
                        '<i class="bi ' + escapeHtml(icon) + '"></i>' +
                    '</div>' +
                    '<div>' +
                        '<div class="app-confirmation-review-title">Review before continuing</div>' +
                        '<p class="app-confirmation-review-message mb-0">' + escapeHtml(message) + '</p>' +
                    '</div>' +
                '</div>' +
                (impact
                    ? '<div class="app-confirmation-impact">' +
                        '<div class="app-confirmation-impact-label">What this changes</div>' +
                        '<div>' + escapeHtml(impact) + '</div>' +
                      '</div>'
                    : '') +
                (assist
                    ? '<div class="app-confirmation-assist">' +
                        '<i class="bi bi-lightbulb me-2"></i>' +
                        '<span>' + escapeHtml(assist) + '</span>' +
                      '</div>'
                    : '') +
                '<div class="small text-body-secondary mt-3">' +
                    'Nothing has been submitted yet. Choose Go Back if you want to review or change any information.' +
                '</div>' +
            '</div>' +
            '<div class="modal-footer">' +
                '<button type="button" class="btn btn-outline-secondary" data-confirm-cancel>' +
                    '<i class="bi bi-arrow-left me-1"></i>Go Back' +
                '</button>' +
                '<button type="button" class="btn btn-' + escapeHtml(variant) + '" data-confirm-proceed>' +
                    '<i class="bi ' + escapeHtml(icon) + ' me-1"></i>' + escapeHtml(label) +
                '</button>' +
            '</div>';

        form.appendChild(stage);

        var proceed = stage.querySelector('[data-confirm-proceed]');

        if (proceed) {
            proceed.focus();
        }
    }

    function restoreFormConfirmation(form) {
        if (!form) {
            return null;
        }

        var state = confirmationStates.get(form);

        if (!state) {
            return null;
        }

        while (form.firstChild) {
            form.removeChild(form.firstChild);
        }

        form.appendChild(state.fragment);
        confirmationStates.delete(form);

        window.requestAnimationFrame(function () {
            if (state.submitButton && document.body.contains(state.submitButton)) {
                state.submitButton.focus();
            }
        });

        return state;
    }

    async function submitModalForm(form, submitButton) {
        if (!form) {
            return;
        }

        submitButton = submitButton || form.querySelector('button[type="submit"]');

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
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

            var html = await response.text();

            if (!response.ok) {
                var body = form.querySelector('.modal-body') || form;
                showInlineProblem(
                    body,
                    responseProblem(response, html, 'save these changes')
                );
                return;
            }

            modalContent.innerHTML = html;
            enhanceFeedback(modalContent);
        } catch (error) {
            var formBody = form.querySelector('.modal-body') || form;
            showInlineProblem(
                formBody,
                statusProblem(0, 'save these changes')
            );
        } finally {
            if (submitButton && document.body.contains(submitButton)) {
                submitButton.disabled = false;
                submitButton.removeAttribute('aria-busy');
            }
        }
    }

    function filenameFromDisposition(disposition, fallback) {
        if (!disposition) {
            return fallback;
        }

        var utfMatch = disposition.match(/filename\*=UTF-8''([^;]+)/i);

        if (utfMatch && utfMatch[1]) {
            try {
                return decodeURIComponent(utfMatch[1].replace(/["']/g, '').trim());
            } catch (error) {
                return utfMatch[1].replace(/["']/g, '').trim();
            }
        }

        var match = disposition.match(/filename="?([^";]+)"?/i);
        return match && match[1] ? match[1].trim() : fallback;
    }

    async function downloadReport(button) {
        var url = button.getAttribute('data-export-url');

        if (!url) {
            return;
        }

        var originalHtml = button.innerHTML;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.innerHTML =
            '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>' +
            'Preparing...';

        try {
            var response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/csv, application/pdf, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                }
            });

            var contentType = (response.headers.get('Content-Type') || '').toLowerCase();

            if (response.redirected && contentType.indexOf('text/html') !== -1) {
                window.location.href = response.url;
                return;
            }

            if (!response.ok || contentType.indexOf('text/html') !== -1) {
                var html = await response.text();
                var problem = responseProblem(response, html, 'generate and download this report');

                if (response.ok) {
                    problem.status = 500;
                    problem.title = 'Unexpected export response';
                    problem.message = 'The server returned a web page instead of a report file.';
                    problem.causes = [
                        'The login session may have expired.',
                        'The export may have failed before the file was generated.',
                        'A server error page may have been returned instead of the requested file.'
                    ];
                }

                showProblem(problem);
                return;
            }

            var blob = await response.blob();

            if (!blob || blob.size === 0) {
                var emptyProblem = statusProblem(500, 'download this report');
                emptyProblem.message = 'The server returned an empty report file.';
                emptyProblem.causes = [
                    'The export process may have stopped before writing the file.',
                    'A temporary file or output-stream problem may have occurred.'
                ];
                showProblem(emptyProblem);
                return;
            }

            var fallback = 'business-report';
            var filename = filenameFromDisposition(
                response.headers.get('Content-Disposition'),
                fallback
            );
            var objectUrl = URL.createObjectURL(blob);
            var link = document.createElement('a');

            link.href = objectUrl;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            link.remove();

            window.setTimeout(function () {
                URL.revokeObjectURL(objectUrl);
            }, 1000);
        } catch (error) {
            showProblem(statusProblem(0, 'generate and download this report'));
        } finally {
            button.disabled = false;
            button.removeAttribute('aria-busy');
            button.innerHTML = originalHtml;
        }
    }

    document.addEventListener('input', function (event) {
        var quantityInput = event.target.closest('[data-stock-quantity]');

        if (quantityInput) {
            updateStockPreview(quantityInput);
        }

        var productFilter = event.target.closest('[data-stock-product-filter]');

        if (productFilter) {
            filterStockProducts(productFilter);
        }
    });

    document.addEventListener('change', function (event) {
        var supplierSelect = event.target.closest('[data-stock-in-supplier]');

        if (supplierSelect) {
            loadSupplierProducts(supplierSelect);
        }

        var adjustmentSupplier = event.target.closest('[data-adjustment-supplier]');

        if (adjustmentSupplier) {
            filterAdjustmentProducts(adjustmentSupplier);
        }
    });

    document.addEventListener('click', function (event) {
        var confirmationCancel = event.target.closest('[data-confirm-cancel]');

        if (confirmationCancel) {
            event.preventDefault();
            restoreFormConfirmation(confirmationCancel.closest('form[data-modal-form]'));
            return;
        }

        var confirmationProceed = event.target.closest('[data-confirm-proceed]');

        if (confirmationProceed) {
            event.preventDefault();

            var confirmationForm = confirmationProceed.closest('form[data-modal-form]');
            var confirmationState = restoreFormConfirmation(confirmationForm);

            submitModalForm(
                confirmationForm,
                confirmationState ? confirmationState.submitButton : null
            );
            return;
        }

        var exportButton = event.target.closest('[data-report-export]');

        if (exportButton) {
            event.preventDefault();
            downloadReport(exportButton);
            return;
        }

        var trigger = event.target.closest('[data-modal-url]');

        if (trigger) {
            event.preventDefault();
            loadModal(trigger.getAttribute('data-modal-url'));
            return;
        }

        if (event.target.closest('[data-retry-page]')) {
            event.preventDefault();
            window.location.reload();
            return;
        }

        if (event.target.closest('[data-modal-close]') && modalInstance) {
            event.preventDefault();
            modalInstance.hide();
        }
    });

    document.addEventListener('submit', function (event) {
        var form = event.target.closest('form[data-modal-form]');

        if (!form) {
            return;
        }

        event.preventDefault();

        var submitButton = form.querySelector('button[type="submit"]');

        if (form.getAttribute('data-confirm-required') === '1') {
            showFormConfirmation(form, submitButton);
            return;
        }

        submitModalForm(form, submitButton);
    });

    if (modalElement) {
        modalElement.addEventListener('hidden.bs.modal', function () {
            if (modalContent) {
                modalContent.querySelectorAll('form[data-modal-form]').forEach(function (form) {
                    confirmationStates.delete(form);
                });
                modalContent.innerHTML = '';
            }
        });
    }

    enhanceFeedback(document);
});
