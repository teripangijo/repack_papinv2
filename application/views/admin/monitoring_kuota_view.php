<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Monitoring Kuota Perusahaan'; ?></h1>
    </div>

    <?php
    // Flashdata seharusnya sudah ditampilkan secara global oleh templates/topbar.php
    // if ($this->session->flashdata('message')) {
    //     echo $this->session->flashdata('message');
    // }
    ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Kuota Returnable Package per Perusahaan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableMonitoringKuota" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Perusahaan</th>
                            <th>Email Kontak</th>
                            <th class="text-right">Kuota Awal</th>
                            <th class="text-right">Sisa Kuota</th>
                            <th class="text-right">Kuota Terpakai</th>
                            <th>No. KEP Kuota Terakhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($monitoring_data) && is_array($monitoring_data)) : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($monitoring_data as $md) : ?>
                                <?php
                                    // Pastikan variabel ada sebelum digunakan untuk menghindari notice, default ke 0 jika tidak ada
                                    $initial_quota = isset($md['initial_quota']) ? (int)$md['initial_quota'] : 0;
                                    $remaining_quota = isset($md['remaining_quota']) ? (int)$md['remaining_quota'] : 0;
                                    $used_quota = $initial_quota - $remaining_quota;
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= isset($md['NamaPers']) ? htmlspecialchars($md['NamaPers']) : 'N/A'; ?></td>
                                    <td><?= isset($md['user_email']) ? htmlspecialchars($md['user_email']) : 'N/A'; ?></td>
                                    <td class="text-right"><?= number_format($initial_quota, 0, ',', '.'); ?></td>
                                    <td class="text-right <?= ($remaining_quota <= 0 && $initial_quota > 0) ? 'text-danger font-weight-bold' : ''; ?>"><?= number_format($remaining_quota, 0, ',', '.'); ?></td>
                                    <td class="text-right"><?= number_format($used_quota, 0, ',', '.'); ?></td>
                                    <td><?= isset($md['kep_terakhir']) ? htmlspecialchars($md['kep_terakhir']) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php // Jika $monitoring_data kosong, tbody akan kosong, dan DataTables akan menampilkan pesan defaultnya atau yang dikustomisasi ?>
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
        $('#dataTableMonitoringKuota').DataTable({
            "order": [[ 1, "asc" ]], // Urutkan berdasarkan Nama Perusahaan (kolom indeks 1)
            "language": {
                "emptyTable": "Belum ada data perusahaan untuk dimonitor.", // Pesan kustom jika tabel kosong
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
            }
            // Anda bisa menambahkan opsi lain DataTables di sini jika perlu
            // "columnDefs": [
            //    { "orderable": false, "targets": [0] } // Contoh: Kolom '#' tidak bisa di-sort
            // ]
        });
    } else {
        console.error("DataTables plugin is not loaded for 'dataTableMonitoringKuota'.");
    }
});
</script>
