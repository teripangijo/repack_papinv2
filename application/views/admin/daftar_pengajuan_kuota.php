<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Daftar Pengajuan Kuota'; ?></h1>
    </div>

    <?php
    if ($this->session->flashdata('message')) {
        echo $this->session->flashdata('message');
    }
    ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Pengajuan Kuota dari Perusahaan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTablePengajuanKuota" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID Pengajuan</th>
                            <th>Nama Perusahaan</th>
                            <th>Email User</th>
                            <th>Kuota Diajukan</th>
                            <th>Alasan</th>
                            <th>Tgl Pengajuan</th>
                            <th>Status</th>
                            <th>Kuota Disetujui</th>
                            <th>Catatan Admin</th>
                            <th>Tgl Proses</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pengajuan_kuota) && is_array($pengajuan_kuota)) : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($pengajuan_kuota as $pk) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= isset($pk['id']) ? htmlspecialchars($pk['id']) : '-'; ?></td>
                                    <td><?= isset($pk['NamaPers']) ? htmlspecialchars($pk['NamaPers']) : 'N/A'; ?></td>
                                    <td><?= isset($pk['user_email']) ? htmlspecialchars($pk['user_email']) : 'N/A'; ?></td>
                                    <td><?= isset($pk['requested_quota']) ? number_format($pk['requested_quota'],0,',','.') : '-'; ?> Unit</td>
                                    <td><?= isset($pk['reason']) ? nl2br(htmlspecialchars($pk['reason'])) : '-'; ?></td>
                                    <td><?= isset($pk['submission_date']) ? date('d/m/Y H:i', strtotime($pk['submission_date'])) : '-'; ?></td>
                                    <td>
                                        <?php
                                        $status_text = ucfirst(isset($pk['status']) ? $pk['status'] : 'N/A');
                                        $status_badge = 'secondary';
                                        if (isset($pk['status'])) {
                                            switch ($pk['status']) {
                                                case 'pending': $status_badge = 'warning'; break;
                                                case 'approved': $status_badge = 'success'; break;
                                                case 'rejected': $status_badge = 'danger'; break;
                                            }
                                        }
                                        echo '<span class="badge badge-' . $status_badge . '">' . htmlspecialchars($status_text) . '</span>';
                                        ?>
                                    </td>
                                    <td><?= ($pk['status'] == 'approved' && isset($pk['approved_quota'])) ? number_format($pk['approved_quota'],0,',','.') . ' Unit' : '-'; ?></td>
                                    <td><?= isset($pk['admin_notes']) ? nl2br(htmlspecialchars($pk['admin_notes'])) : '-'; ?></td>
                                    <td><?= isset($pk['processed_date']) ? date('d/m/Y H:i', strtotime($pk['processed_date'])) : '-'; ?></td>
                                    <td>
                                        <?php if (isset($pk['status']) && $pk['status'] == 'pending') : ?>
                                            <a href="<?= site_url('admin/proses_pengajuan_kuota/' . $pk['id']); ?>" class="btn btn-sm btn-info" title="Proses Pengajuan">
                                                <i class="fas fa-cogs"></i> Proses
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled>Diproses</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="12" class="text-center">Belum ada data pengajuan kuota.</td>
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
        $('#dataTablePengajuanKuota').DataTable({
            "order": [[ 6, "desc" ]] // Urutkan berdasarkan Tgl Pengajuan terbaru
        });
    } else {
        console.error("DataTables plugin is not loaded.");
    }
});
</script>
