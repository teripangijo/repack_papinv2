<?php // application/views/petugas/monitoring_permohonan_view.php ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Monitoring Permohonan Impor'; ?></h1>
    </div>

    <?php if ($this->session->flashdata('message')) { echo $this->session->flashdata('message'); } ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Seluruh Permohonan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableMonitoringPetugas" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID Aju</th>
                            <th>No Surat Pemohon</th>
                            <th>Tgl Surat</th>
                            <th>Nama Perusahaan</th>
                            <th>Diajukan Oleh</th>
                            <th>Waktu Submit</th>
                            <th>Petugas Ditugaskan</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($permohonan_list) && is_array($permohonan_list)) : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($permohonan_list as $p) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($p['id']); ?></td>
                                    <td><?= htmlspecialchars($p['nomorSurat'] ?? '-'); ?></td>
                                    <td><?= isset($p['TglSurat']) && $p['TglSurat'] != '0000-00-00' ? date('d/m/Y', strtotime($p['TglSurat'])) : '-'; ?></td>
                                    <td><?= htmlspecialchars($p['NamaPers'] ?? 'N/A'); ?></td>
                                    <td><?= htmlspecialchars($p['nama_pengaju_permohonan'] ?? 'N/A'); // Menggunakan alias dari query ?></td>
                                    <td><?= isset($p['time_stamp']) && $p['time_stamp'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($p['time_stamp'])) : '-'; ?></td>
                                    <td><?= !empty($p['nama_petugas_pemeriksa']) ? htmlspecialchars($p['nama_petugas_pemeriksa']) : '<span class="text-muted font-italic">Belum Ditunjuk</span>'; ?></td>
                                    <td>
                                        <?php
                                        $status_text = '-'; $status_badge = 'secondary';
                                        if (isset($p['status'])) {
                                            // Gunakan helper atau switch case seperti di view admin/user
                                            // Contoh sederhana:
                                            if (function_exists('status_permohonan_text_badge')) {
                                                $status_info = status_permohonan_text_badge($p['status']);
                                                $status_text = $status_info['text'];
                                                $status_badge = $status_info['badge'];
                                            } else { // Fallback manual
                                                 switch ($p['status']) {
                                                     case '0': $status_text = 'Baru Masuk'; $status_badge = 'dark'; break;
                                                     case '5': $status_text = 'Diproses Admin'; $status_badge = 'info'; break;
                                                     case '1': $status_text = 'Penunjukan Pemeriksa'; $status_badge = 'primary'; break;
                                                     case '2': $status_text = 'LHP Direkam'; $status_badge = 'warning'; break;
                                                     case '3': $status_text = 'Selesai (Disetujui)'; $status_badge = 'success'; break;
                                                     case '4': $status_text = 'Selesai (Ditolak)'; $status_badge = 'danger'; break;
                                                     default: $status_text = 'Status Tidak Dikenal (' . htmlspecialchars($p['status']) . ')';
                                                 }
                                            }
                                        }
                                        echo '<span class="badge badge-pill badge-' . $status_badge . ' p-2">' . htmlspecialchars($status_text) . '</span>';
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if(isset($p['id'])): ?>
                                            <a href="<?= site_url('petugas/detail_monitoring_permohonan/' . $p['id']); ?>" class="btn btn-info btn-circle btn-sm my-1" title="Lihat Detail Permohonan">
                                                <i class="fas fa-eye"></i>
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
        $('#dataTableMonitoringPetugas').DataTable({
            "order": [], // Biarkan urutan dari server
            "language": { /* ... bahasa DataTables Anda ... */ },
             "columnDefs": [
                 { "orderable": false, "searchable": false, "targets": [0, 9] }
             ]
        });
    }
});
</script>