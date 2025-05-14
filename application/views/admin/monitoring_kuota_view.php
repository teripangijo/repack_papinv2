<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Monitoring Kuota Perusahaan'); ?></h1>
        </div>

    <!-- <?php
    // Menampilkan flash message jika ada
    if ($this->session->flashdata('message')) {
        echo $this->session->flashdata('message');
    }
    ?> -->

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
                        <?php if (!empty($monitoring_data)): $no = 1; ?>
                            <?php foreach ($monitoring_data as $item): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td>
                                    <?php
                                    // Pastikan $item['id_pers'] ada dan tidak kosong.
                                    // Ini adalah asumsi bahwa kolom di tabel user_perusahaan adalah 'id_pers'.
                                    // Jika nama kolomnya berbeda (misal 'id'), ganti $item['id_pers'] menjadi $item['id']
                                    // dan pastikan kolom tersebut di-SELECT di controller.
                                    if (isset($item['id_pers']) && !empty($item['id_pers'])) {
                                        $link_histori = site_url('admin/histori_kuota_perusahaan/' . $item['id_pers']);
                                        echo '<a href="' . $link_histori . '" title="Lihat Histori Kuota ' . htmlspecialchars($item['NamaPers']) . '">' . htmlspecialchars($item['NamaPers']) . '</a>';
                                    } else {
                                        echo htmlspecialchars($item['NamaPers'] ?? 'Nama Perusahaan Tidak Ada');
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($item['user_email'] ?? '-'); ?></td>
                                <td class="text-right"><?= htmlspecialchars(number_format($item['initial_quota'] ?? 0)); ?></td>
                                <td class="text-right font-weight-bold <?= (($item['remaining_quota'] ?? 0) <=0 && ($item['initial_quota'] ?? 0) > 0) ? 'text-danger' : 'text-success'; ?>">
                                    <?= htmlspecialchars(number_format($item['remaining_quota'] ?? 0)); ?>
                                </td>
                                <td class="text-right">
                                    <?php
                                        $initial_quota = $item['initial_quota'] ?? 0;
                                        $remaining_quota = $item['remaining_quota'] ?? 0;
                                        echo htmlspecialchars(number_format($initial_quota - $remaining_quota));
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($item['kep_terakhir'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data perusahaan untuk dimonitor.</td>
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
    // Cek apakah DataTables sudah di-load sebelum menginisialisasi
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#dataTableMonitoringKuota').DataTable({
            "order": [[1, "asc"]], // Urutkan berdasarkan Nama Perusahaan A-Z
            // Opsi lain DataTables bisa ditambahkan di sini
            // Contoh: paging, searching, lengthChange, dll. akan aktif secara default
            // "language": {
            //     "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json" // Jika ingin bahasa Indonesia
            // }
        });
    } else {
        console.warn("DataTables plugin is not loaded.");
    }
});
</script>