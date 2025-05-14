<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Rekam LHP'); ?></h1>
        <a href="<?= site_url('petugas/daftar_pemeriksaan'); ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Tugas
        </a>
    </div>

    <?php if ($this->session->flashdata('message')) { echo $this->session->flashdata('message'); } ?>
    <?= validation_errors('<div class="alert alert-danger mb-3" role="alert">', '</div>'); ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <?= isset($lhp_data) && !empty($lhp_data) ? 'Edit' : 'Perekaman'; ?> LHP untuk Permohonan ID: <?= htmlspecialchars($permohonan['id']); ?>
                <br><small>(Perusahaan: <?= htmlspecialchars($permohonan['NamaPers'] ?? 'N/A'); ?> - No. Surat: <?= htmlspecialchars($permohonan['nomorSurat'] ?? '-'); ?>)</small>
            </h6>
        </div>
        <div class="card-body">
            <?php
            // ** PERBAIKAN DI SINI: Gunakan 'JumlahBarang' **
            $jumlahDiajukanDariPermohonan = $permohonan['JumlahBarang'] ?? 0;
            // Logging di view (hanya untuk debug, hapus atau komentari setelah selesai)
            // log_message('debug', 'VIEW FORM REKAM LHP - Data Permohonan: ' . print_r($permohonan, true));
            // log_message('debug', 'VIEW FORM REKAM LHP - Jumlah Diajukan Dari Permohonan: ' . $jumlahDiajukanDariPermohonan);
            ?>
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Nama Barang (sesuai permohonan):</strong> <?= htmlspecialchars($permohonan['NamaBarang'] ?? '-'); // Pastikan 'NamaBarang' juga benar ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Jumlah Diajukan (oleh pemohon):</strong> <span class="font-weight-bold"><?= htmlspecialchars(number_format($jumlahDiajukanDariPermohonan, 0, ',', '.')); ?></span> Unit</p>
                </div>
            </div>
            <hr>

            <?php echo form_open_multipart(site_url('petugas/rekam_lhp/' . $permohonan['id'])); ?>
                <input type="hidden" name="id_lhp_existing" value="<?= isset($lhp_data['id']) ? htmlspecialchars($lhp_data['id']) : (isset($lhp_data['id_lhp']) ? htmlspecialchars($lhp_data['id_lhp']) : ''); ?>">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="NoLHP">Nomor LHP <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= form_error('NoLHP') ? 'is-invalid' : ''; ?>" id="NoLHP" name="NoLHP" value="<?= set_value('NoLHP', $lhp_data['NoLHP'] ?? ''); ?>" required>
                        <div class="invalid-feedback"><?= form_error('NoLHP'); ?></div>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="TglLHP">Tanggal LHP <span class="text-danger">*</span></label>
                        <input type="date" class="form-control <?= form_error('TglLHP') ? 'is-invalid' : ''; ?>" id="TglLHP" name="TglLHP" value="<?= set_value('TglLHP', isset($lhp_data['TglLHP']) && $lhp_data['TglLHP'] != '0000-00-00' ? $lhp_data['TglLHP'] : date('Y-m-d')); ?>" required>
                        <div class="invalid-feedback"><?= form_error('TglLHP'); ?></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="JumlahAjuInfo">Jumlah Diajukan (Info)</label>
                        <input type="number" class="form-control" id="JumlahAjuInfo" name="JumlahAjuInfo" value="<?= htmlspecialchars($jumlahDiajukanDariPermohonan); ?>" readonly title="Jumlah ini diambil dari data permohonan awal">
                        <small class="form-text text-muted">Diambil dari data permohonan.</small>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="JumlahBenar">Jumlah Disetujui (LHP) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control <?= form_error('JumlahBenar') ? 'is-invalid' : ''; ?>" id="JumlahBenar" name="JumlahBenar" value="<?= set_value('JumlahBenar', $lhp_data['JumlahBenar'] ?? $jumlahDiajukanDariPermohonan); ?>" required min="0">
                        <div class="invalid-feedback"><?= form_error('JumlahBenar'); ?></div>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="JumlahSalah">Jumlah Ditolak (LHP) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control <?= form_error('JumlahSalah') ? 'is-invalid' : ''; ?>" id="JumlahSalah" name="JumlahSalah" value="<?= set_value('JumlahSalah', $lhp_data['JumlahSalah'] ?? 0); ?>" required min="0">
                        <div class="invalid-feedback"><?= form_error('JumlahSalah'); ?></div>
                    </div>
                </div>
                 <script>
                    $(document).ready(function(){
                        function hitungJumlahSalah() {
                            var diajukan = parseInt($('#JumlahAjuInfo').val()) || 0;
                            var disetujui = parseInt($('#JumlahBenar').val()) || 0;
                            var ditolak = diajukan - disetujui;
                            $('#JumlahSalah').val(ditolak < 0 ? 0 : ditolak);
                        }
                        hitungJumlahSalah(); // Panggil saat load

                        $('#JumlahBenar').on('input', function(){
                            hitungJumlahSalah();
                        });
                         $('#JumlahSalah').on('input', function(){
                            var diajukan = parseInt($('#JumlahAjuInfo').val()) || 0;
                            var ditolakManual = parseInt($('#JumlahSalah').val()) || 0;
                            var disetujuiHitung = diajukan - ditolakManual;
                             if (disetujuiHitung < 0) {
                                 $('#JumlahBenar').val(0);
                                 $('#JumlahSalah').val(diajukan);
                             } else {
                                 $('#JumlahBenar').val(disetujuiHitung);
                             }
                        });
                    });
                </script>

                <div class="form-group">
                    <label for="Catatan">Catatan Hasil Pemeriksaan</label>
                    <textarea class="form-control <?= form_error('Catatan') ? 'is-invalid' : ''; ?>" id="Catatan" name="Catatan" rows="4"><?= set_value('Catatan', $lhp_data['Catatan'] ?? ''); ?></textarea>
                    <div class="invalid-feedback"><?= form_error('Catatan'); ?></div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="FileLHP">Upload File LHP Resmi (PDF/DOC/JPG/PNG, maks 2MB) <?= (!isset($lhp_data['FileLHP']) || empty($lhp_data['FileLHP'])) ? '<span class="text-danger">*</span>' : ''; ?></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input <?= form_error('FileLHP') ? 'is-invalid' : ''; ?>" id="FileLHP" name="FileLHP" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <label class="custom-file-label" for="FileLHP"><?= isset($lhp_data['FileLHP']) && !empty($lhp_data['FileLHP']) ? htmlspecialchars($lhp_data['FileLHP']) : 'Pilih file...'; ?></label>
                        </div>
                        <div class="invalid-feedback"><?= form_error('FileLHP'); ?></div>
                        <?php if (isset($lhp_data['FileLHP']) && !empty($lhp_data['FileLHP'])): ?>
                            <small class="form-text text-muted">File saat ini: <a href="<?= base_url('uploads/lhp/' . $lhp_data['FileLHP']); ?>" target="_blank"><?= htmlspecialchars($lhp_data['FileLHP']); ?></a>. Pilih file baru untuk mengganti.</small>
                        <?php elseif (!isset($lhp_data['FileLHP']) || empty($lhp_data['FileLHP'])): ?>
                             <small class="form-text text-muted">Wajib diisi untuk perekaman LHP baru.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="file_dokumentasi_foto">Upload File Dokumentasi Foto (JPG/PNG/GIF, maks 2MB)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input <?= form_error('file_dokumentasi_foto') ? 'is-invalid' : ''; ?>" id="file_dokumentasi_foto" name="file_dokumentasi_foto" accept="image/jpeg,image/png,image/gif">
                            <label class="custom-file-label" for="file_dokumentasi_foto"><?= isset($lhp_data['file_dokumentasi_foto']) && !empty($lhp_data['file_dokumentasi_foto']) ? htmlspecialchars($lhp_data['file_dokumentasi_foto']) : 'Pilih file...'; ?></label>
                        </div>
                        <div class="invalid-feedback"><?= form_error('file_dokumentasi_foto'); ?></div>
                        <?php if (isset($lhp_data['file_dokumentasi_foto']) && !empty($lhp_data['file_dokumentasi_foto'])): ?>
                            <small class="form-text text-muted">File saat ini: <a href="<?= base_url('uploads/dokumentasi_lhp/' . $lhp_data['file_dokumentasi_foto']); ?>" target="_blank"><?= htmlspecialchars($lhp_data['file_dokumentasi_foto']); ?></a>. Pilih file baru untuk mengganti.</small>
                        <?php else: ?>
                            <small class="form-text text-muted">Opsional, kosongkan jika tidak ada.</small>
                        <?php endif; ?>
                    </div>
                </div>
                <script>
                    $('.custom-file-input').on('change', function(event) {
                        var inputFile = event.target;
                        if (inputFile.files.length > 0) {
                            var fileName = inputFile.files[0].name;
                            $(inputFile).next('.custom-file-label').addClass("selected").html(fileName);
                        } else {
                             // Jika tidak ada file dipilih (misal user klik cancel setelah memilih file),
                             // kembalikan label ke default jika ini adalah input untuk file yang sudah ada.
                             // Anda mungkin perlu logika tambahan di sini jika ingin label kembali ke nama file lama.
                             // Untuk sekarang, jika batal, label akan tetap "Pilih file..." atau nama file lama jika sudah ada.
                            var currentFileLabel = $(inputFile).next('.custom-file-label');
                            var originalText = currentFileLabel.data('original-text');
                            if(!originalText) { // simpan teks original jika belum
                                currentFileLabel.data('original-text', currentFileLabel.html());
                                originalText = currentFileLabel.html();
                            }
                             if (fileName === "" || typeof fileName === "undefined") { // jika dibatalkan
                                 currentFileLabel.removeClass("selected").html(originalText);
                             }
                        }
                    });
                     // Inisialisasi teks original untuk label file saat dokumen ready
                    $(document).ready(function(){
                        $('.custom-file-input').each(function(){
                            var label = $(this).next('.custom-file-label');
                            label.data('original-text', label.html());
                        });
                    });
                </script>

                <hr>
                <button type="submit" class="btn btn-primary btn-user btn-block">
                    <i class="fas fa-save fa-fw"></i> <?= isset($lhp_data) && !empty($lhp_data) ? 'Update' : 'Simpan'; ?> Data LHP
                </button>
                <a href="<?= site_url('petugas/daftar_pemeriksaan'); ?>" class="btn btn-secondary btn-user btn-block mt-2">
                    <i class="fas fa-times fa-fw"></i> Batal
                </a>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>