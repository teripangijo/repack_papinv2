<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Monitoring Kuota Perusahaan'; ?></h1>
    </div>

    <?php
    if ($this->session->flashdata('message')) {
        echo $this->session->flashdata('message');
    }
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
                            <th>Kuota Awal</th>
                            <th>Sisa Kuota</th>
                            <th>Kuota Terpakai</th>
                            <th>No. KEP Kuota Terakhir</th>
                            </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($monitoring_data) && is_array($monitoring_data)) : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($monitoring_data as $md) : ?>
                                <?php
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
                        <?php else : ?>
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
    // Pastikan jQuery dan DataTables sudah dimuat di template footer
    if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
        $('#dataTableMonitoringKuota').DataTable({
            "order": [[ 1, "asc" ]], // Urutkan berdasarkan Nama Perusahaan
            // "columnDefs": [
            //     { "orderable": false, "targets": [7] } // Jika ada kolom action
            // ]
        });
    } else {
        console.error("DataTables plugin is not loaded for Monitoring Kuota page.");
    }
});
</script>
