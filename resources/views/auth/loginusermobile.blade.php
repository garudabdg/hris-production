<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>HRIS Mobile Login</title>

    <!-- PWA Meta Tags -->
    <meta name="application-name" content="E-Presensi GPS V2">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="E-Presensi">
    <meta name="description" content="Aplikasi Presensi GPS untuk Karyawan">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#106f62">

    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="/assets/img/icons/pwa/icon-192x192.png">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <style>
        :root {
            --theme-color-1: {{ $general_setting->theme_color_1 ?? '#106f62' }};
            --theme-color-2: {{ $general_setting->theme_color_2 ?? '#0b5247' }};
            --theme-color-light: color-mix(in srgb, var(--theme-color-1) 15%, #ffffff);
            --theme-color-fade: color-mix(in srgb, var(--theme-color-1) 30%, #ffffff);
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/auth_mobile.css') }}?v={{ time() }}">
</head>
<body>
    @if (session('error'))
        <div class="alert alert-danger" id="alert-box">
            <span>{{ session('error') }}</span>
            <button class="alert-close" onclick="document.getElementById('alert-box').style.display='none'">&times;</button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" id="alert-box-2">
            <span>{{ $errors->first() }}</span>
            <button class="alert-close" onclick="document.getElementById('alert-box-2').style.display='none'">&times;</button>
        </div>
    @endif

    <div class="top-section">
        <div class="blob-1"></div>
        
        
        <div class="plant-wrapper">
            <div class="leaf-1"></div>
            <div class="leaf-2"></div>
            <div class="plant-pot"></div>
            <div class="pot-shadow"></div>
        </div>
    </div>
    
    <div class="bottom-section">
        <div class="container">
            <div style="margin-top: 5px;">
                <div class="login-title" style="margin-bottom: 8px; font-size: 30px;">Welcome!</div>
                <div style="font-size: 14px; font-weight: 500; color: #64748b; margin-bottom: 25px;">to {{ $general_setting->nama_perusahaan ?? 'E-Presensi GPS' }}</div>
            </div>
            
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <ion-icon name="mail-outline"></ion-icon>
                    <input type="text" name="id_user" class="form-control" placeholder="Email / NIK / ID" autocomplete="off" required value="{{ old('id_user') }}">
                </div>
                
                <div class="form-group">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                
                <div class="forgot-pass" style="display: flex; align-items: center; justify-content: space-between; margin-top: -5px; margin-bottom: 25px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; color: #518b82; font-weight: 600; margin: 0;">
                        <input type="checkbox" name="remember" id="remember" style="width: 16px; height: 16px; accent-color: var(--theme-color-1); cursor: pointer;">
                        Remember me
                    </label>
                    <a href="#" onclick="showForgotPasswordModal()">Forgot Password</a>
                </div>
                
                <button type="submit" class="btn-login">Login</button>
            </form>
            
            {{-- 
            REGISTER BUTTON DISABLED - Feature removed by admin
            <a href="{{ route('register') }}" class="sign-btn" style="background-color: #0b6a3a; margin-top: 10px; display: block; text-align: center; text-decoration: none; color: white; cursor: pointer;">
                Create New Account
            </a> 
            --}}
            
            <!-- <div class="divider">
                <span>Or login with</span>
            </div> -->
            
            <!-- <div class="social-login">
                <a href="#" class="social-btn">
                    <ion-icon name="logo-facebook" class="fb-icon"></ion-icon>
                </a>
                <a href="#" class="social-btn" style="border: 1px solid #eee;">
                    <!-- Using raw svg for google logo for better crossbrowser rendering -->
                    <!-- <svg viewBox="0 0 24 24" width="22" height="22" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                </a> -->
                <!-- <a href="#" class="social-btn">
                    <ion-icon name="logo-apple" class="apple-icon"></ion-icon>
                </a>
            </div> -->
            
            {{-- 
            REGISTER LINK DISABLED - Feature removed by admin
            <div class="signup-text">
                Don't have account? <a href="{{ route('register') }}">Sign Up</a>
            </div>
            --}}
        </div>
    </div>
    
    <!-- Forgot Password Modal - Step 1: Email Input -->
    <div id="forgotPasswordModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; width: 90%; max-width: 400px; border-radius: 20px; padding: 30px; position: relative;">
            <button onclick="hideForgotPasswordModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; color: #999; cursor: pointer;">&times;</button>
            
            <h2 style="color: var(--theme-color-1); margin-bottom: 20px; font-size: 24px;">Reset Password</h2>
            
            <div id="forgotPasswordMessage" style="display: none; padding: 10px; border-radius: 10px; margin-bottom: 15px;"></div>
            
            <form id="forgotPasswordForm">
                @csrf
                <div class="form-group">
                    <ion-icon name="mail-outline"></ion-icon>
                    <input type="email" name="email" id="resetEmail" class="form-control" placeholder="Email address" required>
                </div>
                
                <button type="submit" class="btn-login" style="margin-bottom: 15px;">Send OTP</button>
                
                <div style="text-align: center; color: #666; font-size: 14px;">
                    Enter your email address and we'll send you an OTP to reset your password.
                </div>
            </form>
        </div>
    </div>
    
    <!-- OTP Verification Modal - Step 2: OTP Input -->
    <div id="otpVerificationModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1001; align-items: center; justify-content: center;">
        <div style="background: white; width: 90%; max-width: 400px; border-radius: 20px; padding: 30px; position: relative;">
            <button onclick="hideOtpModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; color: #999; cursor: pointer;">&times;</button>
            
            <h2 style="color: var(--theme-color-1); margin-bottom: 20px; font-size: 24px;">Verify OTP</h2>
            
            <div id="otpMessage" style="display: none; padding: 10px; border-radius: 10px; margin-bottom: 15px;"></div>
            
            <div style="text-align: center; margin-bottom: 20px;">
                <p>We sent a 6-digit OTP to:</p>
                <p id="otpEmailDisplay" style="font-weight: bold; color: var(--theme-color-1);"></p>
                <p style="font-size: 14px; color: #666;">Check your email inbox</p>
            </div>
            
            <form id="otpVerificationForm">
                @csrf
                <input type="hidden" id="resetToken" name="token">
                
                <div class="form-group" style="text-align: center;">
                    <div style="display: flex; justify-content: center; gap: 10px; margin-bottom: 20px;">
                        <input type="text" name="otp1" maxlength="1" class="otp-input" style="width: 50px; height: 60px; text-align: center; font-size: 24px; border: 2px solid #ddd; border-radius: 10px;" oninput="moveToNext(this, 2)" autofocus>
                        <input type="text" name="otp2" maxlength="1" class="otp-input" style="width: 50px; height: 60px; text-align: center; font-size: 24px; border: 2px solid #ddd; border-radius: 10px;" oninput="moveToNext(this, 3)">
                        <input type="text" name="otp3" maxlength="1" class="otp-input" style="width: 50px; height: 60px; text-align: center; font-size: 24px; border: 2px solid #ddd; border-radius: 10px;" oninput="moveToNext(this, 4)">
                        <input type="text" name="otp4" maxlength="1" class="otp-input" style="width: 50px; height: 60px; text-align: center; font-size: 24px; border: 2px solid #ddd; border-radius: 10px;" oninput="moveToNext(this, 5)">
                        <input type="text" name="otp5" maxlength="1" class="otp-input" style="width: 50px; height: 60px; text-align: center; font-size: 24px; border: 2px solid #ddd; border-radius: 10px;" oninput="moveToNext(this, 6)">
                        <input type="text" name="otp6" maxlength="1" class="otp-input" style="width: 50px; height: 60px; text-align: center; font-size: 24px; border: 2px solid #ddd; border-radius: 10px;" oninput="moveToNext(this, null)">
                    </div>
                    <input type="hidden" id="fullOtp" name="otp">
                </div>
                
                <button type="submit" class="btn-login" style="margin-bottom: 15px;">Verify OTP</button>
                
                <div style="text-align: center;">
                    <a href="#" onclick="resendOtp()" style="color: var(--theme-color-1); font-size: 14px;">Resend OTP</a>
                    <div id="resendTimer" style="font-size: 12px; color: #666; margin-top: 5px;"></div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- New Password Modal - Step 3: Set New Password -->
    <div id="newPasswordModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1002; align-items: center; justify-content: center;">
        <div style="background: white; width: 90%; max-width: 400px; border-radius: 20px; padding: 30px; position: relative;">
            <button onclick="hideNewPasswordModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; color: #999; cursor: pointer;">&times;</button>
            
            <h2 style="color: var(--theme-color-1); margin-bottom: 20px; font-size: 24px;">Set New Password</h2>
            
            <div id="newPasswordMessage" style="display: none; padding: 10px; border-radius: 10px; margin-bottom: 15px;"></div>
            
            <form id="newPasswordForm">
                @csrf
                <input type="hidden" id="passwordResetToken" name="token">
                <input type="hidden" id="passwordResetOtp" name="otp">
                
                <div class="form-group">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input type="password" name="password" id="newPassword" class="form-control" placeholder="New Password" required minlength="8">
                </div>
                
                <div class="form-group">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm New Password" required minlength="8">
                </div>
                
                <div style="font-size: 12px; color: #666; margin-bottom: 15px;">
                    Password must be at least 8 characters long.
                </div>
                
                <button type="submit" class="btn-login">Reset Password</button>
            </form>
        </div>
    </div>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .catch(function(err) {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
        
        // Global variables
        let currentResetToken = '';
        let currentResetEmail = '';
        let resendTimer = null;
        let resendTimeLeft = 60;
        
        // Forgot Password Modal Functions
        function showForgotPasswordModal() {
            document.getElementById('forgotPasswordModal').style.display = 'flex';
        }
        
        function hideForgotPasswordModal() {
            document.getElementById('forgotPasswordModal').style.display = 'none';
            document.getElementById('forgotPasswordMessage').style.display = 'none';
            document.getElementById('forgotPasswordForm').reset();
        }
        
        function hideOtpModal() {
            document.getElementById('otpVerificationModal').style.display = 'none';
            document.getElementById('otpMessage').style.display = 'none';
            document.getElementById('otpVerificationForm').reset();
            clearInterval(resendTimer);
        }
        
        function hideNewPasswordModal() {
            document.getElementById('newPasswordModal').style.display = 'none';
            document.getElementById('newPasswordMessage').style.display = 'none';
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
        
        // Resend OTP
        function resendOtp() {
            if (resendTimeLeft > 0) {
                showMessage('otpMessage', `Please wait ${resendTimeLeft} seconds before resending`, 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('email', currentResetEmail);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            
            showMessage('otpMessage', 'Resending OTP...', 'loading');
            
            fetch('{{ route("password.send-otp") }}', {
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
        
        // Show message helper
        function showMessage(elementId, message, type) {
            const messageDiv = document.getElementById(elementId);
            messageDiv.innerHTML = message;
            messageDiv.style.display = 'block';
            
            switch(type) {
                case 'success':
                    messageDiv.style.background = '#d4edda';
                    messageDiv.style.color = '#155724';
                    messageDiv.style.border = '1px solid #c3e6cb';
                    break;
                case 'error':
                    messageDiv.style.background = '#f8d7da';
                    messageDiv.style.color = '#721c24';
                    messageDiv.style.border = '1px solid #f5c6cb';
                    break;
                case 'loading':
                    messageDiv.style.background = '#fff3cd';
                    messageDiv.style.color = '#856404';
                    messageDiv.style.border = '1px solid #ffeaa7';
                    break;
            }
        }
        
        // Step 1: Send OTP
        document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('resetEmail').value;
            const formData = new FormData(this);
            
            showMessage('forgotPasswordMessage', 'Sending OTP...', 'loading');
            
            fetch('{{ route("password.send-otp") }}', {
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
                    
                    // Show OTP modal
                    hideForgotPasswordModal();
                    document.getElementById('otpEmailDisplay').textContent = email;
                    document.getElementById('resetToken').value = currentResetToken;
                    document.getElementById('otpVerificationModal').style.display = 'flex';
                    
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
        
        // Step 2: Verify OTP
        document.getElementById('otpVerificationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const otp = document.getElementById('fullOtp').value;
            
            if (otp.length !== 6) {
                showMessage('otpMessage', 'Please enter 6-digit OTP', 'error');
                return;
            }
            
            showMessage('otpMessage', 'Verifying OTP...', 'loading');
            
            fetch('{{ route("password.verify-otp") }}', {
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
                    document.getElementById('newPasswordModal').style.display = 'flex';
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
        
        // Step 3: Reset Password
        document.getElementById('newPasswordForm').addEventListener('submit', function(e) {
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
            
            fetch('{{ route("password.reset-with-otp") }}', {
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
        
        // Close modals when clicking outside
        document.getElementById('forgotPasswordModal').addEventListener('click', function(e) {
            if (e.target === this) hideForgotPasswordModal();
        });
        
        document.getElementById('otpVerificationModal').addEventListener('click', function(e) {
            if (e.target === this) hideOtpModal();
        });
        
        document.getElementById('newPasswordModal').addEventListener('click', function(e) {
            if (e.target === this) hideNewPasswordModal();
        });
        
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
    </script>
    @include('components.pwa-install-prompt')
</body>
</html>
