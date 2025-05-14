<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Rekam LHP'); ?></h1>
    <?php if ($this->session->flashdata('message')) { echo $this->session->flashdata('message'); } ?>
    <?= validation_errors('<div class="alert alert-danger" role="alert">', '</div>'); ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Perekaman LHP untuk Permohonan ID: <?= htmlspecialchars($permohonan['id']); ?>
                (<?= htmlspecialchars($permohonan['NamaPers']); ?> - No. Surat: <?= htmlspecialchars($permohonan['nomorSurat']); ?>)
            </h6>
        </div>
        <div class="card-body">
            <p><strong>Nama Barang:</strong> <?= htmlspecialchars($permohonan['NamaBarang'] ?? '-'); ?></p>
            <p><strong>Jumlah Diajukan:</strong> <?= htmlspecialchars(number_format($permohonan['JumlahBarang'] ?? 0, 0, ',', '.')); ?> Unit</p>
            <hr>
            <form action="<?= site_url('petugas/rekam_lhp/' . $permohonan['id']); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="tanggal_lhp">Tanggal LHP <span class="text-danger">*</span></label>
                    <input type="date" class="form-control <?= form_error('tanggal_lhp') ? 'is-invalid' : ''; ?>" id="tanggal_lhp" name="tanggal_lhp" value="<?= set_value('tanggal_lhp', date('Y-m-d')); ?>" required>
                    <div class="invalid-feedback"><?= form_error('tanggal_lhp'); ?></div>
                </div>
                <div class="form-group">
                    <label for="jumlah_barang_benar">Jumlah Barang Sesuai/Benar (Unit) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control <?= form_error('jumlah_barang_benar') ? 'is-invalid' : ''; ?>" id="jumlah_barang_benar" name="jumlah_barang_benar" value="<?= set_value('jumlah_barang_benar', $permohonan['JumlahBarang'] ?? 0); ?>" required min="0">
                    <div class="invalid-feedback"><?= form_error('jumlah_barang_benar'); ?></div>
                </div>
                <div class="form-group">
                    <label for="catatan_pemeriksaan">Catatan Hasil Pemeriksaan <span class="text-danger">*</span></label>
                    <textarea class="form-control <?= form_error('catatan_pemeriksaan') ? 'is-invalid' : ''; ?>" id="catatan_pemeriksaan" name="catatan_pemeriksaan" rows="5" required><?= set_value('catatan_pemeriksaan'); ?></textarea>
                    <div class="invalid-feedback"><?= form_error('catatan_pemeriksaan'); ?></div>
                </div>
                <div class="form-group">
                    <label for="file_dokumentasi_foto">Upload Dokumentasi Foto (JPG/PNG/JPEG, maks 2MB)</label>
                    <input type="file" class="form-control-file <?= form_error('file_dokumentasi_foto') ? 'is-invalid' : ''; ?>" id="file_dokumentasi_foto" name="file_dokumentasi_foto" accept="image/jpeg,image/png">
                    <div class="invalid-feedback"><?= form_error('file_dokumentasi_foto'); ?></div>
                    <small class="form-text text-muted">Kosongkan jika tidak ada dokumentasi foto.</small>
                </div>
                <button type="submit" class="btn btn-primary">Simpan LHP</button>
                <a href="<?= site_url('petugas/daftar_pemeriksaan'); ?>" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>