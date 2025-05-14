<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Penunjukan Petugas Pemeriksa'); ?></h1>
        <a href="<?= site_url('admin/permohonanMasuk'); ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Permohonan
        </a>
    </div>

    <?php if ($this->session->flashdata('message_transient')) : ?>
        <?= $this->session->flashdata('message_transient'); ?>
    <?php endif; ?>
    <?php if ($this->session->flashdata('message')) : ?>
        <?= $this->session->flashdata('message'); ?>
    <?php endif; ?>
    <?php if (validation_errors()) : ?>
        <div class="alert alert-danger" role="alert">
            <?= validation_errors(); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulir Penunjukan Petugas untuk Permohonan ID: <?= htmlspecialchars($permohonan['id']); ?> (<?= htmlspecialchars($permohonan['NamaPers'] ?? 'N/A'); ?>)</h6>
        </div>
        <div class="card-body">
            <p>Status Permohonan Saat Ini:
                <?php
                $status_text_current = '-'; $status_badge_current = 'secondary';
                if (isset($permohonan['status'])) {
                    switch ($permohonan['status']) {
                        case '0': $status_text_current = 'Baru Masuk'; $status_badge_current = 'info'; break;
                        case '5': $status_text_current = 'Diproses Admin'; $status_badge_current = 'warning'; break;
                        case '1': $status_text_current = 'Penunjukan Pemeriksa'; $status_badge_current = 'primary'; break;
                        default: $status_text_current = 'Status Tidak Dikenal (' . htmlspecialchars($permohonan['status']) . ')';
                    }
                }
                ?>
                <span class="badge badge-pill badge-<?= $status_badge_current; ?>"><?= htmlspecialchars($status_text_current); ?></span>
            </p>
            <hr>

            <form action="<?= site_url('admin/penunjukanPetugas/' . $permohonan['id']); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="petugas_id">Pilih Petugas/Pemeriksa <span class="text-danger">*</span></label>
                    <select class="form-control <?= form_error('petugas_id') ? 'is-invalid' : ''; ?>" id="petugas_id" name="petugas_id" required>
                        <option value="">-- Pilih Petugas --</option>
                        <?php if (!empty($list_petugas)): ?>
                            <?php foreach ($list_petugas as $petugas_item): ?>
                                <?php // PASTIKAN CONTROLLER MENGIRIM 'id_user' (yang merupakan user.id) ?>
                                <option value="<?= htmlspecialchars($petugas_item['id_user']); // PERUBAHAN DI SINI: Gunakan id_user (user.id) ?>" 
                                        <?= set_select('petugas_id', $petugas_item['id_user'], ($permohonan['petugas'] ?? '') == $petugas_item['id_user']); ?>>
                                    <?= htmlspecialchars($petugas_item['Nama']); ?>
                                     (NIP: <?= htmlspecialchars($petugas_item['NIP'] ?? '-'); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Tidak ada petugas tersedia</option>
                        <?php endif; ?>
                    </select>
                    <div class="invalid-feedback"><?= form_error('petugas_id'); ?></div>
                </div>

                <div class="form-group">
                    <label for="nomor_surat_tugas">Nomor Surat Tugas <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= form_error('nomor_surat_tugas') ? 'is-invalid' : ''; ?>" id="nomor_surat_tugas" name="nomor_surat_tugas" value="<?= set_value('nomor_surat_tugas', $permohonan['NoSuratTugas'] ?? ''); ?>" required>
                    <div class="invalid-feedback"><?= form_error('nomor_surat_tugas'); ?></div>
                </div>

                <div class="form-group">
                    <label for="tanggal_surat_tugas">Tanggal Surat Tugas <span class="text-danger">*</span></label>
                    <input type="date" class="form-control <?= form_error('tanggal_surat_tugas') ? 'is-invalid' : ''; ?>" id="tanggal_surat_tugas" name="tanggal_surat_tugas" value="<?= set_value('tanggal_surat_tugas', $permohonan['TglSuratTugas'] ?? ''); ?>" required>
                    <div class="invalid-feedback"><?= form_error('tanggal_surat_tugas'); ?></div>
                </div>

                <div class="form-group">
                    <label for="file_surat_tugas">Upload File Surat Tugas (PDF, JPG, PNG, DOC, DOCX maks 2MB)</label>
                    <input type="file" class="form-control-file <?= form_error('file_surat_tugas') ? 'is-invalid' : ''; ?>" id="file_surat_tugas" name="file_surat_tugas">
                    <?php if (!empty($permohonan['FileSuratTugas'])): ?>
                        <small class="form-text text-muted mt-1">File saat ini:
                            <a href="<?= base_url('uploads/surat_tugas/' . $permohonan['FileSuratTugas']); ?>" target="_blank">
                                <?= htmlspecialchars($permohonan['FileSuratTugas']); ?>
                            </a>. Upload file baru akan menggantikannya.
                        </small>
                    <?php endif; ?>
                    <div class="invalid-feedback"><?= form_error('file_surat_tugas'); ?></div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Penunjukan</button>
                <a href="<?= site_url('admin/permohonanMasuk'); ?>" class="btn btn-secondary ml-2">Batal</a>
            </form>
        </div>
    </div>
</div>
