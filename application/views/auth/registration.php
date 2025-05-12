<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Halaman Registrasi">
    <meta name="author" content="Pengembang Aplikasi">

    <title><?= isset($title) ? htmlspecialchars($title) : 'Registration'; ?></title>

    <link href="<?= base_url('assets/vendor/fontawesome-free/css/all.min.css'); ?>" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <link href="<?= base_url('assets/css/sb-admin-2.min.css'); ?>" rel="stylesheet">
    <style>
        /* Style untuk centering vertikal dan horizontal */
        html, body {
            height: 100%;
        }
        body.bg-gradient-primary {
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            /* Anda bisa menambahkan padding atas/bawah jika card terlalu menempel ke tepi viewport */
            /* padding-top: 2rem; */
            /* padding-bottom: 2rem; */
        }
        /* Menghapus margin atas/bawah default dari card jika body sudah flex-center */
        .card.o-hidden.border-0.shadow-lg {
             margin-top: 0 !important;
             margin-bottom: 0 !important;
        }
        .register-image-custom {
            /* Jika Anda ingin gambar di sisi kiri, atur background-image di sini */
            /* background-image: url("<?= base_url('assets/img/your-register-image.jpg'); ?>"); */
            background-size: cover;
            background-position: center;
        }
    </style>
</head>

<body class="bg-gradient-primary">

    <div class="container">
        <div class="row justify-content-center">

            <?php // Kelas kolom ini disamakan dengan halaman login untuk konsistensi lebar card ?>
            <div class="col-xl-6 col-lg-7 col-md-9">

                <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body p-0">
                        <div class="row">
                            <?php // Jika Anda tidak menggunakan gambar di sisi kiri registrasi, buat kolom ini mengambil lebar penuh ?>
                            <div class="col-lg-12"> 
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Create an Account!</h1>
                                    </div>

                                    <?php
                                    // Menampilkan flashdata message jika ada
                                    // Sebaiknya hanya pesan sukses atau info yang relevan dengan registrasi
                                    $flashdata_message = $this->session->flashdata('message');
                                    if ($flashdata_message) {
                                        // Filter untuk hanya menampilkan pesan sukses atau info di halaman registrasi
                                        if (strpos($flashdata_message, 'alert-success') !== false || strpos($flashdata_message, 'alert-info') !== false) {
                                            echo $flashdata_message;
                                        }
                                    }
                                    ?>

                                    <form class="user" method="post" action="<?= base_url('auth/registration'); ?>">
                                        <div class="form-group">
                                            <input type="text" class="form-control form-control-user <?= (form_error('name')) ? 'is-invalid' : ''; ?>" id="name" name="name"
                                                placeholder="Full Name" value="<?= set_value('name'); ?>" required>
                                            <?= form_error('name', '<small class="text-danger pl-3">', '</small>'); ?>
                                        </div>
                                        <div class="form-group">
                                            <input type="email" class="form-control form-control-user <?= (form_error('email')) ? 'is-invalid' : ''; ?>" id="email" name="email"
                                                placeholder="Email Address" value="<?= set_value('email'); ?>" required>
                                            <?= form_error('email', '<small class="text-danger pl-3">', '</small>'); ?>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-6 mb-3 mb-sm-0">
                                                <input type="password" class="form-control form-control-user <?= (form_error('password')) ? 'is-invalid' : ''; ?>"
                                                    id="password" name="password" placeholder="Password" required>
                                                <?= form_error('password', '<small class="text-danger pl-3">', '</small>'); ?>
                                            </div>
                                            <div class="col-sm-6">
                                                <input type="password" class="form-control form-control-user <?= (form_error('password2')) ? 'is-invalid' : ''; ?>"
                                                    id="password2" name="password2" placeholder="Repeat Password" required>
                                                <?= form_error('password2', '<small class="text-danger pl-3">', '</small>'); ?>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Register Account
                                        </button>
                                    </form>
                                    <hr>
                                    <div class="text-center">
                                        <!-- <a class="small" href="<?= base_url('auth/forgot_password'); ?>">Forgot Password?</a> -->
                                    </div>
                                    <div class="text-center mt-2">
                                        <a class="small" href="<?= base_url('auth'); ?>">Already have an account? Login!</a>
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
