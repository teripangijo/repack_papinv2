<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Form Permohonan Impor Kembali'; ?></h1>
    </div>

    <?php
    // Menampilkan flashdata message jika ada
    if ($this->session->flashdata('message')) {
        echo $this->session->flashdata('message');
    }
    ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulir Pengajuan Permohonan Impor Kembali</h6>
        </div>
        <div class="card-body">
            <?php if (isset($user['is_active']) && $user['is_active'] == 1 && !empty($user_perusahaan)) : ?>
                <?php echo form_open(site_url('user/permohonan_impor_kembali'), ['class' => 'needs-validation', 'novalidate' => '']); ?>

                <div class="alert alert-info small">
                    <strong>Data Perusahaan:</strong><br>
                    Nama: <?= isset($user_perusahaan['NamaPers']) ? htmlspecialchars($user_perusahaan['NamaPers']) : 'N/A'; ?><br>
                    Alamat: <?= isset($user_perusahaan['alamat']) ? htmlspecialchars($user_perusahaan['alamat']) : 'N/A'; ?><br>
                    NPWP: <?= isset($user_perusahaan['npwp']) ? htmlspecialchars($user_perusahaan['npwp']) : 'N/A'; ?>
                </div>
                <hr>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="nomorSurat">Nomor Surat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= (form_error('nomorSurat')) ? 'is-invalid' : ''; ?>" id="nomorSurat" name="nomorSurat" value="<?= set_value('nomorSurat'); ?>" required>
                        <?= form_error('nomorSurat', '<small class="text-danger pl-1">', '</small>'); ?>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="TglSurat">Tanggal Surat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= (form_error('TglSurat')) ? 'is-invalid' : ''; ?>" id="TglSurat" name="TglSurat" placeholder="YYYY-MM-DD" value="<?= set_value('TglSurat'); ?>" required>
                        <?= form_error('TglSurat', '<small class="text-danger pl-1">', '</small>'); ?>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Perihal">Perihal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= (form_error('Perihal')) ? 'is-invalid' : ''; ?>" id="Perihal" name="Perihal" value="<?= set_value('Perihal'); ?>" required>
                        <?= form_error('Perihal', '<small class="text-danger pl-1">', '</small>'); ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="NamaBarang">Nama / Jenis Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= (form_error('NamaBarang')) ? 'is-invalid' : ''; ?>" id="NamaBarang" name="NamaBarang" value="<?= set_value('NamaBarang'); ?>" required>
                        <?= form_error('NamaBarang', '<small class="text-danger pl-1">', '</small>'); ?>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="JumlahBarang">Jumlah Barang <span class="text-danger">*</span></label>
                        <input type="number" class="form-control <?= (form_error('JumlahBarang')) ? 'is-invalid' : ''; ?>" id="JumlahBarang" name="JumlahBarang" value="<?= set_value('JumlahBarang'); ?>" required min="1">
                        <?= form_error('JumlahBarang', '<small class="text-danger pl-1">', '</small>'); ?>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="NegaraAsal">Negara Asal Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= (form_error('NegaraAsal')) ? 'is-invalid' : ''; ?>" id="NegaraAsal" name="NegaraAsal" value="<?= set_value('NegaraAsal'); ?>" required>
                        <?= form_error('NegaraAsal', '<small class="text-danger pl-1">', '</small>'); ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="NamaKapal">Nama Kapal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= (form_error('NamaKapal')) ? 'is-invalid' : ''; ?>" id="NamaKapal" name="NamaKapal" value="<?= set_value('NamaKapal'); ?>" required>
                        <?= form_error('NamaKapal', '<small class="text-danger pl-1">', '</small>'); ?>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="noVoyage">No. Voyage <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= (form_error('noVoyage')) ? 'is-invalid' : ''; ?>" id="noVoyage" name="noVoyage" value="<?= set_value('noVoyage'); ?>" required>
                        <?= form_error('noVoyage', '<small class="text-danger pl-1">', '</small>'); ?>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="NoSkep">No. SKEP (Jika Ada)</label>
                        <input type="text" class="form-control" id="NoSkep" name="NoSkep" value="<?= isset($user_perusahaan['NoSkep']) ? htmlspecialchars($user_perusahaan['NoSkep']) : set_value('NoSkep'); ?>" readonly>
                        <small class="form-text text-muted">Diambil dari profil perusahaan.</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="TglKedatangan">Tanggal Perkiraan Kedatangan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= (form_error('TglKedatangan')) ? 'is-invalid' : ''; ?>" id="TglKedatangan" name="TglKedatangan" placeholder="YYYY-MM-DD" value="<?= set_value('TglKedatangan'); ?>" required>
                        <?= form_error('TglKedatangan', '<small class="text-danger pl-1">', '</small>'); ?>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="TglBongkar">Tanggal Perkiraan Bongkar <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= (form_error('TglBongkar')) ? 'is-invalid' : ''; ?>" id="TglBongkar" name="TglBongkar" placeholder="YYYY-MM-DD" value="<?= set_value('TglBongkar'); ?>" required>
                        <?= form_error('TglBongkar', '<small class="text-danger pl-1">', '</small>'); ?>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="lokasi">Lokasi Bongkar <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= (form_error('lokasi')) ? 'is-invalid' : ''; ?>" id="lokasi" name="lokasi" value="<?= set_value('lokasi'); ?>" required>
                        <?= form_error('lokasi', '<small class="text-danger pl-1">', '</small>'); ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-user btn-block mt-4">
                    Simpan Permohonan
                </button>
                <?php echo form_close(); ?>
            <?php else : ?>
                <div class="alert alert-warning" role="alert">
                    Akun Anda belum aktif atau data perusahaan belum lengkap. Silakan lengkapi <a href="<?= site_url('user/edit'); ?>" class="alert-link">profil perusahaan Anda</a> terlebih dahulu untuk dapat mengajukan permohonan.
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
<?php // Script untuk Gijgo Datepicker diletakkan di akhir body, setelah semua library JS utama dimuat (di footer.php) ?>
<script>
    $(document).ready(function () {
        // Cek sekali lagi apakah jQuery dan Gijgo Datepicker sudah ada
        if (typeof $ !== 'undefined' && typeof $.fn.datepicker !== 'undefined') {
            console.log('Permohonan Impor Kembali: jQuery and Gijgo Datepicker are loaded.'); 
            var datepickerConfig = {
                uiLibrary: 'bootstrap4',
                format: 'yyyy-mm-dd',
                showOnFocus: true,    
                showRightIcon: true,  // Bisa diatur false jika autoClose sudah cukup
                modal: false,         // Non-modal lebih baik untuk autoClose
                header: false,        // Hilangkan header kalender (opsional)
                footer: false,        // Hilangkan footer dengan tombol OK/Cancel
                autoClose: true       // Memastikan kalender menutup otomatis setelah tanggal dipilih
            };
            $('#TglSurat').datepicker(datepickerConfig);
            $('#TglKedatangan').datepicker(datepickerConfig);
            $('#TglBongkar').datepicker(datepickerConfig);
            console.log('Permohonan Impor Kembali: Datepickers initialized with autoClose.');
        } else {
            if (typeof $ === 'undefined') {
                console.error("Permohonan Impor Kembali: jQuery is not loaded.");
            }
            if (typeof $.fn.datepicker === 'undefined' && typeof $ !== 'undefined') {
                console.error("Permohonan Impor Kembali: Gijgo Datepicker ($.fn.datepicker) is not loaded. Ensure gijgo.min.js is included AFTER jQuery and jQuery is loaded ONLY ONCE.");
            }
        }
    });
</script>
