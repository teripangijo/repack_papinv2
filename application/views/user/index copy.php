<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <?= form_error('menu', '<div class="alert alert-danger " role="alert">', '</div>') ?>
    <?= $this->session->flashdata('message'); ?>
    <h1 class="h3  text-gray-800"> <?= $subtitle; ?></h1>
    <h5>Status: <?php if ($user['is_active'] == 1) {
                    echo "Active";
                } else {
                    echo "Not Active! Please Update Profile Data Below";
                } ?></h5>
    <!-- <div class="card " style="max-width: 540px;">
        <div class="row g-0">
            <div class="col-md-4">
                <img src="<?= base_url('assets') ?>/img/<?= $user['image'] ?>" alt="...">
            </div>
            <div class="col-md-8">
                <div class="card-body">
                    <h5 class="card-title"><?= $user['name'] ?></h5>
                    <p class="card-text"><?= $user['email'] ?></p>
                    <p class="card-text">Member since <?= date('d F Y', $user['date_created']); ?></p>
                </div>
            </div>
        </div>
    </div> -->
    <form>
        <fieldset disabled>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="inputEmail4">Nama Perusahaan</label>
                    <input type="text" class="form-control is-valid text-dark" id="NamaPers" name="NamaPers" placeholder="<?= $user_perusahaan['NamaPers'] ?>" value="<?= $user_perusahaan['NamaPers'] ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="inputPassword4">NPWP</label>
                    <input type="text" class="form-control is-valid text-dark" id="npwp" name="npwp" placeholder="<?= $user_perusahaan['npwp'] ?>" value="<?= $user_perusahaan['npwp'] ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="inputAddress">Alamat</label>
                <input type="text" class="form-control is-valid text-dark" id="alamat" name="alamat" placeholder="<?= $user_perusahaan['alamat'] ?>" value="<?= $user_perusahaan['alamat'] ?>">
            </div>
            <div class="form-group">
                <label for="inputAddress2">Nomor Telp</label>
                <input type="text" class="form-control is-valid text-dark" id="telp" name="telp" placeholder="<?= $user_perusahaan['telp'] ?>" value="<?= $user_perusahaan['telp'] ?>">
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="inputCity">Nama PIC</label>
                    <input type="text" class="form-control is-valid text-dark" id="pic" name="pic" placeholder="<?= $user_perusahaan['pic'] ?>" value="<?= $user_perusahaan['pic'] ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="inputCity">Jabatan</label>
                    <input type="text" class="form-control is-valid text-dark" id="jabatanPic" name="jabatanPic" placeholder="<?= $user_perusahaan['jabatanPic'] ?>" value="<?= $user_perusahaan['jabatanPic'] ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="inputCity">No Skep</label>
                    <input type="text" class="form-control is-valid text-dark" id="NoSkep" name="NoSkep" placeholder="No Skep" value="<?= $user_perusahaan['NoSkep'] ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="inputCity">Quota</label>
                    <input type="text" class="form-control is-valid text-dark" id="quota" name="quota" placeholder="Quota" value="<?= $user_perusahaan['quota'] ?>">
                </div>
            </div>
        </fieldset>
        <a href="<?= base_url('user/edit'); ?>" class="btn btn-primary">Update Data</a>
    </form>

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->