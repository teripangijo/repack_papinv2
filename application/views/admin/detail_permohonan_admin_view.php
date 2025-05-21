<?php // application/views/admin/detail_permohonan_admin_view.php ?>

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Detail Permohonan Impor'); ?></h1>
        <div>
            <?php if (isset($permohonan_detail['status'])): ?>
                <?php if ($permohonan_detail['status'] == '0' || $permohonan_detail['status'] == '5'): ?>
                    <a href="<?= site_url('admin/penunjukanPetugas/' . $permohonan_detail['id']); ?>" class="btn btn-sm btn-success shadow-sm mr-2">
                        <i class="fas fa-user-plus fa-sm text-white-50"></i> Proses & Tunjuk Petugas
                    </a>
                <?php elseif ($permohonan_detail['status'] == '1'): ?>
                    <a href="<?= site_url('admin/penunjukanPetugas/' . $permohonan_detail['id']); ?>" class="btn btn-sm btn-warning shadow-sm mr-2">
                        <i class="fas fa-edit fa-sm text-white-50"></i> Lihat/Edit Penunjukan
                    </a>
                <?php elseif ($permohonan_detail['status'] == '2'): ?>
                     <a href="<?= site_url('admin/prosesSurat/' . $permohonan_detail['id']); ?>" class="btn btn-sm btn-primary shadow-sm mr-2">
                        <i class="fas fa-flag-checkered fa-sm text-white-50"></i> Proses Penyelesaian Akhir
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <a href="<?= site_url('admin/permohonanMasuk'); ?>" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Permohonan
            </a>
        </div>
    </div>

    <!-- <?php if ($this->session->flashdata('message')) { echo $this->session->flashdata('message'); } ?> -->

    <?php if (isset($permohonan_detail) && !empty($permohonan_detail)): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Permohonan (ID Aju: <?= htmlspecialchars($permohonan_detail['id']); ?>)</h6>
                <span>
                    <?php
                    $status_text = 'Tidak Diketahui'; $status_badge = 'light';
                    if (isset($permohonan_detail['status'])) {
                        switch ($permohonan_detail['status']) {
                            case '0': $status_text = 'Baru Masuk'; $status_badge = 'dark'; break;
                            case '5': $status_text = 'Diproses Admin'; $status_badge = 'info'; break;
                            case '1': $status_text = 'Penunjukan Pemeriksa'; $status_badge = 'primary'; break;
                            case '2': $status_text = 'LHP Direkam'; $status_badge = 'warning'; break;
                            case '3': $status_text = 'Selesai (Disetujui)'; $status_badge = 'success'; break;
                            case '4': $status_text = 'Selesai (Ditolak)'; $status_badge = 'danger'; break;
                            default: $status_text = 'Status Tidak Dikenal (' . htmlspecialchars($permohonan_detail['status']) . ')';
                        }
                    }
                    echo '<span class="badge badge-pill badge-' . $status_badge . ' p-2">' . htmlspecialchars($status_text) . '</span>';
                    ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <strong class="d-block text-primary">Data Pengajuan:</strong>
                        <table class="table table-sm table-borderless table-hover">
                            <tr><td width="40%">No. Surat Pemohon</td><td>: <?= htmlspecialchars($permohonan_detail['nomorSurat'] ?? '-'); ?></td></tr>
                            <tr><td>Tgl. Surat Pemohon</td><td>: <?= isset($permohonan_detail['TglSurat']) && $permohonan_detail['TglSurat'] != '0000-00-00' ? date('d M Y', strtotime($permohonan_detail['TglSurat'])) : '-'; ?></td></tr>
                            <tr><td>Waktu Submit Sistem</td><td>: <?= isset($permohonan_detail['time_stamp']) ? date('d M Y H:i:s', strtotime($permohonan_detail['time_stamp'])) : '-'; ?></td></tr>
                            </table>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <strong class="d-block text-primary">Data Perusahaan & Kontak:</strong>
                        <table class="table table-sm table-borderless table-hover">
                            <tr><td width="40%">Nama Perusahaan</td><td>: <?= htmlspecialchars($permohonan_detail['NamaPers'] ?? '-'); ?></td></tr>
                            <tr><td>NPWP</td><td>: <?= htmlspecialchars($permohonan_detail['npwp'] ?? '-'); ?></td></tr>
                            <tr><td>Diajukan Oleh</td><td>: <?= htmlspecialchars($permohonan_detail['nama_pengaju_permohonan'] ?? '-'); ?></td></tr>
                            <tr><td>Email Kontak</td><td>: <?= htmlspecialchars($permohonan_detail['email_pengaju_permohonan'] ?? '-'); ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Detail Penugasan Pemeriksa</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($permohonan_detail['nama_petugas_pemeriksa']) || !empty($permohonan_detail['NoSuratTugas'])): ?>
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                            <table class="table table-sm table-borderless table-hover">
                                <tr><td width="40%">Nama Petugas</td><td>: <?= htmlspecialchars($permohonan_detail['nama_petugas_pemeriksa'] ?? 'Belum Ditunjuk'); ?></td></tr>
                                <tr><td>No. Surat Tugas</td><td>: <?= htmlspecialchars($permohonan_detail['NoSuratTugas'] ?? '-'); ?></td></tr>
                                <tr><td>Tgl. Surat Tugas</td><td>: <?= isset($permohonan_detail['TglSuratTugas']) && $permohonan_detail['TglSuratTugas'] != '0000-00-00' ? date('d M Y', strtotime($permohonan_detail['TglSuratTugas'])) : '-'; ?></td></tr>
                                <?php if (isset($permohonan_detail['FileSuratTugas']) && !empty($permohonan_detail['FileSuratTugas'])): ?>
                                    <tr><td>File Surat Tugas</td><td>: <a href="<?= base_url('uploads/surat_tugas/' . $permohonan_detail['FileSuratTugas']); ?>" target="_blank" title="Unduh/Lihat Surat Tugas"><i class="fas fa-file-alt"></i> <?= htmlspecialchars($permohonan_detail['FileSuratTugas']); ?></a></td></tr>
                                <?php else: ?>
                                    <tr><td>File Surat Tugas</td><td>: <span class="text-muted"><em>Tidak ada file</em></span></td></tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        <div class="col-lg-6 mb-3">
                            <p class="font-italic">Waktu Penunjukan Petugas: <?= isset($permohonan_detail['WaktuPenunjukanPetugas']) && $permohonan_detail['WaktuPenunjukanPetugas'] != '0000-00-00 00:00:00' ? date('d M Y H:i:s', strtotime($permohonan_detail['WaktuPenunjukanPetugas'])) : '-'; ?></p>
                            </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted"><em>Petugas pemeriksa belum ditunjuk atau data penugasan belum lengkap.</em></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Detail Laporan Hasil Pemeriksaan (LHP)</h6>
            </div>
            <div class="card-body">
                <?php if (isset($lhp_detail) && !empty($lhp_detail)): ?>
                     <div class="row">
                        <div class="col-lg-6 mb-3">
                             <table class="table table-sm table-borderless table-hover">
                                <tr><td width="40%">ID LHP</td><td>: <?= htmlspecialchars($lhp_detail['id_lhp'] ?? '-'); ?></td></tr>
                                <tr><td>No. LHP</td><td>: <?= htmlspecialchars($lhp_detail['NoLHP'] ?? '-'); ?></td></tr>
                                <tr><td>Tgl. LHP</td><td>: <?= isset($lhp_detail['TglLHP']) && $lhp_detail['TglLHP'] != '0000-00-00' ? date('d M Y', strtotime($lhp_detail['TglLHP'])) : '-'; ?></td></tr>
                                <tr><td>Jumlah Diajukan</td><td>: <?= htmlspecialchars(number_format($lhp_detail['JumlahAju'] ?? 0)); ?> Unit</td></tr>
                                <tr class="font-weight-bold text-success"><td>Jumlah Disetujui (LHP)</td><td>: <?= htmlspecialchars(number_format($lhp_detail['JumlahBenar'] ?? 0)); ?> Unit</td></tr>
                                <tr><td>Jumlah Salah</td><td>: <?= htmlspecialchars(number_format($lhp_detail['JumlahSalah'] ?? 0)); ?> Unit</td></tr>
                            </table>
                        </div>
                        <div class="col-lg-6 mb-3">
                             <table class="table table-sm table-borderless table-hover">
                                <tr><td width="40%">Waktu Rekam LHP</td><td>: <?= isset($lhp_detail['submit_time']) && $lhp_detail['submit_time'] != '0000-00-00 00:00:00' ? date('d M Y H:i:s', strtotime($lhp_detail['submit_time'])) : '-'; ?></td></tr>
                                <tr><td>Catatan LHP</td><td>: <?= !empty($lhp_detail['Catatan']) ? nl2br(htmlspecialchars($lhp_detail['Catatan'])) : '<span class="text-muted"><em>Tidak ada catatan</em></span>'; ?></td></tr>
                                <?php if (isset($lhp_detail['FileLHP']) && !empty($lhp_detail['FileLHP'])): ?>
                                    <tr><td>File LHP</td><td>: <a href="<?= base_url('uploads/lhp/' . $lhp_detail['FileLHP']); ?>" target="_blank" title="Unduh/Lihat LHP"><i class="fas fa-file-pdf"></i> <?= htmlspecialchars($lhp_detail['FileLHP']); ?></a></td></tr>
                                <?php else: ?>
                                    <tr><td>File LHP</td><td>: <span class="text-muted"><em>Tidak ada file</em></span></td></tr>
                                <?php endif; ?>
                                <?php if (isset($lhp_detail['file_dokumentasi_foto']) && !empty($lhp_detail['file_dokumentasi_foto'])): ?>
                                    <tr>
                                        <td>File Dokumentasi Foto</td>
                                        <td>: 
                                            <a href="<?= base_url('uploads/dokumentasi_lhp/' . htmlspecialchars($lhp_detail['file_dokumentasi_foto'])); ?>" target="_blank" title="Unduh/Lihat Dokumentasi Foto">
                                                <i class="fas fa-camera"></i> <?= htmlspecialchars($lhp_detail['file_dokumentasi_foto']); ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td>File Dokumentasi Foto</td>
                                        <td>: <span class="text-muted"><em>Tidak ada file dokumentasi foto</em></span></td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted"><em>Data LHP belum direkam atau tidak ditemukan untuk permohonan ini.</em></p>
                <?php endif; ?>
            </div>
        </div>


        <?php if ($permohonan_detail['status'] == '3' || $permohonan_detail['status'] == '4'): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Hasil Akhir & Dokumen Penyelesaian</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                            <strong class="d-block text-primary">Surat Keputusan:</strong>
                            <table class="table table-sm table-borderless table-hover">
                                <tr><td width="40%">No. SK Penyelesaian</td><td>: <?= htmlspecialchars($permohonan_detail['nomorSetuju'] ?? '-'); ?></td></tr>
                                <tr><td>Tanggal SK</td><td>: <?= isset($permohonan_detail['tgl_S']) && $permohonan_detail['tgl_S'] != '0000-00-00' ? date('d M Y', strtotime($permohonan_detail['tgl_S'])) : '-'; ?></td></tr>
                                <tr><td>Link SK</td><td>: <?= isset($permohonan_detail['link']) && !empty($permohonan_detail['link']) ? '<a href="'.htmlspecialchars($permohonan_detail['link']).'" target="_blank" title="Lihat Dokumen SK">Lihat Dokumen <i class="fas fa-external-link-alt fa-xs"></i></a>' : '<span class="text-muted"><em>Tidak ada link</em></span>'; ?></td></tr>
                            </table>
                        </div>
                        <div class="col-lg-6 mb-3">
                            <strong class="d-block text-primary">Nota Dinas (Jika Ada):</strong>
                             <table class="table table-sm table-borderless table-hover">
                                <tr><td width="40%">No. Nota Dinas</td><td>: <?= htmlspecialchars($permohonan_detail['nomorND'] ?? '-'); ?></td></tr>
                                <tr><td>Tanggal Nota Dinas</td><td>: <?= isset($permohonan_detail['tgl_ND']) && $permohonan_detail['tgl_ND'] != '0000-00-00' ? date('d M Y', strtotime($permohonan_detail['tgl_ND'])) : '-'; ?></td></tr>
                                 <tr><td>Link Nota Dinas</td><td>: <?= isset($permohonan_detail['linkND']) && !empty($permohonan_detail['linkND']) ? '<a href="'.htmlspecialchars($permohonan_detail['linkND']).'" target="_blank" title="Lihat Dokumen Nota Dinas">Lihat Dokumen <i class="fas fa-external-link-alt fa-xs"></i></a>' : '<span class="text-muted"><em>Tidak ada link</em></span>'; ?></td></tr>
                            </table>
                        </div>
                    </div>
                     <p class="font-italic mt-2">Waktu Penyelesaian Permohonan: <?= isset($permohonan_detail['time_selesai']) && $permohonan_detail['time_selesai'] != '0000-00-00 00:00:00' ? date('d M Y H:i:s', strtotime($permohonan_detail['time_selesai'])) : '-'; ?></p>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Data detail permohonan tidak dapat dimuat atau tidak ditemukan.
        </div>
    <?php endif; ?>

</div>