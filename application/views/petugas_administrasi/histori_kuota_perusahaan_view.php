<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Variabel dari Controller Admin::histori_kuota_perusahaan():
// $user (array): Data admin yang login
// $perusahaan (array): Data perusahaan yang dilihat historinya
// $daftar_kuota_barang_perusahaan (array): List kuota per barang dari user_kuota_barang
// $histori_kuota_transaksi (array): Log transaksi dari log_kuota_perusahaan
// $title (string)
// $subtitle (string)
// $id_pers_untuk_histori (int)

// Hitung total agregat dari daftar kuota barang untuk ditampilkan di summary (jika perlu)
$total_initial_agregat = 0;
$total_remaining_agregat = 0;
if (isset($daftar_kuota_barang_perusahaan) && !empty($daftar_kuota_barang_perusahaan)) {
    foreach ($daftar_kuota_barang_perusahaan as $kuota_brg) {
        $total_initial_agregat += (float)($kuota_brg['initial_quota_barang'] ?? 0);
        $total_remaining_agregat += (float)($kuota_brg['remaining_quota_barang'] ?? 0);
    }
}
$total_terpakai_agregat = $total_initial_agregat - $total_remaining_agregat;

?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Histori & Detail Kuota Perusahaan'); ?></h1>
        <a href="<?= site_url('admin/monitoring_kuota'); ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Monitoring Kuota
        </a>
    </div>

    <?php if ($this->session->flashdata('message')) { echo $this->session->flashdata('message'); } ?>

    <?php if (isset($perusahaan) && !empty($perusahaan)): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Detail Kuota untuk: <?= htmlspecialchars($perusahaan['NamaPers']); ?>
                (NPWP: <?= htmlspecialchars($perusahaan['npwp'] ?? 'N/A'); ?>)
                <br><small>Kontak User: <?= htmlspecialchars($perusahaan['nama_kontak_user'] ?? ($perusahaan['email_kontak'] ?? 'N/A')); ?></small>
            </h6>
        </div>
        <div class="card-body">
            <h5 class="text-gray-800">Ringkasan Total Kuota (Agregat dari Semua Jenis Barang)</h5>
            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Total Kuota Awal Diberikan:</strong> <?= htmlspecialchars(number_format($total_initial_agregat, 0, ',', '.')); ?> Unit
                </div>
                <div class="col-md-4">
                    <strong>Total Sisa Kuota Saat Ini:</strong>
                    <span class="font-weight-bold <?= ($total_remaining_agregat <=0 && $total_initial_agregat > 0) ? 'text-danger' : 'text-success'; ?>">
                        <?= htmlspecialchars(number_format($total_remaining_agregat, 0, ',', '.')); ?> Unit
                    </span>
                </div>
                <div class="col-md-4">
                    <strong>Total Kuota Terpakai:</strong> <?= htmlspecialchars(number_format($total_terpakai_agregat, 0, ',', '.')); ?> Unit
                </div>
            </div>
            <hr>

            <h5 class="text-gray-800 mt-4">Rincian Kuota per Jenis Barang</h5>
            <?php if (isset($daftar_kuota_barang_perusahaan) && !empty($daftar_kuota_barang_perusahaan)): ?>
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-bordered table-hover" id="dataTableRincianKuotaBarang" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Barang</th>
                                <th class="text-right">Kuota Awal Barang</th>
                                <th class="text-right">Sisa Kuota Barang</th>
                                <th>No. SKEP Asal</th>
                                <th>Tgl. SKEP Asal</th>
                                <th>Status Kuota Barang</th>
                                <th>Waktu Pencatatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no_rincian = 1; foreach ($daftar_kuota_barang_perusahaan as $kuota_brg): ?>
                            <tr>
                                <td><?= $no_rincian++; ?></td>
                                <td><?= htmlspecialchars($kuota_brg['nama_barang']); ?></td>
                                <td class="text-right"><?= htmlspecialchars(number_format($kuota_brg['initial_quota_barang'] ?? 0, 0, ',', '.')); ?></td>
                                <td class="text-right font-weight-bold <?= (($kuota_brg['remaining_quota_barang'] ?? 0) <= 0) ? 'text-danger' : 'text-success'; ?>">
                                    <?= htmlspecialchars(number_format($kuota_brg['remaining_quota_barang'] ?? 0, 0, ',', '.')); ?>
                                </td>
                                <td><?= htmlspecialchars($kuota_brg['nomor_skep_asal'] ?? '-'); ?></td>
                                <td><?= (isset($kuota_brg['tanggal_skep_asal']) && $kuota_brg['tanggal_skep_asal'] != '0000-00-00') ? date('d M Y', strtotime($kuota_brg['tanggal_skep_asal'])) : '-'; ?></td>
                                <td>
                                    <?php
                                    $status_kb_badge = 'secondary'; $status_kb_text = ucfirst(htmlspecialchars($kuota_brg['status_kuota_barang'] ?? 'N/A'));
                                    if (isset($kuota_brg['status_kuota_barang'])) {
                                        if ($kuota_brg['status_kuota_barang'] == 'active') $status_kb_badge = 'success';
                                        else if ($kuota_brg['status_kuota_barang'] == 'habis') $status_kb_badge = 'danger';
                                        else if ($kuota_brg['status_kuota_barang'] == 'expired') $status_kb_badge = 'warning';
                                        else if ($kuota_brg['status_kuota_barang'] == 'canceled') $status_kb_badge = 'dark';
                                    }
                                    ?>
                                    <span class="badge badge-<?= $status_kb_badge; ?>"><?= $status_kb_text; ?></span>
                                </td>
                                <td><?= isset($kuota_brg['waktu_pencatatan']) ? date('d/m/Y H:i', strtotime($kuota_brg['waktu_pencatatan'])) : '-'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-light small">Belum ada rincian kuota per jenis barang yang tercatat untuk perusahaan ini.</div>
            <?php endif; ?>
            <hr>

            <h5 class="text-gray-800 mt-4">Log Transaksi Kuota</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover" id="dataTableHistoriTransaksiKuota" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tgl Transaksi</th>
                            <th>Jenis Transaksi</th>
                            <th>Nama Barang Terkait</th> <th class="text-right">Jumlah Perubahan</th>
                            <th class="text-right">Sisa Kuota Barang Sblm.</th> <th class="text-right">Sisa Kuota Barang Stlh.</th> <th>Keterangan</th>
                            <th>Ref. Tipe & ID</th>
                            <th>Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($histori_kuota_transaksi)): $no_log = 1; ?>
                            <?php foreach ($histori_kuota_transaksi as $log): ?>
                            <tr>
                                <td><?= $no_log++; ?></td>
                                <td><?= isset($log['tanggal_transaksi']) ? date('d/m/Y H:i', strtotime($log['tanggal_transaksi'])) : '-'; ?></td>
                                <td>
                                    <?php /* ... (logika badge jenis transaksi seperti sebelumnya) ... */
                                    $jenis_badge = 'secondary';
                                    if (isset($log['jenis_transaksi'])) {
                                        if ($log['jenis_transaksi'] == 'penambahan') $jenis_badge = 'success';
                                        elseif ($log['jenis_transaksi'] == 'pengurangan') $jenis_badge = 'danger';
                                        elseif ($log['jenis_transaksi'] == 'koreksi') $jenis_badge = 'warning';
                                    }
                                    echo '<span class="badge badge-'.$jenis_badge.'">'.ucfirst(htmlspecialchars($log['jenis_transaksi'] ?? 'N/A')).'</span>';
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($log['nama_barang_terkait'] ?? '<span class="text-muted"><em>Umum</em></span>'); ?></td> <td class="text-right <?= (isset($log['jenis_transaksi']) && $log['jenis_transaksi'] == 'penambahan') ? 'text-success' : ((isset($log['jenis_transaksi']) && $log['jenis_transaksi'] == 'pengurangan') ? 'text-danger' : ''); ?>">
                                    <?= (isset($log['jenis_transaksi']) && $log['jenis_transaksi'] == 'penambahan') ? '+' : ((isset($log['jenis_transaksi']) && $log['jenis_transaksi'] == 'pengurangan') ? '-' : ''); ?>
                                    <?= htmlspecialchars(number_format(abs($log['jumlah_perubahan'] ?? 0), 0, ',', '.')); ?> Unit
                                </td>
                                <td class="text-right"><?= htmlspecialchars(number_format($log['sisa_kuota_sebelum'] ?? 0, 0, ',', '.')); ?></td>
                                <td class="text-right"><?= htmlspecialchars(number_format($log['sisa_kuota_setelah'] ?? 0, 0, ',', '.')); ?></td>
                                <td style="max-width: 300px; word-wrap: break-word;">
                                    <?= htmlspecialchars($log['keterangan'] ?? '-'); ?>
                                    <?php if (!empty($log['id_referensi_transaksi']) && !empty($log['tipe_referensi'])): ?>
                                        <?php
                                        $link_ref = '#'; $id_ref = $log['id_referensi_transaksi'];
                                        if (in_array($log['tipe_referensi'], ['pengajuan_kuota', 'pengajuan_kuota_disetujui'])) {
                                            $link_ref = site_url('admin/detailPengajuanKuotaAdmin/' . $id_ref);
                                        } elseif (in_array($log['tipe_referensi'], ['permohonan_impor', 'permohonan_impor_barang', 'permohonan_impor_selesai'])) {
                                            $link_ref = site_url('admin/detail_permohonan_admin/' . $id_ref);
                                        } elseif ($log['tipe_referensi'] == 'input_kuota_awal_user' && !empty($log['id_kuota_barang_referensi'])) {
                                            $link_ref = '#!'; // Placeholder
                                        }
                                        ?>
                                        <br><small><a href="<?= $link_ref; ?>" <?= $link_ref != '#!' ? 'target="_blank"' : ''; ?> title="Lihat detail referensi">(Ref: <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $log['tipe_referensi']))) . ' ID ' . $id_ref; ?><?= !empty($log['id_kuota_barang_referensi']) ? ' / KuotaBrg ID '.$log['id_kuota_barang_referensi'] : '' ?>)</a></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($log['id_referensi_transaksi'] ?? '-'); ?><?= !empty($log['id_kuota_barang_referensi']) ? ' <small class="text-muted">(KB:'.$log['id_kuota_barang_referensi'].')</small>' : ''; ?></td>
                                <td><?= htmlspecialchars($log['nama_pencatat'] ?? 'Sistem'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-warning" role="alert"> Data perusahaan tidak ditemukan atau tidak dapat diakses. </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    if(typeof $.fn.DataTable !== 'undefined'){
        $('#dataTableRincianKuotaBarang').DataTable({ // Inisialisasi untuk tabel rincian kuota barang
            "order": [[ 1, "asc" ]], // Urutkan berdasarkan Nama Barang
            "language": { "emptyTable": "Belum ada rincian kuota per jenis barang untuk perusahaan ini." /* ... bahasa lain ... */ }
        });

        $('#dataTableHistoriTransaksiKuota').DataTable({ // Inisialisasi untuk tabel log transaksi
            "order": [[ 1, "desc" ]], // Urutkan berdasarkan Tanggal Transaksi terbaru
            "language": { "emptyTable": "Belum ada histori transaksi kuota untuk perusahaan ini." /* ... bahasa lain ... */ }
        });
    } else {
        console.error("DataTables plugin is not loaded.");
    }
});
</script>