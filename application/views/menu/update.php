<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800"> <?= $subtitle; ?></h1>



    <div class="row">
        <div class="col-lg-6">

            <?= form_error('menu', '<div class="alert alert-danger " role="alert">', '</div>') ?>
            <?= $this->session->flashdata('message'); ?>
            <form action="<?= base_url(); ?>menu/update_menu/<?= $id ?>" method="POST">
                <div class="form-group">
                    <input type="text" class="form-control" id="update_menu" name="update_menu" value="<?= $menu['menu'] ?>" aria-describedby="emailHelp" >
                    <input type="text" style="display:none" class="form-control" id="id" name="id" value="<?= $id ?>" aria-describedby="emailHelp" placeholder="<?= $id ?>">
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="<?= base_url('menu'); ?>" class="btn btn-primary">Cancel</a>
            </form>



        </div>
    </div>

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->

<!-- Button trigger modal -->