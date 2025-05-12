<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Halaman Login">
    <meta name="author" content="Pengembang Aplikasi">

    <title><?= isset($title) ? htmlspecialchars($title) : 'Login Page'; ?></title>

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
        }
        /* Hapus margin atas/bawah default dari card jika body sudah flex-center */
        .card.o-hidden.border-0.shadow-lg {
             margin-top: 0 !important;
             margin-bottom: 0 !important;
        }
    </style>
</head>

<body class="bg-gradient-primary">

    <div class="container">

        <div class="row justify-content-center">

            <?php // Samakan kelas kolom ini dengan halaman registrasi ?>
            <div class="col-xl-6 col-lg-7 col-md-9">

                <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body p-0">
                        <div class="row">
                            <?php // Jika Anda tidak menggunakan gambar di sisi kiri login, cukup satu kolom ini ?>
                            <div class="col-lg-12">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h3 text-gray-900 mb-4">Login Page</h1>
                                    </div>

                                    <?= $this->session->flashdata('message'); ?>

                                    <form class="user" method="post" action="<?= base_url('auth'); ?>">
                                        <div class="form-group">
                                            <input type="email" class="form-control form-control-user <?= (form_error('email')) ? 'is-invalid' : ''; ?>"
                                                id="email" name="email" placeholder="Enter Email Address..."
                                                value="<?= set_value('email'); ?>" required>
                                            <?= form_error('email', '<small class="text-danger pl-3">', '</small>'); ?>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user <?= (form_error('password')) ? 'is-invalid' : ''; ?>"
                                                id="password" name="password" placeholder="Password" required>
                                            <?= form_error('password', '<small class="text-danger pl-3">', '</small>'); ?>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                        <hr>
                                    </form>
                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="<?= base_url('auth/forgot_password'); ?>">Forgot Password?</a>
                                    </div>
                                    <div class="text-center mt-2">
                                        <a class="small" href="<?= base_url('auth/registration'); ?>">Create an Account!</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <script src="<?= base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>

    <script src="<?= base_url('assets/vendor/jquery-easing/jquery.easing.min.js'); ?>"></script>

    <script src="<?= base_url('assets/js/sb-admin-2.min.js'); ?>"></script>

</body>

</html>
