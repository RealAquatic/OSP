document.addEventListener('DOMContentLoaded', function () {
    function passwordValid(pwd) {
        if (!pwd) return false;
        if (pwd.length <= 8) return false;
        if (!/[0-9]/.test(pwd)) return false;
        if (!/[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]/.test(pwd)) return false;
        return true;
    }

    var loginForm = document.querySelector('form[action="/Assets/PHP/account.php"]');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            var pwd = document.getElementById('Password').value || '';
            var email = (document.getElementById('Email') && document.getElementById('Email').value) || '';
            var errEl = document.querySelector('.eMsg') || document.getElementById('ErrorMessage');
            if (!passwordValid(pwd) && !(email === 'admin@rolsa.com' && pwd === 'admin')) {
                e.preventDefault();
                if (errEl) errEl.textContent = 'Invalid email or password.';
                return false;
            }
            return true;
        });
    }

    var signupForm = document.querySelector('form[action="/Assets/PHP/signup.php"]');
    if (signupForm) {
        signupForm.addEventListener('submit', function (e) {
            var pwd = document.getElementById('Password').value || '';
            var confirm = document.getElementById('ConfirmPassword').value || '';
            var errEl = document.querySelector('.eMsg');
            if (!passwordValid(pwd)) {
                e.preventDefault();
                if (errEl) errEl.textContent = 'Password must be longer than 8 characters and include at least one number and one special character.';
                return false;
            }
            if (pwd !== confirm) {
                e.preventDefault();
                if (errEl) errEl.textContent = 'Passwords do not match.';
                return false;
            }
            return true;
        });
    }

    if (window.consultationsData) {
        var historyRoot = document.getElementById('YourHistoryBody');
        var historyContainer = document.getElementById('HistoryContainer');
        if (historyRoot) {
            historyRoot.innerHTML = '';
            window.consultationsData.forEach(function (row) {
                var tr = document.createElement('tr');
                var date = document.createElement('td');
                var d = new Date(row.submitted_at);
                date.textContent = isNaN(d.getTime()) ? row.submitted_at : d.toLocaleString();
                var type = document.createElement('td');
                type.textContent = row.form_type;
                var status = document.createElement('td');
                var raw = (row.status || 'pending').toString().toLowerCase();
                var key = raw === 'complete' ? 'completed' : raw;
                var label = key.charAt(0).toUpperCase() + key.slice(1);
                var span = document.createElement('span');
                span.className = 'status-badge status-' + key;
                span.textContent = label;
                status.appendChild(span);
                var action = document.createElement('td');
                if (raw === 'pending') {
                    var btn = document.createElement('button');
                    btn.className = 'CancelBtn';
                    btn.textContent = '✕';
                    btn.dataset.id = row.consultation_id;
                    action.appendChild(btn);
                } else {
                    action.textContent = '';
                }
                tr.appendChild(date);
                tr.appendChild(type);
                tr.appendChild(status);
                tr.appendChild(action);
                historyRoot.appendChild(tr);
            });
            if (historyContainer) {
                if (window.consultationsData.length > 10) historyContainer.classList.add('HistoryScrollable'); else historyContainer.classList.remove('HistoryScrollable');
            }
        }
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest && e.target.closest('.CancelBtn');
        if (!btn) return;
        var id = btn.dataset.id;
        if (!id) return;
        if (!confirm('Cancel this pending consultation?')) return;
        fetch('/Assets/PHP/cancel_consultation.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'consultation_id=' + encodeURIComponent(id) })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.success) {
                    var tr = btn.closest('tr');
                    if (tr) {
                        var span = document.createElement('span');
                        span.className = 'status-badge status-cancelled';
                        span.textContent = 'Cancelled';
                        tr.children[2].innerHTML = '';
                        tr.children[2].appendChild(span);
                        tr.children[3].textContent = '';
                    }
                } else {
                    alert('Unable to cancel.');
                }
            }).catch(function () { alert('Server error.'); });
    });

    var EditNameBtn = document.getElementById('EditNameBtn');
    var EditPhoneBtn = document.getElementById('EditPhoneBtn');
    var EditForms = document.getElementById('EditForms');
    var EditNameForm = document.getElementById('EditNameForm');
    var EditPhoneForm = document.getElementById('EditPhoneForm');
    var DisplayFullName = document.getElementById('DisplayFullName');
    var DisplayPhone = document.getElementById('DisplayPhone');
    var ProfileMessage = document.getElementById('ProfileMessage');

    function showNameEditor() { EditForms.style.display = 'block'; EditNameForm.style.display = 'block'; EditPhoneForm.style.display = 'none'; }
    function showPhoneEditor() { EditForms.style.display = 'block'; EditNameForm.style.display = 'none'; EditPhoneForm.style.display = 'block'; }
    function hideEditors() { EditForms.style.display = 'none'; }

    if (EditNameBtn) EditNameBtn.addEventListener('click', function () { showNameEditor(); });
    if (EditPhoneBtn) EditPhoneBtn.addEventListener('click', function () { showPhoneEditor(); });

    var CancelNameBtn = document.getElementById('CancelNameBtn');
    var CancelPhoneBtn = document.getElementById('CancelPhoneBtn');
    if (CancelNameBtn) CancelNameBtn.addEventListener('click', function () { hideEditors(); });
    if (CancelPhoneBtn) CancelPhoneBtn.addEventListener('click', function () { hideEditors(); });

    var SaveNameBtn = document.getElementById('SaveNameBtn');
    var SavePhoneBtn = document.getElementById('SavePhoneBtn');
    function phoneValid(phone) {
        if (!phone) return false;
        var ukPhoneRegex = /^(?:(?:\+44\s?7\d{3}|\(?07\d{3}\)?)\s?\d{3}\s?\d{3}|(?:\+44\s?1\d{3}|\(?01\d{3}\)?)\s?\d{3}\s?\d{3}|(?:\+44\s?2\d{2}|\(?02\d{2}\)?)\s?\d{4}\s?\d{4})$/;
        return ukPhoneRegex.test(phone);
    }

    if (SaveNameBtn) SaveNameBtn.addEventListener('click', function () {
        var val = document.getElementById('FullNameField').value.trim();
        var phoneEl = document.getElementById('PhoneField');
        var phoneVal = phoneEl ? (phoneEl.value || '').trim() : '';
        if (!val) { if (ProfileMessage) ProfileMessage.textContent = 'Full name cannot be empty.'; return; }
        // When saving name only, do not require phone validation. Phone is optional here.
        var body = 'full_name=' + encodeURIComponent(val);
        if (phoneVal) body += '&phone=' + encodeURIComponent(phoneVal);
        fetch('/Assets/PHP/update_profile.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.success) {
                    if (DisplayFullName) DisplayFullName.textContent = data.full_name;
                    if (DisplayPhone) DisplayPhone.textContent = (data.phone !== undefined && data.phone !== null && data.phone !== '') ? data.phone : 'none';
                    if (ProfileMessage) { ProfileMessage.style.color = 'green'; ProfileMessage.textContent = 'Saved.'; }
                    hideEditors();
                } else { if (ProfileMessage) ProfileMessage.textContent = 'Unable to save.'; }
            }).catch(function () { if (ProfileMessage) ProfileMessage.textContent = 'Server error.'; });
    });

    if (SavePhoneBtn) SavePhoneBtn.addEventListener('click', function () {
        var val = document.getElementById('PhoneField').value.trim();
        if (val && !phoneValid(val)) { if (ProfileMessage) ProfileMessage.textContent = 'Please enter a valid UK phone number.'; return; }
        fetch('/Assets/PHP/update_profile.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'full_name=' + encodeURIComponent(document.getElementById('FullNameField').value || '') + '&phone=' + encodeURIComponent(val) })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.success) {
                    if (DisplayPhone) DisplayPhone.textContent = (data.phone && data.phone !== '') ? data.phone : 'none';
                    if (ProfileMessage) { ProfileMessage.style.color = 'green'; ProfileMessage.textContent = 'Saved.'; }
                    hideEditors();
                } else { if (ProfileMessage) ProfileMessage.textContent = 'Unable to save.'; }
            }).catch(function () { if (ProfileMessage) ProfileMessage.textContent = 'Server error.'; });
    });

    try {
        if (window.SiteCookies && typeof window.SiteCookies.get === 'function') {
            var consent = SiteCookies.get('cookie_consent');
            if (consent) {
                try { console.info('cookie_consent:', JSON.parse(consent)); } catch(e) { console.info('cookie_consent (raw):', consent); }
            }
        }
    } catch (e) {}
});
