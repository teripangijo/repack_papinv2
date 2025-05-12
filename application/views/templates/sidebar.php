<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= site_url('user/index'); // Arahkan ke Dashboard User ?>">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-box-open"></i> </div>
        <div class="sidebar-brand-text mx-3">REPACK <sup>User</sup></div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item <?= ($this->uri->segment(1) == 'user' && ($this->uri->segment(2) == '' || $this->uri->segment(2) == 'index')) ? 'active' : ''; ?>">
        <a class="nav-link" href="<?= site_url('user/index'); ?>">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Manajemen Layanan
    </div>

    <li class="nav-item <?= ($this->uri->segment(1) == 'user' && $this->uri->segment(2) == 'pengajuan_kuota') ? 'active' : ''; ?>">
        <a class="nav-link" href="<?= site_url('user/pengajuan_kuota'); ?>">
            <i class="fas fa-fw fa-file-invoice-dollar"></i>
            <span>Pengajuan Kuota</span></a>
    </li>

    <li class="nav-item <?= ($this->uri->segment(1) == 'user' && $this->uri->segment(2) == 'permohonan_impor_kembali') ? 'active' : ''; ?>">
        <a class="nav-link" href="<?= site_url('user/permohonan_impor_kembali'); ?>">
            <i class="fas fa-fw fa-file-import"></i>
            <span>Permohonan Impor Kembali</span></a>
    </li>

    <li class="nav-item <?= ($this->uri->segment(1) == 'user' && $this->uri->segment(2) == 'daftarPermohonan') ? 'active' : ''; ?>">
        <a class="nav-link" href="<?= site_url('user/daftarPermohonan'); ?>">
            <i class="fas fa-fw fa-list-alt"></i>
            <span>Daftar Permohonan</span></a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <li class="nav-item">
        <a class="nav-link" href="#" data-toggle="modal" data-target="#logoutModal">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Logout</span></a>
    </li>

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
