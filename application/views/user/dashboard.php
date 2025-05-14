<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Dashboard'; ?></h1>
    </div>

    <!-- <?php
    // Menampilkan flashdata message jika ada
    if ($this->session->flashdata('message')) {
        echo $this->session->flashdata('message');
    }
    ?> -->

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
                                    <?= isset($user_perusahaan['initial_quota']) ? number_format($user_perusahaan['initial_quota'], 0, ',', '.') : '0'; ?> Unit
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
                                     <?= isset($user_perusahaan['remaining_quota']) ? number_format($user_perusahaan['remaining_quota'], 0, ',', '.') : '0'; ?> Unit
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
                                    <?php
                                        $terpakai = (isset($user_perusahaan['initial_quota']) ? $user_perusahaan['initial_quota'] : 0) - (isset($user_perusahaan['remaining_quota']) ? $user_perusahaan['remaining_quota'] : 0);
                                        echo number_format($terpakai, 0, ',', '.');
                                    ?> Unit
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
                <?php if (!empty($recent_permohonan)) : ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>No Surat</th>
                                    <th>Tgl Surat</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Waktu Submit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_permohonan as $p) : ?>
                                    <tr>
                                        <td><?= isset($p['nomorSurat']) ? htmlspecialchars($p['nomorSurat']) : '-'; ?></td>
                                        <td><?= isset($p['TglSurat']) ? date('d/m/Y', strtotime($p['TglSurat'])) : '-'; ?></td>
                                        <td><?= isset($p['NamaBarang']) ? htmlspecialchars($p['NamaBarang']) : '-'; ?></td>
                                        <td><?= isset($p['JumlahBarang']) ? htmlspecialchars($p['JumlahBarang']) : '-'; ?> Unit</td>
                                        <td>
                                            <?php
                                            $status_text = '-'; $status_badge = 'secondary';
                                            if (isset($p['status'])) {
                                                switch ($p['status']) {
                                                    case '0': $status_text = 'Permohonan Masuk'; $status_badge = 'info'; break;
                                                    case '1': $status_text = 'Diproses Petugas'; $status_badge = 'primary'; break;
                                                    case '2': $status_text = 'LHP Direkam'; $status_badge = 'warning'; break;
                                                    case '3': $status_text = 'Selesai'; $status_badge = 'success'; break;
                                                }
                                            }
                                            echo '<span class="badge badge-' . $status_badge . '">' . htmlspecialchars($status_text) . '</span>';
                                            ?>
                                        </td>
                                        <td><?= isset($p['time_stamp']) ? date('d/m/Y H:i:s', strtotime($p['time_stamp'])) : '-'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-right">
                        <a href="<?= site_url('user/daftarPermohonan'); ?>">Lihat Semua Permohonan &rarr;</a>
                    </div>
                <?php else : ?>
                    <p>Belum ada data permohonan.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; // End if empty($user_perusahaan) ?>
</div>
