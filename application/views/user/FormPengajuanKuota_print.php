<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pengajuan Kuota - ID: <?= isset($pengajuan['id']) ? htmlspecialchars($pengajuan['id']) : 'Detail'; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            padding: 4px 6px; /* Sedikit padding lebih banyak untuk keterbacaan */
            vertical-align: top;
            border: 1px solid #ccc; /* Tambahkan border untuk tampilan tabel */
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
        }
        h2, h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        hr {
            border: none;
            border-top: 1px solid black;
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .signature-block {
            width: 35%;
            float: right;
            text-align: center; /* Tanda tangan biasanya di tengah */
            margin-top: 40px;
        }
        .signature-block img {
            margin-bottom: 5px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .signature-block p {
            margin: 0;
            line-height: 1.4;
        }
        .clear { clear: both; }
        .no-print { display: none; } /* Sembunyikan elemen dengan class no-print saat cetak */

        @media print {
            body {
                margin: 0.5in;
                font-size: 10pt;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .back-button-container {
                display: none !important;
            }
        }
        .info-label {
            font-weight: bold;
            width: 30%;
        }
        .info-value {
            width: 70%;
        }
    </style>
</head>
<body onload="window.print()">
    <?php
    if (!function_exists('dateConvertFull')) { // Menggunakan nama berbeda agar tidak konflik jika ada helper lain
        function dateConvertFull($date_sql) {
            if (!is_string($date_sql) || empty(trim($date_sql)) || $date_sql == '0000-00-00' || $date_sql == '0000-00-00 00:00:00') {
                return '-';
            }
            try {
                $date_obj = new DateTime($date_sql);
                if ($date_obj) {
                    $bulan = array(
                        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    );
                    $hari = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');
                    return $hari[$date_obj->format('w')] . ', ' . (int)$date_obj->format('d') . ' ' . $bulan[(int)$date_obj->format('m')] . ' ' . $date_obj->format('Y') . ' Pukul ' . $date_obj->format('H:i');
                }
                return htmlspecialchars($date_sql);
            } catch (Exception $e) {
                return htmlspecialchars($date_sql);
            }
        }
    }
    $logo_perusahaan_file = (isset($user_login['image']) && $user_login['image'] != 'default.jpg' && !empty($user_login['image'])) ? $user_login['image'] : null;
    ?>

    <div class="back-button-container no-print">
        <button onclick="goBack()" style="padding: 8px 15px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">&laquo; Kembali</button>
    </div>

    <h2>BUKTI PENGAJUAN KUOTA RETURNABLE PACKAGE</h2>
    <hr>

    <?php if (isset($pengajuan) && !empty($pengajuan)) : ?>
        <h3>Detail Perusahaan</h3>
        <table>
            <tr>
                <td class="info-label">Nama Perusahaan</td>
                <td class="info-value">: <?= isset($pengajuan['NamaPers']) ? htmlspecialchars($pengajuan['NamaPers']) : 'N/A'; ?></td>
            </tr>
            <tr>
                <td class="info-label">Email Pengaju</td>
                <td class="info-value">: <?= isset($pengajuan['user_email']) ? htmlspecialchars($pengajuan['user_email']) : 'N/A'; ?></td>
            </tr>
            <tr>
                <td class="info-label">NPWP</td>
                <td class="info-value">: <?= isset($pengajuan['npwp_perusahaan']) ? htmlspecialchars($pengajuan['npwp_perusahaan']) : 'N/A'; // Asumsi kolom NPWP ada di data $pengajuan ?></td>
            </tr>
        </table>
        <hr>

        <h3>Detail Pengajuan</h3>
        <table>
            <tr>
                <td class="info-label">ID Pengajuan</td>
                <td class="info-value">: <?= htmlspecialchars($pengajuan['id']); ?></td>
            </tr>
            <tr>
                <td class="info-label">Nomor Surat Pengajuan</td>
                <td class="info-value">: <?= isset($pengajuan['nomor_surat_pengajuan']) ? htmlspecialchars($pengajuan['nomor_surat_pengajuan']) : '-'; ?></td>
            </tr>
            <tr>
                <td class="info-label">Tanggal Surat Pengajuan</td>
                <td class="info-value">: <?= isset($pengajuan['tanggal_surat_pengajuan']) ? dateConvertFull($pengajuan['tanggal_surat_pengajuan']) : '-'; ?></td>
            </tr>
             <tr>
                <td class="info-label">Perihal Surat Pengajuan</td>
                <td class="info-value">: <?= isset($pengajuan['perihal_pengajuan']) ? htmlspecialchars($pengajuan['perihal_pengajuan']) : '-'; ?></td>
            </tr>
            <tr>
                <td class="info-label">Nama/Jenis Barang (untuk Kuota)</td>
                <td class="info-value">: <?= isset($pengajuan['nama_barang_kuota']) ? htmlspecialchars($pengajuan['nama_barang_kuota']) : '-'; ?></td>
            </tr>
            <tr>
                <td class="info-label">Jumlah Kuota Diajukan</td>
                <td class="info-value">: <strong><?= isset($pengajuan['requested_quota']) ? number_format($pengajuan['requested_quota'], 0, ',', '.') : '0'; ?> Unit</strong></td>
            </tr>
            <tr>
                <td class="info-label">Alasan Pengajuan</td>
                <td class="info-value" style="white-space: pre-wrap;"><?= isset($pengajuan['reason']) ? nl2br(htmlspecialchars($pengajuan['reason'])) : '-'; ?></td>
            </tr>
            <tr>
                <td class="info-label">Tanggal Pengajuan ke Sistem</td>
                <td class="info-value">: <?= isset($pengajuan['submission_date']) ? dateConvertFull($pengajuan['submission_date']) : '-'; ?></td>
            </tr>
        </table>
        <hr>

        <h3>Status Pemrosesan oleh Admin</h3>
        <table>
            <tr>
                <td class="info-label">Status</td>
                <td class="info-value">: <strong><?= isset($pengajuan['status']) ? strtoupper(htmlspecialchars($pengajuan['status'])) : 'N/A'; ?></strong></td>
            </tr>
            <?php if (isset($pengajuan['status']) && $pengajuan['status'] == 'approved') : ?>
            <tr>
                <td class="info-label">Jumlah Kuota Disetujui</td>
                <td class="info-value">: <strong><?= isset($pengajuan['approved_quota']) ? number_format($pengajuan['approved_quota'], 0, ',', '.') : '0'; ?> Unit</strong></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="info-label">Catatan dari Admin</td>
                <td class="info-value" style="white-space: pre-wrap;"><?= isset($pengajuan['admin_notes']) && !empty($pengajuan['admin_notes']) ? nl2br(htmlspecialchars($pengajuan['admin_notes'])) : '-'; ?></td>
            </tr>
            <tr>
                <td class="info-label">Tanggal Diproses Admin</td>
                <td class="info-value">: <?= isset($pengajuan['processed_date']) ? dateConvertFull($pengajuan['processed_date']) : '-'; ?></td>
            </tr>
        </table>
        <br><br>

        <table style="width:100%;">
            <tr>
                <td style="width: 60%;"></td>
                <td style="width: 40%; text-align: center;">
                    Pangkalpinang, <?= dateConvertFull(date('Y-m-d H:i:s')); // Tanggal cetak ?>
                    <br>Pengguna Jasa,
                    <br><br><br><br><br>
                    <strong>( <?= isset($user_login['name']) ? strtoupper(htmlspecialchars($user_login['name'])) : 'Nama Pengguna Jasa'; ?> )</strong>
                </td>
            </tr>
        </table>


    <?php else : ?>
        <p>Data pengajuan kuota tidak ditemukan.</p>
    <?php endif; ?>

    <script>
        function goBack() {
            if (history.length > 1 && document.referrer.indexOf(window.location.host) !== -1) { // Cek jika ada history dan dari domain yang sama
                history.back();
            } else {
                // Arahkan ke daftar pengajuan kuota admin atau user, tergantung siapa yang print
                // Untuk sekarang, kita asumsikan ini dicetak dari sisi admin
                window.location.href = "<?= site_url('admin/daftar_pengajuan_kuota'); ?>"; 
            }
        }
    </script>
</body>
</html>
