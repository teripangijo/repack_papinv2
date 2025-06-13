<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">Verifikasi Login Anda</h1>
                                    <p>Buka aplikasi authenticator Anda dan masukkan kode 6-digit.</p>
                                </div>
                                <?= $this->session->flashdata('message'); ?>
                                <form class="user" method="post" action="<?= base_url('auth/verify_mfa_login'); ?>">
                                    <div class="form-group">
                                        <input type="text" class="form-control form-control-user text-center" id="mfa_code" name="mfa_code" placeholder="xxxxxx" autofocus autocomplete="off" maxlength="6">
                                        <?= form_error('mfa_code', '<small class="text-danger pl-3">', '</small>'); ?>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-user btn-block">
                                        Verifikasi
                                    </button>
                                </form>
                                <hr>
                                <div class="text-center">
                                    <a class="small" href="<?= base_url('auth/logout'); ?>">Bukan Anda? Kembali ke Login</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>