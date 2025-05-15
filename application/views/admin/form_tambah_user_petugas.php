<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Tambah User Petugas'); ?></h1>
        <a href="<?= site_url('admin/manajemen_user'); ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Manajemen User
        </a>
    </div>

    <?php if (validation_errors()) : ?>
        <div class="alert alert-danger" role="alert">
            <?= validation_errors(); ?>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('message')) { echo $this->session->flashdata('message'); } ?>


    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulir Tambah User <?= htmlspecialchars($target_role_info['role'] ?? 'Petugas'); ?></h6>
        </div>
        <div class="card-body">
            <form action="<?= site_url('admin/tambah_user_petugas'); // Atau URL dinamis jika untuk role lain ?>" method="post">
                <div class="form-group">
                    <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= form_error('name') ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?= set_value('name'); ?>" required>
                    <?= form_error('name', '<small class="text-danger pl-3">', '</small>'); ?>
                </div>

                <div class="form-group">
                    <label for="nip">NIP (Nomor Induk Pegawai) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= form_error('nip') ? 'is-invalid' : ''; ?>" id="nip" name="nip" placeholder="Masukkan NIP untuk login" value="<?= set_value('nip'); ?>" required>
                    <small class="form-text text-muted">NIP akan digunakan untuk login.</small>
                    <?= form_error('nip', '<small class="text-danger pl-3">', '</small>'); ?>
                </div>

                <div class="form-group">
                    <label for="password">Password Awal <span class="text-danger">*</span></label>
                    <input type="password" class="form-control <?= form_error('password') ? 'is-invalid' : ''; ?>" id="password" name="password" required>
                    <small class="form-text text-muted">Minimal 6 karakter. User akan diminta mengganti password ini saat login pertama.</small>
                    <?= form_error('password', '<small class="text-danger pl-3">', '</small>'); ?>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password Awal <span class="text-danger">*</span></label>
                    <input type="password" class="form-control <?= form_error('confirm_password') ? 'is-invalid' : ''; ?>" id="confirm_password" name="confirm_password" required>
                    <?= form_error('confirm_password', '<small class="text-danger pl-3">', '</small>'); ?>
                </div>

                <?php if (isset($target_role_info) && $target_role_info['id'] == 3) : // Asumsi Role ID 3 adalah Petugas ?>
                <hr>
                <h6 class="text-muted">Data Detail Spesifik untuk Role Petugas</h6>
                <div class="form-group">
                    <label for="jabatan_petugas">Jabatan Petugas <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= form_error('jabatan_petugas') ? 'is-invalid' : ''; ?>" id="jabatan_petugas" name="jabatan_petugas" value="<?= set_value('jabatan_petugas'); ?>" required>
                    <?= form_error('jabatan_petugas', '<small class="text-danger pl-3">', '</small>'); ?>
                </div>
                <?php endif; ?>
                <?php // Tambahkan blok serupa jika ada field spesifik untuk role Monitoring ?>


                <button type="submit" class="btn btn-primary">Simpan User</button>
                <a href="<?= site_url('admin/manajemen_user'); ?>" class="btn btn-secondary ml-2">Batal</a>
            </form>
        </div>
    </div>
</div>
