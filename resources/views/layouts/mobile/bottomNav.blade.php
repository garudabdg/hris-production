<style>
    /* App Bottom Menu Base */
    .appBottomMenu {
        height: 56px;
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 999;
        background: #ffffff !important;
        border-top: 1px solid #e2e8f0 !important;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.05) !important;
    }

    /* Items Layout */
    .appBottomMenu .item {
        width: 20%;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.1s ease-in-out, background-color 0.2s;
        -webkit-tap-highlight-color: transparent; 
        border-radius: 12px;
        margin: 0 2px;
    }
    .appBottomMenu .item:active {
        transform: scale(0.92);
        background-color: rgba(0,0,0,0.03);
    }

    .appBottomMenu .item .col {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        line-height: 1.2;
    }

    /* Icon & Label Styles */
    .appBottomMenu .item ion-icon {
        font-size: 22px;
        margin-bottom: 3px;
        color: #64748b !important;
        transition: color 0.3s;
    }
    .appBottomMenu .item strong {
        display: block;
        font-size: 10px;
        font-weight: 500;
        color: #64748b !important;
        transition: color 0.3s;
    }

    /* Active State */
    .appBottomMenu .item.active ion-icon, 
    .appBottomMenu .item.active strong {
        color: {{ $t['primary'] ?? '#2d5a4c' }} !important;
        font-weight: 700 !important;
    }

    /* Center Action Button (Fingerprint) */
    .appBottomMenu .item .action-button.large {
        width: 60px !important;
        height: 60px !important;
        margin-top: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: {{ $t['primary'] ?? '#2d5a4c' }} !important;
        box-shadow: 0 4px 12px {{ ($t['primary'] ?? '#2d5a4c') }}4d !important;
        position: relative !important;
        top: -10px !important;
        transition: transform 0.1s ease-in-out, box-shadow 0.2s !important;
    }
    .appBottomMenu .item .action-button.large ion-icon {
        color: #ffffff !important;
        font-size: 32px !important;
        margin-bottom: 0 !important;
    }
    .appBottomMenu .item:active .action-button.large {
        transform: scale(0.9) !important;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important;
    }

    /* Reset potential safe-area-inset conflict from style.css */
    .appBottomMenu {
        padding-bottom: 0 !important;
    }
</style>
<div class="appBottomMenu">
    <a href="/dashboard" class="item {{ request()->is('dashboard') ? 'active' : '' }}">
        <div class="col">
            <ion-icon name="home-outline"></ion-icon>
            <strong>Home</strong>
        </div>
    </a>
    <a href="{{ route('presensi.histori') }}" class="item {{ request()->is('presensi/histori') ? 'active' : '' }}">
        <div class="col">
            <ion-icon name="document-text-outline" role="img" class="md hydrated" aria-label="document text outline"></ion-icon>
            <strong>Histori</strong>
        </div>
    </a>

    <a href="/presensi/create" class="item ">
        <div class="col">
            <div class="action-button large">
                <ion-icon name="finger-print-outline"></ion-icon>
            </div>
        </div>
    </a>
    <a href="{{ route('pengajuanizin.index') }}" class="item {{ request()->is('pengajuanizin') ? 'active' : '' }}">
        <div class="col">
            <ion-icon name="calendar-outline"></ion-icon>
            <strong>Ajuan Izin</strong>
        </div>
    </a>
    <a href="{{ route('users.editpassword', Crypt::encrypt(Auth::user()->id)) }}"
        class="item {{ request()->is('/users/:id/editpassword') ? 'active' : '' }}">
        <div class="col">
            <ion-icon name="settings-outline"></ion-icon>
            <strong>Setting</strong>
        </div>
    </a>
</div>
<!-- * App Bottom Menu -->
