// ── Password Visibility Toggle ──
// Menangani fungsi untuk memperlihatkan/menyembunyikan password pada form login
document.addEventListener('DOMContentLoaded', function() {
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function() {
            // Periksa tipe input saat ini
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Ubah icon mata sesuai dengan tipe input
            if (type === 'text') {
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                `;
            } else {
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        });
    }
});

// ── Forgot Password Modals ──
// Global variables
let currentResetToken = '';
let currentResetEmail = '';
let resendTimer = null;
let resendTimeLeft = 60;

function showForgotPasswordModal() {
    document.getElementById('forgotPasswordModal').classList.remove('hidden');
}

function hideForgotPasswordModal() {
    document.getElementById('forgotPasswordModal').classList.add('hidden');
    document.getElementById('forgotPasswordMessage').classList.add('hidden');
    document.getElementById('forgotPasswordForm').reset();
}

function hideOtpModal() {
    document.getElementById('otpVerificationModal').classList.add('hidden');
    document.getElementById('otpMessage').classList.add('hidden');
    document.getElementById('otpVerificationForm').reset();
    clearInterval(resendTimer);
}

function hideNewPasswordModal() {
    document.getElementById('newPasswordModal').classList.add('hidden');
    document.getElementById('newPasswordMessage').classList.add('hidden');
    document.getElementById('newPasswordForm').reset();
}

// OTP input navigation
function moveToNext(current, nextIndex) {
    if (current.value.length === 1) {
        if (nextIndex) {
            document.querySelector(`.otp-input:nth-child(${nextIndex})`).focus();
        }
        updateFullOtp();
    }
}

function updateFullOtp() {
    const otpInputs = document.querySelectorAll('.otp-input');
    let fullOtp = '';
    otpInputs.forEach(input => {
        fullOtp += input.value;
    });
    document.getElementById('fullOtp').value = fullOtp;
}

// Start resend timer
function startResendTimer() {
    resendTimeLeft = 60;
    const timerDiv = document.getElementById('resendTimer');
    timerDiv.textContent = `Resend OTP in ${resendTimeLeft} seconds`;
    
    resendTimer = setInterval(() => {
        resendTimeLeft--;
        timerDiv.textContent = `Resend OTP in ${resendTimeLeft} seconds`;
        
        if (resendTimeLeft <= 0) {
            clearInterval(resendTimer);
            timerDiv.textContent = '';
        }
    }, 1000);
}

// Show message helper
function showMessage(elementId, message, type) {
    const messageDiv = document.getElementById(elementId);
    messageDiv.innerHTML = message;
    messageDiv.classList.remove('hidden');
    
    // Reset classes
    messageDiv.className = 'p-4 rounded-xl text-sm mb-5 font-medium border flex items-start';
    
    switch(type) {
        case 'success':
            messageDiv.classList.add('bg-green-50', 'text-green-700', 'border-green-100');
            break;
        case 'error':
            messageDiv.classList.add('bg-red-50', 'text-red-700', 'border-red-100');
            break;
        case 'loading':
            messageDiv.classList.add('bg-yellow-50', 'text-yellow-700', 'border-yellow-100');
            break;
    }
}

// Resend OTP
function resendOtp() {
    if (resendTimeLeft > 0) {
        showMessage('otpMessage', `Please wait ${resendTimeLeft} seconds before resending`, 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('email', currentResetEmail);
    formData.append('_token', window.Config.csrfToken);
    
    showMessage('otpMessage', 'Resending OTP...', 'loading');
    
    fetch(window.Config.routes.sendOtp, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentResetToken = data.token;
            showMessage('otpMessage', 'OTP resent successfully!', 'success');
            startResendTimer();
        } else {
            showMessage('otpMessage', data.message || 'Failed to resend OTP', 'error');
        }
    })
    .catch(error => {
        showMessage('otpMessage', 'Network error. Please try again.', 'error');
        console.error('Error:', error);
    });
}

// Step 1: Send OTP
document.addEventListener('DOMContentLoaded', function() {
    
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('resetEmail').value;
            const formData = new FormData(this);
            
            showMessage('forgotPasswordMessage', 'Sending OTP...', 'loading');
            
            fetch(window.Config.routes.sendOtp, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentResetToken = data.token;
                    currentResetEmail = email;
                    window.isResettingAdmin = data.isAdmin || false;
                    
                    // Show OTP modal
                    hideForgotPasswordModal();
                    document.getElementById('otpEmailDisplay').textContent = email;
                    document.getElementById('resetToken').value = currentResetToken;
                    document.getElementById('otpVerificationModal').classList.remove('hidden');
                    
                    // Start resend timer
                    startResendTimer();
                    
                    // Auto-focus first OTP input
                    setTimeout(() => {
                        document.querySelector('.otp-input').focus();
                    }, 100);
                } else {
                    showMessage('forgotPasswordMessage', data.message || 'Failed to send OTP', 'error');
                }
            })
            .catch(error => {
                showMessage('forgotPasswordMessage', 'Network error. Please try again.', 'error');
                console.error('Error:', error);
            });
        });
    }
    
    // Step 2: Verify OTP
    const otpVerificationForm = document.getElementById('otpVerificationForm');
    if (otpVerificationForm) {
        otpVerificationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const otp = document.getElementById('fullOtp').value;
            
            if (otp.length !== 6) {
                showMessage('otpMessage', 'Please enter 6-digit OTP', 'error');
                return;
            }
            
            showMessage('otpMessage', 'Verifying OTP...', 'loading');
            
            fetch(window.Config.routes.verifyOtp, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show new password modal
                    hideOtpModal();
                    document.getElementById('passwordResetToken').value = currentResetToken;
                    document.getElementById('passwordResetOtp').value = otp;
                    
                    if (window.isResettingAdmin) {
                        document.getElementById('adminPasswordHint')?.classList.remove('hidden');
                        document.getElementById('karyawanPasswordHint')?.classList.add('hidden');
                    } else {
                        document.getElementById('karyawanPasswordHint')?.classList.remove('hidden');
                        document.getElementById('adminPasswordHint')?.classList.add('hidden');
                    }
                    
                    document.getElementById('newPasswordModal').classList.remove('hidden');
                    document.getElementById('newPassword').focus();
                } else {
                    showMessage('otpMessage', data.message || 'Invalid OTP', 'error');
                }
            })
            .catch(error => {
                showMessage('otpMessage', 'Network error. Please try again.', 'error');
                console.error('Error:', error);
            });
        });
    }
    
    // Step 3: Reset Password
    const newPasswordForm = document.getElementById('newPasswordForm');
    if (newPasswordForm) {
        newPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const password = document.getElementById('newPassword').value;
            const confirmPassword = document.querySelector('input[name="password_confirmation"]').value;
            
            if (password !== confirmPassword) {
                showMessage('newPasswordMessage', 'Passwords do not match', 'error');
                return;
            }
            
            if (password.length < 8) {
                showMessage('newPasswordMessage', 'Password must be at least 8 characters', 'error');
                return;
            }
            
            showMessage('newPasswordMessage', 'Resetting password...', 'loading');
            
            fetch(window.Config.routes.resetPassword, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('newPasswordMessage', 'Password reset successfully! You can now login with your new password.', 'success');
                    
                    // Close modal and redirect to login after 3 seconds
                    setTimeout(() => {
                        hideNewPasswordModal();
                        window.location.reload();
                    }, 3000);
                } else {
                    showMessage('newPasswordMessage', data.message || 'Failed to reset password', 'error');
                }
            })
            .catch(error => {
                showMessage('newPasswordMessage', 'Network error. Please try again.', 'error');
                console.error('Error:', error);
            });
        });
    }
    
    // Auto-focus next OTP input on paste
    document.querySelectorAll('.otp-input').forEach((input, index) => {
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pasteData = e.clipboardData.getData('text').trim();
            if (/^\d{6}$/.test(pasteData)) {
                const inputs = document.querySelectorAll('.otp-input');
                inputs.forEach((input, i) => {
                    input.value = pasteData[i] || '';
                });
                updateFullOtp();
                document.getElementById('fullOtp').value = pasteData;
            }
        });
    });
    
    // Removed close modals when clicking outside per user request
});
