<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Manajemen User'); ?></h1>
        <a href="<?= site_url('admin/tambah_user_petugas'); ?>" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-user-plus fa-sm text-white-50"></i> Tambah User Petugas
        </a>
    </div>

    <?php if ($this->session->flashdata('message')) { echo $this->session->flashdata('message'); } ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar User Sistem</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableManajemenUser" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status Aktif</th>
                            <th>Tgl. Dibuat</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users_list)): $no = 1; foreach ($users_list as $usr): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($usr['name']); ?></td>
                            <td><?= htmlspecialchars($usr['email']); ?></td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($usr['role_name'] ?? 'N/A'); ?></span></td>
                            <td>
                                <?php if ($usr['is_active'] == 1): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Tidak Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td><?= isset($usr['date_created']) ? date('d/m/Y H:i', $usr['date_created']) : '-'; ?></td>
                            <td>
                                <a href="<?= site_url('admin/edit_user/' . $usr['id']); ?>" class="btn btn-warning btn-circle btn-sm" title="Edit User">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($usr['role_id'] == 2 || $usr['role_id'] == 3): // Hanya untuk Pengguna Jasa & Petugas ?>
                                <a href="<?= site_url('admin/ganti_password_user/' . $usr['id']); ?>" class="btn btn-info btn-circle btn-sm" title="Ganti Password User Ini">
                                    <i class="fas fa-key"></i>
                                </a>
                                <?php endif; ?>
                                <?php if ($usr['role_id'] != 1) : // Admin tidak bisa dihapus dari sini ?>
                                <a href="<?= site_url('admin/delete_user/' . $usr['id']); ?>" class="btn btn-danger btn-circle btn-sm" title="Hapus User" onclick="return confirm('Apakah Anda yakin ingin menghapus user <?= htmlspecialchars($usr['name']); ?>?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="7" class="text-center">Tidak ada data user.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#dataTableManajemenUser').DataTable({
            "order": [[1, "asc"]], // Urut berdasarkan Nama
            "columnDefs": [
                { "orderable": false, "targets": [0, 6] } // Kolom # dan Action
            ]
        });
    }
});
</script>