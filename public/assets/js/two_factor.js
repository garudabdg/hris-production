// ── Two Factor Auth Interactions ──
// Menangani input OTP dan timer resend
document.addEventListener('DOMContentLoaded', function () {
    const digits   = document.querySelectorAll('.otp-digit');
    const hidden   = document.getElementById('two_factor_code');
    const btnResend  = document.getElementById('btnResend');
    const timerEl    = document.getElementById('resendTimer');
    const formResend = document.getElementById('formResend');
    const timerBar   = document.getElementById('timerBar');

    // ── OTP digit navigation ──────────────────────────────────────────
    if (digits.length > 0) {
        digits.forEach((el, i) => {
            el.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace') {
                    el.value = '';
                    el.classList.remove('filled');
                    syncHidden();
                    if (i > 0) digits[i - 1].focus();
                }
            });

            el.addEventListener('input', () => {
                // only digits
                el.value = el.value.replace(/\D/g, '').slice(-1);
                if (el.value !== '') {
                    el.classList.add('filled');
                } else {
                    el.classList.remove('filled');
                }
                syncHidden();
                if (el.value && i < 5) digits[i + 1].focus();
            });

            el.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
                pasted.split('').slice(0, 6).forEach((ch, j) => {
                    if (digits[j]) {
                        digits[j].value = ch;
                        digits[j].classList.add('filled');
                    }
                });
                syncHidden();
                digits[Math.min(pasted.length, 5)].focus();
            });
        });

        function syncHidden() {
            hidden.value = Array.from(digits).map(d => d.value).join('');
        }

        // Auto-focus first digit
        digits[0].focus();
    }

    // ── Timer bar (10 menit = 600 detik) ─────────────────────────────
    if (timerBar) {
        let totalSecs = 600;
        let elapsed   = 0;
        const barInterval = setInterval(() => {
            elapsed++;
            const pct = Math.max(0, 100 - (elapsed / totalSecs * 100));
            timerBar.style.width = pct + '%';
            
            // Change color to red if less than 30%
            if (pct <= 30) {
                timerBar.style.background = '#dc2626'; // Tailwind red-600
            }
            
            if (elapsed >= totalSecs) clearInterval(barInterval);
        }, 1000);
    }

    // ── Resend cooldown ───────────────────────────────────────────────
    function startCooldown(seconds) {
        let countdown = seconds;
        if(btnResend && timerEl) {
            btnResend.style.display = 'none';
            timerEl.style.display   = 'inline';
            timerEl.classList.remove('hidden');
            timerEl.textContent     = 'Kirim ulang dalam ' + countdown + 's';
            
            const iv = setInterval(() => {
                countdown--;
                timerEl.textContent = 'Kirim ulang dalam ' + countdown + 's';
                if (countdown <= 0) {
                    clearInterval(iv);
                    timerEl.style.display   = 'none';
                    timerEl.classList.add('hidden');
                    btnResend.style.display = 'inline';
                }
            }, 1000);
        }
    }

    if (btnResend && formResend) {
        btnResend.addEventListener('click', () => formResend.submit());
    }

    if (window.TwoFactorConfig && window.TwoFactorConfig.hasStatus) {
        startCooldown(60);
    }
});
