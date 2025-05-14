<?php // application/views/templates/header_petugas.php ?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistem Informasi Returnable Package - Petugas">
    <meta name="author" content="Pengembang Anda">

    <title><?= htmlspecialchars($title ?? 'Dashboard Petugas'); ?> - <?= htmlspecialchars($subtitle ?? 'Repack'); ?></title>

    <link href="<?= base_url('assets/vendor/fontawesome-free/css/all.min.css'); ?>" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <link href="<?= base_url('assets/css/sb-admin-2.min.css'); ?>" rel="stylesheet">

    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">

    <link href="<?= base_url('assets/css/custom-style.css'); // Contoh ?>" rel="stylesheet">

    <script src="<?= base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>

    <style>
        /* Tambahkan CSS kustom di sini jika perlu, atau di file custom-style.css */
    </style>

</head>

<body id="page-top">

    <div id="wrapper">