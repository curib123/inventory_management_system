
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    if (!form) return;

    const button = document.getElementById('loginButton');
    const buttonText = document.getElementById('loginText');
    const buttonIcon = document.getElementById('loginIcon');

    const alertBox = document.getElementById('loginAlert');
    const alertIcon = document.getElementById('alertIcon');
    const alertMessage = document.getElementById('alertMessage');

    const password = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const eyeIcon = document.getElementById('eyeIcon');

    const csrfToken = document.getElementById('csrfToken');

    // Alert
    const showAlert = (type, message) => {
        alertBox.classList.remove(
            'd-none',
            'alert-danger',
            'alert-success',
            'alert-warning'
        );
        alertBox.classList.add(`alert-${type}`);

        alertIcon.className = 'bi me-2';
        alertIcon.classList.add(
            type === 'success'
                ? 'bi-check-circle-fill'
                : type === 'warning'
                    ? 'bi-exclamation-circle-fill'
                    : 'bi-exclamation-triangle-fill'
        );

        alertMessage.textContent = message;
    };

    const hideAlert = () => {
        alertBox.classList.add('d-none');
        alertBox.classList.remove(
            'alert-danger',
            'alert-success',
            'alert-warning'
        );
    };

    // Loading
    const setLoading = (loading) => {
        button.disabled = loading;
        buttonIcon.className = loading
            ? 'bi bi-arrow-repeat me-2 spin'
            : 'bi bi-box-arrow-in-right me-2';
        buttonText.textContent = loading ? 'Signing in...' : 'Sign In';
    };

  
    // AJAX Login
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        hideAlert();
        setLoading(true);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            // Update CSRF token
            if (data.csrf_hash && csrfToken) {
                csrfToken.value = data.csrf_hash;
            }

            // Success
            if (data.status === true) {
                showAlert('success', data.message || 'Login successful.');

                buttonIcon.className = 'bi bi-check-circle-fill me-2';
                buttonText.textContent = 'Success';

                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 500);

                return;
            }

            // Failed
            showAlert(
                'danger',
                data.message || 'Invalid username or password.'
            );
            setLoading(false);

        } catch (error) {
            console.error('Login error:', error);

            showAlert(
                'danger',
                'Unable to connect to the server. Please try again.'
            );
            setLoading(false);
        }
    });
});

