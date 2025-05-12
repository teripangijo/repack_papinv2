<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Daftar Permohonan'; ?></h1>
        <a href="<?= site_url('user/permohonan'); ?>" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Ajukan Permohonan Baru
        </a>
    </div>

    <?php
    // Menampilkan flashdata message jika ada
    if ($this->session->flashdata('message')) {
        echo $this->session->flashdata('message');
    }
    ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Permohonan Anda</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTablePermohonan" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Id Aju</th>
                            <th>No Surat</th>
                            <th>Tanggal Surat</th>
                            <th>Nama Perusahaan</th> <th>Waktu Submit</th>
                            <th>Petugas</th>
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
                                    <td><?= isset($p['nomorSurat']) ? htmlspecialchars($p['nomorSurat']) : '-'; ?></td>
                                    <td><?= isset($p['TglSurat']) ? date('d/m/Y', strtotime($p['TglSurat'])) : '-'; ?></td>
                                    <td><?= isset($p['NamaPers']) ? htmlspecialchars($p['NamaPers']) : '-'; ?></td>
                                    <td><?= isset($p['time_stamp']) ? date('d/m/Y H:i:s', strtotime($p['time_stamp'])) : '-'; ?></td>
                                    <td>
                                        <?php
                                        // Logika untuk menampilkan nama petugas jika ada
                                        // Anda mungkin perlu query tambahan di controller untuk mendapatkan nama petugas berdasarkan $p['petugas'] (ID petugas)
                                        echo isset($p['nama_petugas']) ? htmlspecialchars($p['nama_petugas']) : (isset($p['petugas']) && !empty($p['petugas']) ? 'ID: '.htmlspecialchars($p['petugas']) : '-');
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status_text = '-';
                                        $status_badge = 'secondary';
                                        if (isset($p['status'])) {
                                            switch ($p['status']) {
                                                case '0':
                                                    $status_text = 'Permohonan Masuk';
                                                    $status_badge = 'info';
                                                    break;
                                                case '1':
                                                    $status_text = 'Diproses Petugas';
                                                    $status_badge = 'primary';
                                                    break;
                                                case '2':
                                                    $status_text = 'LHP Direkam';
                                                    $status_badge = 'warning';
                                                    break;
                                                case '3':
                                                    $status_text = 'Selesai (Disetujui/Ditolak)';
                                                    $status_badge = 'success'; // Atau 'danger' jika ditolak
                                                    break;
                                                default:
                                                    $status_text = 'Status Tidak Dikenal';
                                            }
                                        }
                                        echo '<span class="badge badge-' . $status_badge . '">' . htmlspecialchars($status_text) . '</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('user/printPdf/' . (isset($p['id']) ? $p['id'] : '')); ?>" class="btn btn-info btn-sm" title="Preview/Print">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (isset($p['status']) && $p['status'] == '0') : // Hanya bisa edit jika status 'Permohonan Masuk' ?>
                                            <a href="<?= site_url('user/editpermohonan/' . (isset($p['id']) ? $p['id'] : '')); ?>" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php // Tambahkan tombol delete jika perlu, dengan konfirmasi ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="9" class="text-center">Belum ada data permohonan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<?php // Tempatkan script inisialisasi DataTables di sini atau di footer template ?>
<script>
$(document).ready(function() {
    $('#dataTablePermohonan').DataTable({
        // Opsi DataTables bisa ditambahkan di sini
        // Misalnya, untuk mengubah bahasa:
        // "language": {
        //     "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" // Untuk Bahasa Indonesia
        // }
        // Atau untuk mengatur kolom mana yang bisa di-search atau di-order
        // "columnDefs": [
        //     { "searchable": false, "targets": [0, 8] }, // Kolom # dan Action tidak bisa dicari
        //     { "orderable": false, "targets": [8] }     // Kolom Action tidak bisa di-sort
        // ]
    });
});
</script>
