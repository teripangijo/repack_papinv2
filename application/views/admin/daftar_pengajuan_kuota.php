<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Daftar Pengajuan Kuota'; ?></h1>
    </div>

    <?php
    if ($this->session->flashdata('message')) {
        echo $this->session->flashdata('message');
    }
    ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Pengajuan Kuota dari Perusahaan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableAdminPengajuanKuota" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID Pengajuan</th>
                            <th>Nama Perusahaan</th>
                            <th>Email User</th>
                            <th>No. Surat Pengajuan</th>
                            <th>Tgl. Surat Pengajuan</th>
                            <th>Kuota Diajukan</th>
                            <th>Alasan</th>
                            <th>Tgl. Submit Sistem</th>
                            <th>Status</th>
                            <th>Kuota Disetujui</th>
                            <th>Catatan Petugas</th>
                            <th>Tgl. Proses</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pengajuan_kuota) && is_array($pengajuan_kuota)) : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($pengajuan_kuota as $pk) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= isset($pk['id']) ? htmlspecialchars($pk['id']) : '-'; ?></td>
                                    <td><?= isset($pk['NamaPers']) ? htmlspecialchars($pk['NamaPers']) : 'N/A'; ?></td>
                                    <td><?= isset($pk['user_email']) ? htmlspecialchars($pk['user_email']) : 'N/A'; ?></td>
                                    <td><?= isset($pk['nomor_surat_pengajuan']) ? htmlspecialchars($pk['nomor_surat_pengajuan']) : '-'; ?></td>
                                    <td><?= isset($pk['tanggal_surat_pengajuan']) && $pk['tanggal_surat_pengajuan'] != '0000-00-00' ? date('d/m/Y', strtotime($pk['tanggal_surat_pengajuan'])) : '-'; ?></td>
                                    <td class="text-right"><?= isset($pk['requested_quota']) ? number_format($pk['requested_quota'],0,',','.') : '-'; ?> Unit</td>
                                    <td><?= isset($pk['reason']) ? nl2br(htmlspecialchars($pk['reason'])) : '-'; ?></td>
                                    <td><?= isset($pk['submission_date']) && $pk['submission_date'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($pk['submission_date'])) : '-'; ?></td>
                                    <td>
                                        <?php
                                        $status_text = ucfirst(isset($pk['status']) ? $pk['status'] : 'N/A');
                                        $status_badge = 'secondary';
                                        if (isset($pk['status'])) {
                                            switch (strtolower($pk['status'])) {
                                                case 'pending': $status_badge = 'warning'; $status_text = 'Pending'; break;
                                                case 'diproses': $status_badge = 'info'; $status_text = 'Diproses'; break;
                                                case 'approved': $status_badge = 'success'; $status_text = 'Disetujui'; break;
                                                case 'rejected': $status_badge = 'danger'; $status_text = 'Ditolak'; break;
                                                // Anda mungkin tidak menggunakan 'selesai' lagi jika 'approved' sudah final
                                                // case 'selesai': $status_badge = 'primary'; $status_text = 'Selesai (SK Terbit)'; break;
                                            }
                                        }
                                        echo '<span class="badge badge-' . $status_badge . '">' . htmlspecialchars($status_text) . '</span>';
                                        ?>
                                    </td>
                                    <td class="text-right"><?= (isset($pk['status']) && strtolower($pk['status']) == 'approved' && isset($pk['approved_quota'])) ? number_format($pk['approved_quota'],0,',','.') . ' Unit' : '-'; ?></td>
                                    <td><?= isset($pk['admin_notes']) && !empty($pk['admin_notes']) ? nl2br(htmlspecialchars($pk['admin_notes'])) : '-'; ?></td>
                                    <td><?= isset($pk['processed_date']) && $pk['processed_date'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($pk['processed_date'])) : '-'; ?></td>
                                    <td>
                                        <a href="<?= site_url('admin/detailPengajuanKuotaAdmin/' . $pk['id']); ?>" class="btn btn-sm btn-info mb-1" title="Lihat Detail Proses Admin">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>

                                        <?php if (isset($pk['status']) && (strtolower($pk['status']) == 'pending' || strtolower($pk['status']) == 'diproses')) : ?>
                                            <a href="<?= site_url('admin/proses_pengajuan_kuota/' . $pk['id']); ?>" class="btn btn-sm btn-success mb-1" title="Proses Pengajuan">
                                                <i class="fas fa-cogs"></i> Proses
                                            </a>
                                        <?php endif; ?>

                                        <?php if (!empty($pk['file_sk_petugas']) && (strtolower($pk['status']) == 'approved' || strtolower($pk['status']) == 'rejected')): // Tampilkan tombol download SK jika file ada dan status sudah final ?>
                                            <a href="<?= site_url('admin/download_sk_kuota_admin/' . $pk['id']); ?>" class="btn btn-sm btn-primary mb-1" title="Unduh SK Petugas">
                                                <i class="fas fa-download"></i> SK
                                            </a>
                                        <?php endif; ?>
                                        
                                        </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="14" class="text-center">Belum ada data pengajuan kuota.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<script>
$(document).ready(function() {
    if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
        $('#dataTableAdminPengajuanKuota').DataTable({
            "order": [[ 8, "desc" ]], // Urutkan berdasarkan Tgl Submit Sistem terbaru (indeks kolom ke-8 dari 0)
            "columnDefs": [
                { "orderable": false, "targets": [13] } // Kolom Action (indeks ke-13 dari 0) tidak bisa di-sort
            ],
            // Menambahkan konfigurasi bahasa jika diperlukan
            // "language": {
            //     "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json" // Contoh untuk Bahasa Indonesia
            // }
        });
    } else {
        console.error("DataTables plugin is not loaded for Daftar Pengajuan Kuota (Admin).");
    }
});
</script>