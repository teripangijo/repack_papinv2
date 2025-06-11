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
                        <th scope="col">Nama Perusahaan</th>
                        <th scope="col">Alamat</th>
                        <th scope="col">NPWP</th>
                        <th scope="col">Nomor Skep</th>
                        <th scope="col">Tgl Skep</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; ?>
                    <?php foreach ($perusahaan as $p) : ?>
                        <tr>
                            <th scope="row"><?= $i; ?></th>
                            <td><?= $p['NamaPers']; ?></td>
                            <td><?= $p['alamat']; ?></td>
                            <td><?= $p['npwp']; ?></td>
                            <td><?= $p['NoSkep']; ?></td>
                            <td><?= $p['tgl_skep']; ?></td>
                            <td><a class="btn btn-success btn-sm" href="<?= base_url();?>petugas_administrasi/uploadproses/<?= $p['id'] ?>">Upload Dokumen</a></td>
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