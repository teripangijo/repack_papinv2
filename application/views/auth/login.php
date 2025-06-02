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
    <link href="<?= base_url('assets/css/custom-modern.css'); ?>" rel="stylesheet">

    <link rel="icon" href="<?= base_url('favicon.ico'); ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?= base_url('favicon.png'); ?>" type="image/x-icon">
</head>

<body class="modern-login-bg">

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-xl-5 col-lg-6 col-md-8">
                <div class="card o-hidden border-0 shadow-lg my-4 modern-login-card">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="p-4 p-sm-5">
                                    <div class="text-center">
                                        <img src="<?= base_url('/assets/img/logo_papin.png');?>" alt="Logo Instansi" class="login-logo mb-3">
                                        <h1 class="h4 text-gray-900 mb-2">Selamat Datang</h1>
                                        <p class="text-muted mb-4">Login ke akun REPACK Anda.</p>
                                    </div>

                                    <?= $this->session->flashdata('message'); ?>

                                    <form class="user" method="post" action="<?= base_url('auth'); ?>">
                                        <div class="form-group">
                                            <input type="text" class="form-control form-control-user modern-form-control <?= (form_error('login_identifier')) ? 'is-invalid' : ''; ?>"
                                                id="login_identifier" name="login_identifier" 
                                                placeholder="Email atau NIP Anda"
                                                value="<?= set_value('login_identifier'); ?>" required>
                                            <?= form_error('login_identifier', '<small class="text-danger pl-3">', '</small>'); ?>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user modern-form-control <?= (form_error('password')) ? 'is-invalid' : ''; ?>"
                                                id="password" name="password" placeholder="Password" required>
                                            <?= form_error('password', '<small class="text-danger pl-3">', '</small>'); ?>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary btn-user btn-block modern-btn-login">
                                            Login
                                        </button>
                                    </form>
                                    <hr class="my-4">
                                    <div class="text-center">
                                        <a class="small modern-login-link" href="<?= base_url('auth/forgot_password'); ?>">Lupa Password?</a>
                                    </div>
                                    <div class="text-center">
                                        <a class="small modern-login-link" href="<?= base_url('auth/registration'); ?>">Buat Akun Baru (Pengguna Jasa)</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modern-auth-footer">
                    Copyright © Bea Cukai Pangkalpinang 2025
                </div>
            </div>
        </div>
    </div>

    <div class="image-attribution">
        Gambar oleh <a href="https://pixabay.com/id/users/ellisedelacruz-2310550/?utm_source=link-attribution&utm_medium=referral&utm_campaign=image&utm_content=1315672" target="_blank" rel="noopener noreferrer">Claire Dela Cruz</a> dari <a href="https://pixabay.com/id//?utm_source=link-attribution&utm_medium=referral&utm_campaign=image&utm_content=1315672" target="_blank" rel="noopener noreferrer">Pixabay</a>
    </div>
    
    <script src="<?= base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/jquery-easing/jquery.easing.min.js'); ?>"></script>
    <script src="<?= base_url('assets/js/sb-admin-2.min.js'); ?>"></script>

</body>
</html>