<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Daftar Permohonan Impor Kembali'; // Judul disesuaikan dengan screenshot Anda ?></h1>
        <a href="<?= site_url('user/permohonan_impor_kembali'); // Pastikan ini link ke method yang benar untuk form pengajuan baru ?>" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Ajukan Permohonan Baru
        </a>
    </div>

    <?php
    // Flashdata seharusnya sudah ditampilkan secara global oleh templates/topbar.php
    // Jika belum, dan Anda ingin pesan spesifik untuk halaman ini, Anda bisa menambahkannya di sini.
    // if ($this->session->flashdata('message_user_permohonan')) {
    //     echo $this->session->flashdata('message_user_permohonan');
    // }
    ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Permohonan Anda</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTablePermohonanUser" width="100%" cellspacing="0"> <?php // Ganti ID tabel agar unik jika diperlukan ?>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Id Aju</th>
                            <th>No Surat Anda</th>
                            <th>Tanggal Surat Anda</th>
                            <th>Nama Perusahaan</th> <?php // Jika pengguna jasa hanya satu perusahaan, ini mungkin tidak perlu atau bisa diisi otomatis ?>
                            <th>Waktu Submit</th>
                            <th>Petugas Pemeriksa</th>
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
                                    <td><?= isset($p['id']) ? htmlspecialchars($p['id']) : '-'; ?></td>
                                    <td><?= isset($p['nomorSurat']) ? htmlspecialchars($p['nomorSurat']) : '-'; // Nomor surat dari pengguna ?></td>
                                    <td><?= isset($p['TglSurat']) && $p['TglSurat'] != '0000-00-00' ? date('d/m/Y', strtotime($p['TglSurat'])) : '-'; // Tanggal surat dari pengguna ?></td>
                                    <td><?= isset($p['NamaPers']) ? htmlspecialchars($p['NamaPers']) : '-'; // Ini dari join, jika user terkait satu perusahaan, bisa diambil dari data user/session ?></td>
                                    <td><?= isset($p['time_stamp']) && $p['time_stamp'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i:s', strtotime($p['time_stamp'])) : '-'; ?></td>
                                    <td>
                                        <?php // Menampilkan nama petugas jika sudah ditunjuk (status 1 atau lebih tinggi yang relevan)
                                        if (isset($p['status']) && in_array($p['status'], ['1', '2']) && isset($p['nama_petugas_pemeriksa']) && !empty($p['nama_petugas_pemeriksa'])) {
                                            echo htmlspecialchars($p['nama_petugas_pemeriksa']);
                                        } elseif (isset($p['status']) && in_array($p['status'], ['1', '2']) && empty($p['nama_petugas_pemeriksa']) && !empty($p['petugas'])) {
                                            echo 'Petugas Ditunjuk (ID: '.htmlspecialchars($p['petugas']).')'; // Fallback jika nama tidak ada tapi ID ada
                                        }
                                        else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status_text_user = '-';
                                        $status_badge_user = 'secondary';
                                        if (isset($p['status'])) {
                                            switch ($p['status']) {
                                                case '0': // Baru diajukan oleh user, menunggu diproses admin
                                                    $status_text_user = 'Diajukan (Menunggu Verifikasi)';
                                                    $status_badge_user = 'info'; // Atau 'dark'
                                                    break;
                                                case '5': // Admin sedang memproses (misalnya, tahap sebelum menunjuk petugas)
                                                    $status_text_user = 'Sedang Diproses Kantor';
                                                    $status_badge_user = 'warning';
                                                    break;
                                                case '1': // Admin sudah menunjuk petugas/pemeriksa
                                                    $status_text_user = 'Dalam Pemeriksaan Petugas';
                                                    $status_badge_user = 'primary';
                                                    break;
                                                case '2': // LHP sudah direkam oleh petugas/pemeriksa
                                                    $status_text_user = 'Menunggu Keputusan Akhir';
                                                    $status_badge_user = 'warning';
                                                    break;
                                                case '3': // Selesai dan disetujui
                                                    $status_text_user = 'Disetujui';
                                                    $status_badge_user = 'success';
                                                    break;
                                                case '4': // Selesai dan ditolak
                                                    $status_text_user = 'Ditolak';
                                                    $status_badge_user = 'danger';
                                                    break;
                                                default:
                                                    $status_text_user = 'Status Proses Tidak Dikenal (' . htmlspecialchars($p['status']) . ')'; // Lebih informatif untuk default
                                                    $status_badge_user = 'dark';
                                            }
                                        }
                                        echo '<span class="badge badge-pill badge-' . $status_badge_user . '">' . htmlspecialchars($status_text_user) . '</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('user/printPdf/' . (isset($p['id']) ? $p['id'] : '#')); // Pastikan method printPdf ada dan aman ?>" class="btn btn-info btn-sm mb-1" title="Lihat/Cetak Detail Permohonan Anda" target="_blank">
                                            <i class="fas fa-print"></i> Cetak
                                        </a>
                                        <?php if (isset($p['status']) && ($p['status'] == '0' || $p['status'] == '5')) : // Hanya bisa edit jika status 'Diajukan' atau 'Diproses Admin' (belum ada tindakan petugas) ?>
                                            <a href="<?= site_url('user/editpermohonan/' . (isset($p['id']) ? $p['id'] : '#')); // Pastikan method editpermohonan ada ?>" class="btn btn-warning btn-sm mb-1" title="Edit Permohonan">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        <?php endif; ?>
                                        <?php // Tombol untuk melihat detail hasil atau LHP bisa ditambahkan di sini jika relevan untuk user ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="9" class="text-center">Anda belum memiliki data permohonan.</td> <?php // Sesuaikan colspan ?>
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
    if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
        // console.log('Daftar Permohonan User: jQuery and DataTables are loaded.'); // Hapus console.log yang tidak perlu
        try {
            $('#dataTablePermohonanUser').DataTable({ // Sesuaikan ID tabel
                "order": [[ 5, "desc" ]], // Urutkan berdasarkan Waktu Submit terbaru (kolom ke-6, indeks 5)
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
                    { "orderable": false, "targets": [8] } // Kolom "Action" (indeks ke-8 dari 0) tidak bisa diurutkan
                ]
                // Opsi DataTables lain bisa ditambahkan di sini
            });
            // console.log('Daftar Permohonan User: DataTable initialized.'); // Hapus console.log yang tidak perlu
        } catch (e) {
            console.error('Daftar Permohonan User: Error initializing DataTable: ', e);
        }
    } else {
        // Hapus console.error yang terlalu detail jika tidak dalam mode debugging aktif
        // if (typeof $ === 'undefined') {
        //     console.error("Daftar Permohonan User: jQuery is not loaded.");
        // }
        // if (typeof $.fn.DataTable === 'undefined' && typeof $ !== 'undefined') {
        //     console.error("Daftar Permohonan User: DataTables plugin ($.fn.DataTable) is not loaded.");
        // }
        console.error("Daftar Permohonan User: jQuery atau DataTables tidak termuat.");
    }
});
</script>