<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <?= form_error('menu', '<div class="alert alert-danger " role="alert">', '</div>') ?>
    <?= $this->session->flashdata('message'); ?>
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
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; ?>
                    <?php foreach ($permohonan as $p) : ?>
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
                            <td>
                                <a href="<?= base_url() ?>umum/printpdf/<?= $p['id']; ?>" target="_blank" class="badge badge-warning">Permohonan</a>
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