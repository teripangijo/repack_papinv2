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
                        <th scope="col">Waktu Selesai</th>
                        <th scope="col">Nama Petugas</th>
                        <th scope="col">Status</th>
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
                            <td><?= $p['time_selesai']; ?></td>
                            <td><?php if ($p['petugas'] == 1) {
                                    echo "Suci Dwi Anggraieni";
                                } elseif ($p['petugas'] == 2) {
                                    echo "Bayu Raharjo Putra";
                                } elseif ($p['petugas'] == 3) {
                                    echo "Zulkifli";
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