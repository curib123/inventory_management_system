document.addEventListener('DOMContentLoaded', function () {
    var sidebar = document.getElementById('sidebar');
    var sidebarToggle = document.getElementById('sidebar-toggle');
    var sidebarBackdrop = document.getElementById('sidebar-backdrop');

    // Keep initials visible until the photo loads, including cached image requests.
    document.querySelectorAll('[data-avatar-image]').forEach(function (image) {
        var initials = image.parentElement.querySelector('[data-avatar-initials]');

        function showPhoto(loaded) {
            image.hidden = !loaded;
            if (initials) {
                initials.hidden = loaded;
            }
        }

        image.addEventListener('load', function () {
            showPhoto(image.naturalWidth > 0);
        });
        image.addEventListener('error', function () {
            showPhoto(false);
        });

        if (image.complete) {
            showPhoto(image.naturalWidth > 0);
        }
    });

    var modalElement = document.getElementById('action-modal');
    var modalContent = document.getElementById('action-modal-content');
    var modalState = {
        open: false,
        previousFocus: null,
        closeTimer: null
    };

    function focusableModalElements() {
        if (!modalElement) {
            return [];
        }

        return Array.from(modalElement.querySelectorAll(
            'a[href], area[href], button:not([disabled]), input:not([disabled]), ' +
            'select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        )).filter(function (element) {
            return element.offsetWidth > 0 || element.offsetHeight > 0 || element === document.activeElement;
        });
    }

    function focusActionModal() {
        var focusable = focusableModalElements();

        if (focusable.length) {
            focusable[0].focus();
            return;
        }

        if (modalElement) {
            modalElement.focus();
        }
    }

    function clearActionModalContent() {
        if (!modalContent) {
            return;
        }

        modalContent.querySelectorAll('form[data-modal-form]').forEach(function (form) {
            if (typeof confirmationStates !== 'undefined') {
                confirmationStates.delete(form);
            }
        });
        modalContent.innerHTML = '';
    }

    function showActionModal() {
        if (!modalElement || !modalContent) {
            return;
        }

        if (modalState.closeTimer) {
            window.clearTimeout(modalState.closeTimer);
            modalState.closeTimer = null;
        }

        if (!modalState.open) {
            modalState.previousFocus = document.activeElement;
        }

        modalState.open = true;
        modalElement.hidden = false;
        modalElement.setAttribute('aria-hidden', 'false');
        modalElement.setAttribute('aria-modal', 'true');
        modalElement.classList.add('is-open');
        document.body.classList.add('app-modal-open');

        window.requestAnimationFrame(function () {
            if (modalState.open && modalElement) {
                modalElement.classList.add('is-visible');
                focusActionModal();
            }
        });
    }

    function hideActionModal(restoreFocus) {
        if (!modalElement || !modalState.open) {
            return;
        }

        modalState.open = false;
        modalElement.classList.remove('is-visible');
        modalElement.setAttribute('aria-hidden', 'true');
        modalElement.removeAttribute('aria-modal');
        document.body.classList.remove('app-modal-open');

        modalState.closeTimer = window.setTimeout(function () {
            if (modalState.open || !modalElement) {
                return;
            }

            modalElement.classList.remove('is-open');
            modalElement.hidden = true;
            clearActionModalContent();

            if (restoreFocus && modalState.previousFocus && typeof modalState.previousFocus.focus === 'function') {
                modalState.previousFocus.focus();
            }

            modalState.previousFocus = null;
            modalState.closeTimer = null;
        }, 180);
    }

    if (modalElement) {
        modalElement.addEventListener('keydown', function (event) {
            if (!modalState.open) {
                return;
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                hideActionModal(true);
                return;
            }

            if (event.key !== 'Tab') {
                return;
            }

            var focusable = focusableModalElements();

            if (!focusable.length) {
                event.preventDefault();
                modalElement.focus();
                return;
            }

            var first = focusable[0];
            var last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
    }

    // Frontend helper ni para escape html; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
    function escapeHtml(value) {
        var node = document.createElement('div');
        node.textContent = value === null || value === undefined ? '' : String(value);
        return node.innerHTML;
    }

    // Frontend helper ni para clean text; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
    function cleanText(value, fallback) {
        var text = String(value || '').replace(/\s+/g, ' ').trim();

        if (!text) {
            return fallback || '';
        }

        return text.length > 420 ? text.substring(0, 417) + '...' : text;
    }

    // Frontend helper ni para status problem; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
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

    // Frontend helper ni para parse server message; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
    function parseServerMessage(html) {
        if (!html) {
            return {};
        }

        try {
            var documentNode = new DOMParser().parseFromString(html, 'text/html');
            var heading = documentNode.querySelector('[data-error-heading], h1, h2, title');
            var message = documentNode.querySelector('[data-error-message], .app-error-message, main p, body p');
            var errorId = documentNode.querySelector('[data-error-id]');
            var errorLog = documentNode.querySelector('[data-error-log]');

            return {
                title: heading ? cleanText(heading.textContent, '') : '',
                message: message ? cleanText(message.textContent, '') : '',
                reference: errorId ? cleanText(errorId.textContent, '') : '',
                log: errorLog ? cleanText(errorLog.textContent, '') : ''
            };
        } catch (error) {
            return {};
        }
    }

    // Frontend helper ni para response problem; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
    function responseProblem(response, html, context) {
        var problem = statusProblem(response ? response.status : 0, context);
        var server = parseServerMessage(html);
        var reference = response && response.headers
            ? response.headers.get('X-Error-Reference')
            : '';
        var logReference = response && response.headers
            ? response.headers.get('X-Error-Log')
            : '';

        if (server.title && !/^error$/i.test(server.title)) {
            problem.title = server.title;
        }

        if (server.message) {
            problem.message = server.message;
        }

        problem.status = response ? response.status : 0;
        problem.reference = reference || server.reference || '';
        problem.log = logReference || server.log || '';

        return problem;
    }

    // Frontend helper ni para problem body html; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
    function problemBodyHtml(problem) {
        var causes = Array.isArray(problem.causes) ? problem.causes : [];
        var steps = Array.isArray(problem.steps) ? problem.steps : [];
        var status = problem.status
            ? '<span class="app-problem-code">HTTP ' + escapeHtml(problem.status) + '</span>'
            : '<span class="app-problem-code">NETWORK</span>';
        var reference = problem.reference
            ? '<div class="app-problem-reference"><span>Error ID</span><strong>' + escapeHtml(problem.reference) + '</strong></div>'
            : '';
        var logReference = problem.log
            ? '<div class="app-problem-reference"><span>Application log</span><strong>' + escapeHtml(problem.log) + '</strong></div>'
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
                logReference +
            '</div>';
    }

    // Frontend helper ni para show problem; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
    function showProblem(problem) {
        if (!modalElement || !modalContent) {
            window.alert((problem.title || 'Request failed') + '\n\n' + (problem.message || ''));
            return;
        }

        modalContent.innerHTML =
            '<div class="app-modal-header app-modal-header-danger">' +
                '<div class="app-modal-heading">' +
                    '<span class="app-modal-icon app-modal-icon-danger">' +
                        '<i class="bi bi-exclamation-triangle"></i>' +
                    '</span>' +
                    '<div class="app-modal-heading-copy">' +
                        '<div class="app-modal-eyebrow">System message</div>' +
                        '<h2 class="app-modal-title" id="action-modal-title">' + escapeHtml(problem.title || 'Request failed') + '</h2>' +
                        '<p class="app-modal-subtitle">The system could not complete the requested operation.</p>' +
                    '</div>' +
                '</div>' +
                '<button type="button" class="btn-close app-modal-close" data-modal-close aria-label="Close"></button>' +
            '</div>' +
            '<div class="app-modal-body">' +
                problemBodyHtml(problem) +
            '</div>' +
            '<div class="app-modal-footer">' +
                '<div class="app-modal-footer-actions">' +
                    '<button type="button" class="btn btn-outline-secondary" data-modal-close>Close</button>' +
                    '<button type="button" class="btn btn-primary" data-retry-page>' +
                        '<i class="bi bi-arrow-clockwise me-1"></i>Refresh Page' +
                    '</button>' +
                '</div>' +
            '</div>';

        showActionModal();
    }

    // Frontend helper ni para show inline problem; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
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

    // Frontend helper ni para initialize searchable select; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
    function initializeSearchableSelect(select) {
        if (!select || select.getAttribute('data-searchable-ready') === '1') {
            return;
        }

        select.setAttribute('data-searchable-ready', '1');

        var wrapper = document.createElement('div');
        wrapper.className = 'app-searchable-select';

        var trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'app-searchable-select-trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.setAttribute('aria-controls', (select.id || 'searchable-select') + '-options');

        var triggerValue = document.createElement('span');
        triggerValue.className = 'app-searchable-select-value';

        var triggerCaret = document.createElement('span');
        triggerCaret.className = 'app-searchable-select-caret';
        triggerCaret.setAttribute('aria-hidden', 'true');
        triggerCaret.textContent = '⌄';

        trigger.appendChild(triggerValue);
        trigger.appendChild(triggerCaret);

        var panel = document.createElement('div');
        panel.className = 'app-searchable-select-panel';
        panel.hidden = true;

        var search = document.createElement('input');
        search.type = 'search';
        search.className = 'app-searchable-select-search';
        search.placeholder = select.getAttribute('data-search-placeholder') || 'Search options...';
        search.autocomplete = 'off';
        search.setAttribute('aria-label', search.placeholder);

        var options = document.createElement('div');
        options.className = 'app-searchable-select-options';
        options.id = (select.id || 'searchable-select') + '-options';
        options.setAttribute('role', 'listbox');

        panel.appendChild(search);
        panel.appendChild(options);

        var parent = select.parentNode;
        parent.insertBefore(wrapper, select);
        wrapper.appendChild(trigger);
        wrapper.appendChild(panel);
        wrapper.appendChild(select);
        select.classList.add('app-searchable-select-native');

        select._searchableInput = search;

        var remoteUrl = select.getAttribute('data-search-url') || '';
        var remoteMode = select.getAttribute('data-search-mode') || '';
        var dependentSelector = select.getAttribute('data-search-dependent') || '';
        var dependentParam = select.getAttribute('data-search-dependent-param') || '';
        var remoteMinLength = parseInt(select.getAttribute('data-search-min-length'), 10);
        var staticOptions = Array.from(select.options)
            .filter(function (option) {
                return option.getAttribute('data-static-option') === '1';
            })
            .map(function (option) {
                return {
                    value: option.value,
                    text: option.textContent,
                    searchText: option.getAttribute('data-search-text') || option.textContent
                };
            });
        var searchTimer = null;
        var searchSequence = 0;
        var searchableOptionLimit = 10;
        var defaultOptionsLoaded = !remoteUrl;
        var isOpen = false;

        if (Number.isNaN(remoteMinLength)) {
            remoteMinLength = 2;
        }

        // Frontend helper ni para selected snapshot; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
        function selectedSnapshot() {
            if (!select.value || select.selectedIndex < 0) {
                return null;
            }

            var option = select.options[select.selectedIndex];

            if (!option || option.value === '') {
                return null;
            }

            return {
                value: option.value,
                text: option.textContent,
                searchText: option.getAttribute('data-search-text') || option.textContent,
                currentStock: option.getAttribute('data-current-stock'),
                unit: option.getAttribute('data-unit'),
                supplierId: option.getAttribute('data-supplier-id'),
                supplierName: option.getAttribute('data-supplier-name')
            };
        }

        function syncSearchableTrigger() {
            var selectedOption = select.selectedIndex >= 0
                ? select.options[select.selectedIndex]
                : null;
            var hasValue = selectedOption && selectedOption.value !== '';
            var placeholder = select.getAttribute('data-placeholder') ||
                (select.options.length ? select.options[0].textContent : 'Select an option');

            triggerValue.textContent = selectedOption && (hasValue || !select.required)
                ? selectedOption.textContent.trim()
                : placeholder.trim();
            triggerValue.classList.toggle('is-placeholder', !hasValue && select.required);
            trigger.disabled = select.disabled;
            wrapper.classList.toggle('is-disabled', select.disabled);
        }

        function closeSearchableDropdown(restoreFocus) {
            isOpen = false;
            wrapper.classList.remove('is-open');
            panel.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');

            if (restoreFocus) {
                trigger.focus();
            }
        }

        function matchingOptions(query) {
            var normalizedQuery = String(query || '').trim().toLowerCase();
            var selectedValue = String(select.value || '');
            var matches = Array.from(select.options).filter(function (option, index) {
                if (index === 0 && option.value === '' && select.required) {
                    return false;
                }

                if (option.disabled || option.getAttribute('data-filter-hidden') === '1') {
                    return false;
                }

                var searchableText = String(
                    option.getAttribute('data-search-text') || option.textContent || ''
                ).toLowerCase();

                return normalizedQuery === '' || searchableText.indexOf(normalizedQuery) !== -1;
            });
            var limited = matches.slice(0, searchableOptionLimit);
            var selected = matches.find(function (option) {
                return String(option.value) === selectedValue;
            });

            if (selected && limited.indexOf(selected) === -1) {
                limited.push(selected);
            }

            return limited;
        }

        function renderSearchableOptions(query) {
            options.innerHTML = '';

            var matches = matchingOptions(query);

            if (!matches.length) {
                var empty = document.createElement('div');
                empty.className = 'app-searchable-select-empty';
                empty.textContent = 'No matching options';
                options.appendChild(empty);
                return;
            }

            matches.forEach(function (option) {
                var optionButton = document.createElement('button');
                optionButton.type = 'button';
                optionButton.className = 'app-searchable-select-option';
                optionButton.setAttribute('role', 'option');
                optionButton.setAttribute('aria-selected', String(option.value) === String(select.value));
                optionButton.textContent = option.textContent.trim();

                if (String(option.value) === String(select.value)) {
                    optionButton.classList.add('is-selected');
                }

                optionButton.addEventListener('click', function () {
                    select.value = option.value;
                    search.value = '';
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    syncSearchableTrigger();
                    renderSearchableOptions('');
                    closeSearchableDropdown(true);
                });

                options.appendChild(optionButton);
            });
        }

        // Frontend helper ni para rebuild remote options; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
        function rebuildRemoteOptions(items, selected) {
            var placeholderText = select.getAttribute('data-placeholder') ||
                (select.options.length ? select.options[0].textContent : 'Select an option');

            select.innerHTML = '';

            var placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = placeholderText;
            select.appendChild(placeholder);

            var selectedIncluded = false;

            staticOptions.forEach(function (item) {
                var option = document.createElement('option');
                option.value = String(item.value);
                option.textContent = item.text;
                option.setAttribute('data-search-text', item.searchText || item.text);
                option.setAttribute('data-static-option', '1');

                if (selected && String(selected.value) === option.value) {
                    option.selected = true;
                    selectedIncluded = true;
                }

                select.appendChild(option);
            });

            (items || []).forEach(function (item) {
                if (!item || !item.id) {
                    return;
                }

                var option = document.createElement('option');
                option.value = String(item.id);
                option.textContent = item.secondary
                    ? String(item.text || '') + ' — ' + String(item.secondary)
                    : String(item.text || '');
                option.setAttribute(
                    'data-search-text',
                    (String(item.text || '') + ' ' + String(item.secondary || '')).trim()
                );

                if (item.current_stock !== undefined && item.current_stock !== null) {
                    option.setAttribute('data-current-stock', String(item.current_stock));
                }

                if (item.unit !== undefined && item.unit !== null) {
                    option.setAttribute('data-unit', String(item.unit));
                }

                if (item.supplier_id !== undefined && item.supplier_id !== null) {
                    option.setAttribute('data-supplier-id', String(item.supplier_id));
                }

                if (item.supplier_name !== undefined && item.supplier_name !== null) {
                    option.setAttribute('data-supplier-name', String(item.supplier_name));
                }

                if (selected && String(selected.value) === option.value) {
                    option.selected = true;
                    selectedIncluded = true;
                }

                select.appendChild(option);
            });

            if (selected && !selectedIncluded) {
                var selectedOption = document.createElement('option');
                selectedOption.value = String(selected.value);
                selectedOption.textContent = selected.text;
                selectedOption.setAttribute('data-search-text', selected.searchText || selected.text);

                if (selected.currentStock !== null) {
                    selectedOption.setAttribute('data-current-stock', selected.currentStock);
                }

                if (selected.unit !== null) {
                    selectedOption.setAttribute('data-unit', selected.unit);
                }

                if (selected.supplierId !== null) {
                    selectedOption.setAttribute('data-supplier-id', selected.supplierId);
                }

                if (selected.supplierName !== null) {
                    selectedOption.setAttribute('data-supplier-name', selected.supplierName);
                }

                selectedOption.selected = true;
                select.appendChild(selectedOption);
            }
        }

        async function searchRemoteOptions(query) {
            var selected = selectedSnapshot();
            var sequence = ++searchSequence;
            var normalizedQuery = String(query || '').trim();

            var dependent = dependentSelector ? document.querySelector(dependentSelector) : null;
            var dependentValue = dependent ? dependent.value : '';

            if (dependentSelector && !dependentValue) {
                rebuildRemoteOptions([], selected);
                renderSearchableOptions(normalizedQuery);
                return;
            }

            if (normalizedQuery !== '' && normalizedQuery.length < remoteMinLength) {
                renderSearchableOptions(normalizedQuery);
                return;
            }

            search.setAttribute('aria-busy', 'true');

            try {
                var url = remoteUrl +
                    (remoteUrl.indexOf('?') === -1 ? '?' : '&') +
                    'q=' + encodeURIComponent(query) +
                    (remoteMode ? '&mode=' + encodeURIComponent(remoteMode) : '') +
                    (dependentParam && dependentValue
                        ? '&' + encodeURIComponent(dependentParam) + '=' + encodeURIComponent(dependentValue)
                        : '');

                var response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    return;
                }

                var payload = await response.json();

                if (sequence !== searchSequence) {
                    return;
                }

                rebuildRemoteOptions(
                    payload && Array.isArray(payload.items) ? payload.items : [],
                    selected
                );
                defaultOptionsLoaded = normalizedQuery === '';
                renderSearchableOptions(normalizedQuery);
            } catch (error) {
                // Keep the current selected value if remote search temporarily fails.
            } finally {
                if (sequence === searchSequence) {
                    search.removeAttribute('aria-busy');
                }
            }
        }

        function openSearchableDropdown(focusSearch) {
            if (select.disabled) {
                return;
            }

            isOpen = true;
            wrapper.classList.add('is-open');
            panel.hidden = false;
            trigger.setAttribute('aria-expanded', 'true');
            renderSearchableOptions(search.value);

            if (remoteUrl && !defaultOptionsLoaded) {
                searchRemoteOptions('');
            }

            if (focusSearch) {
                window.requestAnimationFrame(function () {
                    search.focus();
                });
            }
        }

        select._searchableTrigger = trigger;
        select._syncSearchableDisplay = syncSearchableTrigger;
        select._setSearchableDisabled = function (disabled) {
            trigger.disabled = disabled;
            search.disabled = disabled;
            wrapper.classList.toggle('is-disabled', disabled);

            if (disabled) {
                closeSearchableDropdown(false);
            }
        };
        select._refreshSearchable = function () {
            syncSearchableTrigger();
            renderSearchableOptions(search.value);
        };
        select._remoteSearch = searchRemoteOptions;

        trigger.addEventListener('click', function () {
            if (isOpen) {
                closeSearchableDropdown(false);
            } else {
                openSearchableDropdown(true);
            }
        });

        trigger.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ' || event.key === 'ArrowDown') {
                event.preventDefault();
                openSearchableDropdown(true);
            } else if (event.key === 'Escape') {
                closeSearchableDropdown(false);
            }
        });

        search.addEventListener('input', function () {
            window.clearTimeout(searchTimer);
            var query = search.value.trim();

            if (!remoteUrl) {
                renderSearchableOptions(query);
                return;
            }

            searchTimer = window.setTimeout(function () {
                searchRemoteOptions(query);
            }, 250);
        });

        search.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                event.preventDefault();
                closeSearchableDropdown(true);
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                var firstOption = options.querySelector('.app-searchable-select-option');

                if (firstOption) {
                    firstOption.focus();
                }
            }
        });

        options.addEventListener('keydown', function (event) {
            var current = event.target.closest('.app-searchable-select-option');

            if (!current) {
                return;
            }

            var optionButtons = Array.from(options.querySelectorAll('.app-searchable-select-option'));
            var currentIndex = optionButtons.indexOf(current);

            if (event.key === 'ArrowDown' && optionButtons[currentIndex + 1]) {
                event.preventDefault();
                optionButtons[currentIndex + 1].focus();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();

                if (optionButtons[currentIndex - 1]) {
                    optionButtons[currentIndex - 1].focus();
                } else {
                    search.focus();
                }
            } else if (event.key === 'Escape') {
                event.preventDefault();
                closeSearchableDropdown(true);
            }
        });

        select.addEventListener('change', function () {
            syncSearchableTrigger();
            renderSearchableOptions(search.value);
        });

        document.addEventListener('click', function (event) {
            if (isOpen && !wrapper.contains(event.target)) {
                closeSearchableDropdown(false);
            }
        });

        syncSearchableTrigger();
        renderSearchableOptions('');
    }

    // Frontend helper ni para initialize searchable selects; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
    function initializeSearchableSelects(container) {
        if (!container) {
            return;
        }

        container.querySelectorAll('select[data-searchable-select]').forEach(function (select) {
            initializeSearchableSelect(select);
        });
    }

    // Frontend helper ni para filter adjustment products; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
    function filterAdjustmentProducts(supplierSelect, preserveSelection) {
        if (!supplierSelect) {
            return;
        }

        var targetSelector = supplierSelect.getAttribute('data-product-target');
        var productSelect = targetSelector ? document.querySelector(targetSelector) : null;

        if (!productSelect) {
            return;
        }

        var hasSupplierScope = supplierSelect.value !== '';

        if (productSelect.getAttribute('data-search-url')) {
            productSelect.disabled = !hasSupplierScope;

            if (typeof productSelect._setSearchableDisabled === 'function') {
                productSelect._setSearchableDisabled(!hasSupplierScope);
            }

            if (productSelect._searchableInput) {
                productSelect._searchableInput.disabled = !hasSupplierScope;
                productSelect._searchableInput.placeholder = hasSupplierScope
                    ? (productSelect.getAttribute('data-search-placeholder') || 'Search products...')
                    : 'Select supplier first';
            }

            if (preserveSelection && productSelect.value) {
                updateAdjustmentPreview(productSelect);
                return;
            }

            productSelect.value = '';

            if (productSelect._searchableInput) {
                productSelect._searchableInput.value = '';
            }

            if (typeof productSelect._syncSearchableDisplay === 'function') {
                productSelect._syncSearchableDisplay();
            }

            Array.from(productSelect.options).forEach(function (option, index) {
                if (index > 0) {
                    option.remove();
                }
            });

            updateAdjustmentPreview(productSelect);

            if (
                hasSupplierScope &&
                typeof productSelect._remoteSearch === 'function'
            ) {
                productSelect._remoteSearch('');
            }

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

        updateAdjustmentPreview(productSelect);
    }

    // Frontend helper ni para update adjustment preview; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
    function updateAdjustmentPreview(productSelect) {
        if (!productSelect) {
            return;
        }

        var modalBody = productSelect.closest('.app-modal-body');

        if (!modalBody) {
            return;
        }

        var snapshot = modalBody.querySelector('[data-adjustment-snapshot]');
        var systemStockNode = modalBody.querySelector('[data-adjustment-system-stock]');
        var actualInput = modalBody.querySelector('[data-adjustment-actual]');
        var unitNodes = modalBody.querySelectorAll('[data-adjustment-unit], [data-adjustment-unit-copy]');
        var varianceNode = modalBody.querySelector('[data-adjustment-variance]');
        var varianceLabel = modalBody.querySelector('[data-adjustment-variance-label]');
        var selectedOption = productSelect.options[productSelect.selectedIndex];
        var hasProduct = selectedOption && selectedOption.value !== '';
        var currentStock = hasProduct
            ? parseInt(selectedOption.getAttribute('data-current-stock'), 10)
            : NaN;
        var unit = hasProduct
            ? (selectedOption.getAttribute('data-unit') || 'unit')
            : '';

        if (systemStockNode) {
            systemStockNode.textContent = hasProduct && !Number.isNaN(currentStock)
                ? String(currentStock)
                : '—';
        }

        unitNodes.forEach(function (node) {
            node.textContent = unit;
        });

        if (snapshot) {
            snapshot.classList.toggle('is-empty', !hasProduct);
        }

        if (actualInput) {
            actualInput.disabled = !hasProduct;

            if (!hasProduct) {
                actualInput.value = '';
            }
        }

        updateAdjustmentVariance(actualInput);
    }

    // Frontend helper ni para update adjustment variance; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
    function updateAdjustmentVariance(actualInput) {
        if (!actualInput) {
            return;
        }

        var modalBody = actualInput.closest('.app-modal-body');

        if (!modalBody) {
            return;
        }

        var productSelect = modalBody.querySelector('[data-adjustment-product]');
        var varianceNode = modalBody.querySelector('[data-adjustment-variance]');
        var varianceLabel = modalBody.querySelector('[data-adjustment-variance-label]');
        var selectedOption = productSelect && productSelect.selectedIndex >= 0
            ? productSelect.options[productSelect.selectedIndex]
            : null;

        var currentStock = selectedOption && selectedOption.value !== ''
            ? parseInt(selectedOption.getAttribute('data-current-stock'), 10)
            : NaN;
        var actualRaw = actualInput.value.trim();
        var actualStock = /^\d+$/.test(actualRaw) ? parseInt(actualRaw, 10) : NaN;

        var form = actualInput.closest('form[data-modal-form]');
        var submitButton = form ? form.querySelector('button[type="submit"]') : null;

        if (!varianceNode || !varianceLabel || Number.isNaN(currentStock) || Number.isNaN(actualStock)) {
            if (varianceNode) {
                varianceNode.textContent = '—';
                varianceNode.classList.remove('text-success', 'text-danger', 'text-body-secondary');
            }

            if (varianceLabel) {
                varianceLabel.textContent = selectedOption && selectedOption.value !== ''
                    ? 'Enter physical count'
                    : 'Select a product';
            }

            if (submitButton) {
                submitButton.disabled = true;
            }

            return;
        }

        var variance = actualStock - currentStock;

        if (submitButton) {
            submitButton.disabled = variance === 0;
        }
        varianceNode.textContent = variance > 0 ? '+' + variance : String(variance);
        varianceNode.classList.remove('text-success', 'text-danger', 'text-body-secondary');

        if (variance > 0) {
            varianceNode.classList.add('text-success');
            varianceLabel.textContent = 'Physical count is higher';
        } else if (variance < 0) {
            varianceNode.classList.add('text-danger');
            varianceLabel.textContent = 'Physical count is lower';
        } else {
            varianceNode.classList.add('text-body-secondary');
            varianceLabel.textContent = 'No difference — adjustment not needed';
        }
    }

    // Frontend helper ni para filter stock products; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
    function filterStockProducts(input) {
        var list = input ? input.closest('.app-modal-body') : null;

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
        var resultCount = list.querySelector('[data-stock-result-count]');

        if (empty) {
            empty.classList.toggle('d-none', visibleCount > 0);
        }

        if (resultCount) {
            resultCount.textContent = String(visibleCount);
        }
    }

    // Frontend helper ni para enhance feedback; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
    function enhanceFeedback(container) {
        if (!container) {
            return;
        }

        initializeSearchableSelects(container);

        container.querySelectorAll('[data-adjustment-supplier]').forEach(function (supplierSelect) {
            filterAdjustmentProducts(supplierSelect, true);
        });

        container.querySelectorAll('[data-adjustment-product]').forEach(function (productSelect) {
            updateAdjustmentPreview(productSelect);
        });

        container.querySelectorAll('.alert-danger, .alert-warning').forEach(function (alert) {
            alert.classList.add('app-feedback-alert');
            alert.setAttribute('role', 'alert');

        });

        var firstAlert = container.querySelector('.alert-danger, .alert-warning');

        if (firstAlert) {
            firstAlert.setAttribute('tabindex', '-1');
            firstAlert.focus({ preventScroll: true });
        }
    }

    // Frontend helper ni para set sidebar open; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
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

    // Frontend helper ni para render table cell; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
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

        if (renderType === 'stock_value') {
            return '<span class="app-table-number-badge app-table-badge-info">' +
                escapeHtml(raw === '' ? '0' : raw) +
            '</span>';
        }

        if (renderType === 'activity_action') {
            var activityClass = 'app-table-badge-secondary';
            var activityLabel = raw.replace(/_/g, ' ');

            if (/stock_in|created|login|success/i.test(normalized)) {
                activityClass = 'app-table-badge-success';
            } else if (/stock_out|deleted|logout|failed|error/i.test(normalized)) {
                activityClass = 'app-table-badge-danger';
            } else if (/adjust|updated|edit|change/i.test(normalized)) {
                activityClass = 'app-table-badge-warning';
            } else if (/report|export|view/i.test(normalized)) {
                activityClass = 'app-table-badge-info';
            }

            return '<span class="badge rounded-pill app-table-badge ' + activityClass + '">' +
                escapeHtml(activityLabel) +
            '</span>';
        }

        if (renderType === 'product_stock') {
            var stockParts = raw.split('|');
            var productStockRaw = stockParts[0] === '' ? '0' : stockParts[0];
            var reorderRaw = stockParts.length > 1 && stockParts[1] !== '' ? stockParts[1] : '0';
            var productStock = Number(productStockRaw);
            var reorderLevel = Number(reorderRaw);
            var productStockClass = '';

            if (productStock <= 0) {
                productStockClass = 'app-table-stock-danger';
            } else if (productStock <= reorderLevel) {
                productStockClass = 'app-table-stock-warning';
            } else {
                productStockClass = 'app-table-stock-healthy';
            }

            return '<span class="app-table-number-badge ' + productStockClass + '">' +
                escapeHtml(productStockRaw) +
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
                // Keep raw server values for sorting/searching, then decorate only the visible cell.
                // This is more reliable across DataTables versions and prevents status/stock text
                // from disappearing when a custom display renderer is used.
                definition.createdCell = (function (resolvedRenderType) {
                    return function (cell, cellData) {
                        cell.innerHTML = renderTableCell(resolvedRenderType, cellData);

                        if (resolvedRenderType === 'product_stock') {
                            var stockParts = String(cellData === null || cellData === undefined ? '' : cellData).split('|');
                            var stockValue = Number(stockParts[0] || 0);
                            var reorderValue = Number(stockParts.length > 1 ? stockParts[1] : 0);
                            var row = cell.closest('tr');

                            if (row) {
                                row.classList.toggle('app-table-row-danger', stockValue <= 0);
                                row.classList.toggle(
                                    'app-table-row-warning',
                                    stockValue > 0 && stockValue <= reorderValue
                                );
                                 row.classList.toggle(
                                    'app-table-row-healthy',
                                    stockValue > 0 && stockValue > reorderValue
                                );

                            }
                        }
                    };
                }(renderType));
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

                    card.querySelectorAll('[data-table-date-filter]').forEach(function (input) {
                        var name = input.getAttribute('data-table-date-filter');
                        var value = input.value;

                        if (name && value !== '' && !input.disabled) {
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

        function syncCustomDateRange() {
            var periodSelect = card.querySelector('[data-table-filter="period"]');
            var customRange = card.querySelector('[data-table-custom-range]');
            var dateInputs = card.querySelectorAll('[data-table-date-filter]');
            var dateError = card.querySelector('[data-table-date-range-error]');

            if (!periodSelect || !customRange) {
                return true;
            }

            var isCustom = periodSelect.value === 'custom';
            customRange.classList.toggle('d-none', !isCustom);
            customRange.setAttribute('aria-hidden', String(!isCustom));

            dateInputs.forEach(function (input) {
                input.disabled = !isCustom;
            });

            if (!isCustom) {
                if (dateError) {
                    dateError.textContent = '';
                    dateError.classList.add('d-none');
                }
                return true;
            }

            var fromInput = card.querySelector('[data-table-date-filter="date_from"]');
            var toInput = card.querySelector('[data-table-date-filter="date_to"]');
            var from = fromInput ? fromInput.value : '';
            var to = toInput ? toInput.value : '';
            var message = '';

            if ((from && !to) || (!from && to)) {
                message = 'Choose both a start and end date.';
            } else if (from && to && from > to) {
                message = 'The end date must be on or after the start date.';
            }

            if (dateError) {
                dateError.textContent = message;
                dateError.classList.toggle('d-none', message === '');
            }

            return message === '' && (!from || !to ? false : true);
        }

        // Frontend helper ni para update toolbar state; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
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
                if (select.getAttribute('data-table-filter') === 'period') {
                    var validDateRange = syncCustomDateRange();
                    updateToolbarState();

                    if (select.value === 'custom') {
                        return;
                    }

                    if (!validDateRange) {
                        return;
                    }
                }

                updateToolbarState();
                dataTable.ajax.reload(null, true);
            });
        });

        card.querySelectorAll('[data-table-date-filter]').forEach(function (input) {
            input.addEventListener('input', function () {
                var validDateRange = syncCustomDateRange();
                updateToolbarState();

                if (validDateRange) {
                    dataTable.ajax.reload(null, true);
                }
            });
        });

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                card.querySelectorAll('[data-table-filter]').forEach(function (select) {
                    select.value = '';
                });

                card.querySelectorAll('[data-table-date-filter]').forEach(function (input) {
                    input.value = '';
                });

                if (searchInput) {
                    searchInput.value = '';
                }

                syncCustomDateRange();

                updateToolbarState();
                dataTable.search('').draw();
            });
        }

        syncCustomDateRange();
        updateToolbarState();
    });

    async function loadModal(url) {
        if (!modalElement || !modalContent) {
            window.location.href = url;
            return;
        }

        modalContent.innerHTML =
            '<div class="app-modal-body text-center py-5">' +
                '<div class="spinner-border" role="status">' +
                    '<span class="visually-hidden">Loading...</span>' +
                '</div>' +
                '<div class="small text-body-secondary mt-3">Loading action...</div>' +
            '</div>';

        showActionModal();

        try {
            var response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.redirected) {
                // Fetch follows CodeIgniter redirects automatically. The redirected GET can consume
                // flashdata before the browser performs its own navigation, so preserve the rendered
                // success modal from that response and restore it on the destination page.
                var redirectedHtml = await response.text();

                try {
                    var redirectedDocument = new DOMParser().parseFromString(redirectedHtml, 'text/html');
                    var redirectedSuccess = redirectedDocument.querySelector('[data-flash-success-template]');

                    if (redirectedSuccess && window.sessionStorage) {
                        window.sessionStorage.setItem(
                            'app-pending-success-modal',
                            redirectedSuccess.innerHTML
                        );
                    }
                } catch (redirectParseError) {
                    // Navigation should still continue even if the success payload cannot be extracted.
                }

                window.location.href = response.url;
                return;
            }

            var html = await response.text();

            if (!response.ok) {
                showProblem(responseProblem(response, html, 'load this action'));
                return;
            }

            if (response.headers.get('X-Modal-Close') === '1') {
                hideActionModal(true);

                if (response.headers.get('X-Page-Reload') === '1') {
                    window.location.reload();
                }

                return;
            }

            modalContent.innerHTML = html;
            enhanceFeedback(modalContent);
        } catch (error) {
            showProblem(statusProblem(0, 'load this action'));
        }
    }

    async function loadSupplierProducts(select) {
        var container = document.querySelector('[data-stock-products]');

        if (!container) {
            return;
        }

        var supplierScope = select.value;
        var baseUrl = select.getAttribute('data-products-url');
        var mode = select.getAttribute('data-stock-mode') === 'stock_out'
            ? 'stock_out'
            : 'stock_in';

        if (!supplierScope) {
            container.innerHTML =
                '<div class="app-stock-product-empty">' +
                    '<i class="bi bi-truck d-block fs-3 mb-2"></i>' +
                    'Select a supplier or Unassigned Products to load products.' +
                '</div>';
            return;
        }

        container.innerHTML =
            '<div class="app-stock-product-empty">' +
                '<div class="spinner-border spinner-border-sm me-2" role="status">' +
                    '<span class="visually-hidden">Loading...</span>' +
                '</div>' +
                'Loading products for the selected scope...' +
            '</div>';

        try {
            var response = await fetch(
                baseUrl.replace(/\/$/, '') + '/' + encodeURIComponent(supplierScope) +
                    '?mode=' + encodeURIComponent(mode),
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
                    responseProblem(response, html, 'load products for this supplier/product scope')
                );
                return;
            }

            container.innerHTML = html;
            enhanceFeedback(container);
        } catch (error) {
            showInlineProblem(
                container,
                statusProblem(0, 'load products for this supplier/product scope')
            );
        }
    }

    // Frontend helper ni para update stock preview; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
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

    // Frontend helper ni para confirmation variant class; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
    function confirmationVariantClass(variant) {
        return ['primary', 'success', 'warning', 'danger'].indexOf(variant) !== -1
            ? variant
            : 'primary';
    }

    // Frontend helper ni para show form confirmation; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
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
        var message = form.getAttribute('data-confirm-message') || '';
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
            '<div class="app-modal-header app-modal-header-' + escapeHtml(variant) + '">' +
                '<div class="app-modal-heading">' +
                    '<span class="app-modal-icon app-modal-icon-' + escapeHtml(variant) + '">' +
                        '<i class="bi ' + escapeHtml(icon) + '"></i>' +
                    '</span>' +
                    '<div class="app-modal-heading-copy">' +
                        '<div class="app-modal-eyebrow">Confirmation</div>' +
                        '<h2 class="app-modal-title" id="action-modal-title">' + escapeHtml(title) + '</h2>' +
                    '</div>' +
                '</div>' +
                '<button type="button" class="btn-close app-modal-close" data-confirm-cancel aria-label="Cancel"></button>' +
            '</div>' +
            '<div class="app-modal-body">' +
                '<div class="app-confirmation-review app-confirmation-review-' + escapeHtml(variant) + '">' +
                    '<div class="app-confirmation-review-icon app-confirmation-review-icon-' + escapeHtml(variant) + '" aria-hidden="true">' +
                        '<i class="bi ' + escapeHtml(icon) + '"></i>' +
                    '</div>' +
                    '<div class="min-w-0">' +
                        '<div class="app-confirmation-review-title">Please review this action</div>' +
                        (message
                            ? '<p class="app-confirmation-review-message mb-0">' + escapeHtml(message) + '</p>'
                            : '') +
                        (impact
                            ? '<div class="app-confirmation-impact"><div class="app-confirmation-impact-label">What happens next</div>' + escapeHtml(impact) + '</div>'
                            : '') +
                        (assist
                            ? '<div class="app-confirmation-assist"><i class="bi bi-info-circle me-2" aria-hidden="true"></i><span>' + escapeHtml(assist) + '</span></div>'
                            : '') +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="app-modal-footer">' +
                '<div class="app-modal-footer-actions">' +
                    '<button type="button" class="btn btn-outline-secondary" data-confirm-cancel>Cancel</button>' +
                    '<button type="button" class="btn btn-' + escapeHtml(variant) + '" data-confirm-proceed>' +
                        '<i class="bi ' + escapeHtml(icon) + ' me-1"></i>' + escapeHtml(label) +
                    '</button>' +
                '</div>' +
            '</div>';

        form.appendChild(stage);

        var proceed = stage.querySelector('[data-confirm-proceed]');

        if (proceed) {
            proceed.focus();
        }
    }

    // Frontend helper ni para restore form confirmation; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
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
            var formData = new FormData(form);

            if (submitButton && submitButton.name) {
                formData.set(submitButton.name, submitButton.value || '');
            }

            var response = await fetch(form.action, {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.redirected) {
                var redirectedHtml = await response.text();

                try {
                    var redirectedDocument = new DOMParser().parseFromString(redirectedHtml, 'text/html');
                    var redirectedSuccess = redirectedDocument.querySelector('[data-flash-success-template]');

                    if (redirectedSuccess && window.sessionStorage) {
                        window.sessionStorage.setItem(
                            'app-pending-success-modal',
                            redirectedSuccess.innerHTML
                        );
                    }
                } catch (redirectParseError) {
                    // Keep navigation working even when there is no success payload to preserve.
                }

                window.location.href = response.url;
                return;
            }

            var html = await response.text();

            if (!response.ok) {
                var body = form.querySelector('.app-modal-body') || form;
                showInlineProblem(
                    body,
                    responseProblem(response, html, 'save these changes')
                );
                return;
            }

            if (response.headers.get('X-Modal-Close') === '1') {
                hideActionModal(true);

                if (response.headers.get('X-Page-Reload') === '1') {
                    window.location.reload();
                }

                return;
            }

            modalContent.innerHTML = html;
            enhanceFeedback(modalContent);
        } catch (error) {
            var formBody = form.querySelector('.app-modal-body') || form;
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

    // Frontend helper ni para filename from disposition; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
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

    // Frontend helper ni para build report export url; caller naa ra sa assets/js/app.js ug gi-trigger sa data-* hooks gikan application/views/.
    function buildReportExportUrl(baseUrl) {
        if (!baseUrl) {
            return '';
        }

        var url;

        try {
            url = new URL(baseUrl, window.location.href);
        } catch (error) {
            return baseUrl;
        }

        var table = document.getElementById('report-table');
        var card = table ? table.closest('.app-table-card') : null;

        if (!card) {
            return url.toString();
        }

        var searchInput = card.querySelector('[data-table-search]');
        var search = searchInput ? searchInput.value.trim() : '';

        if (search !== '') {
            url.searchParams.set('search', search);
        }

        var periodSelect = card.querySelector('[data-table-filter="period"]');
        var dateError = card.querySelector('[data-table-date-range-error]');

        if (periodSelect && periodSelect.value === 'custom') {
            var dateFrom = card.querySelector('[data-table-date-filter="date_from"]');
            var dateTo = card.querySelector('[data-table-date-filter="date_to"]');
            var fromValue = dateFrom ? dateFrom.value : '';
            var toValue = dateTo ? dateTo.value : '';
            var dateMessage = '';

            if (!fromValue || !toValue) {
                dateMessage = 'Choose both a start and end date.';
            } else if (fromValue > toValue) {
                dateMessage = 'The end date must be on or after the start date.';
            }

            if (dateError) {
                dateError.textContent = dateMessage;
                dateError.classList.toggle('d-none', dateMessage === '');
            }

            if (dateMessage !== '') {
                return '';
            }
        }

        card.querySelectorAll('[data-table-filter]').forEach(function (select) {
            var name = select.getAttribute('data-table-filter');
            var value = select.value;

            if (name && value !== '') {
                url.searchParams.set('table_filters[' + name + ']', value);
            }
        });

        card.querySelectorAll('[data-table-date-filter]').forEach(function (input) {
            var name = input.getAttribute('data-table-date-filter');
            var value = input.value;

            if (name && value !== '' && !input.disabled) {
                url.searchParams.set('table_filters[' + name + ']', value);
            }
        });

        return url.toString();
    }

    async function downloadReport(button) {
        var url = buildReportExportUrl(button.getAttribute('data-export-url'));

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

        var adjustmentActual = event.target.closest('[data-adjustment-actual]');

        if (adjustmentActual) {
            updateAdjustmentVariance(adjustmentActual);
        }
    });

    document.addEventListener('change', function (event) {
        var supplierSelect = event.target.closest('[data-stock-supplier]');

        if (supplierSelect) {
            loadSupplierProducts(supplierSelect);
        }

        var adjustmentSupplier = event.target.closest('[data-adjustment-supplier]');

        if (adjustmentSupplier) {
            filterAdjustmentProducts(adjustmentSupplier);
        }

        var adjustmentProduct = event.target.closest('[data-adjustment-product]');

        if (adjustmentProduct) {
            updateAdjustmentPreview(adjustmentProduct);
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

        var printReport = event.target.closest('[data-report-print]');

        if (printReport) {
            event.preventDefault();

            var printUrl = buildReportExportUrl(printReport.getAttribute('href'));

            if (printUrl) {
                window.open(printUrl, '_blank', 'noopener');
            }

            return;
        }

        var copyTemporaryPassword = event.target.closest('[data-copy-temporary-password]');

        if (copyTemporaryPassword) {
            event.preventDefault();

            var passwordNode = document.querySelector('[data-temporary-password]');
            var passwordText = passwordNode ? passwordNode.textContent.trim() : '';

            if (passwordText !== '') {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(passwordText).then(function () {
                        copyTemporaryPassword.innerHTML =
                            '<i class="bi bi-check-lg me-1" aria-hidden="true"></i>Copied';
                    }).catch(function () {
                        window.prompt('Copy temporary password:', passwordText);
                    });
                } else {
                    window.prompt('Copy temporary password:', passwordText);
                }
            }

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

        if (modalState.open && event.target === modalElement) {
            event.preventDefault();
            hideActionModal(true);
            return;
        }

        if (event.target.closest('[data-modal-close]') && modalState.open) {
            event.preventDefault();
            hideActionModal(true);
        }
    });

    document.addEventListener('submit', function (event) {
        var form = event.target.closest('form[data-modal-form]');

        if (!form) {
            return;
        }

        event.preventDefault();

        var submitButton = event.submitter || form.querySelector('button[type="submit"]');

        if (form.getAttribute('data-confirm-required') === '1') {
            showFormConfirmation(form, submitButton);
            return;
        }

        submitModalForm(form, submitButton);
    });

    enhanceFeedback(document);

    var successTemplate = document.querySelector('[data-flash-success-template]');
    var pendingSuccessHtml = '';

    try {
        pendingSuccessHtml = window.sessionStorage
            ? (window.sessionStorage.getItem('app-pending-success-modal') || '')
            : '';

        if (pendingSuccessHtml && window.sessionStorage) {
            window.sessionStorage.removeItem('app-pending-success-modal');
        }
    } catch (storageError) {
        pendingSuccessHtml = '';
    }

    var passwordPrompt = document.querySelector('[data-password-change-prompt-url]');

    if ((pendingSuccessHtml || successTemplate) && modalContent) {
        window.requestAnimationFrame(function () {
            modalContent.innerHTML = pendingSuccessHtml || successTemplate.innerHTML;
            enhanceFeedback(modalContent);
            showActionModal();
        });
    } else if (passwordPrompt) {
        window.requestAnimationFrame(function () {
            loadModal(passwordPrompt.getAttribute('data-password-change-prompt-url'));
        });
    }
});
