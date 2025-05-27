<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <?php
    $role_id = $this->session->userdata('role_id');
    // $user_name di-pass dari controller ke view header, lalu ke sidebar jika diperlukan
    // Untuk brand, kita hanya butuh role_id untuk teksnya
    $brand_text_main = "REPACK";
    $brand_super_text = "";
    $dashboard_link_sidebar = site_url(); // Default
    $sidebar_brand_icon_class = "fas fa-recycle"; // Default icon

    if ($role_id) {
        switch ($role_id) {
            case 1: // ADMIN
                $dashboard_link_sidebar = site_url('admin');
                $brand_super_text = "Admin";
                $sidebar_brand_icon_class = "fas fa-user-shield";
                break;
            case 2: // USER
                $dashboard_link_sidebar = site_url('user');
                $brand_super_text = "User";
                $sidebar_brand_icon_class = "fas fa-box-open";
                break;
            case 3: // PETUGAS
                $dashboard_link_sidebar = site_url('petugas');
                $brand_super_text = "Petugas";
                $sidebar_brand_icon_class = "fas fa-user-secret";
                break;
            case 4: // MONITORING
                $dashboard_link_sidebar = site_url('monitoring');
                $brand_super_text = "Monitoring";
                $sidebar_brand_icon_class = "fas fa-binoculars";
                break;
            case 5: // PETUGAS ADMINISTRASI (BARU)
                $dashboard_link_sidebar = site_url('petugas_administrasi'); // Asumsi controller baru
                $brand_super_text = "Pet. Administrasi";
                $sidebar_brand_icon_class = "fas fa-user-cog"; // Contoh ikon, bisa disesuaikan
                break;
        }
    }
    ?>

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= $dashboard_link_sidebar; ?>">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="<?= $sidebar_brand_icon_class; ?>"></i>
        </div>
        <div class="sidebar-brand-text mx-3"><?= $brand_text_main; ?> <?php if(!empty($brand_super_text)): ?><sup><?= $brand_super_text; ?></sup><?php endif; ?></div>
    </a>

    <hr class="sidebar-divider my-0">

    <?php if ($role_id): // Hanya tampilkan jika sudah login ?>
    <li class="nav-item <?= ($this->uri->segment(1) == strtolower(str_replace('.','_',str_replace(' ', '_', $brand_super_text))) && ($this->uri->segment(2) == '' || $this->uri->segment(2) == 'index')) ? 'active' : ''; ?>">
        <a class="nav-link" href="<?= $dashboard_link_sidebar; ?>">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>
    <?php endif; ?>


    <?php if ($role_id == 1) : // ADMIN MENU (LENGKAP) ?>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Manajemen Layanan</div>
        <li class="nav-item <?= ($this->uri->segment(1) == 'admin' && ($this->uri->segment(2) == 'monitoring_kuota' || $this->uri->segment(2) == 'histori_kuota_perusahaan')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('admin/monitoring_kuota'); ?>"><i class="fas fa-fw fa-chart-pie"></i><span>Monitoring Kuota</span></a>
        </li>
        <li class="nav-item <?= ($this->uri->segment(1) == 'admin' && ($this->uri->segment(2) == 'daftar_pengajuan_kuota' || $this->uri->segment(2) == 'proses_pengajuan_kuota' || $this->uri->segment(2) == 'detailPengajuanKuotaAdmin')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('admin/daftar_pengajuan_kuota'); ?>"><i class="fas fa-fw fa-file-invoice-dollar"></i><span>Pengajuan Kuota</span></a>
        </li>
        <li class="nav-item <?= ($this->uri->segment(1) == 'admin' && in_array($this->uri->segment(2), ['permohonanMasuk', 'penunjukanPetugas', 'prosesSurat', 'detail_permohonan_admin'])) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('admin/permohonanMasuk'); ?>"><i class="fas fa-fw fa-file-import"></i><span>Permohonan Impor</span></a>
        </li>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Pengaturan Sistem</div>
        <li class="nav-item <?= ($this->uri->segment(1) == 'admin' && $this->uri->segment(2) == 'manajemen_user') ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('admin/manajemen_user'); ?>"><i class="fas fa-fw fa-users-cog"></i><span>Manajemen User</span></a>
        </li>
        <li class="nav-item <?= ($this->uri->segment(1) == 'admin' && ($this->uri->segment(2) == 'role' || $this->uri->segment(2) == 'roleAccess')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('admin/role'); ?>"><i class="fas fa-fw fa-user-tag"></i><span>Manajemen Role</span></a>
        </li>

    <?php elseif ($role_id == 2) : // PENGGUNA JASA MENU ?>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Layanan</div>
        <li class="nav-item <?= ($this->uri->segment(1) == 'user' && $this->uri->segment(2) == 'pengajuan_kuota') ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('user/pengajuan_kuota'); ?>"><i class="fas fa-fw fa-file-signature"></i><span>Pengajuan Kuota</span></a>
        </li>
        <li class="nav-item <?= ($this->uri->segment(1) == 'user' && ($this->uri->segment(2) == 'daftar_pengajuan_kuota' || $this->uri->segment(2) == 'print_bukti_pengajuan_kuota')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('user/daftar_pengajuan_kuota'); ?>"><i class="fas fa-fw fa-list-alt"></i><span>Daftar Pengajuan Kuota</span></a>
        </li>
        <li class="nav-item <?= ($this->uri->segment(1) == 'user' && $this->uri->segment(2) == 'permohonan_impor_kembali') ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('user/permohonan_impor_kembali'); ?>"><i class="fas fa-fw fa-pallet"></i><span>Buat Permohonan Impor</span></a>
        </li>
        <li class="nav-item <?= ($this->uri->segment(1) == 'user' && in_array($this->uri->segment(2), ['daftarPermohonan', 'editpermohonan', 'printPdf', 'detailPermohonan'])) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('user/daftarPermohonan'); ?>"><i class="fas fa-fw fa-history"></i><span>Daftar Permohonan</span></a>
        </li>

    <?php elseif ($role_id == 3) : // PETUGAS MENU ?>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Pemeriksaan</div>
        <li class="nav-item <?= ($this->uri->segment(1) == 'petugas' && ($this->uri->segment(2) == 'daftar_pemeriksaan' || $this->uri->segment(2) == 'rekam_lhp')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('petugas/daftar_pemeriksaan'); ?>"><i class="fas fa-fw fa-tasks"></i><span>Tugas Pemeriksaan</span></a>
        </li>
        <li class="nav-item <?= ($this->uri->segment(1) == 'petugas' && ($this->uri->segment(2) == 'riwayat_lhp_direkam' || $this->uri->segment(2) == 'detail_lhp_direkam')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('petugas/riwayat_lhp_direkam'); ?>"><i class="fas fa-fw fa-history"></i><span>Riwayat LHP Direkam</span></a>
        </li>
        <hr class="sidebar-divider mt-2 mb-2">
        <div class="sidebar-heading">Pantauan (Petugas)</div>
        <li class="nav-item <?= ($this->uri->segment(1) == 'petugas' && ($this->uri->segment(2) == 'monitoring_permohonan' || $this->uri->segment(2) == 'detail_monitoring_permohonan')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('petugas/monitoring_permohonan'); ?>"><i class="fas fa-fw fa-search-location"></i><span>Monitoring Permohonan</span></a>
        </li>

    <?php elseif ($role_id == 4) : // MONITORING MENU ?>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Pantauan Data Utama</div>
        <li class="nav-item <?= ($this->uri->segment(1) == 'monitoring' && ($this->uri->segment(2) == 'pengajuan_kuota' || $this->uri->segment(2) == 'detail_pengajuan_kuota')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('monitoring/pengajuan_kuota'); ?>"><i class="fas fa-fw fa-file-contract"></i><span>Pantauan Pengajuan Kuota</span></a>
        </li>
        <li class="nav-item <?= ($this->uri->segment(1) == 'monitoring' && ($this->uri->segment(2) == 'permohonan_impor' || $this->uri->segment(2) == 'detail_permohonan_impor')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('monitoring/permohonan_impor'); ?>"><i class="fas fa-fw fa-ship"></i><span>Pantauan Permohonan Impor</span></a>
        </li>
        <li class="nav-item <?= ($this->uri->segment(1) == 'monitoring' && ($this->uri->segment(2) == 'pantau_kuota_perusahaan' || $this->uri->segment(2) == 'detail_kuota_perusahaan')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('monitoring/pantau_kuota_perusahaan'); ?>"><i class="fas fa-fw fa-chart-pie"></i><span>Pantauan Kuota Perusahaan</span></a>
        </li>
    
    <?php elseif ($role_id == 5) : // PETUGAS ADMINISTRASI BARU ?>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Manajemen Layanan</div>
        <li class="nav-item <?= ($this->uri->segment(1) == 'petugas_administrasi' && ($this->uri->segment(2) == 'monitoring_kuota' || $this->uri->segment(2) == 'histori_kuota_perusahaan')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('petugas_administrasi/monitoring_kuota'); ?>"><i class="fas fa-fw fa-chart-pie"></i><span>Monitoring Kuota</span></a>
        </li>
        <li class="nav-item <?= ($this->uri->segment(1) == 'petugas_administrasi' && ($this->uri->segment(2) == 'daftar_pengajuan_kuota' || $this->uri->segment(2) == 'proses_pengajuan_kuota' || $this->uri->segment(2) == 'detailPengajuanKuotaAdmin')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('petugas_administrasi/daftar_pengajuan_kuota'); ?>"><i class="fas fa-fw fa-file-invoice-dollar"></i><span>Pengajuan Kuota</span></a>
        </li>
        <li class="nav-item <?= ($this->uri->segment(1) == 'petugas_administrasi' && in_array($this->uri->segment(2), ['permohonanMasuk', 'penunjukanPetugas', 'prosesSurat', 'detail_permohonan_admin'])) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('petugas_administrasi/permohonanMasuk'); ?>"><i class="fas fa-fw fa-file-import"></i><span>Permohonan Impor</span></a>
        </li>
        <?php // Tidak ada menu Pengaturan Sistem (Manajemen User & Role) untuk Petugas Administrasi ?>

    <?php else : // GUEST atau role tidak dikenal ?>
        <hr class="sidebar-divider my-0">
        <li class="nav-item <?= ($this->uri->segment(1) == 'auth' || $this->uri->segment(1) == '') ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('auth/login'); ?>"><i class="fas fa-fw fa-sign-in-alt"></i><span>Login</span></a>
        </li>
    <?php endif; ?>

    <?php // Menu Umum untuk semua role yang login (Edit Profil, Logout) ?>
    <?php if ($this->session->userdata('role_id')):
        $edit_profil_controller_for_link = '';
        // Tentukan controller yang benar untuk link Edit Profil berdasarkan role
        switch($this->session->userdata('role_id')) {
            case 1: $edit_profil_controller_for_link = 'admin'; break;
            case 2: $edit_profil_controller_for_link = 'user'; break;
            case 3: $edit_profil_controller_for_link = 'petugas'; break;
            case 4: $edit_profil_controller_for_link = 'monitoring'; break;
            case 5: $edit_profil_controller_for_link = 'petugas_administrasi'; break; // Tambahkan role baru
        }
        
        if (!empty($edit_profil_controller_for_link)) {
             $edit_profil_method_name_for_link = ($edit_profil_controller_for_link == 'user') ? 'edit' : 'edit_profil';
             $edit_profil_url = site_url($edit_profil_controller_for_link . '/' . $edit_profil_method_name_for_link);
        }
    ?>
    <hr class="sidebar-divider">
    <div class="sidebar-heading">
        Akun Saya
    </div>
    <?php if (!empty($edit_profil_url)): ?>
    <li class="nav-item <?= ($this->uri->segment(1) == $edit_profil_controller_for_link && $this->uri->segment(2) == $edit_profil_method_name_for_link) ? 'active' : ''; ?>">
        <a class="nav-link" href="<?= $edit_profil_url; ?>">
            <i class="fas fa-fw fa-user-edit"></i>
            <span>Edit Profil Saya</span>
        </a>
    </li>
    <?php endif; ?>
    <li class="nav-item">
        <a class="nav-link" href="#" data-toggle="modal" data-target="#logoutModal">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </li>
    <?php endif; ?>

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>