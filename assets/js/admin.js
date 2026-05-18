/* aGo Contact Admin JS */
(function () {
    'use strict';

    var $ = document.querySelector.bind(document);
    var $$ = document.querySelectorAll.bind(document);

    var restUrl = (window.agoContact || {}).restUrl || '';
    var nonce   = (window.agoContact || {}).nonce || '';

    /* ───── Settings Page ───── */

    var saveBtn = $('#ago-save-settings');
    if (saveBtn) {
        initSettingsPage();
    }

    function initSettingsPage() {
        // Auto-reply toggle
        var arToggle = $('#ago-autoreply-enabled');
        if (arToggle) {
            arToggle.addEventListener('change', function () {
                $$('.ago-autoreply-fields').forEach(function (el) {
                    el.style.display = arToggle.checked ? '' : 'none';
                });
            });
        }

        // Captcha type radio toggle
        $$('input[name="ago-captcha-type"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                var isTurnstile = this.value === 'turnstile';
                $$('.ago-turnstile-fields').forEach(function (el) {
                    el.style.display = isTurnstile ? '' : 'none';
                });
            });
        });

        // Save
        saveBtn.addEventListener('click', function () {
            var data = {
                fields: {},
                theme: '',
                notification_email: ($('#ago-notification-email') || {}).value || '',
                autoreply_enabled: ($('#ago-autoreply-enabled') || {}).checked || false,
                autoreply_subject: ($('#ago-autoreply-subject') || {}).value || '',
                autoreply_body: ($('#ago-autoreply-body') || {}).value || '',
                captcha_type: (function() { var r = $('input[name="ago-captcha-type"]:checked'); return r ? r.value : 'none'; })(),
                turnstile_site_key: ($('#ago-turnstile-site-key') || {}).value || '',
                turnstile_secret_key: ($('#ago-turnstile-secret-key') || {}).value || '',
                rate_limit: parseInt(($('#ago-rate-limit') || {}).value || '5', 10),
                success_message: ($('#ago-success-message') || {}).value || '',
                gdpr_text: ($('#ago-gdpr-text') || {}).value || '',
                department_options: ($('#ago-department-options') || {}).value || '',
            };

            // Collect fields
            var fieldNames = ['name', 'email', 'phone', 'subject', 'company', 'department', 'message', 'gdpr'];
            fieldNames.forEach(function (key) {
                data.fields[key] = {
                    enabled: getFieldProp(key, 'enabled'),
                    required: getFieldProp(key, 'required'),
                    label: getFieldLabel(key),
                };
            });

            // Theme
            var themeInput = $('input[name="ago-theme"]:checked');
            data.theme = themeInput ? themeInput.value : 'modern';

            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving...';

            fetch(restUrl + '/settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify(data),
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.saved) {
                    showStatus('success', 'Settings saved successfully.');
                } else {
                    showStatus('error', 'Error saving settings.');
                }
            })
            .catch(function (err) {
                showStatus('error', 'Error: ' + err.message);
            })
            .finally(function () {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save Settings';
            });
        });
    }

    function getFieldProp(field, prop) {
        // Check hidden input first
        var hidden = $('input[type="hidden"][data-field="' + field + '"][data-prop="' + prop + '"]');
        if (hidden) return true;
        var cb = $('input[type="checkbox"][data-field="' + field + '"][data-prop="' + prop + '"]');
        return cb ? cb.checked : false;
    }

    function getFieldLabel(field) {
        var input = $('input[type="text"][data-field="' + field + '"][data-prop="label"]');
        return input ? input.value : '';
    }

    function showStatus(type, message) {
        var box = $('#ago-contact-status');
        if (!box) return;
        box.style.display = 'block';
        box.className = type;
        box.textContent = message;
        setTimeout(function () { box.style.display = 'none'; }, 3000);
    }

    /* ───── Submissions Page ───── */

    var subsList = $('#ago-submissions-list');
    if (subsList) {
        initSubmissionsPage();
    }

    function initSubmissionsPage() {
        var currentFilter = '';
        var currentPage = 1;

        // Filters
        $$('.ago-filter').forEach(function (btn) {
            btn.addEventListener('click', function () {
                $$('.ago-filter').forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                currentFilter = btn.getAttribute('data-status');
                currentPage = 1;
                loadSubmissions();
            });
        });

        // Export CSV
        var exportBtn = $('#ago-export-csv');
        if (exportBtn) {
            exportBtn.addEventListener('click', function () {
                window.location.href = restUrl + '/export?_wpnonce=' + nonce;
            });
        }

        // Modal close
        var modalClose = $('.ago-modal-close');
        if (modalClose) {
            modalClose.addEventListener('click', closeModal);
        }
        var modal = $('#ago-submission-modal');
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeModal();
            });
        }

        // Initial load
        loadSubmissions();

        function loadSubmissions() {
            var loading = subsList.querySelector('.ago-submissions-loading');
            var table = subsList.querySelector('table');
            var empty = subsList.querySelector('.ago-submissions-empty');

            if (loading) loading.style.display = 'block';
            if (table) table.style.display = 'none';
            if (empty) empty.style.display = 'none';

            var url = restUrl + '/submissions?page=' + currentPage;
            if (currentFilter) url += '&status=' + currentFilter;

            fetch(url, { headers: { 'X-WP-Nonce': nonce } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (loading) loading.style.display = 'none';
                var items = data.items || [];

                if (!items.length) {
                    if (empty) empty.style.display = 'block';
                    return;
                }

                var tbody = $('#ago-submissions-tbody');
                tbody.innerHTML = '';

                items.forEach(function (item) {
                    var tr = document.createElement('tr');
                    if (item.status === 'new') tr.classList.add('status-new');
                    tr.innerHTML =
                        '<td><span class="status-badge ' + esc(item.status) + '">' + esc(item.status) + '</span></td>' +
                        '<td>' + esc(item.name || ',') + '</td>' +
                        '<td>' + esc(item.email || ',') + '</td>' +
                        '<td>' + esc(item.subject || ',') + '</td>' +
                        '<td>' + esc(item.status) + '</td>' +
                        '<td>' + esc(item.created_at) + '</td>' +
                        '<td><button class="ago-view-btn" data-id="' + item.id + '">View</button> <button class="ago-delete-inline" data-id="' + item.id + '">Delete</button></td>';
                    tbody.appendChild(tr);
                });

                if (table) table.style.display = 'table';

                // Pagination
                renderPagination(data.pages, data.page);

                // Bind view buttons
                $$('.ago-view-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        openSubmission(parseInt(btn.getAttribute('data-id'), 10));
                    });
                });

                // Bind delete buttons
                $$('.ago-delete-inline').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        if (!confirm('Delete this submission?')) return;
                        deleteSubmission(parseInt(btn.getAttribute('data-id'), 10));
                    });
                });
            })
            .catch(function () {
                if (loading) loading.textContent = 'Error loading submissions.';
            });
        }

        function renderPagination(pages, current) {
            var container = $('#ago-pagination');
            if (!container) return;
            container.innerHTML = '';
            if (pages <= 1) return;
            for (var i = 1; i <= pages; i++) {
                var btn = document.createElement('button');
                btn.className = 'button' + (i === current ? ' active' : '');
                btn.textContent = i;
                btn.setAttribute('data-page', i);
                btn.addEventListener('click', function () {
                    currentPage = parseInt(this.getAttribute('data-page'), 10);
                    loadSubmissions();
                });
                container.appendChild(btn);
            }
        }

        function openSubmission(id) {
            fetch(restUrl + '/submissions/' + id, { headers: { 'X-WP-Nonce': nonce } })
            .then(function (r) { return r.json(); })
            .then(function (item) {
                var body = $('#ago-modal-body');
                body.innerHTML = '';
                var fields = ['name', 'email', 'phone', 'subject', 'company', 'department'];
                fields.forEach(function (f) {
                    if (item[f]) {
                        body.innerHTML += '<div class="ago-detail-row"><div class="ago-detail-label">' + f.toUpperCase() + '</div><div class="ago-detail-value">' + esc(item[f]) + '</div></div>';
                    }
                });
                body.innerHTML += '<div class="ago-detail-row"><div class="ago-detail-label">MESSAGE</div><div class="ago-detail-message">' + esc(item.message || '') + '</div></div>';
                body.innerHTML += '<div class="ago-detail-row"><div class="ago-detail-label">IP</div><div class="ago-detail-value">' + esc(item.ip_address || '') + '</div></div>';
                body.innerHTML += '<div class="ago-detail-row"><div class="ago-detail-label">DATE</div><div class="ago-detail-value">' + esc(item.created_at) + '</div></div>';

                // Set action buttons
                $('.ago-mark-replied').setAttribute('data-id', item.id);
                $('.ago-mark-spam').setAttribute('data-id', item.id);
                $('.ago-delete-sub').setAttribute('data-id', item.id);

                $('#ago-submission-modal').style.display = 'flex';

                // Refresh list to update read status
                loadSubmissions();
            });
        }

        // Modal action buttons
        var markReplied = $('.ago-mark-replied');
        if (markReplied) {
            markReplied.addEventListener('click', function () {
                updateStatus(parseInt(this.getAttribute('data-id'), 10), 'replied');
            });
        }
        var markSpam = $('.ago-mark-spam');
        if (markSpam) {
            markSpam.addEventListener('click', function () {
                updateStatus(parseInt(this.getAttribute('data-id'), 10), 'spam');
            });
        }
        var deleteSub = $('.ago-delete-sub');
        if (deleteSub) {
            deleteSub.addEventListener('click', function () {
                if (!confirm('Delete this submission?')) return;
                deleteSubmission(parseInt(this.getAttribute('data-id'), 10));
                closeModal();
            });
        }

        function updateStatus(id, status) {
            fetch(restUrl + '/submissions/' + id + '/status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify({ status: status }),
            })
            .then(function () {
                closeModal();
                loadSubmissions();
            });
        }

        function deleteSubmission(id) {
            fetch(restUrl + '/submissions/' + id, {
                method: 'DELETE',
                headers: { 'X-WP-Nonce': nonce },
            })
            .then(function () { loadSubmissions(); });
        }

        function closeModal() {
            var modal = $('#ago-submission-modal');
            if (modal) modal.style.display = 'none';
        }
    }

    function esc(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }

})();
