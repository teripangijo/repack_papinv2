<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
            <?php // Ini adalah AKHIR dari konten spesifik halaman (misalnya dari <div class="container-fluid"> di view_konten.php) ?>
            <?php // SEHARUSNYA TIDAK ADA TAG PENUTUP DIV DI SINI JIKA VIEW KONTEN SUDAH MENUTUP DIV-NYA SENDIRI ?>
            <?php // View konten (misal, edit_profil_view.php) harusnya ditutup dengan </div> untuk container-fluid-nya. ?>

            </div>
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Bea Cukai Pangkalpinang <?= date('Y') ?></span>
                    </div>
                </div>
            </footer>
            </div>
        </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Yakin ingin Keluar?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Pilih "Logout" di bawah jika Anda siap untuk mengakhiri sesi Anda saat ini.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <a class="btn btn-primary" href="<?= site_url('auth/logout'); // Pastikan URL logout benar ?>">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>

    <script src="<?= base_url('assets/vendor/jquery-easing/jquery.easing.min.js'); ?>"></script>

    <script src="<?= base_url('assets/js/sb-admin-2.min.js'); ?>"></script>

    <script src="https://unpkg.com/gijgo@1.9.13/js/gijgo.min.js" type="text/javascript"></script>
    <script src="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.js'); ?>"></script>

    <?php // Skrip AJAX custom Anda (contoh) ?>
    <?php if(isset($user['role_id']) && $user['role_id'] == 1 && $this->uri->segment(1) == 'admin' && $this->uri->segment(2) == 'roleaccess'): ?>
    <script>
        $(document).ready(function(){
            // ... (script AJAX Anda yang sudah ada) ...
        });
    </script>
    <?php endif; ?>

</body>
</html>