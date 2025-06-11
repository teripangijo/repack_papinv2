<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle); ?></h1>
    </div>

    <?php if (validation_errors()) : ?>
        <div class="alert alert-danger" role="alert">
            <?= validation_errors(); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Tolak Permohonan ID: <?= htmlspecialchars($permohonan['id']); ?>
            </h6>
        </div>
        <div class="card-body">
            <p>Anda akan menolak permohonan dari: <strong><?= htmlspecialchars($permohonan['NamaPers']); ?></strong></p>
            <p>Nomor Surat Pemohon: <strong><?= htmlspecialchars($permohonan['nomorSurat']); ?></strong></p>
            <hr>
            
            <?= form_open('petugas_administrasi/tolak_permohonan_awal/' . $permohonan['id']); ?>
            
                <div class="form-group">
                    <label for="alasan_penolakan"><strong>Alasan Penolakan <span class="text-danger">*</span></strong></label>
                    <textarea class="form-control" id="alasan_penolakan" name="alasan_penolakan" rows="4" placeholder="Jelaskan alasan mengapa permohonan ini ditolak..." required><?= set_value('alasan_penolakan'); ?></textarea>
                </div>
                
                <div class="text-right">
                    <a href="<?= site_url('petugas_administrasi/permohonanMasuk'); ?>" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Tolak Permohonan Ini</button>
                </div>

            <?= form_close(); ?>
        </div>
    </div>
</div>