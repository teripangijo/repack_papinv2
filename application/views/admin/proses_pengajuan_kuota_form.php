<?php
// Load data user dan pengajuan (diasumsikan sudah ada di controller dan dikirim ke view)
// $user = ... ;
// $pengajuan = ... ;
// $title = ... ;
// $subtitle = ... ;
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Proses Pengajuan Kuota'; ?></h1>
    </div>

    <?php if (validation_errors()) : ?>
        <div class="alert alert-danger" role="alert">
            <?= validation_errors(); ?>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('message')) : ?>
        <?= $this->session->flashdata('message'); ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Pengajuan Kuota dari: <?= htmlspecialchars($pengajuan['NamaPers'] ?? 'Perusahaan tidak ditemukan'); ?></h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nama Perusahaan:</strong> <?= htmlspecialchars($pengajuan['NamaPers'] ?? 'N/A'); ?></p>
                    <p><strong>Email User:</strong> <?= htmlspecialchars($pengajuan['user_email'] ?? 'N/A'); ?></p>
                    <p><strong>Kuota Awal Saat Ini:</strong> <?= htmlspecialchars(number_format($pengajuan['initial_quota'] ?? 0, 0, ',', '.')); ?> Unit</p>
                    <p><strong>Sisa Kuota Saat Ini:</strong> <?= htmlspecialchars(number_format($pengajuan['remaining_quota'] ?? 0, 0, ',', '.')); ?> Unit</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Jumlah Kuota Diajukan:</strong> <?= htmlspecialchars(number_format($pengajuan['requested_quota'] ?? 0, 0, ',', '.')); ?> Unit</p>
                    <p><strong>Alasan Pengajuan:</strong> <?= nl2br(htmlspecialchars($pengajuan['reason'] ?? 'N/A')); ?></p>
                    <p><strong>Tanggal Pengajuan:</strong> <?= isset($pengajuan['submission_date']) ? date('d M Y H:i:s', strtotime($pengajuan['submission_date'])) : 'N/A'; ?></p>
                </div>
            </div>
            <hr>
            <h5>Tindakan Admin</h5>
            <form action="<?= base_url('admin/proses_pengajuan_kuota/' . $pengajuan['id']); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="status_pengajuan">Status Pengajuan <span class="text-danger">*</span></label>
                    <select class="form-control" id="status_pengajuan" name="status_pengajuan">
                        <option value="pending" <?= set_select('status_pengajuan', 'pending', ($pengajuan['status'] ?? 'pending') == 'pending'); ?>>Pending</option>
                        <option value="diproses" <?= set_select('status_pengajuan', 'diproses', ($pengajuan['status'] ?? '') == 'diproses'); ?>>Diproses Petugas</option>
                        <option value="approved" <?= set_select('status_pengajuan', 'approved', ($pengajuan['status'] ?? '') == 'approved'); ?>>Approved (Disetujui)</option>
                        <option value="rejected" <?= set_select('status_pengajuan', 'rejected', ($pengajuan['status'] ?? '') == 'rejected'); ?>>Rejected (Ditolak)</option>
                    </select>
                </div>

                <div id="approved_fields" style="display:none;">
                    <div class="form-group">
                        <label for="approved_quota">Jumlah Kuota Disetujui <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="approved_quota" name="approved_quota" value="<?= set_value('approved_quota', $pengajuan['requested_quota'] ?? '0'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="nomor_sk_petugas">Nomor Surat Keputusan (KEP) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nomor_sk_petugas" name="nomor_sk_petugas" value="<?= set_value('nomor_sk_petugas', $pengajuan['nomor_sk_petugas'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="file_sk_petugas">Upload File SK Petugas (.pdf, .jpg, .png, .jpeg maks 2MB) <span id="file_sk_petugas_label_required" class="text-danger" style="display:none;">*</span></label>
                        <input type="file" class="form-control-file" id="file_sk_petugas" name="file_sk_petugas">
                        <?php if (!empty($pengajuan['file_sk_petugas'])): ?>
                            <small class="form-text text-muted">File SK saat ini:
                                <a href="<?= base_url('admin/download_sk_kuota_admin/' . $pengajuan['id']); ?>" target="_blank">
                                    <?= htmlspecialchars($pengajuan['file_sk_petugas']); ?>
                                </a>.
                                Upload file baru akan menggantikannya.
                            </small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="admin_notes">Catatan Petugas (Jika ditolak, alasan penolakan wajib diisi)</label>
                    <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3"><?= set_value('admin_notes', $pengajuan['admin_notes'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Proses Pengajuan</button>
                <a href="<?= base_url('admin/daftar_pengajuan_kuota'); ?>" class="btn btn-secondary ml-2">Batal</a>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusPengajuanDropdown = document.getElementById('status_pengajuan');
    const approvedFieldsDiv = document.getElementById('approved_fields');
    const fileSkPetugasLabelRequired = document.getElementById('file_sk_petugas_label_required');

    function toggleApprovedFields() {
        if (statusPengajuanDropdown.value === 'approved') {
            approvedFieldsDiv.style.display = 'block';
            // Cek apakah sudah ada file SK lama atau belum
            <?php if (empty($pengajuan['file_sk_petugas'])): ?>
                // Jika BELUM ada file SK lama, maka label "wajib" untuk upload baru muncul
                fileSkPetugasLabelRequired.style.display = 'inline';
            <?php else: ?>
                // Jika SUDAH ada file SK lama, upload baru bersifat opsional (menggantikan)
                fileSkPetugasLabelRequired.style.display = 'none';
            <?php endif; ?>

        } else {
            approvedFieldsDiv.style.display = 'none';
            fileSkPetugasLabelRequired.style.display = 'none';
        }
    }

    // Panggil fungsi saat halaman pertama kali dimuat
    toggleApprovedFields();

    // Panggil fungsi setiap kali nilai dropdown status berubah
    statusPengajuanDropdown.addEventListener('change', toggleApprovedFields);
});
</script>