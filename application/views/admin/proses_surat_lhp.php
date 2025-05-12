<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Proses Surat & LHP'; ?></h1>
        <a href="<?= site_url('admin/permohonanMasuk'); ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Permohonan
        </a>
    </div>

    <?php
    if ($this->session->flashdata('message')) {
        echo $this->session->flashdata('message');
    }
    ?>

    <?php if (empty($permohonan)) : ?>
        <div class="alert alert-danger">Data permohonan tidak ditemukan.</div>
    <?php else : ?>
        <div class="row">
            <div class="col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Formulir Penyelesaian Permohonan ID: <?= htmlspecialchars($permohonan['id']); ?></h6>
                    </div>
                    <div class="card-body">
                        <?php echo form_open(site_url('admin/prosesSurat/' . $permohonan['id'])); ?>
                        
                        <p><strong>Nama Perusahaan:</strong> <?= htmlspecialchars($permohonan['NamaPers']); ?></p>
                        <p><strong>No Surat Pengajuan:</strong> <?= htmlspecialchars($permohonan['nomorSurat']); ?></p>
                        <p><strong>Sisa Kuota Saat Ini:</strong> <?= isset($permohonan['remaining_quota']) ? number_format($permohonan['remaining_quota'],0,',','.') : 'N/A'; ?> Unit</p>
                        
                        <?php if ($lhp) : ?>
                            <p class="text-success"><strong>LHP sudah direkam. Jumlah Barang Disetujui (dari LHP): <?= isset($lhp['JumlahBenar']) ? htmlspecialchars($lhp['JumlahBenar']) : '0'; ?> Unit</strong></p>
                        <?php else: ?>
                            <p class="text-danger"><strong>LHP belum direkam. Pemotongan kuota tidak dapat dilakukan tanpa LHP.</strong></p>
                        <?php endif; ?>
                        <hr>

                        <div class="form-group">
                            <label for="nomorSetuju">Nomor Surat Keputusan (Persetujuan/Penolakan) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= (form_error('nomorSetuju')) ? 'is-invalid' : ''; ?>" id="nomorSetuju" name="nomorSetuju" value="<?= set_value('nomorSetuju'); ?>" required>
                            <?= form_error('nomorSetuju', '<small class="text-danger pl-1">', '</small>'); ?>
                        </div>
                        <div class="form-group">
                            <label for="tgl_S">Tanggal Surat Keputusan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control gj-datepicker <?= (form_error('tgl_S')) ? 'is-invalid' : ''; ?>" id="tgl_S" name="tgl_S" placeholder="YYYY-MM-DD" value="<?= set_value('tgl_S'); ?>" required>
                            <?= form_error('tgl_S', '<small class="text-danger pl-1">', '</small>'); ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="status_final">Status Final Permohonan <span class="text-danger">*</span></label>
                            <select class="form-control <?= (form_error('status_final')) ? 'is-invalid' : ''; ?>" id="status_final" name="status_final" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="3" <?= set_select('status_final', '3'); ?>>Disetujui (Kuota Akan Dipotong Sesuai LHP)</option>
                                <option value="4" <?= set_select('status_final', '4'); ?>>Ditolak</option> 
                                <?php // Anda bisa menambahkan status lain jika perlu ?>
                            </select>
                            <?= form_error('status_final', '<small class="text-danger pl-1">', '</small>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="nomorND">Nomor Nota Dinas (Opsional)</label>
                            <input type="text" class="form-control" id="nomorND" name="nomorND" value="<?= set_value('nomorND'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="tgl_ND">Tanggal Nota Dinas (Opsional)</label>
                            <input type="text" class="form-control gj-datepicker" id="tgl_ND" name="tgl_ND" placeholder="YYYY-MM-DD" value="<?= set_value('tgl_ND'); ?>">
                        </div>
                         <div class="form-group">
                            <label for="link">Link Surat Keputusan (Opsional)</label>
                            <input type="url" class="form-control" id="link" name="link" placeholder="https://" value="<?= set_value('link'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="linkND">Link Nota Dinas (Opsional)</label>
                            <input type="url" class="form-control" id="linkND" name="linkND" placeholder="https://" value="<?= set_value('linkND'); ?>">
                        </div>


                        <button type="submit" class="btn btn-primary btn-user btn-block">
                            Simpan & Selesaikan Permohonan
                        </button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
             <div class="col-lg-5">
                 <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Panduan</h6>
                    </div>
                    <div class="card-body small">
                        <p>Pastikan data LHP sudah direkam sebelum menyetujui permohonan jika pemotongan kuota diperlukan.</p>
                        <p>Jika permohonan disetujui, sisa kuota perusahaan akan dikurangi berdasarkan "Jumlah Barang Disetujui" pada LHP.</p>
                        <p>Jika LHP belum ada atau jumlah disetujui 0, kuota tidak akan terpotong meskipun permohonan disetujui.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<script>
$(document).ready(function () {
    if (typeof $.fn.datepicker !== 'undefined') {
        $('.gj-datepicker').datepicker({ // Inisialisasi untuk semua class gj-datepicker
            uiLibrary: 'bootstrap4',
            format: 'yyyy-mm-dd',
            showOnFocus: true,
            showRightIcon: true
        });
    }
});
</script>
