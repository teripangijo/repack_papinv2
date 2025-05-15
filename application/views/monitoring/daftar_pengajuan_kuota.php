<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Pantauan Data Pengajuan Kuota'); ?></h1>
    <?php if ($this->session->flashdata('message')) { echo $this->session->flashdata('message'); } ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Seluruh Data Pengajuan Kuota</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableMonitorPengajuanKuota" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Perusahaan</th>
                            <th>Tgl. Pengajuan</th>
                            <th>Jml Diajukan</th>
                            <th>Status</th>
                            <th>Jml Disetujui</th>
                            <th>Catatan Admin</th>
                            <th>Tgl. Proses Admin</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($daftar_pengajuan_kuota)): foreach ($daftar_pengajuan_kuota as $pk): ?>
                        <tr>
                            <td><?= htmlspecialchars($pk['id']); ?></td>
                            <td><?= htmlspecialchars($pk['NamaPers'] ?? $pk['id_pers']); ?></td>
                            <td><?= isset($pk['submission_date']) ? date('d/m/Y H:i', strtotime($pk['submission_date'])) : '-'; ?></td>
                            <td class="text-right"><?= number_format($pk['requested_quota'] ?? 0, 0, ',', '.'); ?></td>
                            <td>
                                <?php
                                $status_text = ucfirst($pk['status'] ?? 'N/A');
                                $status_badge = 'secondary';
                                switch (strtolower($pk['status'] ?? '')) {
                                    case 'pending': $status_badge = 'warning'; $status_text = 'Pending'; break;
                                    case 'approved': $status_badge = 'success'; $status_text = 'Disetujui'; break;
                                    case 'rejected': $status_badge = 'danger'; $status_text = 'Ditolak'; break;
                                    case 'diproses': $status_badge = 'info'; $status_text = 'Diproses'; break;
                                }
                                echo '<span class="badge badge-'.$status_badge.'">'.htmlspecialchars($status_text).'</span>';
                                ?>
                            </td>
                            <td class="text-right"><?= ($pk['status'] == 'approved' && isset($pk['approved_quota'])) ? number_format($pk['approved_quota'],0,',','.') : '-'; ?></td>
                            <td><?= nl2br(htmlspecialchars($pk['admin_notes'] ?? '-')); ?></td>
                            <td><?= isset($pk['processed_date']) ? date('d/m/Y H:i', strtotime($pk['processed_date'])) : '-'; ?></td>
                            <td>
                                <a href="<?= site_url('monitoring/detail_pengajuan_kuota/' . $pk['id']); ?>" class="btn btn-sm btn-info" title="Lihat Detail">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                <?php if (!empty($pk['file_sk_petugas'])): ?>
                                    <a href="<?= site_url('admin/download_sk_kuota_admin/' . $pk['id']); // Ganti ke path download yg sesuai jika monitoring punya akses beda ?>" class="btn btn-sm btn-success" title="Unduh SK">
                                        <i class="fas fa-download"></i> SK
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="9" class="text-center">Tidak ada data pengajuan kuota.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
 $(document).ready(function() { $('#dataTableMonitorPengajuanKuota').DataTable({"order": [[2, "desc"]]}); });
</script>