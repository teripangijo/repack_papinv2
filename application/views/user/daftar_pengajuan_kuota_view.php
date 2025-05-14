<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Daftar Pengajuan Kuota Saya'; ?></h1>
        <a href="<?= site_url('user/pengajuan_kuota'); ?>" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Ajukan Kuota Baru
        </a>
    </div>

    <?php
    // Flashdata seharusnya sudah ditampilkan secara global oleh templates/topbar.php
    // if ($this->session->flashdata('message')) {
    //     echo $this->session->flashdata('message');
    // }
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
                            <th>Tgl. Submit</th>
                            <th>No. Surat Anda</th>
                            <th>Perihal</th>
                            <th class="text-right">Jml. Diajukan</th>
                            <th>Status</th>
                            <th class="text-right">Jml. Disetujui</th>
                            <th>No. SK Petugas</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($daftar_pengajuan) && is_array($daftar_pengajuan)) : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($daftar_pengajuan as $dp) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($dp['id']); ?></td>
                                    <td><?= isset($dp['submission_date']) && $dp['submission_date'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($dp['submission_date'])) : '-'; ?></td>
                                    <td><?= htmlspecialchars($dp['nomor_surat_pengajuan'] ?? '-'); ?></td>
                                    <td><?= htmlspecialchars($dp['perihal_pengajuan'] ?? '-'); ?></td>
                                    <td class="text-right"><?= isset($dp['requested_quota']) ? number_format($dp['requested_quota'],0,',','.') : '-'; ?> Unit</td>
                                    <td>
                                        <?php
                                        $status_text_pq = ucfirst(isset($dp['status']) ? htmlspecialchars($dp['status']) : 'N/A');
                                        $status_badge_pq = 'secondary';
                                        if (isset($dp['status'])) {
                                            switch (strtolower($dp['status'])) {
                                                case 'pending': $status_badge_pq = 'warning'; $status_text_pq = 'Pending'; break;
                                                case 'diproses': $status_badge_pq = 'info'; $status_text_pq = 'Diproses'; break;
                                                case 'approved': $status_badge_pq = 'success'; $status_text_pq = 'Disetujui'; break;
                                                case 'rejected': $status_badge_pq = 'danger'; $status_text_pq = 'Ditolak'; break;
                                            }
                                        }
                                        echo '<span class="badge badge-pill badge-' . $status_badge_pq . '">' . $status_text_pq . '</span>';
                                        ?>
                                    </td>
                                    <td class="text-right"><?= (isset($dp['status']) && strtolower($dp['status']) == 'approved' && isset($dp['approved_quota'])) ? number_format($dp['approved_quota'],0,',','.') . ' Unit' : '-'; ?></td>
                                    <td><?= htmlspecialchars($dp['nomor_sk_petugas'] ?? '-'); ?></td>
                                    <td>
                                        <a href="<?= site_url('user/print_bukti_pengajuan_kuota/' . $dp['id']); ?>" class="btn btn-info btn-circle btn-sm my-1" title="Cetak Bukti Pengajuan" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <?php if (isset($dp['status']) && strtolower($dp['status']) == 'approved' && !empty($dp['file_sk_petugas'])): ?>
                                            <a href="<?= site_url('user/download_sk_kuota_user/' . $dp['id']); ?>" class="btn btn-success btn-circle btn-sm my-1" title="Download SK Penetapan">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        <?php endif; ?>
                                        </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php // Baris "Belum ada data..." yang menggunakan colspan DIHAPUS dari sini ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<script>
$(document).ready(function() {
    // Pastikan jQuery dan DataTables sudah dimuat di template footer Anda
    if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
        $('#dataTableDaftarPengajuanKuota').DataTable({
            "order": [[ 2, "desc" ]], // Urutkan berdasarkan Tgl Submit (indeks kolom ke-2 dari 0)
            "language": {
                "emptyTable": "Anda belum memiliki riwayat pengajuan kuota.", // Pesan kustom jika tabel kosong
                "zeroRecords": "Tidak ada data yang cocok ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                "infoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                "lengthMenu": "Tampilkan _MENU_ entri",
                "search": "Cari:",
                "paginate": {
                    "first":    "Pertama",
                    "last":     "Terakhir",
                    "next":     "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            },
            "columnDefs": [
                 // Kolom '#' (indeks 0) dan Action (indeks 9) tidak bisa di-sort
                { "orderable": false, "targets": [0, 9] }
            ]
        });
    } else {
        console.error("DataTables plugin is not loaded for 'dataTableDaftarPengajuanKuota'.");
    }
});
</script>
