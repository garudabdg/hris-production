<!DOCTYPE html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-wide" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('/assets/') }}" data-template="vertical-menu-template-no-customizer">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>@yield('titlepage')</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}" />

    <!-- DNS Prefetch for external resources -->
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://unpkg.com">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">

    @include('layouts.fonts')

    @include('layouts.icons')

    @include('layouts.styles')

    <!-- Helpers -->
    <script src="{{ asset('/assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('/assets/js/config.js') }}"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA Meta Tags -->
    <meta name="application-name" content="E-Presensi GPS V2">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="E-Presensi">
    <meta name="description" content="Aplikasi Presensi GPS untuk Karyawan">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="msapplication-config" content="/assets/img/icons/browserconfig.xml">
    <meta name="msapplication-TileColor" content="#696cff">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="theme-color" content="#696cff">

    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="/assets/img/icons/pwa/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/assets/img/icons/pwa/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/assets/img/icons/pwa/icon-512x512.png">

    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful with scope: ', registration.scope);
                    })
                    .catch(function(err) {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script>

    @if(config('services.onesignal.app_id'))
    <!-- OneSignal Web Push SDK -->
    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
    <script>
        window.OneSignalDeferred = window.OneSignalDeferred || [];
        OneSignalDeferred.push(async function(OneSignal) {
            await OneSignal.init({
                appId: "{{ config('services.onesignal.app_id') }}",
                serviceWorkerParam: { scope: "/" },
                serviceWorkerPath: "sw.js",
                safari_web_id: "",
                notifyButton: {
                    enable: true,
                    size: 'medium',
                    theme: 'default',
                    position: 'bottom-right',
                    text: {
                        'tip.state.unsubscribed': 'Berlangganan Notifikasi',
                        'tip.state.subscribed': 'Anda berlangganan notifikasi',
                        'tip.state.blocked': 'Anda memblokir notifikasi',
                        'message.prenotify': 'Klik untuk menerima notifikasi pengumuman dari HRIS',
                        'message.action.subscribed': 'Terima kasih telah berlangganan!',
                        'message.action.resubscribed': 'Anda telah berlangganan kembali',
                        'message.action.unsubscribed': 'Anda batal berlangganan notifikasi',
                        'dialog.main.title': 'Kelola Notifikasi',
                        'dialog.main.button.subscribe': 'BERLANGGANAN',
                        'dialog.main.button.unsubscribe': 'BATAL BERLANGGANAN',
                        'dialog.blocked.title': 'Buka Blokir Notifikasi',
                        'dialog.blocked.message': 'Ikuti instruksi ini untuk menerima notifikasi:'
                    }
                },
                promptOptions: {
                    slidedown: {
                        prompts: [
                            {
                                type: "push",
                                autoPrompt: true,
                                text: {
                                    actionMessage: "Kami ingin mengirimkan notifikasi penting seperti Pengumuman dan Info Gaji.",
                                    acceptButton: "Izinkan",
                                    cancelButton: "Nanti"
                                },
                                delay: {
                                    pageViews: 1,
                                    timeDelay: 3
                                }
                            }
                        ]
                    }
                }
            });
            
            // Opsional: Hubungkan External ID dengan ID User Login
            @auth
            OneSignal.login("{{ auth()->user()->id }}");
            @endauth
        });
    </script>
    @endif
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Sidebar -->
            @include('layouts.sidebar')
            <!-- / Sidebar-->
            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                @include('layouts.navbar')

                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-fluid flex-grow-1 container-p-y">
                        <h4 class="py-3 mb-4">@yield('navigasi')</h4>
                        @yield('content')
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    @include('layouts.footer')
                    <!-- / Footer -->
                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    @include('layouts.scripts')
    <!-- Page JS -->
</body>

</html>
