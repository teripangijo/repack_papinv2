<?php // application/views/admin/form_tambah_user_view.php ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Tambah User Baru'); ?></h1>
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
            <h6 class="m-0 font-weight-bold text-primary">Formulir Tambah User untuk Role: <?= htmlspecialchars($target_role_info['role'] ?? 'Tidak Diketahui'); ?></h6>
        </div>
        <div class="card-body">
            <?php echo form_open(site_url('admin/tambah_user/' . $role_id_to_add)); ?>
                <input type="hidden" name="role_id_hidden" value="<?= htmlspecialchars($role_id_to_add); ?>"> <?php // Opsional, bisa juga diambil dari URL di controller ?>

                <div class="form-group">
                    <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= form_error('name') ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?= set_value('name'); ?>" required>
                    <?= form_error('name', '<small class="text-danger pl-3">', '</small>'); ?>
                </div>

                <?php
                $login_identifier_label_view = 'Login Identifier';
                $login_identifier_placeholder = 'Masukkan Email atau NIP';
                $login_identifier_help_text = 'Digunakan untuk login.';
                if ($role_id_to_add == 2) { // Pengguna Jasa
                    $login_identifier_label_view = 'Email';
                    $login_identifier_placeholder = 'Contoh: user@example.com';
                } elseif ($role_id_to_add == 3) { // Petugas
                    $login_identifier_label_view = 'NIP (Nomor Induk Pegawai)';
                    $login_identifier_placeholder = 'Masukkan NIP Petugas';
                    $login_identifier_help_text = 'NIP akan digunakan untuk login.';
                } elseif ($role_id_to_add == 4) { // Monitoring
                    $login_identifier_label_view = 'NIP (Nomor Induk Pegawai)';
                    $login_identifier_placeholder = 'Masukkan NIP Monitoring';
                    $login_identifier_help_text = 'NIP akan digunakan untuk login.';
                }
                ?>
                <div class="form-group">
                    <label for="login_identifier"><?= htmlspecialchars($login_identifier_label_view); ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= form_error('login_identifier') ? 'is-invalid' : ''; ?>" id="login_identifier" name="login_identifier" placeholder="<?= htmlspecialchars($login_identifier_placeholder); ?>" value="<?= set_value('login_identifier'); ?>" required>
                    <small class="form-text text-muted"><?= htmlspecialchars($login_identifier_help_text); ?></small>
                    <?= form_error('login_identifier', '<small class="text-danger pl-3">', '</small>'); ?>
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

                <?php // Field spesifik untuk Role Petugas (ID 3) ?>
                <?php if ($role_id_to_add == 3) : ?>
                <hr>
                <h6 class="text-muted">Data Detail Spesifik untuk Role Petugas</h6>
                <div class="form-group">
                    <label for="jabatan_petugas">Jabatan Petugas <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= form_error('jabatan_petugas') ? 'is-invalid' : ''; ?>" id="jabatan_petugas" name="jabatan_petugas" value="<?= set_value('jabatan_petugas'); ?>" required>
                    <?= form_error('jabatan_petugas', '<small class="text-danger pl-3">', '</small>'); ?>
                </div>
                <?php endif; ?>

                <?php // Tambahkan blok elseif untuk field spesifik Role Monitoring (ID 4) jika ada ?>
                <?php /*
                <?php elseif ($role_id_to_add == 4) : ?>
                <hr>
                <h6 class="text-muted">Data Detail Spesifik untuk Role Monitoring</h6>
                <div class="form-group">
                    <label for="field_monitoring">Field Monitoring <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= form_error('field_monitoring') ? 'is-invalid' : ''; ?>" id="field_monitoring" name="field_monitoring" value="<?= set_value('field_monitoring'); ?>" required>
                    <?= form_error('field_monitoring', '<small class="text-danger pl-3">', '</small>'); ?>
                </div>
                <?php endif; ?>
                */ ?>

                <button type="submit" class="btn btn-primary">Simpan User</button>
                <a href="<?= site_url('admin/manajemen_user'); ?>" class="btn btn-secondary ml-2">Batal</a>
            </form>
        </div>
    </div>
</div>