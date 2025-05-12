<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800"> <?= $subtitle; ?></h1>
    <?php if (validation_errors()) : ?>
        <div class="alert alert-danger" role="alert"><?= validation_errors(); ?></div>
        <?= $this->session->flashdata('message'); ?>
    <?php endif; ?>
    <h5>Status: <?php if ($user['is_active'] == 1) {
                    echo "Active";
                } else {
                    echo "Not Active! Please Update Profile Data Below";
                }
                ?></h5>

    <form action="<?= base_url('user/edit'); ?>" method="POST">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="inputEmail4">Nama Perusahaan</label>
                <input type="text" class="form-control" id="NamaPers" name="NamaPers" placeholder="Nama Perusahaan" value="<?= $user_perusahaan['NamaPers'] ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="inputPassword4">NPWP</label>
                <input type="text" class="form-control" id="npwp" name="npwp" placeholder="NPWP" value="<?= $user_perusahaan['npwp'] ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="inputAddress">Alamat</label>
            <input type="text" class="form-control" id="alamat" name="alamat" placeholder="Alamat" value="<?= $user_perusahaan['alamat'] ?>">
        </div>
        <div class="form-group">
            <label for="inputAddress2">Nomor Telp</label>
            <input type="text" class="form-control" id="telp" name="telp" placeholder="No Telp" value="<?= $user_perusahaan['telp'] ?>">
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="inputCity">Nama PIC</label>
                <input type="text" class="form-control" id="pic" name="pic" placeholder="PIC" value="<?= $user_perusahaan['pic'] ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="inputCity">Jabatan</label>
                <input type="text" class="form-control" id="jabatanPic" name="jabatanPic" placeholder="Jabatan" value="<?= $user_perusahaan['jabatanPic'] ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="inputCity">No Skep</label>
                <input type="text" class="form-control" id="NoSkep" name="NoSkep" placeholder="No Skep" value="<?= $user_perusahaan['NoSkep'] ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="inputCity">Quota</label>
                <input type="text" class="form-control" id="quota" name="quota" placeholder="Quota" value="<?= $user_perusahaan['quota'] ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Update Data</button>
    </form>

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->