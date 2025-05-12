<?php
// Definisikan nilai default untuk semua field dari user_perusahaan
$default_nama_pers = '';
$default_npwp = '';
$default_alamat = '';
$default_telp = '';
$default_pic = '';
$default_jabatan_pic = '';
$default_no_skep = '';
$default_quota = '';
$existing_ttd_file = null; 

// Jika $user_perusahaan ada dan merupakan array, isi nilai default dari sana
if (isset($user_perusahaan) && is_array($user_perusahaan) && !empty($user_perusahaan)) {
    $default_nama_pers = isset($user_perusahaan['NamaPers']) ? $user_perusahaan['NamaPers'] : '';
    $default_npwp = isset($user_perusahaan['npwp']) ? $user_perusahaan['npwp'] : '';
    $default_alamat = isset($user_perusahaan['alamat']) ? $user_perusahaan['alamat'] : '';
    $default_telp = isset($user_perusahaan['telp']) ? $user_perusahaan['telp'] : '';
    $default_pic = isset($user_perusahaan['pic']) ? $user_perusahaan['pic'] : '';
    $default_jabatan_pic = isset($user_perusahaan['jabatanPic']) ? $user_perusahaan['jabatanPic'] : '';
    $default_no_skep = isset($user_perusahaan['NoSkep']) ? $user_perusahaan['NoSkep'] : '';
    $default_quota = isset($user_perusahaan['quota']) ? $user_perusahaan['quota'] : '';
    $existing_ttd_file = isset($user_perusahaan['ttd']) ? $user_perusahaan['ttd'] : null; 
}

// Ambil data user (termasuk nama file gambar saat ini)
$current_user_image = isset($user['image']) ? $user['image'] : 'default.jpg'; // Ambil dari $user

