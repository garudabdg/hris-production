 <!-- Core CSS -->
 <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/core.css') }}" />
 <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/theme-semi-dark.css') }}" />
 <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

 <!-- Vendors CSS -->
 <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
 <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css') }}" />
 <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}" />
 <link rel="stylesheet" href="{{ asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.css') }}" />
 <link rel="stylesheet" href="{{ asset('assets/vendor/libs/pickr/pickr-themes.css') }}" />
 <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
 <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
 <link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
 <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
 <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}" />
 <link rel="stylesheet" href="{{ asset('assets/vendor/libs/spinkit/spinkit.css') }}" />
 <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI="
     crossorigin="" />
 <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />

 <!-- Page CSS -->
 <style>
     :root {
         /* Dynamic Theme Colors */
         --theme-color-1: {{ $general_setting->theme_color_1 ?? '#053b22' }};
         --theme-color-2: {{ $general_setting->theme_color_2 ?? '#0b6a3a' }};

         --bs-primary: var(--theme-color-1);
         /* Note: bootstrap rgb var might need manual conversion if strict compliance needed, skipping for now */
         --bs-primary-rgb: 5, 59, 34; 
     }

     .form-group {
         margin-bottom: 5px !important;
     }

     .swal2-container {
         z-index: 9999 !important;
     }

     .swal2-confirm {
         background-color: #1a6bd1 !important;
     }

     .noborder-form {
         width: 100%;
         border: 0px;
     }

     .noborder-form:focus {
         outline: none;
     }

     /* Custom Sidebar Color */
     #layout-menu {
         background: var(--theme-color-1) !important;
         color: #fff !important;
     }

     #layout-menu .app-brand-link,
     #layout-menu .app-brand-text,
     #layout-menu .menu-link,
     #layout-menu .menu-link .menu-icon,
     #layout-menu .menu-header,
     #layout-menu .menu-sub .menu-link {
         color: #fff !important;
     }

     #layout-menu .menu-inner>.menu-item.active>.menu-link,
     #layout-menu .menu-inner>.menu-item.open>.menu-link,
     #layout-menu .menu-sub>.menu-item.active>.menu-link {
         background: var(--theme-color-2) !important;
         color: #fff !important;
         border-radius: 10px;
         box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
     }

     #layout-menu .menu-inner>.menu-item.active>.menu-link .menu-icon,
     #layout-menu .menu-inner>.menu-item.open>.menu-link .menu-icon,
     #layout-menu .menu-sub>.menu-item.active>.menu-link .menu-icon {
         color: #fff !important;
     }

     #layout-menu .menu-link .badge {
         color: #fff !important;
     }

     .menu-inner-shadow {
         display: none !important;
         background: transparent !important;
     }

     .btn-primary {
         background-color: var(--theme-color-1) !important;
         border-color: var(--theme-color-1) !important;
     }

     .btn-primary:hover,
     .btn-primary:focus,
     .btn-primary:active {
         background-color: var(--theme-color-2) !important;
         border-color: var(--theme-color-2) !important;
     }

     .bg-primary,
     .badge-primary,
     .alert-primary,
     .progress-bar {
         background-color: var(--theme-color-1) !important;
         border-color: var(--theme-color-1) !important;
     }

     .text-primary {
         color: var(--theme-color-1) !important;
     }

     .border-primary {
         border-color: var(--theme-color-1) !important;
     }

     .table thead.thead-dark th,
     .table-dark thead th,
     thead.table-dark th {
         background-color: var(--theme-color-1) !important;
         color: #fff !important;
     }

     .table-dark,
     .table-dark> :not(caption)>*>* {
         background-color: var(--theme-color-1) !important;
         color: #fff !important;
         border-color: rgba(255, 255, 255, 0.15) !important;
     }

     .table-dark.table-striped>tbody>tr:nth-of-type(odd)>* {
         background-color: rgba(255, 255, 255, 0.05) !important;
     }

     .table-dark.table-hover>tbody>tr:hover>* {
         background-color: rgba(255, 255, 255, 0.08) !important;
     }
 </style>
