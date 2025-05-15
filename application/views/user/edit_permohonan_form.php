<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Variabel dari Controller User::editpermohonan():
// $user (array): Data user login
// $user_perusahaan (array): Data perusahaan terkait
// $permohonan_edit (array): Data permohonan yang akan diedit (sebelumnya mungkin $permohonan)
// $list_barang_berkuota (array): Daftar barang yang memiliki kuota aktif (termasuk yg sedang dipilih)
// $title (string)
// $subtitle (string)
// $this->session->flashdata('message_form_edit_permohonan') // Jika ada pesan spesifik

// Inisialisasi untuk pre-fill dan JavaScript berdasarkan data permohonan yang diedit
$id_kuota_barang_saat_ini = $permohonan_edit['id_kuota_barang_digunakan'] ?? set_value('id_kuota_barang_selected', '');
$nama_barang_saat_ini = $permohonan_edit['NamaBarang'] ?? set_value('NamaBarang', '');
$jumlah_barang_saat_ini_di_permohonan = (float)($permohonan_edit['JumlahBarang'] ?? 0); // Jumlah barang di permohonan yang diedit
$no_skep_saat_ini = $permohonan_edit['NoSkep'] ?? '';

// Untuk menampilkan sisa kuota efektif awal di info (sebelum user berinteraksi dengan dropdown)
$sisa_kuota_efektif_awal_display = 'N/A';
if (!empty($id_kuota_barang_saat_ini) && isset($list_barang_berkuota) && is_array($list_barang_berkuota)) {
    foreach ($list_barang_berkuota as $barang_opt_init) {
        if ($barang_opt_init['id_kuota_barang'] == $id_kuota_barang_saat_ini) {
            $sisa_kuota_asli_db = (float)($barang_opt_init['remaining_quota_barang'] ?? 0);
            $sisa_kuota_efektif_awal_display = number_format($sisa_kuota_asli_db + $jumlah_barang_saat_ini_di_permohonan, 0, ',', '.');
            if(empty($no_skep_saat_ini) && isset($barang_opt_init['nomor_skep_asal'])) { // Jika NoSkep di permohonan kosong, ambil dari kuota
                $no_skep_saat_ini = $barang_opt_init['nomor_skep_asal'];
            }
            break;
        }
    }
}
?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Edit Permohonan Impor Kembali'; ?></h1>
        <a href="<?= site_url('user/daftarPermohonan'); ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Permohonan
        </a>
    </div>

    <!-- <?php
    if ($this->session->flashdata('message')) { echo $this->session->flashdata('message'); }
    if ($this->session->flashdata('message_form_edit_permohonan')) { echo $this->session->flashdata('message_form_edit_permohonan'); }
    if (validation_errors()) { echo '<div class="alert alert-danger mt-3" role="alert">' . validation_errors() . '</div>';}
    ?> -->

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Formulir Edit Permohonan Impor Kembali (ID Aju: <?= htmlspecialchars($permohonan_edit['id']); ?>)
            </h6>
        </div>
        <div class="card-body">
            <div class="alert alert-secondary small">
                <strong>Data Perusahaan:</strong><br>
                Nama: <?= htmlspecialchars($user_perusahaan['NamaPers'] ?? 'N/A'); ?><br>
                NPWP: <?= htmlspecialchars($user_perusahaan['npwp'] ?? 'N/A'); ?>
            </div>
            <hr>

            <?php echo form_open(site_url('user/editpermohonan/' . $permohonan_edit['id']), ['class' => 'needs-validation', 'novalidate' => '']); ?>
            <input type="hidden" name="id_kuota_barang_selected" id="id_kuota_barang_selected" value="<?= set_value('id_kuota_barang_selected', $id_kuota_barang_saat_ini); ?>">
            <input type="hidden" name="NamaBarang" id="NamaBarangHidden" value="<?= set_value('NamaBarang', $nama_barang_saat_ini); ?>">

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="nomorSurat">Nomor Surat Pengajuan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= (form_error('nomorSurat')) ? 'is-invalid' : ''; ?>" id="nomorSurat" name="nomorSurat" value="<?= set_value('nomorSurat', $permohonan_edit['nomorSurat'] ?? ''); ?>" required>
                    <?= form_error('nomorSurat', '<small class="text-danger pl-1">', '</small>'); ?>
                </div>
                <div class="form-group col-md-4">
                    <label for="TglSurat">Tanggal Surat <span class="text-danger">*</span></label>
                    <input type="text" class="form-control gj-datepicker <?= (form_error('TglSurat')) ? 'is-invalid' : ''; ?>" id="TglSurat" name="TglSurat" placeholder="YYYY-MM-DD" value="<?= set_value('TglSurat', (isset($permohonan_edit['TglSurat']) && $permohonan_edit['TglSurat']!='0000-00-00') ? $permohonan_edit['TglSurat'] : ''); ?>" required>
                    <?= form_error('TglSurat', '<small class="text-danger pl-1">', '</small>'); ?>
                </div>
                <div class="form-group col-md-4">
                    <label for="Perihal">Perihal Surat <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= (form_error('Perihal')) ? 'is-invalid' : ''; ?>" id="Perihal" name="Perihal" value="<?= set_value('Perihal', $permohonan_edit['Perihal'] ?? ''); ?>" required>
                    <?= form_error('Perihal', '<small class="text-danger pl-1">', '</small>'); ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-5">
                    <label for="id_kuota_barang_selected_dropdown">Nama / Jenis Barang (sesuai kuota) <span class="text-danger">*</span></label>
                    <select class="form-control <?= (form_error('id_kuota_barang_selected') || form_error('NamaBarang')) ? 'is-invalid' : ''; ?>" id="id_kuota_barang_selected_dropdown" name="id_kuota_barang_selected_dropdown_display" required>
                        <option value="">-- Pilih Barang & Kuota SKEP --</option>
                        <?php if (!empty($list_barang_berkuota)): ?>
                            <?php foreach($list_barang_berkuota as $barang): ?>
                                <?php $isSelected = ($id_kuota_barang_saat_ini == $barang['id_kuota_barang']); ?>
                                <option value="<?= htmlspecialchars($barang['id_kuota_barang']); ?>"
                                        data-nama_barang="<?= htmlspecialchars($barang['nama_barang']); ?>"
                                        data-sisa_kuota_asli="<?= htmlspecialchars($barang['remaining_quota_barang'] ?? 0); ?>"
                                        data-skep="<?= htmlspecialchars($barang['nomor_skep_asal'] ?? ''); ?>"
                                        <?= set_select('id_kuota_barang_selected', $barang['id_kuota_barang'], $isSelected); ?>>
                                    <?= htmlspecialchars($barang['nama_barang']); ?> (Sisa Asli: <?= number_format($barang['remaining_quota_barang'] ?? 0, 0, ',', '.'); ?> Unit - SKEP: <?= htmlspecialchars($barang['nomor_skep_asal'] ?? 'N/A'); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?= form_error('id_kuota_barang_selected', '<small class="text-danger pl-1">', '</small>'); ?>
                    <?= form_error('NamaBarang', '<small class="text-danger pl-1">', '</small>'); ?>
                    <small id="sisaKuotaInfoEdit" class="form-text text-info">Sisa kuota efektif: <?= $sisa_kuota_efektif_awal_display; ?> Unit</small>
                </div>
                <div class="form-group col-md-3">
                    <label for="JumlahBarang">Jumlah Barang Diajukan <span class="text-danger">*</span></label>
                    <input type="number" class="form-control <?= (form_error('JumlahBarang')) ? 'is-invalid' : ''; ?>" id="JumlahBarang" name="JumlahBarang" value="<?= set_value('JumlahBarang', $permohonan_edit['JumlahBarang'] ?? ''); ?>" required min="1" max="0">
                    <?= form_error('JumlahBarang', '<small class="text-danger pl-1">', '</small>'); ?>
                </div>
                <div class="form-group col-md-4">
                    <label for="NoSkepOtomatis">No. SKEP (Dasar Permohonan)</label>
                    <input type="text" class="form-control" id="NoSkepOtomatis" name="NoSkepOtomatis" value="<?= set_value('NoSkepOtomatis', $no_skep_saat_ini); ?>" readonly title="No. SKEP otomatis terisi berdasarkan barang yang dipilih.">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4"><label for="NegaraAsal">Negara Asal Barang <span class="text-danger">*</span></label><input type="text" class="form-control <?= (form_error('NegaraAsal')) ? 'is-invalid' : ''; ?>" id="NegaraAsal" name="NegaraAsal" value="<?= set_value('NegaraAsal', $permohonan_edit['NegaraAsal'] ?? ''); ?>" required><?= form_error('NegaraAsal', '<small class="text-danger pl-1">', '</small>'); ?></div>
                <div class="form-group col-md-4"><label for="NamaKapal">Nama Kapal / Sarana Pengangkut <span class="text-danger">*</span></label><input type="text" class="form-control <?= (form_error('NamaKapal')) ? 'is-invalid' : ''; ?>" id="NamaKapal" name="NamaKapal" value="<?= set_value('NamaKapal', $permohonan_edit['NamaKapal'] ?? ''); ?>" required><?= form_error('NamaKapal', '<small class="text-danger pl-1">', '</small>'); ?></div>
                <div class="form-group col-md-4"><label for="noVoyage">No. Voyage / Flight <span class="text-danger">*</span></label><input type="text" class="form-control <?= (form_error('noVoyage')) ? 'is-invalid' : ''; ?>" id="noVoyage" name="noVoyage" value="<?= set_value('noVoyage', $permohonan_edit['noVoyage'] ?? ''); ?>" required><?= form_error('noVoyage', '<small class="text-danger pl-1">', '</small>'); ?></div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4"><label for="TglKedatangan">Tanggal Perkiraan Kedatangan <span class="text-danger">*</span></label><input type="text" class="form-control gj-datepicker <?= (form_error('TglKedatangan')) ? 'is-invalid' : ''; ?>" id="TglKedatangan" name="TglKedatangan" placeholder="YYYY-MM-DD" value="<?= set_value('TglKedatangan', (isset($permohonan_edit['TglKedatangan']) && $permohonan_edit['TglKedatangan']!='0000-00-00') ? $permohonan_edit['TglKedatangan'] : ''); ?>" required><?= form_error('TglKedatangan', '<small class="text-danger pl-1">', '</small>'); ?></div>
                <div class="form-group col-md-4"><label for="TglBongkar">Tanggal Perkiraan Bongkar <span class="text-danger">*</span></label><input type="text" class="form-control gj-datepicker <?= (form_error('TglBongkar')) ? 'is-invalid' : ''; ?>" id="TglBongkar" name="TglBongkar" placeholder="YYYY-MM-DD" value="<?= set_value('TglBongkar', (isset($permohonan_edit['TglBongkar']) && $permohonan_edit['TglBongkar']!='0000-00-00') ? $permohonan_edit['TglBongkar'] : ''); ?>" required><?= form_error('TglBongkar', '<small class="text-danger pl-1">', '</small>'); ?></div>
                <div class="form-group col-md-4"><label for="lokasi">Lokasi Bongkar <span class="text-danger">*</span></label><input type="text" class="form-control <?= (form_error('lokasi')) ? 'is-invalid' : ''; ?>" id="lokasi" name="lokasi" value="<?= set_value('lokasi', $permohonan_edit['lokasi'] ?? ''); ?>" required><?= form_error('lokasi', '<small class="text-danger pl-1">', '</small>'); ?></div>
            </div>

            <button type="submit" class="btn btn-primary btn-user btn-block mt-4" id="submitEditPermohonanBtn" <?= empty($list_barang_berkuota) ? 'disabled title="Tidak ada barang dengan kuota aktif untuk dipilih."' : ''; ?>>
                <i class="fas fa-save fa-fw"></i> Simpan Perubahan Permohonan
            </button>
            <a href="<?= site_url('user/daftarPermohonan'); ?>" class="btn btn-secondary btn-user btn-block mt-2">
                <i class="fas fa-times fa-fw"></i> Batal
            </a>
            <?php echo form_close(); ?>
        </div>
    </div>
</div> <?php // Penutup untuk <div class="container-fluid"> ?>

<script>
$(document).ready(function () {
    // Inisialisasi Gijgo Datepicker
    if (typeof $ !== 'undefined' && typeof $.fn.datepicker !== 'undefined') {
        var datepickerConfig = { uiLibrary: 'bootstrap4', format: 'yyyy-mm-dd', showOnFocus: true, showRightIcon: true, autoClose: true };
        $('#TglSurat').datepicker(datepickerConfig);
        $('#TglKedatangan').datepicker(datepickerConfig);
        $('#TglBongkar').datepicker(datepickerConfig);
    }

    // Simpan jumlah barang lama dan ID kuota barang lama dari permohonan yang diedit
    var jumlahBarangLamaDiPermohonan = parseFloat(<?= json_encode($permohonan_edit['JumlahBarang'] ?? 0); ?>);
    var idKuotaBarangLamaDiPermohonan = parseInt(<?= json_encode($permohonan_edit['id_kuota_barang_digunakan'] ?? 0); ?>);

    function updateKuotaInfoEdit() {
        var selectedOption = $('#id_kuota_barang_selected_dropdown option:selected');
        var idKuotaBarangDipilih = selectedOption.val(); // Ini adalah string, perlu parseInt jika membandingkan dengan angka
        var sisaKuotaAsliDB = parseFloat(selectedOption.data('sisa_kuota_asli')) || 0;
        var skep = selectedOption.data('skep') || 'SKEP Tidak Tersedia';
        var namaBarang = selectedOption.data('nama_barang') || '';

        var sisaKuotaEfektif = sisaKuotaAsliDB;
        if (idKuotaBarangDipilih && parseInt(idKuotaBarangDipilih) === idKuotaBarangLamaDiPermohonan) {
            sisaKuotaEfektif += jumlahBarangLamaDiPermohonan;
        }

        $('#sisaKuotaInfoEdit').text('Sisa kuota efektif untuk barang (' + namaBarang + '): ' + sisaKuotaEfektif.toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 2}) + ' Unit');
        $('#NoSkepOtomatis').val(skep);
        $('#JumlahBarang').attr('max', sisaKuotaEfektif);
        $('#id_kuota_barang_selected').val(idKuotaBarangDipilih);
        $('#NamaBarangHidden').val(namaBarang);

        if(sisaKuotaEfektif <= 0 || idKuotaBarangDipilih === "") {
            $('#submitEditPermohonanBtn').prop('disabled', true).attr('title', 'Tidak ada kuota tersedia atau barang belum dipilih.');
            if(idKuotaBarangDipilih !== "") $('#JumlahBarang').val(0).prop('readonly', true); else $('#JumlahBarang').prop('readonly', false);
        } else {
            $('#submitEditPermohonanBtn').prop('disabled', false).attr('title', '');
            $('#JumlahBarang').prop('readonly', false);
        }
    }

    $('#id_kuota_barang_selected_dropdown').on('change', function() {
        updateKuotaInfoEdit();
        // Saat barang diubah di form edit, reset jumlah barang yang dimohon ke 0 atau nilai kecil
        // agar user harus mengisi ulang sesuai kuota barang baru.
        // Atau biarkan seperti ini jika ingin user menyesuaikan sendiri.
        // $('#JumlahBarang').val(''); // Uncomment jika ingin reset jumlah
    }).trigger('change');

    $('#JumlahBarang').on('input', function() {
        var jumlahDimohon = parseFloat($(this).val()) || 0;
        var sisaKuotaMax = parseFloat($(this).attr('max')) || 0;
        var idKuotaBarangDipilih = $('#id_kuota_barang_selected_dropdown').val();

        $('#sisaKuotaInfoEdit .text-danger').remove();

        if (jumlahDimohon > sisaKuotaMax && idKuotaBarangDipilih !== "") {
            $(this).val(sisaKuotaMax);
            $('#sisaKuotaInfoEdit').append(' <strong class="text-danger">(Jumlah melebihi sisa efektif!)</strong>');
        }

        if (jumlahDimohon <= 0 && idKuotaBarangDipilih !== "") {
             $('#submitEditPermohonanBtn').prop('disabled', true).attr('title', 'Jumlah barang harus lebih dari 0.');
        } else if (sisaKuotaMax > 0 && jumlahDimohon > 0 && jumlahDimohon <= sisaKuotaMax && idKuotaBarangDipilih !== "") {
             $('#submitEditPermohonanBtn').prop('disabled', false).attr('title', '');
        } else if (idKuotaBarangDipilih === "") {
            $('#submitEditPermohonanBtn').prop('disabled', true).attr('title', 'Pilih barang berkuota terlebih dahulu.');
        }
    });
});
</script>