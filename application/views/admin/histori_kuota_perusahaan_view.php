<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Histori Kuota'); ?></h1>
        <a href="<?= site_url('admin/monitoring_kuota'); ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Monitoring Kuota
        </a>
    </div>

    <?php if (isset($perusahaan) && !empty($perusahaan)): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Detail Kuota untuk: <?= htmlspecialchars($perusahaan['NamaPers']); ?>
                (Kontak: <?= htmlspecialchars($perusahaan['nama_kontak'] ?? ($perusahaan['email_kontak'] ?? 'N/A')); ?>)
            </h6>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Kuota Awal Saat Ini:</strong> <?= htmlspecialchars(number_format($perusahaan['initial_quota'] ?? 0)); ?> Unit
                </div>
                <div class="col-md-4">
                    <strong>Sisa Kuota Saat Ini:</strong> <span class="font-weight-bold <?= (($perusahaan['remaining_quota'] ?? 0) <=0 && ($perusahaan['initial_quota'] ?? 0) > 0) ? 'text-danger' : 'text-success'; ?>"><?= htmlspecialchars(number_format($perusahaan['remaining_quota'] ?? 0)); ?> Unit</span>
                </div>
                <div class="col-md-4">
                    <strong>Total Terpakai:</strong> <?= htmlspecialchars(number_format(($perusahaan['initial_quota'] ?? 0) - ($perusahaan['remaining_quota'] ?? 0))); ?> Unit
                </div>
            </div>
            <hr>
            <h6 class="font-weight-bold">Log Perubahan Kuota:</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableHistoriKuota" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tgl Transaksi</th>
                            <th>Jenis</th>
                            <th class="text-right">Jumlah Perubahan</th>
                            <th class="text-right">Sisa Sebelum</th>
                            <th class="text-right">Sisa Sesudah</th>
                            <th>Keterangan</th>
                            <th>Ref. ID</th> <th>Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($histori_kuota)): $no = 1; ?>
                            <?php foreach ($histori_kuota as $log): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= isset($log['tanggal_transaksi']) ? date('d/m/Y H:i', strtotime($log['tanggal_transaksi'])) : '-'; ?></td>
                                <td>
                                    <?php
                                    $jenis_badge = 'secondary';
                                    if (isset($log['jenis_transaksi'])) {
                                        if ($log['jenis_transaksi'] == 'penambahan') $jenis_badge = 'success';
                                        elseif ($log['jenis_transaksi'] == 'pengurangan') $jenis_badge = 'danger';
                                        elseif ($log['jenis_transaksi'] == 'koreksi') $jenis_badge = 'warning';
                                    }
                                    ?>
                                    <span class="badge badge-<?= $jenis_badge; ?>"><?= ucfirst(htmlspecialchars($log['jenis_transaksi'] ?? 'N/A')); ?></span>
                                </td>
                                <td class="text-right <?= (isset($log['jenis_transaksi']) && $log['jenis_transaksi'] == 'penambahan') ? 'text-success' : ((isset($log['jenis_transaksi']) && $log['jenis_transaksi'] == 'pengurangan') ? 'text-danger' : ''); ?>">
                                    <?= (isset($log['jenis_transaksi']) && $log['jenis_transaksi'] == 'penambahan') ? '+' : ((isset($log['jenis_transaksi']) && $log['jenis_transaksi'] == 'pengurangan') ? '-' : ''); ?>
                                    <?= htmlspecialchars(number_format(abs($log['jumlah_perubahan'] ?? 0))); ?> Unit
                                </td>
                                <td class="text-right"><?= htmlspecialchars(number_format($log['sisa_kuota_sebelum'] ?? 0)); ?></td>
                                <td class="text-right"><?= htmlspecialchars(number_format($log['sisa_kuota_setelah'] ?? 0)); ?></td>
                                <td>
                                    <?= htmlspecialchars($log['keterangan'] ?? '-'); ?>
                                    <?php if (!empty($log['id_referensi_transaksi']) && !empty($log['tipe_referensi'])): ?>
                                        <?php
                                        $link_ref = '#';
                                        $id_ref = $log['id_referensi_transaksi'];

                                        if ($log['tipe_referensi'] == 'pengajuan_kuota') {
                                            // Link ke detail pengajuan kuota
                                            // Pastikan method 'detailPengajuanKuotaAdmin' ada dan berfungsi
                                            $link_ref = site_url('admin/detailPengajuanKuotaAdmin/' . $id_ref);
                                        } elseif ($log['tipe_referensi'] == 'permohonan_impor') {
                                            // PERUBAHAN DI SINI: Link ke halaman detail permohonan admin
                                            // Pastikan method 'detail_permohonan_admin' ada dan berfungsi
                                            $link_ref = site_url('admin/detail_permohonan_admin/' . $id_ref);
                                        }
                                        ?>
                                        <br><small><a href="<?= $link_ref; ?>" target="_blank" title="Lihat detail referensi">(Lihat Ref: <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $log['tipe_referensi']))) . ' ID ' . $id_ref; ?>)</a></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($log['id_referensi_transaksi'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($log['nama_pencatat'] ?? 'Sistem'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php // Jika kosong, DataTables akan menampilkan pesan "emptyTable" ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Data perusahaan tidak ditemukan atau tidak dapat diakses.
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    if(typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined'){
        $('#dataTableHistoriKuota').DataTable({
            "order": [[ 1, "desc" ]], // Urutkan berdasarkan Tanggal Transaksi terbaru (indeks kolom ke-1)
            "language": {
                "emptyTable": "Belum ada histori perubahan kuota untuk perusahaan ini.",
                "zeroRecords": "Tidak ada data yang cocok ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                "infoFiltered": "(disaring dari _MAX_ total entri)",
                "lengthMenu": "Tampilkan _MENU_ entri",
                "search": "Cari:",
                "paginate": {
                    "first":    "Awal",
                    "last":     "Akhir",
                    "next":     "Berikutnya",
                    "previous": "Sebelumnya"
                },
                "aria": {
                    "sortAscending":  ": aktifkan untuk mengurutkan kolom secara menaik",
                    "sortDescending": ": aktifkan untuk mengurutkan kolom secara menurun"
                }
            },
            "columnDefs": [
                // Kolom '#' (indeks 0) tidak bisa di-sort
                { "orderable": false, "targets": [0] }
            ]
            // Tambahkan opsi lain jika perlu, misalnya:
            // "paging": true,
            // "searching": true,
            // "info": true,
        });
    } else {
        console.error("jQuery atau DataTables plugin tidak termuat dengan benar untuk 'dataTableHistoriKuota'.");
    }
});
</script>