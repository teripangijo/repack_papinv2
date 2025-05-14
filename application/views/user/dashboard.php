<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Dashboard'; ?></h1>
    </div>

    <?php
    // Flashdata seharusnya sudah ditampilkan secara global oleh templates/topbar.php
    // Jika Anda sudah memastikannya, baris ini bisa tetap dikomentari atau dihapus.
    // if ($this->session->flashdata('message')) {
    //     echo $this->session->flashdata('message');
    // }
    ?>

    <?php if (empty($user_perusahaan)) : ?>
        <div class="alert alert-warning" role="alert">
            Data perusahaan Anda belum lengkap. Silakan <a href="<?= site_url('user/edit'); ?>" class="alert-link">lengkapi profil perusahaan Anda</a> untuk mengaktifkan semua fitur.
        </div>
    <?php else : ?>
        <div class="row">
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Kuota Awal Disetujui</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= isset($kuota_awal) ? number_format($kuota_awal, 0, ',', '.') : '0'; // Menggunakan variabel dari controller ?> Unit
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-cubes fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Sisa Kuota</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                     <?= isset($sisa_kuota) ? number_format($sisa_kuota, 0, ',', '.') : '0'; // Menggunakan variabel dari controller ?> Unit
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-cube fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
             <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Kuota Terpakai</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= isset($total_kuota_terpakai) ? number_format($total_kuota_terpakai, 0, ',', '.') : '0'; // Menggunakan variabel dari controller ?> Unit
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exchange-alt fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Ringkasan Permohonan Impor Kembali Terbaru</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_permohonan) && is_array($recent_permohonan)) : ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>No Surat</th>
                                    <th>Tgl Surat</th>
                                    <th>Nama Barang</th>
                                    <th class="text-right">Jumlah</th>
                                    <th>Status</th>
                                    <th>Waktu Submit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_permohonan as $p) : // Menggunakan $p agar konsisten dengan kode Anda sebelumnya, bukan $rp ?>
                                    <tr>
                                        <td><?= isset($p['nomorSurat']) ? htmlspecialchars($p['nomorSurat']) : '-'; ?></td>
                                        <td><?= isset($p['TglSurat']) && $p['TglSurat'] != '0000-00-00' ? date('d/m/Y', strtotime($p['TglSurat'])) : '-'; ?></td>
                                        <td><?= isset($p['NamaBarang']) ? htmlspecialchars($p['NamaBarang']) : '-'; // Pastikan kolom ini ada di query controller User::index() ?></td>
                                        <td class="text-right"><?= isset($p['JumlahBarang']) ? number_format($p['JumlahBarang'], 0, ',', '.') : '-'; // Ganti 'Jumlah' menjadi 'JumlahBarang' jika itu nama kolomnya ?> Unit</td>
                                        <td>
                                            <?php
                                            $status_text_user = '-';
                                            $status_badge_user = 'secondary'; // Default badge
                                            if (isset($p['status'])) {
                                                switch ($p['status']) {
                                                    case '0': // Baru diajukan oleh user
                                                        $status_text_user = 'Diajukan';
                                                        $status_badge_user = 'info';
                                                        break;
                                                    case '5': // Admin sedang memproses (sebelum menunjuk petugas)
                                                        $status_text_user = 'Sedang Diproses Kantor'; // Status yang diinginkan
                                                        $status_badge_user = 'info'; // Warna bisa disesuaikan, misal 'warning'
                                                        break;
                                                    case '1': // Admin sudah menunjuk petugas/pemeriksa
                                                        $status_text_user = 'Pemeriksaan Petugas';
                                                        $status_badge_user = 'primary';
                                                        break;
                                                    case '2': // LHP sudah direkam oleh petugas/pemeriksa
                                                        $status_text_user = 'Menunggu Keputusan';
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
                                                        $status_text_user = 'Status Proses Tdk Dikenal (' . htmlspecialchars($p['status']) . ')';
                                                        $status_badge_user = 'dark';
                                                }
                                            }
                                            echo '<span class="badge badge-pill badge-' . $status_badge_user . '">' . htmlspecialchars($status_text_user) . '</span>';
                                            ?>
                                        </td>
                                        <td><?= isset($p['time_stamp']) && $p['time_stamp'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i:s', strtotime($p['time_stamp'])) : '-'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-right mt-2">
                        <a href="<?= site_url('user/daftarPermohonan'); ?>">Lihat Semua Permohonan &rarr;</a>
                    </div>
                <?php else : ?>
                    <p class="text-center">Belum ada data permohonan impor kembali terbaru.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; // Penutup untuk if (empty($user_perusahaan)) ?>
</div>