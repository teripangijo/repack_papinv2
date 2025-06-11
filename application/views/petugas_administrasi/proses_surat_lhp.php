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
                        <?php echo form_open(site_url('petugas_administrasi/prosesSurat/' . $permohonan['id'])); ?>

                        <p><strong>Nama Perusahaan:</strong> <?= htmlspecialchars($permohonan['NamaPers']); ?></p>
                        <p><strong>No Surat Pengajuan:</strong> <?= htmlspecialchars($permohonan['nomorSurat']); ?></p>
                        <p><strong>Sisa Kuota Saat Ini:</strong> <?= isset($permohonan['sisa_kuota_perusahaan_saat_ini']) ? number_format($permohonan['sisa_kuota_perusahaan_saat_ini'],0,',','.') : (isset($permohonan['remaining_quota']) ? number_format($permohonan['remaining_quota'],0,',','.') : 'N/A'); ?> Unit</p>

                        <?php if (isset($lhp) && !empty($lhp) && isset($lhp['NoLHP']) && isset($lhp['TglLHP'])) : ?>
                            <p class="text-success"><strong>LHP sudah direkam. Jumlah Barang Disetujui (dari LHP): <?= isset($lhp['JumlahBenar']) ? htmlspecialchars(number_format($lhp['JumlahBenar'])) : '0'; ?> Unit</strong></p>
                            <hr>
                            <div class="form-group">
                                <label for="nomorLhpDariPetugas">Nomor LHP (dari Petugas)</label>
                                <input type="text" class="form-control" id="nomorLhpDariPetugas" value="<?= htmlspecialchars($lhp['NoLHP']); ?>" readonly>
                                </div>
                            <div class="form-group">
                                <label for="tanggalLhpDariPetugas">Tanggal LHP (dari Petugas)</label>
                                <input type="text" class="form-control" id="tanggalLhpDariPetugas" value="<?= htmlspecialchars(date('d M Y', strtotime($lhp['TglLHP']))); ?>" readonly>
                                </div>
                        <?php else: ?>
                            <p class="text-danger"><strong>LHP belum direkam atau data No/Tgl LHP tidak lengkap. Tidak dapat melanjutkan penyelesaian.</strong></p>
                            <?php // Tombol submit akan di-disable di bawah jika kondisi ini terpenuhi ?>
                        <?php endif; ?>
                        <hr>

                        <div class="form-group">
                            <label for="status_final">Status Final Permohonan <span class="text-danger">*</span></label>
                            <select class="form-control <?= (form_error('status_final')) ? 'is-invalid' : ''; ?>" id="status_final" name="status_final" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="3" <?= set_select('status_final', '3'); ?>>Disetujui (Kuota Akan Dipotong Sesuai LHP)</option>
                                <option value="4" <?= set_select('status_final', '4'); ?>>Ditolak</option>
                            </select>
                            <?= form_error('status_final', '<small class="text-danger pl-1">', '</small>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="nomorND">Nomor Nota Dinas (Opsional)</label>
                            <input type="text" class="form-control <?= (form_error('nomorND')) ? 'is-invalid' : ''; ?>" id="nomorND" name="nomorND" value="<?= set_value('nomorND'); ?>">
                            <?= form_error('nomorND', '<small class="text-danger pl-1">', '</small>'); ?>
                        </div>
                        <div class="form-group">
                            <label for="tgl_ND">Tanggal Nota Dinas (Opsional)</label>
                            <input type="text" class="form-control gj-datepicker <?= (form_error('tgl_ND')) ? 'is-invalid' : ''; ?>" id="tgl_ND" name="tgl_ND" placeholder="YYYY-MM-DD" value="<?= set_value('tgl_ND'); ?>">
                            <?= form_error('tgl_ND', '<small class="text-danger pl-1">', '</small>'); ?>
                        </div>
                         <div class="form-group">
                            <label for="link">Link Surat Keputusan (Opsional)</label>
                            <input type="url" class="form-control <?= (form_error('link')) ? 'is-invalid' : ''; ?>" id="link" name="link" placeholder="https://" value="<?= set_value('link'); ?>">
                            <?= form_error('link', '<small class="text-danger pl-1">', '</small>'); ?>
                        </div>
                        <div class="form-group">
                            <label for="linkND">Link Nota Dinas (Opsional)</label>
                            <input type="url" class="form-control <?= (form_error('linkND')) ? 'is-invalid' : ''; ?>" id="linkND" name="linkND" placeholder="https://" value="<?= set_value('linkND'); ?>">
                            <?= form_error('linkND', '<small class="text-danger pl-1">', '</small>'); ?>
                        </div>

                        <button type="submit" class="btn btn-primary btn-user btn-block" <?= !(isset($lhp) && !empty($lhp) && !empty($lhp['NoLHP']) && !empty($lhp['TglLHP'])) ? 'disabled title="Data LHP tidak lengkap"' : '' ?>>
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
                        <p>Pastikan data LHP sudah direkam dengan lengkap (Nomor & Tanggal LHP) oleh Petugas sebelum Anda dapat melanjutkan proses penyelesaian ini.</p>
                        <p>Jika permohonan disetujui, sisa kuota perusahaan akan dikurangi berdasarkan "Jumlah Barang Disetujui" pada LHP.</p>
                        <p>Jika LHP belum ada atau jumlah disetujui 0, kuota tidak akan terpotong meskipun permohonan disetujui (jika statusnya "Disetujui").</p>
                         <p>Nomor LHP dan Tanggal LHP di atas diambil secara otomatis dari data yang direkam oleh Petugas Pemeriksa.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<script>
$(document).ready(function () {
    // Inisialisasi datepicker hanya untuk field yang memerlukan input tanggal manual
    if (typeof $.fn.datepicker !== 'undefined') {
        $('#tgl_ND').datepicker({ // Hanya untuk Tanggal Nota Dinas
            uiLibrary: 'bootstrap4',
            format: 'yyyy-mm-dd',
            showOnFocus: true,
            showRightIcon: true
        });
        // Anda tidak perlu datepicker untuk tgl_S (Tanggal LHP) lagi jika itu readonly
    }
});
</script>