<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Daftar Permohonan Impor'; ?></h1>
    </div>

    <?php
    // Flashdata seharusnya sudah ditampilkan secara global oleh templates/topbar.php
    // if ($this->session->flashdata('message')) {
    //     echo $this->session->flashdata('message');
    // }
    ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Permohonan Masuk</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTablePermohonanAdmin" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID Aju</th>
                            <th>No Surat Pemohon</th>
                            <th>Tgl Surat Pemohon</th>
                            <th>Nama Perusahaan</th>
                            <th>Diajukan Oleh</th>
                            <th>Waktu Submit</th>
                            <th>Petugas Ditugaskan</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($permohonan) && is_array($permohonan)) : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($permohonan as $p) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($p['id']); ?></td>
                                    <td><?= htmlspecialchars($p['nomorSurat'] ?? '-'); ?></td>
                                    <td><?= isset($p['TglSurat']) && $p['TglSurat'] != '0000-00-00' ? date('d/m/Y', strtotime($p['TglSurat'])) : '-'; ?></td>
                                    <td><?= htmlspecialchars($p['NamaPers'] ?? 'N/A'); ?></td>
                                    <td><?= htmlspecialchars($p['nama_pengaju'] ?? 'N/A'); ?></td>
                                    <td><?= isset($p['time_stamp']) && $p['time_stamp'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($p['time_stamp'])) : '-'; ?></td>
                                    <td><?= !empty($p['nama_petugas_assigned']) ? htmlspecialchars($p['nama_petugas_assigned']) : 'Belum Ditunjuk'; ?></td>
                                    <td>
                                        <?php
                                        $status_text = '-'; $status_badge = 'secondary';
                                        if (isset($p['status'])) {
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
                                        echo '<span class="badge badge-pill badge-' . $status_badge . '">' . htmlspecialchars($status_text) . '</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($p['status'] == '0' || $p['status'] == '5'): // Baru Masuk atau sedang Diproses Admin (belum submit penunjukan) ?>
                                            <a href="<?= site_url('admin/penunjukanPetugas/' . $p['id']); ?>" class="btn btn-success btn-circle btn-sm my-1" title="Tunjuk Petugas Pemeriksa">
                                                <i class="fas fa-user-plus"></i>
                                            </a>
                                        <?php elseif ($p['status'] == '1'): // Sudah ada Penunjukan Pemeriksa, bisa dilihat/diedit atau dilanjutkan ?>
                                            <a href="<?= site_url('admin/penunjukanPetugas/' . $p['id']); ?>" class="btn btn-warning btn-circle btn-sm my-1" title="Lihat/Edit Penunjukan Petugas">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= site_url('admin/detail_permohonan/' . $p['id']); // Link ke detail permohonan (buat methodnya jika belum ada) ?>" class="btn btn-info btn-circle btn-sm my-1" title="Lihat Detail Permohonan">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php elseif ($p['status'] == '2'): // LHP Direkam, siap diselesaikan oleh Admin ?>
                                            <a href="<?= site_url('admin/prosesSurat/' . $p['id']); ?>" class="btn btn-primary btn-circle btn-sm my-1" title="Proses Penyelesaian Akhir Permohonan">
                                                <i class="fas fa-flag-checkered"></i>
                                            </a>
                                            <a href="<?= site_url('admin/detail_permohonan/' . $p['id']); ?>" class="btn btn-info btn-circle btn-sm my-1" title="Lihat Detail LHP & Permohonan">
                                                <i class="fas fa-search-plus"></i>
                                            </a>
                                        <?php elseif ($p['status'] == '3' || $p['status'] == '4'): // Selesai (Disetujui atau Ditolak) ?>
                                            <a href="<?= site_url('admin/detail_permohonan/' . $p['id']); ?>" class="btn btn-secondary btn-circle btn-sm my-1" title="Lihat Hasil Akhir Permohonan">
                                                <i class="fas fa-info-circle"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php // Baris "Belum ada data permohonan." yang menggunakan colspan DIHAPUS dari sini ?>
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
        $('#dataTablePermohonanAdmin').DataTable({
            // Menggunakan urutan dari server (PHP) karena sudah diatur dengan CASE status
            "order": [], 
            "language": {
                "emptyTable": "Belum ada data permohonan masuk.", // Pesan kustom jika tabel kosong
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
        console.error("DataTables plugin is not loaded for 'dataTablePermohonanAdmin'.");
    }
});
</script>
