<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800"> <?= $subtitle; ?></h1>


    <div class="row">
        <div class="col-lg">

            <?= form_error('menu', '<div class="alert alert-danger " role="alert">', '</div>') ?>
            <?= $this->session->flashdata('message'); ?>

            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Id Aju</th>
                        <th scope="col">No Surat</th>
                        <th scope="col">Tanggal Surat</th>
                        <th scope="col">Nama Perusahaan</th>
                        <th scope="col">Waktu Submit</th>
                        <th scope="col">Nama Petugas</th>
                        <th scope="col">Status</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; ?>
                    <?php foreach (array_reverse($permohonan) as $p) : ?>
                        <?php if ($p['status'] == 1) {
                            $status = "prosesLHP";
                        } elseif ($p['status'] == 0) {
                            $status = "proses";
                        } elseif ($p['status'] == 2) {
                            $status = "prosesSurat";
                        } elseif ($p['status'] == 3) {
                            $status = "lihatData";
                        }
                        ?>
                        <tr>
                            <th scope="row"><?= $i; ?></th>
                            <td><?= $p['id']; ?></td>
                            <td><?= $p['nomorSurat']; ?></td>
                            <td><?= $p['TglSurat']; ?></td>
                            <td><?= $p['NamaPers']; ?></td>
                            <td><?= $p['time_stamp']; ?></td>
                            <td><?php if ($p['petugas'] == 2) {
                                    echo "Bayu Raharjo Putra";
                                } elseif ($p['petugas'] == 6) {
                                    echo "Bayu Raharjo Putra";
                                } elseif ($p['petugas'] == 7) {
                                    echo "Septian Budi Subroto";
                                } elseif ($p['petugas'] == 4) {
                                    echo "Indra Triansyah";
                                } elseif ($p['petugas'] == 5) {
                                    echo "Ananta Atmadharmika";
                                } elseif ($p['petugas'] == 8) {
                                    echo "Harso Haryadi";
                                } elseif ($p['petugas'] == 9) {
                                    echo "Ismail Martawinata";
                                } elseif ($p['petugas'] == 10) {
                                    echo "Jihad Fadhil Mudhoffar";
                                } elseif ($p['petugas'] == 11) {
                                    echo "Kristian Jimmy Hamonangan";
                                } else {
                                    echo "-";
                                }
                                ?></td>
                            <td>
                                <a class="badge badge-info">
                                    <?php if ($p['status'] == 1) {
                                        echo "Perekaman LHP";
                                    } elseif ($p['status'] == 0) {
                                        echo "Penerimaan Data";
                                    } elseif ($p['status'] == 2) {
                                        echo "Penerbitan Surat Persetujuan";
                                    } elseif ($p['status'] == 3) {
                                        echo "Selesai";
                                    }
                                    ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($p['status'] == '0') : ?>
                                    <a href="<?= base_url() ?>admin/<?= $status; ?>/<?= $p['id']; ?>" class="badge badge-primary">Proses</a>
                                    <a href="<?= base_url() ?>admin/printpdf/<?= $p['id']; ?>" target="_blank" class="badge badge-warning">Permohonan</a>
                                <?php endif; ?>
                                <?php if ($p['status'] == '1') : ?>
                                    <a href="<?= base_url() ?>admin/<?= $status; ?>/<?= $p['id']; ?>" class="badge badge-primary">Proses</a>
                                    <a href="<?= base_url() ?>admin/gantipetugas/<?= $p['id']; ?>" class="badge badge-secondary">Ganti Petugas</a>
                                    <a href="<?= base_url() ?>admin/printpdf/<?= $p['id']; ?>" target="_blank" class="badge badge-warning">Permohonan</a>
                                    <a href="<?= base_url() ?>admin/konsepST/<?= $p['id']; ?>" class="badge badge-success">Konsep ST</a>
                                <?php endif; ?>
                                <?php if ($p['status'] == '2') : ?>
                                    <a href="<?= base_url() ?>admin/<?= $status; ?>/<?= $p['id']; ?>" class="badge badge-primary">Proses</a>
                                    <a href="<?= base_url() ?>admin/printpdf/<?= $p['id']; ?>" target="_blank" class="badge badge-warning">Permohonan</a>
                                    <a href="<?= base_url() ?>admin/konsepSurat/<?= $p['id']; ?>" class="badge badge-success">Konsep Surat</a>
                                    <a href="<?= base_url() ?>admin/konsepND/<?= $p['id']; ?>" class="badge badge-success">Konsep ND</a>
                                    <a href="<?= base_url() ?>admin/cetakLHP/<?= $p['id']; ?>" target="_blank" class="badge badge-info">Cetak LHP</a>
                                    <a href="<?= base_url() ?>admin/editLHP/<?= $p['id']; ?>" target="_blank" class="badge badge-secondary">Edit LHP</a>
                                <?php endif; ?>
                                <?php if ($p['status'] == '3') : ?>
                                    <a href="<?= base_url() ?>admin/printpdf/<?= $p['id']; ?>" target="_blank" class="badge badge-warning">Permohonan</a>
                                    <a href="<?= $p['link']; ?>" target="_blank" class="badge badge-success">Cetak Surat</a>
                                    <a href="<?= $p['linkND']; ?>" target="_blank" class="badge badge-success">Cetak ND</a>
                                    <a href="<?= $p['linkST']; ?>" target="_blank" class="badge badge-success">Cetak ST</a>
                                    <a href="<?= base_url() ?>admin/cetakLHP/<?= $p['id']; ?>" target="_blank" class="badge badge-warning">Cetak LHP</a>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <?php $i++; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>


        </div>
    </div>

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->

<!-- Button trigger modal -->