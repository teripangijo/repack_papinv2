<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Tambah User Petugas'); ?></h1>

    <?php if (validation_errors()) : ?>
        <div class="alert alert-danger" role="alert">
            <?= validation_errors(); ?>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('message')) { echo $this->session->flashdata('message'); } ?>


    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulir Tambah User Petugas</h6>
        </div>
        <div class="card-body">
            <form action="<?= site_url('admin/tambah_user_petugas'); ?>" method="post">
                <div class="form-group">
                    <label for="name">Nama Lengkap Petugas <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= set_value('name'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Petugas <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= set_value('email'); ?>" required>
                    <small class="form-text text-muted">Email akan digunakan untuk login.</small>
                </div>
                <div class="form-group">
                    <label for="password">Password Awal <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <small class="form-text text-muted">Minimal 6 karakter. Petugas akan diminta mengganti password ini saat login pertama.</small>
                </div>
                <hr>
                <h6 class="text-muted">Data Detail Petugas (Opsional, jika tabel `petugas` terpisah)</h6>
                <div class="form-group">
                    <label for="nip_petugas">NIP Petugas</label>
                    <input type="text" class="form-control" id="nip_petugas" name="nip_petugas" value="<?= set_value('nip_petugas'); ?>">
                </div>
                <div class="form-group">
                    <label for="jabatan_petugas">Jabatan Petugas</label>
                    <input type="text" class="form-control" id="jabatan_petugas" name="jabatan_petugas" value="<?= set_value('jabatan_petugas'); ?>">
                </div>

                <button type="submit" class="btn btn-primary">Simpan Petugas</button>
                <a href="<?= site_url('admin/manajemen_user'); ?>" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>