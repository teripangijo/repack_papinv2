<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Daftar Permohonan Impor'; ?></h1>
    </div>

    <?php
    // Flashdata seharusnya sudah ditampilkan secara global oleh templates/topbar.php
    // Jika belum, dan Anda ingin pesan spesifik untuk halaman ini, Anda bisa menambahkannya di sini.
    // Namun, disarankan untuk konsisten dengan tampilan global.
    // if ($this->session->flashdata('message_permohonan_masuk')) { // Contoh jika key flashdata berbeda
    //     echo $this->session->flashdata('message_permohonan_masuk');
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
                            <th>Waktu Submit Sistem</th>
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
                                    <td><?= htmlspecialchars($p['nomorSurat'] ?? '-'); // nomorSurat dari user_permohonan ?></td>
                                    <td><?= isset($p['TglSurat']) && $p['TglSurat'] != '0000-00-00' ? date('d/m/Y', strtotime($p['TglSurat'])) : '-'; // TglSurat dari user_permohonan ?></td>
                                    <td><?= htmlspecialchars($p['NamaPers'] ?? 'N/A'); ?></td>
                                    <td><?= htmlspecialchars($p['nama_pengaju'] ?? 'N/A'); // nama_pengaju dari join dengan tabel user ?></td>
                                    <td><?= isset($p['time_stamp']) && $p['time_stamp'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($p['time_stamp'])) : '-'; ?></td>
                                    <td><?= !empty($p['nama_petugas_assigned']) ? htmlspecialchars($p['nama_petugas_assigned']) : 'Belum Ditunjuk'; ?></td>
                                    <td>
                                        <?php
                                        $status_text = '-'; $status_badge = 'secondary';
                                        if (isset($p['status'])) {
                                            switch ($p['status']) { // Status dari tabel user_permohonan
                                                case '0': $status_text = 'Baru Masuk'; $status_badge = 'dark'; break;
                                                case '5': $status_text = 'Diproses Admin'; $status_badge = 'info'; break; // Status baru saat form penunjukan dibuka
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
                                            <a href="<?= site_url('admin/penunjukanPetugas/' . $p['id']); ?>" class="btn btn-success btn-sm my-1" title="Tunjuk Petugas Pemeriksa">
                                                <i class="fas fa-user-plus"></i> Tunjuk Petugas
                                            </a>
                                        <?php elseif ($p['status'] == '1'): // Sudah ada Penunjukan Pemeriksa, bisa dilihat/diedit atau dilanjutkan ?>
                                            <a href="<?= site_url('admin/penunjukanPetugas/' . $p['id']); ?>" class="btn btn-warning btn-sm my-1" title="Lihat/Edit Penunjukan Petugas">
                                                <i class="fas fa-edit"></i> Edit Penunjukan
                                            </a>
                                            <?php elseif ($p['status'] == '2'): // LHP Direkam, siap diselesaikan oleh Admin ?>
                                            <a href="<?= site_url('admin/prosesSurat/' . $p['id']); ?>" class="btn btn-primary btn-sm my-1" title="Proses Penyelesaian Akhir Permohonan">
                                                <i class="fas fa-flag-checkered"></i> Selesaikan
                                            </a>
                                            <a href="<?= site_url('admin/detail_permohonan/' . $p['id']); // Asumsi ini menampilkan detail LHP juga ?>" class="btn btn-info btn-sm my-1" title="Lihat Detail LHP & Permohonan">
                                                <i class="fas fa-search-plus"></i> Detail
                                            </a>
                                        <?php elseif ($p['status'] == '3' || $p['status'] == '4'): // Selesai (Disetujui atau Ditolak) ?>
                                            <a href="<?= site_url('admin/detail_permohonan/' . $p['id']); // Ini akan menampilkan detail hasil akhir ?>" class="btn btn-secondary btn-sm my-1" title="Lihat Hasil Akhir Permohonan">
                                                <i class="fas fa-info-circle"></i> Lihat Hasil
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="10" class="text-center">Belum ada data permohonan masuk.</td>
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
    // Pastikan jQuery dan DataTables sudah dimuat di template footer Anda
    if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
        $('#dataTablePermohonanAdmin').DataTable({
            "order": [], // MENONAKTIFKAN PENGURUTAN AWAL DARI DATATABLES, MENGGUNAKAN URUTAN DARI SERVER
            "language": { // Opsional: untuk melokalisasi DataTables
                "sEmptyTable":   "Tidak ada data yang tersedia pada tabel ini",
                "sProcessing":   "Sedang memproses...",
                "sLengthMenu":   "Tampilkan _MENU_ entri",
                "sZeroRecords":  "Tidak ditemukan data yang sesuai",
                "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 entri",
                "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                "sInfoPostFix":  "",
                "sSearch":       "Cari:",
                "sUrl":          "",
                "oPaginate": {
                    "sFirst":    "Pertama",
                    "sPrevious": "Sebelumnya",
                    "sNext":     "Selanjutnya",
                    "sLast":     "Terakhir"
                }
            },
            "columnDefs": [
                { "orderable": false, "targets": 9 } // Kolom "Action" (indeks ke-9 dari 0) tidak bisa diurutkan
            ]
            // Anda bisa menambahkan opsi lain seperti pageLength, dll.
            // "pageLength": 25,
        });
    } else {
        console.error("DataTables plugin is not loaded for 'dataTablePermohonanAdmin'.");
    }
});
</script>