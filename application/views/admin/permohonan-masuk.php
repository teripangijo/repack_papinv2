<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Daftar Permohonan Impor'; ?></h1>
    </div>

    <?php
    // Flashdata akan ditampilkan oleh templates/topbar.php atau di tempat lain secara global.
    // Jika Anda ingin menampilkannya khusus di sini, Anda bisa uncomment baris di bawah.
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
                                    <td><?= !empty($p['nama_petugas_assigned']) ? htmlspecialchars($p['nama_petugas_assigned']) : '<span class="text-muted font-italic">Belum Ditunjuk</span>'; ?></td>
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
                                    <td class="text-center">
                                        <?php
                                        // Tombol Detail selalu tersedia (jika ada methodnya)
                                        // Anda mungkin perlu membuat method 'detail_permohonan_admin' atau sejenisnya
                                        // yang bisa menampilkan detail LHP, surat tugas, surat keputusan, dll.
                                        if(isset($p['id'])){ // Pastikan ID ada
                                            echo '<a href="' . site_url('admin/detail_permohonan_admin/' . $p['id']) . '" class="btn btn-info btn-circle btn-sm my-1" title="Lihat Detail Permohonan Lengkap">
                                                    <i class="fas fa-eye"></i>
                                                  </a>';
                                        }

                                        // Tombol Aksi berdasarkan Status
                                        if (isset($p['status'])) {
                                            switch ($p['status']) {
                                                case '0': // Baru Masuk
                                                case '5': // Diproses Admin (belum ada penunjukan petugas / admin masih bisa mengubah sebelum menunjuk)
                                                    echo '<a href="' . site_url('admin/penunjukanPetugas/' . $p['id']) . '" class="btn btn-success btn-circle btn-sm my-1" title="Proses & Tunjuk Petugas Pemeriksa">
                                                            <i class="fas fa-user-plus"></i>
                                                          </a>';
                                                    break;
                                                case '1': // Penunjukan Pemeriksa (petugas sudah ditunjuk, LHP belum direkam)
                                                    // Admin mungkin ingin melihat/mengedit penunjukan atau menunggu LHP
                                                    echo '<a href="' . site_url('admin/penunjukanPetugas/' . $p['id']) . '" class="btn btn-warning btn-circle btn-sm my-1" title="Lihat/Edit Penunjukan Petugas">
                                                            <i class="fas fa-edit"></i>
                                                          </a>';
                                                    // Di sini LHP belum ada, jadi tidak ada tombol selesaikan
                                                    break;
                                                case '2': // LHP Direkam, siap diselesaikan oleh Admin
                                                    echo '<a href="' . site_url('admin/prosesSurat/' . $p['id']) . '" class="btn btn-primary btn-circle btn-sm my-1" title="Proses Penyelesaian Akhir Permohonan (Setujui/Tolak)">
                                                            <i class="fas fa-flag-checkered"></i>
                                                          </a>';
                                                    break;
                                                case '3': // Selesai (Disetujui)
                                                case '4': // Selesai (Ditolak)
                                                    // Tidak ada tombol aksi lagi untuk proses utama.
                                                    // Tombol detail sudah ditampilkan di atas.
                                                    // Mungkin tombol untuk "Cetak SK" atau sejenisnya jika ada.
                                                    break;
                                                default:
                                                    echo '<span class="text-muted">-</span>';
                                            }
                                        } else {
                                            echo '<span class="text-muted">Status Error</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php // Bagian 'else' untuk data kosong akan ditangani oleh DataTables "emptyTable" ?>
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
        $('#dataTablePermohonanAdmin').DataTable({
            // Menggunakan urutan dari server (PHP) karena sudah diatur dengan CASE status di controller
            // Jika tidak ada urutan dari server, Anda bisa set default di sini, contoh: [[ 6, "desc" ]] untuk Waktu Submit terbaru
            "order": [],
            "language": {
                "emptyTable": "Belum ada data permohonan masuk.",
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
                // Kolom '#' (indeks 0) dan Action (indeks 9) tidak bisa di-sort dan tidak bisa dicari (searchable: false)
                { "orderable": false, "searchable": false, "targets": [0, 9] }
            ],
            // Responsive bisa diaktifkan jika tabel Anda lebar
            // "responsive": true
        });
    } else {
        console.error("jQuery atau DataTables plugin tidak termuat dengan benar untuk 'dataTablePermohonanAdmin'.");
    }
});
</script>