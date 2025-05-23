<?php // application/views/user/daftar-permohonan.php ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Daftar Permohonan Impor Kembali Saya'; ?></h1>
        <a href="<?= site_url('user/permohonan_impor_kembali'); ?>" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Buat Permohonan Baru
        </a>
    </div>

    <!-- <?php if ($this->session->flashdata('message')) { echo $this->session->flashdata('message'); } ?> -->

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Riwayat Permohonan Impor Kembali</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTablePermohonanUser" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID Aju</th>
                            <th>No. Surat</th>
                            <th>Tgl Surat</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Waktu Submit</th>
                            <th>Status</th>
                            <th style="min-width: 120px;">Action</th> <?php // Lebarkan sedikit untuk tombol ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($permohonan) && is_array($permohonan)): ?>
                            <?php $no = 1; foreach ($permohonan as $p): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($p['id']); ?></td>
                                    <td><?= htmlspecialchars($p['nomorSurat'] ?? '-'); ?></td>
                                    <td><?= isset($p['TglSurat']) && $p['TglSurat'] != '0000-00-00' ? date('d/m/Y', strtotime($p['TglSurat'])) : '-'; ?></td>
                                    <td><?= htmlspecialchars($p['NamaBarang'] ?? '-'); ?></td>
                                    <td><?= htmlspecialchars(number_format($p['JumlahBarang'] ?? 0)); ?></td>
                                    <td><?= isset($p['time_stamp']) && $p['time_stamp'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($p['time_stamp'])) : '-'; ?></td>
                                    <td>
                                        <?php
                                        $status_text = '-'; $status_badge = 'secondary';
                                        if (isset($p['status'])) {
                                            // Anda bisa membuat helper untuk ini
                                            switch ($p['status']) {
                                                case '0': $status_text = 'Baru Masuk'; $status_badge = 'dark'; break;
                                                case '5': $status_text = 'Diproses Admin'; $status_badge = 'info'; break;
                                                case '1': $status_text = 'Petugas Ditunjuk'; $status_badge = 'primary'; break;
                                                case '2': $status_text = 'LHP Direkam'; $status_badge = 'warning'; break;
                                                case '3': $status_text = 'Selesai (Disetujui)'; $status_badge = 'success'; break;
                                                case '4': $status_text = 'Selesai (Ditolak)'; $status_badge = 'danger'; break;
                                                default: $status_text = 'Status Tidak Dikenal (' . htmlspecialchars($p['status']) . ')';
                                            }
                                        }
                                        echo '<span class="badge badge-pill badge-' . $status_badge . '">' . htmlspecialchars($status_text) . '</span>';
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= site_url('user/detailPermohonan/' . $p['id']); ?>" class="btn btn-info btn-circle btn-sm my-1" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php
                                        // Tombol Edit hanya jika status '0' (Baru Masuk) atau '5' (Diproses Admin - asumsi belum dikunci)
                                        $deletable_import_statuses = ['0', '5'];
                                        if (isset($p['status']) && in_array($p['status'], $deletable_import_statuses)):
                                        ?>
                                            <a href="<?= site_url('user/editpermohonan/' . $p['id']); ?>" class="btn btn-warning btn-circle btn-sm my-1" title="Edit Permohonan">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            <a href="<?= site_url('user/hapus_permohonan_impor/' . $p['id']); ?>" class="btn btn-danger btn-circle btn-sm my-1" title="Hapus Permohonan" onclick="return confirm('Apakah Anda yakin ingin menghapus permohonan impor dengan ID Aju: <?= htmlspecialchars($p['id']); ?> ini?');">
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
        $('#dataTablePermohonanUser').DataTable({ // Sesuaikan ID tabel jika berbeda
            "order": [[ 6, "desc" ]], // Urutkan berdasarkan Waktu Submit terbaru
            "language": { /* ... bahasa ... */ },
            "columnDefs": [
                { "orderable": false, "searchable": false, "targets": [0, 8] } // Kolom # dan Action
            ]
        });
    }
});
</script>