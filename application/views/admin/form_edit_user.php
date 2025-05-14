<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Edit User'); ?></h1>
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
            <h6 class="m-0 font-weight-bold text-primary">
                Edit Data untuk User: <?= htmlspecialchars($target_user_data['name'] ?? 'Tidak Ditemukan'); ?>
                (<?= htmlspecialchars($target_user_data['email'] ?? ''); ?>)
            </h6>
        </div>
        <div class="card-body">
            <?php if (isset($target_user_data) && $target_user_data) : ?>
            <form action="<?= site_url('admin/edit_user/' . $target_user_data['id']); ?>" method="post">
                <div class="form-group">
                    <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= set_value('name', $target_user_data['name']); ?>" required>
                    <?= form_error('name', '<small class="text-danger pl-3">', '</small>'); ?>
                </div>
                <div class="form-group">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= set_value('email', $target_user_data['email']); ?>" required>
                    <?= form_error('email', '<small class="text-danger pl-3">', '</small>'); ?>
                </div>

                <div class="form-group">
                    <label for="role_id">Role <span class="text-danger">*</span></label>
                    <select class="form-control" id="role_id" name="role_id" required <?= ($target_user_data['id'] == 1 || ($target_user_data['role_id'] == 1 && $target_user_data['id'] == $user['id'])) ? 'disabled' : ''; ?>>
                        <option value="">-- Pilih Role --</option>
                        <?php foreach ($roles_list as $role_item) : ?>
                            <option value="<?= $role_item['id']; ?>" <?= set_select('role_id', $role_item['id'], ($target_user_data['role_id'] == $role_item['id'])); ?>>
                                <?= htmlspecialchars($role_item['role']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($target_user_data['id'] == 1 || ($target_user_data['role_id'] == 1 && $target_user_data['id'] == $user['id'])) : ?>
                        <small class="form-text text-muted">Role Admin Utama tidak dapat diubah.</small>
                        <input type="hidden" name="role_id" value="<?= $target_user_data['role_id']; // Kirim nilai asli jika disabled ?>">
                    <?php endif; ?>
                    <?= form_error('role_id', '<small class="text-danger pl-3">', '</small>'); ?>
                </div>

                <div class="form-group">
                    <label for="is_active">Status Akun <span class="text-danger">*</span></label>
                    <select class="form-control" id="is_active" name="is_active" required <?= ($target_user_data['id'] == 1 || ($target_user_data['role_id'] == 1 && $target_user_data['id'] == $user['id'])) ? 'disabled' : ''; ?>>
                        <option value="1" <?= set_select('is_active', '1', ($target_user_data['is_active'] == 1)); ?>>Aktif</option>
                        <option value="0" <?= set_select('is_active', '0', ($target_user_data['is_active'] == 0)); ?>>Tidak Aktif</option>
                    </select>
                     <?php if ($target_user_data['id'] == 1 || ($target_user_data['role_id'] == 1 && $target_user_data['id'] == $user['id'])) : ?>
                        <small class="form-text text-muted">Status Admin Utama tidak dapat diubah.</small>
                         <input type="hidden" name="is_active" value="<?= $target_user_data['is_active']; // Kirim nilai asli jika disabled ?>">
                    <?php endif; ?>
                    <?= form_error('is_active', '<small class="text-danger pl-3">', '</small>'); ?>
                </div>

                <?php if ($target_user_data['role_id'] == 3) : // Asumsi Role ID 3 adalah Petugas ?>
                <?php
                    // Ambil data petugas spesifik jika ada
                    $petugas_detail_edit = $this->db->get_where('petugas', ['id_user' => $target_user_data['id']])->row_array();
                ?>
                <hr>
                <h6 class="text-muted">Data Detail Petugas (jika Role Petugas)</h6>
                <div class="form-group">
                    <label for="nip_petugas_edit">NIP Petugas</label>
                    <input type="text" class="form-control" id="nip_petugas_edit" name="nip_petugas_edit" value="<?= set_value('nip_petugas_edit', $petugas_detail_edit['NIP'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="jabatan_petugas_edit">Jabatan Petugas</label>
                    <input type="text" class="form-control" id="jabatan_petugas_edit" name="jabatan_petugas_edit" value="<?= set_value('jabatan_petugas_edit', $petugas_detail_edit['Jabatan'] ?? ''); ?>">
                </div>
                <?php endif; ?>


                <button type="submit" class="btn btn-primary">Update Data User</button>
            </form>
            <?php else: ?>
                <p class="text-danger">Data user tidak ditemukan.</p>
            <?php endif; ?>
        </div>
    </div>
</div>