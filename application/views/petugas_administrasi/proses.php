i<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800"> <?= $subtitle; ?></h1>
    <?= $this->session->flashdata('message'); ?>
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

    <div class="col-lg">

        <!-- Default Card Example -->
        <div class="card mb-4">
            <div class="card-header m-0 font-weight-bold text-primary">
                Form Permohonan
            </div>
            <div class="card-body">
                <form action="<?= base_url() ?>admin/proses/<?= $permohonan['id']; ?>" method="POST">
                    <div class="row">
                        <div class="col">
                            <label>Nama Perusahaan</label>
                            <input type="text" class="form-control" id="NamaPers" name="NamaPers" value="<?= $user_perusahaan['NamaPers'] ?>" disabled>
                        </div>
                        <div class="col">
                            <label>Alamat</label>
                            <input type="text" class="form-control" id="alamat" name="alamat" value="<?= $user_perusahaan['alamat'] ?>" disabled>
                        </div>
                    </div>
                    </br>
                    <div class="row">
                        <div class="col">
                            <label>Nomor Surat</label>
                            <input type="text" class="form-control" id="nomorSurat" name="nomorSurat" placeholder="Nomor Surat" value="<?= $permohonan['nomorSurat']; ?>" disabled>
                        </div>
                        <div class="col">
                            <label>Tanggal Surat</label>
                            <input id="TglSurat" name="TglSurat" value="<?= $permohonan['TglSurat']; ?>" placeholder="Tanggal Surat" disabled>
                        </div>
                        <div class="col">
                            <label>Perihal</label>
                            <input type="text" class="form-control" id="Perihal" name="Perihal" value="<?= $permohonan['Perihal']; ?>" placeholder="Perihal" disabled />
                        </div>
                    </div>
                    </br>
                    <div class="row">
                        <div class="col">
                            <label>Nama / Jenis Barang</label>
                            <input type="text" class="form-control" id="NamaBarang" name="NamaBarang" value="<?= $permohonan['NamaBarang']; ?>" placeholder="Nama / Jenis Barang" disabled>
                        </div>
                        <div class="col">
                            <label>Jumlah Barang</label>
                            <input type="text" class="form-control" id="JumlahBarang" name="JumlahBarang" value="<?= $permohonan['JumlahBarang']; ?>" placeholder="Jumlah Barang" disabled>
                        </div>
                        <div class="col">
                            <label>Negara Asal Barang</label>
                            <input type="text" class="form-control" id="NegaraAsal" name="NegaraAsal" value="<?= $permohonan['NegaraAsal']; ?>" placeholder="Negara Asal barang" disabled>
                        </div>
                    </div>
                    </br>
                    <div class="row">
                        <div class="col">
                            <label>Nama Kapal</label>
                            <input type="text" class="form-control" id="NamaKapal" name="NamaKapal" value="<?= $permohonan['NamaKapal']; ?>" placeholder="Nama Kapal" disabled>
                        </div>
                        <div class="col">
                            <label>No Voyage</label>
                            <input type="text" class="form-control" id="noVoyage" name="noVoyage" value="<?= $permohonan['noVoyage']; ?>" placeholder="No Voyage" disabled>
                        </div>
                        <div class="col">
                            <label>No SKEP</label>
                            <input type="text" class="form-control" id="NoSkep" name="NoSkep" value="<?= $user_perusahaan['NoSkep'] ?>" disabled>
                        </div>
                    </div>
                    </br>
                    <div class="row">
                        <div class="col">
                            <label>Tanggal Kegiatan</label>
                            <input id="TglKedatangan" name="TglKedatangan" value="<?= $permohonan['TglKedatangan']; ?>" placeholder="Tanggal Kegiatan" disabled>
                        </div>
                        <div class="col">
                            <label>Tanggal Bongkar</label>
                            <input id="TglBongkar" name="TglBongkar" value="<?= $permohonan['TglBongkar']; ?>" placeholder="Tanggal Bongkar" disabled>
                        </div>
                        <div class="col">
                            <label>Lokasi Bongkar</label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi" value="<?= $permohonan['lokasi']; ?>" placeholder="Lokasi Bongkar" disabled>
                        </div>
                    </div>
                    </br>
                    <div class="row">
                        <div class="form-group col">
                            <label for="petugas">Nama Petugas</label>
                            <select class="form-control" name="petugas" id="">
                                <option value="">- Pilih -</option>
                                
                                <option value="8" <?= set_value('petugas') == 'Harso Haryadi' ? "selected" : null ?>>Harso Haryadi
                                </option>
                                <option value="7" <?= set_value('petugas') == 'Septian Budi Subroto' ? "selected" : null ?>>Septian Budi Subroto
                                </option>
                                <option value="6" <?= set_value('petugas') == 'Bayu Raharjo Putra' ? "selected" : null ?>>Bayu Raharjo Putra
                                </option>
                                <option value="9" <?= set_value('petugas') == 'Ismail Martawinata' ? "selected" : null ?>>Ismail Martawinata
                                </option>
                                <option value="10" <?= set_value('petugas') == 'Jihad Fadhil Mudhoffar' ? "selected" : null ?>>Jihad Fadhil Mudhoffar
                                </option>
                                <option value="11" <?= set_value('petugas') == 'Kristian Jimmy Hamonangan' ? "selected" : null ?>>Kristian Jimmy Hamonangan
                                </option>
                            </select>
                        </div>
                    </div>
                    </br>
                    <button type="submit" class="btn btn-success">Simpan</button>
                    <button type="button" href="" class="btn btn-primary">Preview</button>
            </div>
        </div>



        <!-- Basic Card Example -->


    </div>

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->

<script>
    $('#TglSurat').datepicker({
        uiLibrary: 'bootstrap4'
    });
    $('#TglKedatangan').datepicker({
        uiLibrary: 'bootstrap4'
    });
    $('#TglBongkar').datepicker({
        uiLibrary: 'bootstrap4'
    });
</script>