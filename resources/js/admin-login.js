const passwordInput = document.getElementById('password');
const passwordToggle = document.querySelector('[data-password-toggle]');
const helpTrigger = document.querySelector('[data-auth-help]');
const helpPanel = document.getElementById('auth-help-panel');
const loginForm = document.getElementById('admin-login-form');
const submitButton = document.getElementById('admin-login-submit');

passwordToggle?.addEventListener('click', () => {
    const show = passwordInput?.type === 'password';
    if (!passwordInput) return;
    passwordInput.type = show ? 'text' : 'password';
    passwordToggle.classList.toggle('is-visible', show);
    passwordToggle.setAttribute('aria-pressed', String(show));
    passwordToggle.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
});

helpTrigger?.addEventListener('click', () => {
    if (!helpPanel) return;
    helpPanel.hidden = !helpPanel.hidden;
});

const renderLoginError = (message) => {
    let alert = document.querySelector('.auth-alert--error');
    if (!alert) {
        alert = document.createElement('div');
        alert.className = 'auth-alert auth-alert--error';
        alert.setAttribute('role', 'alert');
        alert.innerHTML = '<span class="auth-alert__icon">!</span><div><strong>Login belum berhasil</strong><span data-auth-error></span></div>';
        loginForm?.before(alert);
    }
    const output = alert.querySelector('[data-auth-error]') || alert.querySelector('span:last-child');
    if (output) output.textContent = message;
};

loginForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!loginForm.checkValidity()) { loginForm.reportValidity(); return; }
    submitButton?.classList.add('is-loading');
    if (submitButton) { submitButton.disabled = true; submitButton.setAttribute('aria-busy', 'true'); }

    try {
        const response = await fetch(loginForm.action, {
            method: 'POST',
            body: new FormData(loginForm),
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            const errors = data.errors || {};
            const first = Object.values(errors).flat()[0] || data.message || 'Login belum berhasil.';
            renderLoginError(first);
            return;
        }
        sessionStorage.setItem('ettra-login-boundary', '1');
        window.location.replace(data.redirect || '/admin/dashboard');
    } catch (error) {
        renderLoginError('Tidak dapat menghubungi server. Periksa koneksi lalu coba kembali.');
    } finally {
        submitButton?.classList.remove('is-loading');
        if (submitButton) { submitButton.disabled = false; submitButton.removeAttribute('aria-busy'); }
    }
});

window.addEventListener('pageshow', (event) => {
    if (event.persisted) window.location.reload();
});
