<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Halaman Login Aplikasi Returnable Package">
    <meta name="author" content="Pengembang Aplikasi Anda">

    <title><?= isset($title) ? htmlspecialchars($title) : 'Login REPACK'; ?></title>

    <link href="<?= base_url('assets/vendor/fontawesome-free/css/all.min.css'); ?>" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <link href="<?= base_url('assets/css/sb-admin-2.min.css'); ?>" rel="stylesheet">
    <style>
        /* Memastikan body dan html mengambil tinggi penuh untuk min-height: 100vh bekerja dengan baik */
        html, body {
            height: 100%;
        }
        body.bg-gradient-primary {
            display: flex; /* Menggunakan flexbox untuk centering */
            align-items: center; /* Memusatkan secara vertikal */
            justify-content: center; /* Memusatkan secara horizontal */
            min-height: 100vh; /* Minimal tinggi body adalah tinggi viewport */
            padding-top: 40px; /* Tambahkan padding atas jika diperlukan */
            padding-bottom: 40px; /* Tambahkan padding bawah jika diperlukan */
        }
        /* Hapus margin atas/bawah default dari card jika body sudah flex-center */
        .card.o-hidden.border-0.shadow-lg {
             margin-top: 0 !important;
             margin-bottom: 0 !important;
        }
        .auth-footer-copyright {
            position: fixed; /* Tetap di posisi viewport */
            left: 0;
            bottom: 10px; /* Jarak dari bawah viewport */
            width: 100%;
            text-align: center;
            color: rgba(255, 255, 255, 0.7); /* Warna putih dengan sedikit transparansi, sesuaikan dengan background Anda */
            font-size: 0.875em; /* Ukuran font sedikit lebih kecil */
            padding: 10px 0; /* Sedikit padding atas-bawah */
            z-index: 1000; /* Pastikan di atas elemen lain jika ada yang tumpang tindih */
        }
    </style>
</head>

<body class="bg-gradient-primary">

    <div class="container">

        <div class="row justify-content-center">

            <?php // Sesuaikan lebar kolom jika perlu, misal col-xl-5 atau col-xl-6 ?>
            <div class="col-xl-5 col-lg-6 col-md-8">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Selamat Datang di Aplikasi Repack</h1>
                                        <img src="<?= base_url('/assets/img/lambang_bc.png');?>" alt="Logo Instansi" style="max-width: 150px; margin-bottom: 20px;">
                                        <!-- <h3 class="h4 text-gray-900 mb-4">KPPBC TMP C Pangkalpinang</h3> -->
                                        <p class="text-muted mb-4">Silakan login ke akun REPACK Anda.</p>
                                    </div>

                                    <?= $this->session->flashdata('message'); // Untuk menampilkan pesan sukses logout, error login, dll. ?>

                                    <form class="user" method="post" action="<?= base_url('auth'); // Form action ke Auth controller (method index) ?>">
                                        <div class="form-group">
                                            <input type="text" class="form-control form-control-user <?= (form_error('login_identifier')) ? 'is-invalid' : ''; ?>"
                                                id="login_identifier" name="login_identifier" 
                                                placeholder="Masukkan Email atau NIP Anda..."
                                                value="<?= set_value('login_identifier'); ?>" required>
                                            <?= form_error('login_identifier', '<small class="text-danger pl-3">', '</small>'); ?>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user <?= (form_error('password')) ? 'is-invalid' : ''; ?>"
                                                id="password" name="password" placeholder="Password" required>
                                            <?= form_error('password', '<small class="text-danger pl-3">', '</small>'); ?>
                                        </div>
                                        
                                        <?php // Anda bisa menambahkan "Ingat Saya" jika mau ?>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                        <?php // Contoh jika ingin login dengan Google atau Facebook ?>
                                        </form>
                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="<?= base_url('auth/forgot_password'); // Pastikan method ini ada jika linknya aktif ?>">Lupa Password?</a>
                                    </div>
                                    <div class="text-center">
                                        <a class="small" href="<?= base_url('auth/registration'); ?>">Buat Akun Baru! (Pengguna Jasa)</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="auth-footer-copyright">
        Copyright © Bea Cukai Pangkalpinang 2025
    </div>
    
    <script src="<?= base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/jquery-easing/jquery.easing.min.js'); ?>"></script>
    <script src="<?= base_url('assets/js/sb-admin-2.min.js'); ?>"></script>

</body>

</html>
