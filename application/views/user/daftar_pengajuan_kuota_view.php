<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Daftar Pengajuan Kuota Saya'; ?></h1>
        <a href="<?= site_url('user/pengajuan_kuota'); ?>" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Ajukan Kuota Baru
        </a>
    </div>

    <?php
    // Menampilkan flashdata message jika ada
    if ($this->session->flashdata('message')) {
        echo $this->session->flashdata('message');
    }
    ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Riwayat Pengajuan Kuota Anda</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableDaftarPengajuanKuota" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID Pengajuan</th>
                            <th>Tgl. Surat Pengajuan</th>
                            <th>No. Surat Pengajuan</th>
                            <th>Perihal</th>
                            <th>Nama Barang (Kuota)</th>
                            <th>Kuota Diajukan</th>
                            <th>Tgl. Submit Sistem</th>
                            <th>Status</th>
                            <th>Kuota Disetujui</th>
                            <th>No. Surat Keputusan</th> 
                            <th>Tgl. Proses Petugas</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($daftar_pengajuan) && is_array($daftar_pengajuan)) : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($daftar_pengajuan as $dp) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= isset($dp['id']) ? htmlspecialchars($dp['id']) : '-'; ?></td>
                                    <td><?= isset($dp['tanggal_surat_pengajuan']) ? date('d/m/Y', strtotime($dp['tanggal_surat_pengajuan'])) : '-'; ?></td>
                                    <td><?= isset($dp['nomor_surat_pengajuan']) ? htmlspecialchars($dp['nomor_surat_pengajuan']) : '-'; ?></td>
                                    <td><?= isset($dp['perihal_pengajuan']) ? htmlspecialchars($dp['perihal_pengajuan']) : '-'; ?></td>
                                    <td><?= isset($dp['nama_barang_kuota']) ? htmlspecialchars($dp['nama_barang_kuota']) : '-'; ?></td>
                                    <td><?= isset($dp['requested_quota']) ? number_format($dp['requested_quota'], 0, ',', '.') . ' Unit' : '-'; ?></td>
                                    <td><?= isset($dp['submission_date']) ? date('d/m/Y H:i', strtotime($dp['submission_date'])) : '-'; ?></td>
                                    <td>
                                        <?php
                                        $status_text = ucfirst(isset($dp['status']) ? $dp['status'] : 'N/A');
                                        $status_badge = 'secondary';
                                        if (isset($dp['status'])) {
                                            switch (strtolower($dp['status'])) {
                                                case 'pending': $status_badge = 'warning'; $status_text = 'Pending (Menunggu Petugas)'; break;
                                                case 'diproses': $status_badge = 'info'; $status_text = 'Diproses Petugas'; break;
                                                case 'approved': $status_badge = 'success'; $status_text = 'Selesai (Disetujui)'; break;
                                                case 'rejected': $status_badge = 'danger'; $status_text = 'Selesai (Ditolak)'; break;
                                                case 'selesai': $status_badge = 'primary'; $status_text = 'Selesai (SK Terbit)'; break; 
                                            }
                                        }
                                        echo '<span class="badge badge-' . $status_badge . '">' . htmlspecialchars($status_text) . '</span>';
                                        ?>
                                    </td>
                                    <td><?= (isset($dp['status']) && (strtolower($dp['status']) == 'approved' || strtolower($dp['status']) == 'selesai') && isset($dp['approved_quota'])) ? number_format($dp['approved_quota'],0,',','.') . ' Unit' : '-'; ?></td>
                                    <td>
                                        <?php // Menampilkan No. SK Petugas dan link download jika file SK ada ?>
                                        <?php if (isset($dp['nomor_sk_petugas']) && !empty($dp['nomor_sk_petugas'])) : ?>
                                            <?php if (isset($dp['status']) && (strtolower($dp['status']) == 'approved' || strtolower($dp['status']) == 'selesai' || strtolower($dp['status']) == 'rejected') && !empty($dp['file_sk_petugas'])) : ?>
                                                <a href="<?= site_url('user/download_sk_kuota/' . $dp['id']); ?>" title="Unduh Surat Keputusan: <?= htmlspecialchars($dp['nomor_sk_petugas']); ?>">
                                                    <?= htmlspecialchars($dp['nomor_sk_petugas']); ?> <i class="fas fa-xs fa-download text-success"></i>
                                                </a>
                                            <?php else: ?>
                                                <?= htmlspecialchars($dp['nomor_sk_petugas']); ?>
                                            <?php endif; ?>
                                        <?php else : ?>
                                            -
                                        <?php endif; ?>
                                    </td> 
                                    <td><?= isset($dp['processed_date']) ? date('d/m/Y H:i', strtotime($dp['processed_date'])) : '-'; ?></td>
                                    <td>
                                        <?php // Tombol untuk mencetak surat permohonan pengajuan kuota (selalu ada) ?>
                                        <a href="<?= site_url('user/print_bukti_pengajuan_kuota/' . (isset($dp['id']) ? $dp['id'] : '#')); ?>" class="btn btn-info btn-sm" title="Cetak Surat Permohonan Pengajuan Kuota" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="13" class="text-center">Belum ada data pengajuan kuota.</td>
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
        $('#dataTableDaftarPengajuanKuota').DataTable({
            "order": [[ 7, "desc" ]], 
            "columnDefs": [
                { "orderable": false, "targets": [12] } 
            ]
        });
    } else { /* ... error logging ... */ }
});
</script>
