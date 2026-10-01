document.addEventListener('DOMContentLoaded', () => {
    const lockout = document.querySelector('[data-login-lockout]');
    if (!lockout) return;

    const form = document.querySelector('[data-login-form]');
    const submitButton = form?.querySelector('[data-login-submit]');
    const countdown = lockout.querySelector('[data-lockout-countdown]');
    const readyMessage = lockout.querySelector('[data-lockout-ready]');
    let remainingSeconds = Math.max(0, Number(lockout.dataset.remainingSeconds) || 0);
    let timer = null;

    const updateCountdown = () => {
        const minutes = Math.floor(remainingSeconds / 60);
        const seconds = remainingSeconds % 60;
        countdown.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

        if (remainingSeconds <= 0) {
            clearInterval(timer);
            if (submitButton) submitButton.disabled = false;
            if (readyMessage) readyMessage.hidden = false;
            return;
        }

        if (submitButton) submitButton.disabled = true;
        remainingSeconds -= 1;
    };

    updateCountdown();
    timer = window.setInterval(updateCountdown, 1000);
});
