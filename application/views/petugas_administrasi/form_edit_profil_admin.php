<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $subtitle; ?></h1>

    <div class="row">
        <div class="col-lg-8">
            <!-- <?= $this->session->flashdata('message'); ?> -->

            <?= form_open_multipart('petugas_administrasi/edit_profil'); ?>
            <div class="form-group row">
                <label for="login_identifier" class="col-sm-3 col-form-label">Email (Login)</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="login_identifier" name="login_identifier" value="<?= $user['email']; ?>">
                    <?= form_error('login_identifier', '<small class="text-danger pl-3">', '</small>'); ?>
                </div>
            </div>
            <div class="form-group row">
                <label for="name" class="col-sm-3 col-form-label">Nama Lengkap</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="name" name="name" value="<?= $user['name']; ?>">
                    <?= form_error('name', '<small class="text-danger pl-3">', '</small>'); ?>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-3">Foto Profil</div>
                <div class="col-sm-9">
                    <div class="row">
                        <div class="col-sm-3">
                            <img src="<?= base_url('uploads/profile_images/') . $user['image']; ?>" class="img-thumbnail" alt="Profile Image">
                        </div>
                        <div class="col-sm-9">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="profile_image" name="profile_image">
                                <label class="custom-file-label" for="profile_image">Pilih file...</label>
                                <small class="form-text text-muted">Format: JPG, PNG, JPEG, GIF. Max: 2MB, 1024x1024px.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group row justify-content-end">
                <div class="col-sm-9">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
            </form>

            <hr>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Keamanan Akun</label>
                <div class="col-sm-9">
                     <p class="form-text text-muted">Amankan akun Anda dengan lapisan verifikasi tambahan.</p>
                     <a href="<?= base_url('petugas_administrasi/reset_mfa'); ?>" class="btn btn-info">
                        <i class="fas fa-shield-alt fa-fw"></i> Atur Multi-Factor Authentication (MFA)
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// Untuk menampilkan nama file di input custom file bootstrap
$('.custom-file-input').on('change', function() {
    let fileName = $(this).val().split('\\').pop();
    $(this).next('.custom-file-label').addClass("selected").html(fileName);
});
</script>