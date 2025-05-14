<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Daftar Permohonan Impor Kembali'; ?></h1>
    </div>


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
                            <th>No Surat</th>
                            <th>Tgl Surat</th>
                            <th>Nama Perusahaan</th>
                            <th>Pengaju</th>
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
                                    <td><?= htmlspecialchars($p['nomorSurat']); ?></td>
                                    <td><?= date('d/m/Y', strtotime($p['TglSurat'])); ?></td>
                                    <td><?= htmlspecialchars($p['NamaPers']); ?></td>
                                    <td><?= htmlspecialchars($p['nama_pengaju']); ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($p['time_stamp'])); ?></td>
                                    <td><?= !empty($p['nama_petugas_assigned']) ? htmlspecialchars($p['nama_petugas_assigned']) : '-'; ?></td>
                                    <td>
                                        <?php
                                        $status_text = '-'; $status_badge = 'secondary';
                                        if (isset($p['status'])) {
                                            switch ($p['status']) {
                                                case '0': $status_text = 'Baru Masuk'; $status_badge = 'info'; break;
                                                case '1': $status_text = 'Penugasan Petugas'; $status_badge = 'primary'; break;
                                                case '2': $status_text = 'LHP Direkam'; $status_badge = 'warning'; break;
                                                case '3': $status_text = 'Selesai (Disetujui)'; $status_badge = 'success'; break;
                                                case '4': $status_text = 'Selesai (Ditolak)'; $status_badge = 'danger'; break; // Contoh status ditolak
                                                // Tambahkan case lain jika ada
                                                default: $status_text = 'Status Tidak Dikenal';
                                            }
                                        }
                                        echo '<span class="badge badge-' . $status_badge . '">' . htmlspecialchars($status_text) . '</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('admin/detail_permohonan/' . $p['id']); ?>" class="btn btn-info btn-sm" title="Lihat Detail & Proses">
                                            <i class="fas fa-search-plus"></i> Detail
                                        </a>
                                        <?php // Tombol untuk assign petugas, rekam LHP, proses surat akan ada di halaman detail ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="10" class="text-center">Belum ada data permohonan.</td>
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
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#dataTablePermohonanAdmin').DataTable({
            "order": [[ 6, "desc" ]] // Urutkan berdasarkan Waktu Submit terbaru
        });
    } else {
        console.error("DataTables plugin is not loaded.");
    }
});
</script>
