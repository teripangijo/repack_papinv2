<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <?php
    $role_id = $this->session->userdata('role_id');
    $user_name = $user['name'] ?? ($this->session->userdata('name') ?? 'Guest'); // Ambil dari $data['user'] jika dikirim, atau dari session
    ?>

    <?php if ($role_id == 1) : // ADMIN ?>
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= site_url('admin'); ?>">
            <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-user-shield"></i></div>
            <div class="sidebar-brand-text mx-3">REPACK <sup>Admin</sup></div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item <?= ($this->uri->segment(1) == 'admin' && ($this->uri->segment(2) == '' || $this->uri->segment(2) == 'index')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('admin'); ?>"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a>
        </li>
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
        <div class="sidebar-heading">Pengaturan</div>
        <li class="nav-item <?= ($this->uri->segment(1) == 'admin' && $this->uri->segment(2) == 'manajemen_user') ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('admin/manajemen_user'); ?>"><i class="fas fa-fw fa-users-cog"></i><span>Manajemen User</span></a>
        </li>
        <li class="nav-item <?= ($this->uri->segment(1) == 'admin' && ($this->uri->segment(2) == 'role' || $this->uri->segment(2) == 'roleAccess')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('admin/role'); ?>"><i class="fas fa-fw fa-user-tag"></i><span>Manajemen Role</span></a>
        </li>

    <?php elseif ($role_id == 2) : // PENGGUNA JASA ?>
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= site_url('user'); ?>">
            <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-box-open"></i></div>
            <div class="sidebar-brand-text mx-3">REPACK <sup>User</sup></div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item <?= ($this->uri->segment(1) == 'user' && ($this->uri->segment(2) == '' || $this->uri->segment(2) == 'index')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('user'); ?>"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a>
        </li>
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
        <li class="nav-item <?= ($this->uri->segment(1) == 'user' && ($this->uri->segment(2) == 'daftarPermohonan' || $this->uri->segment(2) == 'editpermohonan' || $this->uri->segment(2) == 'printPdf')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('user/daftarPermohonan'); ?>"><i class="fas fa-fw fa-history"></i><span>Daftar Permohonan</span></a>
        </li>

    <?php elseif ($role_id == 3) : // PETUGAS (ROLE ID 3) - BLOK YANG DIPERBARUI ?>
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= site_url('petugas'); ?>">
            <div class="sidebar-brand-icon rotate-n-15">
                <i class="fas fa-user-secret"></i> </div>
            <div class="sidebar-brand-text mx-3">REPACK <sup>Petugas</sup></div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item <?= ($this->uri->segment(1) == 'petugas' && ($this->uri->segment(2) == '' || $this->uri->segment(2) == 'index')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('petugas'); ?>">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span></a>
        </li>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">
            Pemeriksaan
        </div>
        <li class="nav-item <?= ($this->uri->segment(1) == 'petugas' && ($this->uri->segment(2) == 'daftar_pemeriksaan' || $this->uri->segment(2) == 'rekam_lhp')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('petugas/daftar_pemeriksaan'); ?>">
                <i class="fas fa-fw fa-tasks"></i>
                <span>Tugas Pemeriksaan</span></a>
        </li>
        <li class="nav-item <?= ($this->uri->segment(1) == 'petugas' && ($this->uri->segment(2) == 'riwayat_lhp_direkam' || $this->uri->segment(2) == 'detail_lhp_direkam')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('petugas/riwayat_lhp_direkam'); ?>">
                <i class="fas fa-fw fa-history"></i>
                <span>Riwayat LHP Direkam</span></a>
        </li>

    <?php elseif ($role_id == 4) : // MONITORING (ROLE ID 4) ?>
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= site_url('monitoring'); ?>">
            <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-binoculars"></i></div>
            <div class="sidebar-brand-text mx-3">REPACK <sup>Monitoring</sup></div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item <?= ($this->uri->segment(1) == 'monitoring' && ($this->uri->segment(2) == '' || $this->uri->segment(2) == 'index')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('monitoring'); ?>"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a>
        </li>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Pantauan Data</div>
        <li class="nav-item <?= ($this->uri->segment(1) == 'monitoring' && $this->uri->segment(2) == 'data_pengajuan_kuota') ? 'active' : ''; ?>"><a class="nav-link" href="<?= site_url('monitoring/data_pengajuan_kuota'); ?>"><i class="fas fa-fw fa-file-alt"></i><span>Data Pengajuan Kuota</span></a></li>
        <li class="nav-item <?= ($this->uri->segment(1) == 'monitoring' && $this->uri->segment(2) == 'data_permohonan_impor') ? 'active' : ''; ?>"><a class="nav-link" href="<?= site_url('monitoring/data_permohonan_impor'); ?>"><i class="fas fa-fw fa-ship"></i><span>Data Permohonan Impor</span></a></li>

    <?php else : // GUEST atau role tidak dikenal ?>
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= site_url(); ?>">
            <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-recycle"></i></div>
            <div class="sidebar-brand-text mx-3">REPACK</div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item <?= ($this->uri->segment(1) == 'auth' || $this->uri->segment(1) == '') ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('auth/login'); ?>"><i class="fas fa-fw fa-sign-in-alt"></i><span>Login</span></a>
        </li>
    <?php endif; ?>

    <?php if ($this->session->userdata('role_id')):
        $current_controller_for_edit_profil = $this->uri->segment(1);
        // Pastikan controller valid sebelum membuat link (misalnya, tidak untuk 'auth')
        if (in_array($current_controller_for_edit_profil, ['admin', 'user', 'petugas', 'monitoring'])):
            $edit_profil_method_name = ($current_controller_for_edit_profil == 'user') ? 'edit' : 'edit_profil'; // User menggunakan 'edit', lainnya 'edit_profil'
    ?>
    <hr class="sidebar-divider">
    <li class="nav-item <?= ($this->uri->segment(1) == $current_controller_for_edit_profil && $this->uri->segment(2) == $edit_profil_method_name) ? 'active' : ''; ?>">
        <a class="nav-link" href="<?= site_url($current_controller_for_edit_profil . '/' . $edit_profil_method_name); ?>">
            <i class="fas fa-fw fa-user-edit"></i>
            <span>Edit Profil Saya</span>
        </a>
    </li>
    <?php endif; endif; ?>

    <?php if ($this->session->userdata('role_id')): ?>
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