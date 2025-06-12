<?php ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Daftar Pengajuan Kuota Saya'; ?></h1>
        <a href="<?= site_url('user/pengajuan_kuota'); ?>" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Buat Pengajuan Kuota Baru
        </a>
    </div>

    <!-- <?php if ($this->session->flashdata('message')) { echo $this->session->flashdata('message'); } ?> -->

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Riwayat Pengajuan Kuota</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTablePengajuanKuotaUser" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID Aju</th>
                            <th>No. Surat</th>
                            <th>Tgl Surat</th>
                            <th>Nama Barang</th>
                            <th>Jumlah Diajukan</th>
                            <th>Tgl Submit</th>
                            <th>Status</th>
                            <th>No. SK</th>
                            <th>Kuota Disetujui</th>
                            <th style="min-width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($daftar_pengajuan) && is_array($daftar_pengajuan)): ?>
                            <?php $no = 1; foreach ($daftar_pengajuan as $p): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($p['id']); ?></td>
                                    <td><?= htmlspecialchars($p['nomor_surat_pengajuan'] ?? '-'); ?></td>
                                    <td><?= isset($p['tanggal_surat_pengajuan']) && $p['tanggal_surat_pengajuan'] != '0000-00-00' ? date('d/m/Y', strtotime($p['tanggal_surat_pengajuan'])) : '-'; ?></td>
                                    <td><?= htmlspecialchars($p['nama_barang_kuota'] ?? '-'); ?></td>
                                    <td><?= htmlspecialchars(number_format($p['requested_quota'] ?? 0)); ?></td>
                                    <td><?= isset($p['submission_date']) && $p['submission_date'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($p['submission_date'])) : '-'; ?></td>
                                    <td>
                                        <?php
                                        $status_text = '-'; $status_badge = 'secondary';
                                        if (isset($p['status'])) {
                                            switch (strtolower($p['status'])) {
                                                case 'pending': $status_text = 'Pending'; $status_badge = 'warning'; break;
                                                case 'diproses': $status_text = 'Diproses'; $status_badge = 'info'; break;
                                                case 'approved': $status_text = 'Disetujui'; $status_badge = 'success'; break;
                                                case 'rejected': $status_text = 'Ditolak'; $status_badge = 'danger'; break;
                                                default: $status_text = htmlspecialchars(ucfirst($p['status']));
                                            }
                                        }
                                        echo '<span class="badge badge-pill badge-' . $status_badge . '">' . htmlspecialchars($status_text) . '</span>';
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($p['nomor_sk_petugas'] ?? '-'); ?></td>
                                    <td><?= htmlspecialchars(number_format($p['approved_quota'] ?? 0)); ?></td>
                                    <td class="text-center">
                                        <a href="<?= site_url('user/print_bukti_pengajuan_kuota/' . $p['id']); ?>" class="btn btn-info btn-circle btn-sm my-1" title="Lihat/Cetak Bukti Pengajuan">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <?php
                                        if (isset($p['status']) && strtolower($p['status']) == 'pending'):
                                        ?>
                                            <a href="<?= site_url('user/hapus_pengajuan_kuota/' . $p['id']); ?>" class="btn btn-danger btn-circle btn-sm my-1" title="Hapus Pengajuan Kuota" onclick="return confirm('Apakah Anda yakin ingin menghapus pengajuan kuota untuk barang \'<?= htmlspecialchars($p['nama_barang_kuota']); ?>\' ini?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
        $('#dataTablePengajuanKuotaUser').DataTable({ // Sesuaikan ID tabel jika berbeda
            "order": [[ 6, "desc" ]], // Urutkan berdasarkan Tgl Submit terbaru
            "language": { /* ... bahasa ... */ },
            "columnDefs": [
                { "orderable": false, "searchable": false, "targets": [0, 10] } // Kolom # dan Action
            ]
        });
    }
});
</script>