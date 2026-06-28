(function () {
    'use strict';

    var config = window.agocontactForm || {};
    var form = document.querySelector('.ago-contact-form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var btn = form.querySelector('.ago-submit');
        var status = form.querySelector('.ago-form-status');

        var data = {};
        var inputs = form.querySelectorAll('input, textarea, select');
        for (var i = 0; i < inputs.length; i++) {
            var el = inputs[i];
            if (!el.name) continue;
            if (el.type === 'checkbox') {
                data[el.name] = el.checked ? '1' : '';
            } else {
                data[el.name] = el.value;
            }
        }

        if (config.turnstile) {
            var tsInput = form.querySelector('[name="cf-turnstile-response"]');
            if (tsInput) data['cf-turnstile-response'] = tsInput.value;
        }

        btn.disabled = true;
        btn.textContent = config.i18n ? config.i18n.sending : 'Sending...';
        if (status) status.style.display = 'none';

        fetch(config.ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, data: j }; }); })
        .then(function (res) {
            if (res.ok && res.data.ok) {
                if (status) {
                    status.className = 'ago-form-status success';
                    status.textContent = (config.i18n && config.i18n.success) || 'Message sent!';
                    status.style.display = 'block';
                }
                form.reset();
                if (config.turnstile && window.turnstile) {
                    window.turnstile.reset();
                }
            } else {
                if (status) {
                    status.className = 'ago-form-status error';
                    status.textContent = (res.data && res.data.error) || (config.i18n && config.i18n.error) || 'Error';
                    status.style.display = 'block';
                }
            }
        })
        .catch(function () {
            if (status) {
                status.className = 'ago-form-status error';
                status.textContent = (config.i18n && config.i18n.error) || 'Error';
                status.style.display = 'block';
            }
        })
        .finally(function () {
            btn.disabled = false;
            btn.textContent = btn.getAttribute('data-original') || 'Send Message';
        });
    });

    var submitBtn = form.querySelector('.ago-submit');
    if (submitBtn) submitBtn.setAttribute('data-original', submitBtn.textContent);

})();