?>
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800"> <?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Edit Profile'; ?></h1>

    <?php
        // Menampilkan pesan flashdata (sukses atau error umum dari controller)
        if ($this->session->flashdata('message')) {
            echo $this->session->flashdata('message');
        }
        // Menampilkan error upload spesifik jika ada
        if (isset($upload_error)) {
             echo '<div class="alert alert-danger" role="alert">' . $upload_error . '</div>';
        }
    ?>

    <?php // Menampilkan Status dalam Box Alert ?>
    <?php if (isset($user['is_active'])) : ?>
        <?php if ($user['is_active'] == 1) : ?>
            <div class="alert alert-success" role="alert">
                <h5 class="alert-heading mb-0">Status: Active</h5>
            </div>
        <?php else : ?>
            <div class="alert alert-warning" role="alert">
                 <h5 class="alert-heading">Status: Not Active!</h5>
                 <p class="mb-0">Please complete your profile data below to activate your account.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <hr>

    <?php // PENTING: Gunakan form_open_multipart karena ada input file ?>
    <?php echo form_open_multipart(base_url('user/edit')); ?>

        <?php // Bagian Edit Info Dasar Pengguna (Nama & Gambar) ?>
        <div class="row mb-3">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" class="form-control <?= (form_error('name')) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?= set_value('name', isset($user['name']) ? $user['name'] : ''); ?>" readonly> <?php // Nama mungkin tidak boleh diedit? Jika boleh, hapus readonly ?>
                    <?= form_error('name', '<small class="text-danger pl-1">', '</small>'); ?>
                </div>
                 <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= isset($user['email']) ? $user['email'] : ''; ?>" readonly> <?php // Email biasanya tidak boleh diedit ?>
                </div>
            </div>
            <div class="col-md-4 text-center">
                 <label>Gambar Profil Saat Ini</label><br>
                 <img src="<?= base_url('uploads/kop/') . htmlspecialchars($current_user_image); ?>" class="img-thumbnail mb-2" alt="Profile Image" style="max-width: 150px; max-height: 150px;">
                 <div class="custom-file">
                     <input type="file" class="custom-file-input <?= (form_error('profile_image')) ? 'is-invalid' : ''; ?>" id="profile_image" name="profile_image" aria-describedby="profileImageHelp">
                     <label class="custom-file-label text-left" for="profile_image">Ganti Gambar...</label>
                     <?= form_error('profile_image', '<small class="text-danger">', '</small>'); ?>
                 </div>
                 <small id="profileImageHelp" class="form-text text-muted">Format: jpg, png, gif. Max: 1MB.</small>
            </div>
        </div>
        <hr>

        <h5 class="text-gray-800 mb-3">Informasi Perusahaan</h5>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="NamaPers">Nama Perusahaan <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?= (form_error('NamaPers')) ? 'is-invalid' : ''; ?>" id="NamaPers" name="NamaPers" placeholder="Nama Perusahaan" value="<?= set_value('NamaPers', $default_nama_pers) ?>" required>
                <?= form_error('NamaPers', '<small class="text-danger pl-1">', '</small>'); ?>
            </div>
            <div class="form-group col-md-6">
                <label for="npwp">NPWP <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?= (form_error('npwp')) ? 'is-invalid' : ''; ?>" id="npwp" name="npwp" placeholder="NPWP" value="<?= set_value('npwp', $default_npwp) ?>" required>
                 <?= form_error('npwp', '<small class="text-danger pl-1">', '</small>'); ?>
            </div>
        </div>
        <div class="form-group">
            <label for="alamat">Alamat <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?= (form_error('alamat')) ? 'is-invalid' : ''; ?>" id="alamat" name="alamat" placeholder="Alamat" value="<?= set_value('alamat', $default_alamat) ?>" required>
             <?= form_error('alamat', '<small class="text-danger pl-1">', '</small>'); ?>
        </div>
        <div class="form-group">
            <label for="telp">Nomor Telp <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?= (form_error('telp')) ? 'is-invalid' : ''; ?>" id="telp" name="telp" placeholder="No Telp" value="<?= set_value('telp', $default_telp) ?>" required>
             <?= form_error('telp', '<small class="text-danger pl-1">', '</small>'); ?>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="pic">Nama PIC <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?= (form_error('pic')) ? 'is-invalid' : ''; ?>" id="pic" name="pic" placeholder="PIC" value="<?= set_value('pic', $default_pic) ?>" required>
                 <?= form_error('pic', '<small class="text-danger pl-1">', '</small>'); ?>
            </div>
            <div class="form-group col-md-6">
                <label for="jabatanPic">Jabatan PIC <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?= (form_error('jabatanPic')) ? 'is-invalid' : ''; ?>" id="jabatanPic" name="jabatanPic" placeholder="Jabatan" value="<?= set_value('jabatanPic', $default_jabatan_pic) ?>" required>
                 <?= form_error('jabatanPic', '<small class="text-danger pl-1">', '</small>'); ?>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="NoSkep">No Skep</label>
                <input type="text" class="form-control <?= (form_error('NoSkep')) ? 'is-invalid' : ''; ?>" id="NoSkep" name="NoSkep" placeholder="No Skep (Jika Ada)" value="<?= set_value('NoSkep', $default_no_skep) ?>">
                 <?= form_error('NoSkep', '<small class="text-danger pl-1">', '</small>'); ?>
            </div>
            <div class="form-group col-md-6">
                <label for="quota">Quota</label>
                <input type="number" class="form-control <?= (form_error('quota')) ? 'is-invalid' : ''; ?>" id="quota" name="quota" placeholder="Quota (Jika Ada)" value="<?= set_value('quota', $default_quota) ?>">
                 <?= form_error('quota', '<small class="text-danger pl-1">', '</small>'); ?>
            </div>
        </div>

        <hr>
        <h5 class="text-gray-800 mb-3">Upload Dokumen Perusahaan</h5>

        <div class="form-group">
            <label for="ttd">
                Upload File Tanda Tangan PIC
                <?php if (empty($user_perusahaan)): // Jika aktivasi, wajib ?>
                    <span class="text-danger">*</span>
                <?php else: // Jika edit, opsional ?>
                    (Kosongkan jika tidak ingin mengubah)
                <?php endif; ?>
            </label>
            <div class="custom-file">
                 <input type="file" class="custom-file-input <?= (form_error('ttd')) ? 'is-invalid' : ''; ?>" id="ttd" name="ttd" aria-describedby="ttdHelp">
                 <label class="custom-file-label" for="ttd">Choose file...</label>
                 <div class="invalid-feedback"><?= form_error('ttd'); ?></div>
            </div>
            <small id="ttdHelp" class="form-text text-muted">Format: jpg, png, pdf. Max: 1MB.</small>
            <?php if ($existing_ttd_file): ?>
                <small class="form-text text-muted">File saat ini: <?= htmlspecialchars($existing_ttd_file); ?></small>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary btn-user btn-block mt-4">
             <?php echo (empty($user_perusahaan)) ? 'Simpan Data & Aktifkan Akun' : 'Update Data Profil & Perusahaan'; ?>
        </button>
    <?php echo form_close(); ?>

</div>
</div>
<?php // Tambahkan script kecil untuk menampilkan nama file di input file custom Bootstrap ?>
<script>
// Script untuk menampilkan nama file pada custom file input Bootstrap 4
document.addEventListener('DOMContentLoaded', function () {
    var fileInputs = document.querySelectorAll('.custom-file-input');
    Array.prototype.forEach.call(fileInputs, function(input) {
        input.addEventListener('change', function (e) {
            // Cek apakah file dipilih
            if (e.target.files.length > 0) {
                var fileName = e.target.files[0].name;
                var nextSibling = e.target.nextElementSibling;
                // Pastikan nextSibling ada dan merupakan label
                if (nextSibling && nextSibling.classList.contains('custom-file-label')) {
                    nextSibling.innerText = fileName;
                }
            } else {
                // Jika tidak ada file dipilih (misalnya dibatalkan), kembalikan ke teks default
                 var nextSibling = e.target.nextElementSibling;
                 if (nextSibling && nextSibling.classList.contains('custom-file-label')) {
                    nextSibling.innerText = 'Choose file...';
                }
            }
        });
    });
});
</script>
