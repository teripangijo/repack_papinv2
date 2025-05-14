<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Daftar Pemeriksaan Ditugaskan'); ?></h1>
    <!-- <?php if ($this->session->flashdata('message')) { echo $this->session->flashdata('message'); } ?> -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Permohonan yang Perlu Direkam LHP</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTableTugasPetugas" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID Aju</th>
                            <th>No Surat Pemohon</th>
                            <th>Tgl Surat Tugas</th>
                            <th>Perusahaan</th>
                            <th>Pemohon</th>
                            <th>Waktu Penunjukan</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($daftar_tugas)): $no = 1; foreach ($daftar_tugas as $tugas): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($tugas['id']); ?></td>
                            <td><?= htmlspecialchars($tugas['nomorSurat']); ?></td>
                            <td><?= isset($tugas['TglSuratTugas']) ? date('d/m/Y', strtotime($tugas['TglSuratTugas'])) : '-'; ?></td>
                            <td><?= htmlspecialchars($tugas['NamaPers']); ?></td>
                            <td><?= htmlspecialchars($tugas['nama_pemohon']); ?></td>
                            <td><?= isset($tugas['WaktuPenunjukanPetugas']) ? date('d/m/Y H:i', strtotime($tugas['WaktuPenunjukanPetugas'])) : '-'; ?></td>
                            <td>
                                <a href="<?= site_url('petugas/rekam_lhp/' . $tugas['id']); ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-file-alt"></i> Rekam LHP
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="8" class="text-center">Tidak ada tugas pemeriksaan saat ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
// $(document).ready(function() { $('#dataTableTugasPetugas').DataTable({"order": []}); }); // Aktifkan jika perlu DataTables
</script>