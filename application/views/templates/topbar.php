<?php
// Ambil data user dari variabel $user yang dikirim oleh controller
$userName = isset($user['name']) ? htmlspecialchars($user['name']) : 'Guest';
$userImageName = isset($user['image']) && !empty($user['image']) ? $user['image'] : 'default.jpg';

// Tentukan base path untuk gambar profil berdasarkan role atau path umum
$role_id_for_topbar = $this->session->userdata('role_id'); // Ambil role_id dari session
$profile_image_folder = 'uploads/kop/'; // Default untuk Pengguna Jasa (logo perusahaan)

if ($role_id_for_topbar == 1) { // Admin
    $profile_image_folder = 'uploads/profile_admin/'; // Contoh path untuk admin
} elseif ($role_id_for_topbar == 3) { // Petugas
    $profile_image_folder = 'uploads/profile_images/'; // Path yang Anda gunakan di Petugas::edit_profil()
} elseif ($role_id_for_topbar == 4) { // Monitoring
    $profile_image_folder = 'uploads/profile_monitoring/'; // Contoh path untuk monitoring
}
// Jika Anda menggunakan satu folder untuk semua foto profil user (selain logo perusahaan), sederhanakan:
// $profile_image_folder = 'uploads/profile_images/'; 

$profileImagePath = base_url($profile_image_folder . htmlspecialchars($userImageName));
$fallbackImagePath = base_url('assets/img/default-avatar.png'); // Fallback umum
?>
<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown no-arrow d-sm-none">
                    <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-search fa-fw"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                        aria-labelledby="searchDropdown">
                        <form class="form-inline mr-auto w-100 navbar-search">
                            <div class="input-group">
                                <input type="text" class="form-control bg-light border-0 small"
                                    placeholder="Search for..." aria-label="Search"
                                    aria-describedby="basic-addon2">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button">
                                        <i class="fas fa-search fa-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </li>

                <div class="topbar-divider d-none d-sm-block"></div>

                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                            <?= $userName; ?>
                        </span>
                        <img class="img-profile rounded-circle"
                            src="<?= $profileImagePath; ?>"
                            alt="<?= $userName; ?> profile picture"
                            onerror="this.onerror=null; this.src='<?= $fallbackImagePath; ?>';">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                        aria-labelledby="userDropdown">

                        <?php if ($role_id_for_topbar == 1) : // ADMIN ?>
                            <a class="dropdown-item" href="<?= site_url('admin/profile'); // Buat halaman profil admin jika perlu ?>">
                                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                My Profile (Admin)
                            </a>
                            <a class="dropdown-item" href="<?= site_url('admin/settings'); // Contoh link settings admin ?>">
                                <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                Settings
                            </a>
                        <?php elseif ($role_id_for_topbar == 2) : // PENGGUNA JASA ?>
                            <a class="dropdown-item" href="<?= site_url('user/index'); // Dashboard Pengguna Jasa bisa jadi profilnya juga ?>">
                                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                My Dashboard
                            </a>
                            <a class="dropdown-item" href="<?= site_url('user/edit'); ?>">
                                <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                Edit Profile & Perusahaan
                            </a>
                        <?php elseif ($role_id_for_topbar == 3) : // PETUGAS ?>
                            <a class="dropdown-item" href="<?= site_url('petugas/edit_profil'); // Link ke edit profil petugas ?>">
                                <i class="fas fa-user-edit fa-sm fa-fw mr-2 text-gray-400"></i>
                                Edit Profil Saya
                            </a>
                        <?php elseif ($role_id_for_topbar == 4) : // MONITORING ?>
                             <a class="dropdown-item" href="<?= site_url('monitoring/profile'); // Buat halaman profil monitoring jika perlu ?>">
                                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                My Profile (Monitoring)
                            </a>
                        <?php else: // Guest atau role tidak dikenal ?>
                             <a class="dropdown-item" href="#">
                                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                Profile
                            </a>
                        <?php endif; ?>
                        
                        <?php // Link Ganti Password dan Logout selalu ada jika user login ?>
                        <?php if ($this->session->userdata('email')) : ?>
                            <a class="dropdown-item" href="<?= site_url('auth/changepass'); // Link ke ganti password umum ?>">
                                <i class="fas fa-key fa-sm fa-fw mr-2 text-gray-400"></i>
                                Ganti Password
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Logout
                            </a>
                        <?php endif; ?>
                    </div>
                </li>
            </ul>
        </nav>
        <div class="container-fluid pt-1"> <?php if ($this->session->flashdata('message')) : ?>
                <?= $this->session->flashdata('message'); ?>
            <?php endif; ?>
        </div>
        <?php // Konten halaman spesifik akan dimulai SETELAH ini oleh controller ?>
