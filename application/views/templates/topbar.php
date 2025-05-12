<?php
// Ambil data user dari variabel $user yang dikirim oleh controller
// Sediakan nilai default jika variabel tidak ada atau kosong
$userName = isset($user['name']) ? htmlspecialchars($user['name']) : 'Guest';
// Cek apakah $user['image'] ada dan tidak kosong, jika tidak gunakan default.jpg
$userImage = isset($user['image']) && !empty($user['image']) ? htmlspecialchars($user['image']) : 'default.jpg';
// Bentuk path yang benar ke gambar profil, termasuk subfolder 'profile'
$profileImagePath = base_url('uploads/kop/') . $userImage;
?>
<div id="content-wrapper" class="d-flex flex-column">

    <div id="content">

        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>

            <ul class="navbar-nav ml-auto">

                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                            <?= $userName; // Tampilkan nama user ?>
                        </span>
                        <img class="img-profile rounded-circle"
                             src="<?= $profileImagePath; // Gunakan path yang sudah benar ?>"
                             alt="<?= $userName; ?> profile picture"> <?php // Tambahkan alt text ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="<?= site_url('user'); // Link ke profil user ?>">
                             <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                             Profile
                         </a>
                         <a class="dropdown-item" href="<?= site_url('user/edit'); // Link ke edit profil ?>">
                             <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                             Edit Profile
                         </a>
                         <a class="dropdown-item" href="<?= site_url('auth/changepass/' . (isset($user['id']) ? $user['id'] : '')); // Link ke ganti password ?>">
                             <i class="fas fa-key fa-sm fa-fw mr-2 text-gray-400"></i>
                             Change Password
                         </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Logout
                        </a>
                    </div>
                </li>

            </ul>

        </nav>
        <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                        <a class="btn btn-primary" href="<?= site_url('auth/logout'); ?>">Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <?php // Jangan tutup div #content dan #content-wrapper di sini, ini harusnya ditutup di template footer ?>
