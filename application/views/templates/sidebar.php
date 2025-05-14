<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <?php if (isset($user['role_id']) && $user['role_id'] == 1) : // ADMIN ?>
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= site_url('admin/index'); ?>">
            <div class="sidebar-brand-icon rotate-n-15">
                <i class="fas fa-user-shield"></i> </div>
            <div class="sidebar-brand-text mx-3">REPACK <sup>Admin</sup></div>
        </a>

        <hr class="sidebar-divider my-0">

        <li class="nav-item <?= ($this->uri->segment(1) == 'admin' && ($this->uri->segment(2) == '' || $this->uri->segment(2) == 'index')) ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('admin/index'); ?>">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard Admin</span></a>
        </li>

        <hr class="sidebar-divider">

        <div class="sidebar-heading">
            Manajemen Layanan
        </div>

        <li class="nav-item <?= ($this->uri->segment(1) == 'admin' && $this->uri->segment(2) == 'monitoring_kuota') ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('admin/monitoring_kuota'); ?>">
                <i class="fas fa-fw fa-chart-pie"></i>
                <span>Monitoring Kuota</span></a>
        </li>

        <li class="nav-item <?= ($this->uri->segment(1) == 'admin' && $this->uri->segment(2) == 'daftar_pengajuan_kuota') ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('admin/daftar_pengajuan_kuota'); ?>">
                <i class="fas fa-fw fa-file-invoice-dollar"></i>
                <span>Daftar Pengajuan Kuota</span></a>
        </li>

        <li class="nav-item <?= ($this->uri->segment(1) == 'admin' && $this->uri->segment(2) == 'permohonanMasuk') ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('admin/permohonanMasuk'); ?>">
                <i class="fas fa-fw fa-file-import"></i>
                <span>Daftar Permohonan Impor</span></a>
        </li>

        <hr class="sidebar-divider">

        <div class="sidebar-heading">
            Pengaturan
        </div>
        
        <li class="nav-item <?= ($this->uri->segment(1) == 'admin' && $this->uri->segment(2) == 'manajemen_user') ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('admin/manajemen_user'); ?>">
                <i class="fas fa-fw fa-users-cog"></i>
                <span>Manajemen User</span></a>
        </li>
        
        <li class="nav-item <?= ($this->uri->segment(1) == 'admin' && $this->uri->segment(2) == 'role') ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('admin/role'); ?>">
                <i class="fas fa-fw fa-user-tag"></i>
                <span>Manajemen Role</span></a>
        </li>


    <?php elseif (isset($user['role_id']) && $user['role_id'] == 2) : // PENGGUNA JASA ?>
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= site_url('user/index'); ?>">
            <div class="sidebar-brand-icon rotate-n-15">
                <i class="fas fa-box-open"></i> 
            </div>
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

        <li class="nav-item <?= ($this->uri->segment(1) == 'user' && $this->uri->segment(2) == 'daftar_pengajuan_kuota') ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('user/daftar_pengajuan_kuota'); ?>">
                <i class="fas fa-fw fa-history"></i>
                <span>Daftar Pengajuan Kuota</span></a>
        </li>

        <li class="nav-item <?= ($this->uri->segment(1) == 'user' && $this->uri->segment(2) == 'permohonan_impor_kembali') ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('user/permohonan_impor_kembali'); ?>">
                <i class="fas fa-fw fa-file-import"></i>
                <span>Permohonan Impor Kembali</span></a>
        </li>

        <li class="nav-item <?= ($this->uri->segment(1) == 'user' && $this->uri->segment(2) == 'daftarPermohonan') ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= site_url('user/daftarPermohonan'); ?>">
                <i class="fas fa-fw fa-list-alt"></i>
                <span>Daftar Permohonan Impor</span></a>
        </li>
    <?php else : ?>
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= site_url(); ?>">
            <div class="sidebar-brand-icon rotate-n-15">
                <i class="fas fa-question-circle"></i>
            </div>
            <div class="sidebar-brand-text mx-3">REPACK</div>
        </a>
         <hr class="sidebar-divider my-0">
        <li class="nav-item">
            <a class="nav-link" href="<?= site_url('auth/login'); ?>">
                <i class="fas fa-fw fa-sign-in-alt"></i>
                <span>Login</span></a>
        </li>
    <?php endif; ?>

    <?php if (isset($user['role_id'])) : ?>
    <hr class="sidebar-divider d-none d-md-block">

    <li class="nav-item">
        <a class="nav-link" href="#" data-toggle="modal" data-target="#logoutModal">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Logout</span></a>
    </li>
    <?php endif; ?>

    <div class="text-center d-none d-md-inline mt-3">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
