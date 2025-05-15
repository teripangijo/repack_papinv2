<div class="container-fluid">

    <h1 class="h3 mb-2 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Detail Permohonan'); ?></h1>
    <p class="mb-4">Rincian lengkap dari permohonan impor kembali Anda dengan ID Aju: <strong><?= htmlspecialchars($permohonan_detail['id']); ?></strong></p>

    <?php if ($this->session->flashdata('message')) : ?>
        <?= $this->session->flashdata('message'); ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Informasi Permohonan</h6>
            <div>
                <a href="<?= site_url('user/daftarPermohonan'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left fa-sm"></i> Kembali ke Daftar</a>
                <a href="<?= site_url('user/printPdf/' . $permohonan_detail['id']); ?>" target="_blank" class="btn btn-info btn-sm"><i class="fas fa-print fa-sm"></i> Cetak PDF</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12">
                    <h5>Status Saat Ini:
                        <?php
                        $status_text_user = '-';
                        $status_badge_user = 'secondary';
                        if (isset($permohonan_detail['status'])) {
                            switch ($permohonan_detail['status']) {
                                case '0': $status_text_user = 'Diajukan (Menunggu Verifikasi)'; $status_badge_user = 'info'; break;
                                case '5': $status_text_user = 'Diproses Kantor'; $status_badge_user = 'info'; break;
                                case '1': $status_text_user = 'Pemeriksaan Petugas'; $status_badge_user = 'primary'; break;
                                case '2': $status_text_user = 'Menunggu Keputusan'; $status_badge_user = 'warning'; break;
                                case '3': $status_text_user = 'Disetujui'; $status_badge_user = 'success'; break;
                                case '4': $status_text_user = 'Ditolak'; $status_badge_user = 'danger'; break;
                                default: $status_text_user = 'Status Tidak Dikenal (' . htmlspecialchars($permohonan_detail['status']) . ')'; $status_badge_user = 'dark';
                            }
                        }
                        echo '<span class="badge badge-' . $status_badge_user . ' p-2">' . htmlspecialchars($status_text_user) . '</span>';
                        ?>
                    </h5>
                </div>
            </div>
            <hr>

            <h5 class="mt-4 mb-3 font-weight-bold text-primary">Data Perusahaan</h5>
            <div class="row">
                <div class="col-md-6"><strong>Nama Perusahaan:</strong> <?= htmlspecialchars($permohonan_detail['NamaPers'] ?? '-'); ?></div>
                <div class="col-md-6"><strong>NPWP:</strong> <?= htmlspecialchars($permohonan_detail['npwp_perusahaan'] ?? '-'); ?></div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12"><strong>Alamat:</strong> <?= htmlspecialchars($permohonan_detail['alamat_perusahaan'] ?? '-'); ?></div>
            </div>
             <div class="row mt-2">
                <div class="col-md-6"><strong>No. SKEP Perusahaan:</strong> <?= htmlspecialchars($permohonan_detail['NoSkep_perusahaan'] ?? '-'); ?></div>
            </div>
            <hr>

            <h5 class="mt-4 mb-3 font-weight-bold text-primary">Detail Permohonan Diajukan</h5>
            <div class="row">
                <div class="col-md-4"><strong>Nomor Surat Anda:</strong> <?= htmlspecialchars($permohonan_detail['nomorSurat'] ?? '-'); ?></div>
                <div class="col-md-4"><strong>Tanggal Surat Anda:</strong> <?= isset($permohonan_detail['TglSurat']) && $permohonan_detail['TglSurat'] != '0000-00-00' ? date('d F Y', strtotime($permohonan_detail['TglSurat'])) : '-'; ?></div>
                <div class="col-md-4"><strong>Perihal:</strong> <?= htmlspecialchars($permohonan_detail['Perihal'] ?? '-'); ?></div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4"><strong>Jenis Barang:</strong> <?= htmlspecialchars($permohonan_detail['NamaBarang'] ?? '-'); ?></div>
                <div class="col-md-4"><strong>Jumlah Diajukan:</strong> <?= htmlspecialchars($permohonan_detail['JumlahBarang'] ?? '-'); ?> <?= htmlspecialchars($permohonan_detail['SatuanBarang'] ?? ''); ?></div>
                <div class="col-md-4"><strong>Pelabuhan Muat:</strong> <?= htmlspecialchars($permohonan_detail['PelabuhanMuat'] ?? '-'); ?></div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4"><strong>Negara Asal:</strong> <?= htmlspecialchars($permohonan_detail['NegaraAsal'] ?? '-'); ?></div>
                <div class="col-md-4"><strong>Nama Kapal/Voyage:</strong> <?= htmlspecialchars($permohonan_detail['NamaKapal'] ?? '-'); ?> / <?= htmlspecialchars($permohonan_detail['noVoyage'] ?? '-'); ?></div>
                <div class="col-md-4"><strong>Tanggal Perkiraan Tiba:</strong> <?= isset($permohonan_detail['TglTiba']) && $permohonan_detail['TglTiba'] != '0000-00-00' ? date('d F Y', strtotime($permohonan_detail['TglTiba'])) : '-'; ?></div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12"><strong>Keterangan Tambahan:</strong> <?= nl2br(htmlspecialchars($permohonan_detail['Keterangan'] ?? '-')); ?></div>
            </div>
            <hr>

            <?php if (isset($permohonan_detail['status']) && $permohonan_detail['status'] >= '1') : // Tampil jika sudah ada penunjukan petugas ?>
                <h5 class="mt-4 mb-3 font-weight-bold text-primary">Informasi Penugasan & Pemeriksaan</h5>
                <div class="row">
                    <div class="col-md-3"><strong>Petugas Pemeriksa:</strong></div>
                    <div class="col-md-9"><?= htmlspecialchars($permohonan_detail['nama_petugas_pemeriksa'] ?? 'Belum Ditunjuk'); ?></div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-3"><strong>No. Surat Tugas:</strong></div>
                    <div class="col-md-9"><?= htmlspecialchars($permohonan_detail['NoSuratTugas'] ?? '-'); ?></div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-3"><strong>Tgl. Surat Tugas:</strong></div>
                    <div class="col-md-9"><?= isset($permohonan_detail['TglSuratTugas']) && $permohonan_detail['TglSuratTugas'] != '0000-00-00' ? date('d F Y', strtotime($permohonan_detail['TglSuratTugas'])) : '-'; ?></div>
                </div>
                <?php if (!empty($permohonan_detail['FileSuratTugas'])) : ?>
                <div class="row mt-2">
                    <div class="col-md-3"><strong>File Surat Tugas:</strong></div>
                    <div class="col-md-9"><a href="<?= base_url('uploads/surat_tugas/' . $permohonan_detail['FileSuratTugas']) ?>" target="_blank">Lihat File</a></div>
                </div>
                <?php endif; ?>

                <?php if ($lhp_detail) : ?>
                    <h6 class="mt-4 mb-3 font-weight-bold" style="color: #007bff;">Laporan Hasil Pemeriksaan (LHP)</h6>
                    <div class="row">
                        <div class="col-md-3"><strong>No. LHP:</strong></div>
                        <div class="col-md-3"><?= htmlspecialchars($lhp_detail['NoLHP'] ?? '-'); ?></div>
                        <div class="col-md-3"><strong>Tgl. LHP:</strong></div>
                        <div class="col-md-3"><?= isset($lhp_detail['TglLHP']) && $lhp_detail['TglLHP'] != '0000-00-00' ? date('d F Y', strtotime($lhp_detail['TglLHP'])) : '-'; ?></div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-3"><strong>Jumlah Sebenarnya:</strong></div>
                        <div class="col-md-3"><?= htmlspecialchars($lhp_detail['JumlahBenar'] ?? '-'); ?></div>
                        <div class="col-md-3"><strong>Hasil LHP:</strong></div>
                        <div class="col-md-3">
                            <?php
                            if (isset($lhp_detail['hasil'])) {
                                echo $lhp_detail['hasil'] == 1 ? 'Sesuai' : 'Tidak Sesuai';
                            } else {
                                echo '-';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-3"><strong>Kondisi Barang:</strong></div>
                        <div class="col-md-9"><?= htmlspecialchars($lhp_detail['Kondisi'] ?? '-'); ?></div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-3"><strong>Kesimpulan LHP:</strong></div>
                        <div class="col-md-9"><?= nl2br(htmlspecialchars($lhp_detail['Kesimpulan'] ?? '-')); ?></div>
                    </div>
                <?php else: ?>
                    <p class="mt-3"><em>Laporan Hasil Pemeriksaan (LHP) belum tersedia.</em></p>
                <?php endif; ?>
                <hr>
            <?php endif; ?>



            <?php if (isset($permohonan_detail['status']) && ($permohonan_detail['status'] == '3' || $permohonan_detail['status'] == '4')) : ?>
                <h5 class="mt-4 mb-3 font-weight-bold text-<?= $permohonan_detail['status'] == '3' ? 'success' : 'danger'; ?>">Keputusan Akhir</h5>
                <div class="row">
                    <div class="col-md-6"><strong>No. Surat Keputusan:</strong> <?= htmlspecialchars($permohonan_detail['nomorSetuju'] ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Tgl. Surat Keputusan:</strong> <?= isset($permohonan_detail['tgl_S']) && $permohonan_detail['tgl_S'] != '0000-00-00' ? date('d F Y', strtotime($permohonan_detail['tgl_S'])) : '-'; ?></div>
                </div>
                <?php if(!empty($permohonan_detail['nomorND']) || !empty($permohonan_detail['tgl_ND'])): ?>
                <div class="row mt-2">
                    <div class="col-md-6"><strong>No. Nota Dinas:</strong> <?= htmlspecialchars($permohonan_detail['nomorND'] ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Tgl. Nota Dinas:</strong> <?= isset($permohonan_detail['tgl_ND']) && $permohonan_detail['tgl_ND'] != '0000-00-00' ? date('d F Y', strtotime($permohonan_detail['tgl_ND'])) : '-'; ?></div>
                </div>
                <?php endif; ?>
                 <?php if (!empty($permohonan_detail['link'])) : ?>
                <div class="row mt-2">
                    <div class="col-md-12"><strong>Link Surat Keputusan:</strong> <a href="<?= htmlspecialchars($permohonan_detail['link']) ?>" target="_blank"><?= htmlspecialchars($permohonan_detail['link']) ?></a></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($permohonan_detail['linkND'])) : ?>
                <div class="row mt-2">
                    <div class="col-md-12"><strong>Link Nota Dinas:</strong> <a href="<?= htmlspecialchars($permohonan_detail['linkND']) ?>" target="_blank"><?= htmlspecialchars($permohonan_detail['linkND']) ?></a></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($permohonan_detail['nama_admin_pemroses'])) : ?>
                <div class="row mt-2">
                    <div class="col-md-12"><strong>Diproses oleh Admin:</strong> <?= htmlspecialchars($permohonan_detail['nama_admin_pemroses']); ?> pada <?= isset($permohonan_detail['time_selesai']) ? date('d F Y H:i', strtotime($permohonan_detail['time_selesai'])) : '-'; ?></div>
                </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
</div>