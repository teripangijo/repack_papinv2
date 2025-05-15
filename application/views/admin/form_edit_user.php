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
                (<?= htmlspecialchars($target_user_data['email'] ?? ''); ?>) </h6>
        </div>
        <div class="card-body">
            <?php if (isset($target_user_data) && $target_user_data) : ?>
            <?php
                // Tentukan apakah target adalah Petugas (3) atau Monitoring (4)
                // Variabel $is_target_petugas_or_monitoring sudah disiapkan di controller
                // Jika belum, kita bisa cek di sini:
                $is_petugas_or_monitoring_role = in_array($target_user_data['role_id'], [3, 4]);
                $login_identifier_label = $is_petugas_or_monitoring_role ? 'NIP (Nomor Induk Pegawai)' : 'Email';
                $login_identifier_type = $is_petugas_or_monitoring_role ? 'text' : 'email'; // Bisa juga 'number' untuk NIP jika hanya angka
            ?>
            <form action="<?= site_url('admin/edit_user/' . $target_user_data['id']); ?>" method="post">
                <div class="form-group">
                    <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= set_value('name', $target_user_data['name']); ?>" required>
                    <?= form_error('name', '<small class="text-danger pl-3">', '</small>'); ?>
                </div>

                <div class="form-group">
                    <label for="login_identifier"><?= $login_identifier_label; ?> <span class="text-danger">*</span></label>
                    <input type="<?= $login_identifier_type; ?>" class="form-control" id="login_identifier" name="login_identifier" value="<?= set_value('login_identifier', $target_user_data['email']); // Kolom 'email' di DB menyimpan Email atau NIP ?>" required>
                    <?= form_error('login_identifier', '<small class="text-danger pl-3">', '</small>'); ?>
                </div>

                <div class="form-group">
                    <label for="role_id">Role <span class="text-danger">*</span></label>
                    <select class="form-control" id="role_id" name="role_id" required <?= ($target_user_data['id'] == 1) ? 'disabled' : ''; // Admin utama (ID 1) tidak bisa diubah role-nya ?>>
                        <option value="">-- Pilih Role --</option>
                        <?php foreach ($roles_list as $role_item) : ?>
                            <option value="<?= $role_item['id']; ?>" <?= set_select('role_id', $role_item['id'], ($target_user_data['role_id'] == $role_item['id'])); ?>>
                                <?= htmlspecialchars($role_item['role']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($target_user_data['id'] == 1) : ?>
                        <small class="form-text text-muted">Role Admin Utama tidak dapat diubah.</small>
                        <input type="hidden" name="role_id" value="<?= $target_user_data['role_id']; ?>">
                    <?php endif; ?>
                    <?= form_error('role_id', '<small class="text-danger pl-3">', '</small>'); ?>
                </div>

                <div class="form-group">
                    <label for="is_active">Status Akun <span class="text-danger">*</span></label>
                    <select class="form-control" id="is_active" name="is_active" required <?= ($target_user_data['id'] == 1) ? 'disabled' : ''; ?>>
                        <option value="1" <?= set_select('is_active', '1', ($target_user_data['is_active'] == 1)); ?>>Aktif</option>
                        <option value="0" <?= set_select('is_active', '0', ($target_user_data['is_active'] == 0)); ?>>Tidak Aktif</option>
                    </select>
                     <?php if ($target_user_data['id'] == 1) : ?>
                        <small class="form-text text-muted">Status Admin Utama tidak dapat diubah.</small>
                         <input type="hidden" name="is_active" value="<?= $target_user_data['is_active']; ?>">
                    <?php endif; ?>
                    <?= form_error('is_active', '<small class="text-danger pl-3">', '</small>'); ?>
                </div>

                <?php
                    // Menampilkan field NIP dan Jabatan hanya jika role yang dipilih atau role saat ini adalah Petugas (ID 3)
                    // Ini memerlukan JavaScript untuk mengubah tampilan secara dinamis jika role diubah di dropdown,
                    // atau kita bisa menampilkan field ini jika role saat ini adalah Petugas.
                    // Untuk kesederhanaan, kita tampilkan jika role saat ini adalah Petugas.
                    // Controller akan menangani penyimpanan NIP ke tabel 'petugas' jika role adalah Petugas.
                    $is_target_petugas = ($target_user_data['role_id'] == 3); // Asumsi Role ID 3 adalah Petugas
                    $petugas_detail_edit = null;
                    if ($is_target_petugas) {
                        $petugas_detail_edit = $this->db->get_where('petugas', ['id_user' => $target_user_data['id']])->row_array();
                    }
                ?>
                <div id="petugas_fields_edit" style="<?= $is_target_petugas ? 'display:block;' : 'display:none;'; ?>">
                    <hr>
                    <h6 class="text-muted">Data Detail Petugas (Khusus Role Petugas)</h6>
                    <div class="form-group">
                        <label for="nip_petugas_edit">NIP (Nomor Induk Pegawai)</label>
                        <input type="text" class="form-control" id="nip_petugas_edit" name="nip_petugas_edit" 
                               value="<?= set_value('nip_petugas_edit', $petugas_detail_edit['NIP'] ?? ($is_target_petugas ? $target_user_data['email'] : '') ); ?>" 
                               <?= $is_target_petugas ? 'readonly' : ''; // NIP diambil dari login_identifier jika role Petugas ?>>
                        <small class="form-text text-muted">Untuk role Petugas, NIP diambil dari field NIP di atas dan akan disinkronkan. Field ini hanya untuk referensi atau jika ada NIP terpisah.</small>
                    </div>
                    <div class="form-group">
                        <label for="jabatan_petugas_edit">Jabatan Petugas</label>
                        <input type="text" class="form-control" id="jabatan_petugas_edit" name="jabatan_petugas_edit" value="<?= set_value('jabatan_petugas_edit', $petugas_detail_edit['Jabatan'] ?? ''); ?>">
                    </div>
                </div>


                <button type="submit" class="btn btn-primary">Update Data User</button>
            </form>
            <?php else: ?>
                <p class="text-danger">Data user tidak ditemukan.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// JavaScript untuk menampilkan/menyembunyikan field detail petugas berdasarkan pilihan role
document.addEventListener('DOMContentLoaded', function() {
    const roleDropdown = document.getElementById('role_id');
    const petugasFieldsDiv = document.getElementById('petugas_fields_edit');
    const nipPetugasEditInput = document.getElementById('nip_petugas_edit');
    const loginIdentifierInput = document.getElementById('login_identifier');
    const loginIdentifierLabel = document.querySelector('label[for="login_identifier"]');


    function togglePetugasFields() {
        const selectedRoleId = parseInt(roleDropdown.value);
        // Asumsi Role ID Petugas = 3, Monitoring = 4
        if (selectedRoleId === 3) { // Petugas
            petugasFieldsDiv.style.display = 'block';
            if (loginIdentifierInput) { // Sinkronkan NIP jika field login_identifier ada
                 nipPetugasEditInput.value = loginIdentifierInput.value; // NIP di detail petugas mengikuti NIP login
                 nipPetugasEditInput.setAttribute('readonly', 'readonly');
            }
            if (loginIdentifierLabel) loginIdentifierLabel.textContent = 'NIP (Nomor Induk Pegawai) *';
            if (loginIdentifierInput) loginIdentifierInput.type = 'text'; // atau 'number'

        } else if (selectedRoleId === 4) { // Monitoring
            petugasFieldsDiv.style.display = 'none'; // Monitoring tidak punya detail NIP/Jabatan di tabel petugas
            if (loginIdentifierLabel) loginIdentifierLabel.textContent = 'NIP (Nomor Induk Pegawai) *';
            if (loginIdentifierInput) loginIdentifierInput.type = 'text'; // atau 'number'
        }
        else { // Role lain (Admin, Pengguna Jasa)
            petugasFieldsDiv.style.display = 'none';
            if (loginIdentifierLabel) loginIdentifierLabel.textContent = 'Email *';
            if (loginIdentifierInput) loginIdentifierInput.type = 'email';
        }
    }

    if (roleDropdown) {
        // Panggil saat halaman dimuat untuk set tampilan awal
        togglePetugasFields();
        // Tambahkan event listener untuk perubahan dropdown role
        roleDropdown.addEventListener('change', togglePetugasFields);
    }
    
    // Jika role adalah Petugas atau Monitoring, NIP/Email di field login_identifier juga mengisi field NIP di detail petugas (jika ada)
    if (loginIdentifierInput && nipPetugasEditInput) {
        loginIdentifierInput.addEventListener('input', function() {
            const selectedRoleId = parseInt(roleDropdown.value);
            if (selectedRoleId === 3) { // Hanya jika role Petugas
                nipPetugasEditInput.value = this.value;
            }
        });
    }
});
</script>
