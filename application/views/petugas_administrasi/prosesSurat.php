<?php // application/views/admin/prosesSurat.php ?>

<div class="container-fluid">

    <h1 class="h3 mb-2 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Proses Finalisasi Permohonan'); ?></h1>
    <p class="mb-4">Lakukan finalisasi persetujuan atau penolakan permohonan impor returnable package.</p>

    <?php if ($this->session->flashdata('message')) : ?>
        <?= $this->session->flashdata('message'); ?>
    <?php endif; ?>
    <?php if ($this->session->flashdata('message_error_quota')) : ?>
        <?= $this->session->flashdata('message_error_quota'); ?>
    <?php endif; ?>

    <?php if (validation_errors()) : ?>
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-heading">Oops, ada kesalahan!</h4>
            <p>Mohon periksa kembali data yang Anda masukkan:</p>
            <hr>
            <?= validation_errors('<div>', '</div>'); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulir Finalisasi Permohonan - ID: <?= htmlspecialchars($permohonan['id']); ?></h6>
        </div>
        <div class="card-body">
            <?php // GANTI form action biasa dengan form_open_multipart untuk handle file upload ?>
            <?php echo form_open_multipart(base_url('admin/prosesSurat/' . $permohonan['id'])); ?>
            
                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-2 small font-weight-bold">Data Perusahaan</legend>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1">Nama Perusahaan</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($user_perusahaan['NamaPers'] ?? ($permohonan['NamaPers'] ?? 'N/A')) ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1">NPWP</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($user_perusahaan['npwp'] ?? ($permohonan['npwp'] ?? 'N/A')) ?>" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="small mb-1">Alamat</label>
                            <textarea class="form-control form-control-sm" rows="2" readonly><?= htmlspecialchars($user_perusahaan['alamat'] ?? ($permohonan['alamat'] ?? 'N/A')) ?></textarea>
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1">No. SKEP Perusahaan (Jika Ada)</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($user_perusahaan['NoSkep'] ?? ($permohonan['NoSkep'] ?? 'N/A')) ?>" readonly>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-2 small font-weight-bold">Data Permohonan Awal</legend>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1">Nomor Surat Pemohon</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($permohonan['nomorSurat']); ?>" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1">Tanggal Surat Pemohon</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars(date('d F Y', strtotime($permohonan['TglSurat']))); ?>" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1">Perihal</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($permohonan['Perihal']); ?>" readonly />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1">Nama / Jenis Barang</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($permohonan['NamaBarang']); ?>" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1">Jumlah Barang Diajukan</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($permohonan['JumlahBarang'] . ' ' . ($permohonan['SatuanBarang'] ?? '')); ?>" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1">Negara Asal Barang</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($permohonan['NegaraAsal']); ?>" readonly>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-2 small font-weight-bold">Data Laporan Hasil Pemeriksaan (LHP)</legend>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1">No. LHP</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($lhp['NoLHP'] ?? 'N/A'); ?>" readonly>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1">Tgl. LHP</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars(isset($lhp['TglLHP']) && $lhp['TglLHP'] != '0000-00-00' ? date('d F Y', strtotime($lhp['TglLHP'])) : 'N/A'); ?>" readonly>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1">Jumlah Barang Sebenarnya (LHP)</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($lhp['JumlahBenar'] ?? 'N/A'); ?>" readonly>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1">Hasil Pemeriksaan LHP</label>
                            <?php
                            $hasil_lhp_text = 'N/A';
                            if (isset($lhp['hasil'])) {
                                if ($lhp['hasil'] == 1) $hasil_lhp_text = 'Sesuai';
                                else if ($lhp['hasil'] == 0) $hasil_lhp_text = 'Tidak Sesuai';
                            }
                            ?>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($hasil_lhp_text); ?>" readonly>
                        </div>
                    </div>
                    <div class="row">
                         <div class="col-md-12 mb-3">
                            <label class="small mb-1">Keterangan / Kesimpulan LHP</label>
                            <textarea class="form-control form-control-sm" rows="3" readonly><?= htmlspecialchars($lhp['Kesimpulan'] ?? 'N/A'); ?></textarea>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-2 small font-weight-bold text-danger">Keputusan Akhir Admin</legend>
                     <div class="form-group mb-3">
                        <label class="small mb-1" for="status_final">Status Final Permohonan <span class="text-danger">*</span></label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status_final" id="status_disetujui" value="3" <?= set_radio('status_final', '3', TRUE); ?>>
                                <label class="form-check-label small" for="status_disetujui">Disetujui</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status_final" id="status_ditolak" value="4" <?= set_radio('status_final', '4'); ?>>
                                <label class="form-check-label small" for="status_ditolak">Ditolak</label>
                            </div>
                        </div>
                        <?= form_error('status_final', '<small class="text-danger d-block mt-1">', '</small>'); ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="nomorSetuju">Nomor Surat Persetujuan/Penolakan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm <?= (form_error('nomorSetuju')) ? 'is-invalid' : ''; ?>" id="nomorSetuju" name="nomorSetuju" value="<?= set_value('nomorSetuju', $lhp['NoLHP'] ?? '') ?>" placeholder="Contoh: S-123/WBC.02/KPP.MP.01/2025">
                            <?= form_error('nomorSetuju', '<small class="text-danger">', '</small>'); ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="tgl_S">Tanggal Surat Persetujuan/Penolakan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm datepicker <?= (form_error('tgl_S')) ? 'is-invalid' : ''; ?>" id="tgl_S" name="tgl_S" value="<?= set_value('tgl_S', isset($lhp['TglLHP']) && $lhp['TglLHP'] != '0000-00-00' ? date('Y-m-d', strtotime($lhp['TglLHP'])) : date('Y-m-d')) ?>" placeholder="YYYY-MM-DD">
                            <?= form_error('tgl_S', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>

                    <?php // Bagian Upload Surat Persetujuan/Penolakan (Wajib jika Disetujui) ?>
                    <div class="form-group mb-3" id="upload_surat_persetujuan_box">
                        <label class="small mb-1" for="file_surat_keputusan">Upload File Surat Persetujuan Pengeluaran <span id="surat_keputusan_wajib_text" class="text-danger">*</span> <span class="text-info small">(Max 2MB: PDF, JPG, PNG)</span></label>
                        <?php if(!empty($permohonan['file_surat_keputusan'])): ?>
                            <p class="small mb-1">File saat ini: 
                                <a href="<?= base_url('uploads/sk_penyelesaian/' . htmlspecialchars($permohonan['file_surat_keputusan'])); ?>" target="_blank">
                                    <i class="fas fa-file-alt"></i> <?= htmlspecialchars($permohonan['file_surat_keputusan']); ?>
                                </a>
                                (Upload file baru di bawah akan menggantikan file ini)
                            </p>
                        <?php endif; ?>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input <?= (form_error('file_surat_keputusan')) ? 'is-invalid' : ''; ?>" id="file_surat_keputusan" name="file_surat_keputusan" accept=".pdf,.jpg,.jpeg,.png">
                            <label class="custom-file-label" for="file_surat_keputusan">Pilih file...</label>
                        </div>
                        <?= form_error('file_surat_keputusan', '<small class="text-danger d-block mt-1">', '</small>'); ?>
                    </div>

                    <div class="row">
                        <!-- <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="link">Link Surat Keputusan (Google Drive, dsb. - Opsional)</label>
                            <div class="input-group input-group-sm">
                                <input type="url" class="form-control form-control-sm <?= (form_error('link')) ? 'is-invalid' : ''; ?>" id="link" name="link" value="<?= set_value('link', $permohonan['link'] ?? ''); ?>" placeholder="https://docs.google.com/document/d/...">
                                <div class="input-group-append">
                                    <button type="button" onclick="if ($('#link').val()) { window.open($('#link').val(), '_blank'); } else { alert('Link kosong!'); }" class="btn btn-outline-secondary btn-sm"><i class="fas fa-external-link-alt"></i> Cek</button>
                                </div>
                            </div>
                            <?= form_error('link', '<small class="text-danger">', '</small>'); ?>
                        </div> -->
                         <div class="col-md-6 mb-3" id="catatan_penolakan_box" style="display: none;"> <?php // Awalnya disembunyikan ?>
                            <label class="small mb-1" for="catatan_penolakan">Catatan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control form-control-sm <?= (form_error('catatan_penolakan')) ? 'is-invalid' : ''; ?>" id="catatan_penolakan" name="catatan_penolakan" rows="3" placeholder="Jelaskan alasan penolakan..."><?= set_value('catatan_penolakan', $permohonan['catatan_penolakan'] ?? ''); ?></textarea>
                            <?= form_error('catatan_penolakan', '<small class="text-danger">', '</small>'); ?>
                        </div>
                    </div>
                </fieldset>

                <hr>
                <div class="form-group text-right">
                    <a href="<?= base_url('petugas_administrasi/detail_permohonan_admin/' . $permohonan['id']) ?>" class="btn btn-secondary btn-icon-split mr-2">
                        <span class="icon text-white-50"><i class="fas fa-arrow-left"></i></span>
                        <span class="text">Kembali ke Detail</span>
                    </a>
                    <button type="submit" class="btn btn-success btn-icon-split">
                        <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                        <span class="text">Simpan Keputusan</span>
                    </button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    // Inisialisasi datepicker
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true,
        uiLibrary: 'bootstrap4' // Jika Anda menggunakan Gijgo Datepicker
    });

    // Logika untuk menampilkan/menyembunyikan field berdasarkan status final
    function toggleFinalFields() {
        var statusFinal = $('input[name="status_final"]:checked').val();
        if (statusFinal === '4') { // Ditolak
            $('#catatan_penolakan_box').show();
            $('#upload_surat_persetujuan_box').hide(); // Sembunyikan upload SK jika ditolak
            $('#surat_keputusan_wajib_text').hide(); // Sembunyikan tanda bintang
            $('#file_surat_keputusan').removeAttr('required'); // Hapus atribut required HTML5
        } else if (statusFinal === '3') { // Disetujui
            $('#catatan_penolakan_box').hide();
            $('#upload_surat_persetujuan_box').show();
            $('#surat_keputusan_wajib_text').show(); // Tampilkan tanda bintang
            <?php if(empty($permohonan['file_surat_keputusan'])): ?>
                // Hanya tambahkan 'required' jika belum ada file lama dan status disetujui
                $('#file_surat_keputusan').attr('required', 'required');
            <?php else: ?>
                $('#file_surat_keputusan').removeAttr('required'); // Jika sudah ada file lama, tidak wajib upload baru
            <?php endif; ?>
        } else {
            $('#catatan_penolakan_box').hide();
            $('#upload_surat_persetujuan_box').show(); // Default tampil
            $('#surat_keputusan_wajib_text').show(); // Default tampil
             <?php if(empty($permohonan['file_surat_keputusan'])): ?>
                $('#file_surat_keputusan').attr('required', 'required');
            <?php else: ?>
                $('#file_surat_keputusan').removeAttr('required');
            <?php endif; ?>
        }
    }

    // Panggil saat halaman load
    toggleFinalFields();

    // Panggil saat radio button status_final berubah
    $('input[name="status_final"]').on('change', function() {
        toggleFinalFields();
    });

    // Untuk menampilkan nama file pada custom file input Bootstrap
    $('#file_surat_keputusan').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName || "Pilih file...");
    });
});
</script>