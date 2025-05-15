<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Variabel $nomor_skep_otomatis dan $user_perusahaan dikirim dari controller
$nomor_skep_display = isset($nomor_skep_otomatis) && !empty($nomor_skep_otomatis) ? htmlspecialchars($nomor_skep_otomatis) : '<span class="text-danger font-italic">SKEP Tidak Ditemukan</span>';
$can_submit = isset($nomor_skep_otomatis) && !empty($nomor_skep_otomatis);
?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Form Permohonan Impor Kembali'; ?></h1>
        <a href="<?= site_url('user/index'); // Atau ke halaman daftar permohonan user ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Dashboard
        </a>
    </div>

    <!-- <?php
    if ($this->session->flashdata('message')) {
        echo $this->session->flashdata('message');
    }
    if (validation_errors()) {
        echo '<div class="alert alert-danger" role="alert">' . validation_errors() . '</div>';
    }
    ?> -->

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulir Pengajuan Permohonan Impor Kembali</h6>
        </div>
        <div class="card-body">
            <?php if (isset($user['is_active']) && $user['is_active'] == 1 && !empty($user_perusahaan)) : ?>
                <?php echo form_open(site_url('user/permohonan'), ['class' => 'needs-validation', 'novalidate' => '']); // Sesuaikan action jika nama method controller beda ?>

                <div class="alert alert-secondary small">
                    <strong>Data Perusahaan (Otomatis dari Profil):</strong><br>
                    Nama: <?= isset($user_perusahaan['NamaPers']) ? htmlspecialchars($user_perusahaan['NamaPers']) : 'N/A'; ?><br>
                    Alamat: <?= isset($user_perusahaan['alamat']) ? htmlspecialchars($user_perusahaan['alamat']) : 'N/A'; ?><br>
                    NPWP: <?= isset($user_perusahaan['npwp']) ? htmlspecialchars($user_perusahaan['npwp']) : 'N/A'; ?>
                </div>
                <hr>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="nomorSurat">Nomor Surat Pengajuan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= (form_error('nomorSurat')) ? 'is-invalid' : ''; ?>" id="nomorSurat" name="nomorSurat" value="<?= set_value('nomorSurat'); ?>" required>
                        <?= form_error('nomorSurat', '<small class="text-danger pl-1">', '</small>'); ?>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="TglSurat">Tanggal Surat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control gj-datepicker <?= (form_error('TglSurat')) ? 'is-invalid' : ''; ?>" id="TglSurat" name="TglSurat" placeholder="YYYY-MM-DD" value="<?= set_value('TglSurat', date('Y-m-d')); ?>" required>
                        <?= form_error('TglSurat', '<small class="text-danger pl-1">', '</small>'); ?>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Perihal">Perihal Surat <span class="text-danger">*</span></label>
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
                        <label for="JumlahBarang">Jumlah Barang (Unit) <span class="text-danger">*</span></label>
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
                        <label for="NamaKapal">Nama Kapal / Sarana Pengangkut <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= (form_error('NamaKapal')) ? 'is-invalid' : ''; ?>" id="NamaKapal" name="NamaKapal" value="<?= set_value('NamaKapal'); ?>" required>
                        <?= form_error('NamaKapal', '<small class="text-danger pl-1">', '</small>'); ?>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="noVoyage">No. Voyage / Flight <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= (form_error('noVoyage')) ? 'is-invalid' : ''; ?>" id="noVoyage" name="noVoyage" value="<?= set_value('noVoyage'); ?>" required>
                        <?= form_error('noVoyage', '<small class="text-danger pl-1">', '</small>'); ?>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="NoSkepOtomatis">No. SKEP (Dasar Permohonan)</label>
                        <input type="text" class="form-control" id="NoSkepOtomatis" name="NoSkepOtomatis" value="<?= $nomor_skep_display; ?>" readonly title="No. SKEP diambil dari Profil Perusahaan atau Pengajuan Kuota terakhir yang disetujui.">
                        <?php if (!$can_submit): ?>
                            <small class="form-text text-danger">No. SKEP tidak ditemukan. Anda tidak dapat mengajukan permohonan. Harap lengkapi No. SKEP di profil perusahaan atau pastikan ada pengajuan kuota yang telah disetujui.</small>
                        <?php else: ?>
                            <small class="form-text text-muted">Otomatis terisi berdasarkan data yang valid.</small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="TglKedatangan">Tanggal Perkiraan Kedatangan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control gj-datepicker <?= (form_error('TglKedatangan')) ? 'is-invalid' : ''; ?>" id="TglKedatangan" name="TglKedatangan" placeholder="YYYY-MM-DD" value="<?= set_value('TglKedatangan'); ?>" required>
                        <?= form_error('TglKedatangan', '<small class="text-danger pl-1">', '</small>'); ?>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="TglBongkar">Tanggal Perkiraan Bongkar <span class="text-danger">*</span></label>
                        <input type="text" class="form-control gj-datepicker <?= (form_error('TglBongkar')) ? 'is-invalid' : ''; ?>" id="TglBongkar" name="TglBongkar" placeholder="YYYY-MM-DD" value="<?= set_value('TglBongkar'); ?>" required>
                        <?= form_error('TglBongkar', '<small class="text-danger pl-1">', '</small>'); ?>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="lokasi">Lokasi Bongkar <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= (form_error('lokasi')) ? 'is-invalid' : ''; ?>" id="lokasi" name="lokasi" value="<?= set_value('lokasi'); ?>" required>
                        <?= form_error('lokasi', '<small class="text-danger pl-1">', '</small>'); ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-user btn-block mt-4" <?= !$can_submit ? 'disabled title="Tidak dapat mengajukan permohonan karena No. SKEP tidak ditemukan."' : ''; ?>>
                    <i class="fas fa-paper-plane fa-fw"></i> Ajukan Permohonan
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
<?php // Pastikan skrip Gijgo Datepicker ada di footer.php atau dimuat setelah jQuery ?>
<script>
    $(document).ready(function () {
        if (typeof $ !== 'undefined' && typeof $.fn.datepicker !== 'undefined') {
            var datepickerConfig = {
                uiLibrary: 'bootstrap4',
                format: 'yyyy-mm-dd',
                showOnFocus: true,
                showRightIcon: true,
                autoClose: true
            };
            $('#TglSurat').datepicker(datepickerConfig);
            $('#TglKedatangan').datepicker(datepickerConfig);
            $('#TglBongkar').datepicker(datepickerConfig);
            console.log('Permohonan Impor Kembali: Datepickers initialized.');
        } else {
            if (typeof $ === 'undefined') { console.error("Permohonan Impor Kembali: jQuery is not loaded."); }
            if (typeof $.fn.datepicker === 'undefined' && typeof $ !== 'undefined') { console.error("Permohonan Impor Kembali: Gijgo Datepicker ($.fn.datepicker) is not loaded."); }
        }
    });
</script>