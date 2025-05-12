<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Pengajuan Kuota'; ?></h1>
    </div>

    <?php
    // Menampilkan flashdata message jika ada
    if ($this->session->flashdata('message')) {
        echo $this->session->flashdata('message');
    }
    ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Formulir Pengajuan Kuota Returnable Package</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($user_perusahaan)) : ?>
                         <div class="alert alert-danger" role="alert">
                            Data perusahaan Anda belum lengkap. Tidak dapat mengajukan kuota. Silakan <a href="<?= site_url('user/edit'); ?>" class="alert-link">lengkapi profil perusahaan Anda</a>.
                        </div>
                    <?php else: ?>
                        <?php echo form_open(site_url('user/pengajuan_kuota')); ?>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Nama Perusahaan</label>
                            <div class="col-sm-8">
                                <input type="text" readonly class="form-control-plaintext" value="<?= isset($user_perusahaan['NamaPers']) ? htmlspecialchars($user_perusahaan['NamaPers']) : ''; ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Kuota Awal Saat Ini</label>
                            <div class="col-sm-8">
                                <input type="text" readonly class="form-control-plaintext" value="<?= isset($user_perusahaan['initial_quota']) ? number_format($user_perusahaan['initial_quota'],0,',','.') : '0'; ?> Unit">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Sisa Kuota Saat Ini</label>
                            <div class="col-sm-8">
                                <input type="text" readonly class="form-control-plaintext" value="<?= isset($user_perusahaan['remaining_quota']) ? number_format($user_perusahaan['remaining_quota'],0,',','.') : '0'; ?> Unit">
                            </div>
                        </div>

                        <hr>
                        <h5 class="text-gray-800 my-3">Detail Pengajuan Penambahan Kuota</h5>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="nomor_surat_pengajuan">Nomor Surat Pengajuan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= (form_error('nomor_surat_pengajuan')) ? 'is-invalid' : ''; ?>" id="nomor_surat_pengajuan" name="nomor_surat_pengajuan" value="<?= set_value('nomor_surat_pengajuan'); ?>" placeholder="No. Surat dari Perusahaan" required>
                                <?= form_error('nomor_surat_pengajuan', '<small class="text-danger pl-1">', '</small>'); ?>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="tanggal_surat_pengajuan">Tanggal Surat Pengajuan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= (form_error('tanggal_surat_pengajuan')) ? 'is-invalid' : ''; ?>" id="tanggal_surat_pengajuan" name="tanggal_surat_pengajuan" placeholder="YYYY-MM-DD" value="<?= set_value('tanggal_surat_pengajuan'); ?>" required>
                                <?= form_error('tanggal_surat_pengajuan', '<small class="text-danger pl-1">', '</small>'); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="perihal_pengajuan">Perihal Surat Pengajuan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= (form_error('perihal_pengajuan')) ? 'is-invalid' : ''; ?>" id="perihal_pengajuan" name="perihal_pengajuan" value="<?= set_value('perihal_pengajuan'); ?>" placeholder="Contoh: Permohonan Penambahan Kuota Returnable Package" required>
                            <?= form_error('perihal_pengajuan', '<small class="text-danger pl-1">', '</small>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="nama_barang_kuota">Nama/Jenis Barang (Untuk Kuota Ini) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= (form_error('nama_barang_kuota')) ? 'is-invalid' : ''; ?>" id="nama_barang_kuota" name="nama_barang_kuota" value="<?= set_value('nama_barang_kuota'); ?>" placeholder="Contoh: Fiber Box, Pallet Kayu" required>
                            <?= form_error('nama_barang_kuota', '<small class="text-danger pl-1">', '</small>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="requested_quota">Jumlah Kuota yang Diajukan (Unit) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control <?= (form_error('requested_quota')) ? 'is-invalid' : ''; ?>" id="requested_quota" name="requested_quota" value="<?= set_value('requested_quota'); ?>" placeholder="Masukkan jumlah kuota" min="1" required>
                            <?= form_error('requested_quota', '<small class="text-danger pl-1">', '</small>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="reason">Alasan Pengajuan <span class="text-danger">*</span></label>
                            <textarea class="form-control <?= (form_error('reason')) ? 'is-invalid' : ''; ?>" id="reason" name="reason" rows="4" placeholder="Jelaskan alasan pengajuan penambahan kuota Anda" required><?= set_value('reason'); ?></textarea>
                            <?= form_error('reason', '<small class="text-danger pl-1">', '</small>'); ?>
                        </div>
                        
                        <?php // Anda bisa menambahkan input untuk upload dokumen pendukung di sini jika perlu ?>
                        <button type="submit" class="btn btn-primary btn-user btn-block">
                            Kirim Pengajuan Kuota
                        </button>
                        <?php echo form_close(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi</h6>
                </div>
                <div class="card-body small">
                    <p>Silakan ajukan penambahan kuota jika sisa kuota Anda tidak mencukupi untuk melakukan impor kembali returnable package.</p>
                    <p>Pengajuan Anda akan ditinjau oleh Administrator. Anda akan menerima notifikasi setelah pengajuan diproses.</p>
                    <p>Pastikan alasan pengajuan Anda jelas dan didukung oleh data yang valid jika diperlukan.</p>
                </div>
            </div>
        </div>
    </div>

</div>
<?php // Script untuk Gijgo Datepicker ?>
<script>
    $(document).ready(function () {
        if (typeof $ !== 'undefined' && typeof $.fn.datepicker !== 'undefined') {
            console.log('Pengajuan Kuota: jQuery and Gijgo Datepicker are loaded.');

            var datepickerConfig = {
                uiLibrary: 'bootstrap4',
                format: 'yyyy-mm-dd',
                showOnFocus: true,
                showRightIcon: true, 
                modal: false,
                header: false,       
                footer: false,       
                autoClose: true      
            };

            $('#tanggal_surat_pengajuan').datepicker(datepickerConfig);

            console.log('Pengajuan Kuota: Datepicker initialized for #tanggal_surat_pengajuan');

        } else {
            if (typeof $ === 'undefined') {
                console.error("Pengajuan Kuota: jQuery is not loaded.");
            }
            if (typeof $.fn.datepicker === 'undefined' && typeof $ !== 'undefined') {
                console.error("Pengajuan Kuota: Gijgo Datepicker ($.fn.datepicker) is not loaded. Ensure gijgo.min.js is included AFTER jQuery and jQuery is loaded ONLY ONCE.");
            }
        }
    });
</script>
