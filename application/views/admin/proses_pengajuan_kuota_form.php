<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Proses Pengajuan Kuota'; ?></h1>
        <a href="<?= site_url('admin/daftar_pengajuan_kuota'); ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Pengajuan
        </a>
    </div>

    <?php
    if ($this->session->flashdata('message')) {
        echo $this->session->flashdata('message');
    }
    ?>

    <?php if (empty($pengajuan)) : ?>
        <div class="alert alert-danger">Data pengajuan tidak ditemukan.</div>
    <?php else : ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Detail Pengajuan Kuota - ID: <?= htmlspecialchars($pengajuan['id']); ?></h6>
                    </div>
                    <div class="card-body">
                        <?php echo form_open(site_url('admin/proses_pengajuan_kuota/' . $pengajuan['id'])); ?>
                        
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Nama Perusahaan</label>
                            <div class="col-sm-8">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($pengajuan['NamaPers']); ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Email User</label>
                            <div class="col-sm-8">
                                <input type="text" readonly class="form-control-plaintext" value="<?= htmlspecialchars($pengajuan['user_email']); ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Kuota Awal Saat Ini</label>
                            <div class="col-sm-8">
                                <input type="text" readonly class="form-control-plaintext" value="<?= number_format($pengajuan['initial_quota'],0,',','.'); ?> Unit">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Sisa Kuota Saat Ini</label>
                            <div class="col-sm-8">
                                <input type="text" readonly class="form-control-plaintext" value="<?= number_format($pengajuan['remaining_quota'],0,',','.'); ?> Unit">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Jumlah Kuota Diajukan</label>
                            <div class="col-sm-8">
                                <input type="text" readonly class="form-control-plaintext font-weight-bold text-primary" value="<?= number_format($pengajuan['requested_quota'],0,',','.'); ?> Unit">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Alasan Pengajuan</label>
                            <div class="col-sm-8">
                                <textarea readonly class="form-control-plaintext" rows="3"><?= nl2br(htmlspecialchars($pengajuan['reason'])); ?></textarea>
                            </div>
                        </div>
                         <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Tanggal Pengajuan</label>
                            <div class="col-sm-8">
                                <input type="text" readonly class="form-control-plaintext" value="<?= date('d F Y H:i', strtotime($pengajuan['submission_date'])); ?>">
                            </div>
                        </div>

                        <hr>
                        <h5 class="text-gray-800 mb-3">Tindakan Admin</h5>

                        <div class="form-group">
                            <label for="status_pengajuan">Status Pengajuan <span class="text-danger">*</span></label>
                            <select class="form-control <?= (form_error('status_pengajuan')) ? 'is-invalid' : ''; ?>" id="status_pengajuan" name="status_pengajuan" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="approved" <?= set_select('status_pengajuan', 'approved'); ?>>Approved (Disetujui)</option>
                                <option value="rejected" <?= set_select('status_pengajuan', 'rejected'); ?>>Rejected (Ditolak)</option>
                            </select>
                            <?= form_error('status_pengajuan', '<small class="text-danger pl-1">', '</small>'); ?>
                        </div>

                        <div class="form-group" id="approved_quota_group" style="display: none;"> <?php // Sembunyikan defaultnya ?>
                            <label for="approved_quota">Jumlah Kuota Disetujui <span class="text-danger">*</span></label>
                            <input type="number" class="form-control <?= (form_error('approved_quota')) ? 'is-invalid' : ''; ?>" id="approved_quota" name="approved_quota" value="<?= set_value('approved_quota', $pengajuan['requested_quota']); ?>" placeholder="Masukkan jumlah kuota yang disetujui" min="0">
                            <?= form_error('approved_quota', '<small class="text-danger pl-1">', '</small>'); ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_notes">Catatan Admin (Opsional)</label>
                            <textarea class="form-control <?= (form_error('admin_notes')) ? 'is-invalid' : ''; ?>" id="admin_notes" name="admin_notes" rows="3" placeholder="Tambahkan catatan jika perlu"><?= set_value('admin_notes'); ?></textarea>
                            <?= form_error('admin_notes', '<small class="text-danger pl-1">', '</small>'); ?>
                        </div>

                        <button type="submit" class="btn btn-primary btn-user btn-block">
                            Simpan Proses Pengajuan
                        </button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                 <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Panduan</h6>
                    </div>
                    <div class="card-body small">
                        <p>Pilih status "Approved" jika pengajuan kuota disetujui. Masukkan jumlah kuota yang disetujui pada field yang muncul.</p>
                        <p>Pilih status "Rejected" jika pengajuan kuota ditolak. Anda dapat memberikan alasan penolakan pada bagian Catatan Admin.</p>
                        <p>Setelah diproses, kuota perusahaan akan otomatis diperbarui jika disetujui.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<script>
$(document).ready(function(){
    // Tampilkan/sembunyikan field approved_quota berdasarkan status_pengajuan
    function toggleApprovedQuotaField() {
        if ($('#status_pengajuan').val() == 'approved') {
            $('#approved_quota_group').show();
            $('#approved_quota').prop('required',true); // Jadikan wajib jika approved
        } else {
            $('#approved_quota_group').hide();
            $('#approved_quota').prop('required',false); // Tidak wajib jika rejected
            $('#approved_quota').val(''); // Kosongkan nilainya jika disembunyikan
        }
    }
    // Panggil saat halaman dimuat
    toggleApprovedQuotaField(); 
    // Panggil saat status_pengajuan berubah
    $('#status_pengajuan').change(function(){
        toggleApprovedQuotaField();
    });
});
</script>
