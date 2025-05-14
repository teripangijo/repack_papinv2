<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Petugas extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('upload');
        $this->load->library('session'); 
        $this->load->helper(array('url', 'form'));
        if (!isset($this->db)) {
             $this->load->database();
        }
        
        $excluded_methods = ['force_change_password_page', 'edit_profil', 'logout']; // Izinkan logout dari mana saja
        $current_method = $this->router->fetch_method();

        if (!in_array($current_method, $excluded_methods)) {
            $this->_check_auth_petugas();
        } elseif (!$this->session->userdata('email') && $current_method != 'logout' && !($current_method == 'force_change_password_page' && $this->session->flashdata('message')) ) { 
             // Jika akses halaman yang dikecualikan tapi tidak ada session (kecuali logout atau baru redirect ke force_change_password)
             $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Sesi tidak valid atau telah berakhir. Silakan login kembali.</div>');
             redirect('auth');
             exit;
        }
    }

    private function _check_auth_petugas()
    {
        if (!$this->session->userdata('email')) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Please login to continue.</div>');
            redirect('auth');
            exit;
        }

        // Cek force_change_password PERTAMA
        if ($this->session->userdata('force_change_password') == 1 && 
            $this->router->fetch_method() != 'force_change_password_page' && 
            $this->router->fetch_method() != 'logout') { // Izinkan logout
            
            if ($this->session->userdata('role_id') == 3) { 
                 $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Untuk keamanan, Anda wajib mengganti password Anda terlebih dahulu.</div>');
                 redirect('petugas/force_change_password_page');
                 exit;
            }
        }

        if ($this->session->userdata('role_id') != 3) { 
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Access Denied! Anda tidak diotorisasi untuk mengakses area Petugas.</div>');
            $role_id_session = $this->session->userdata('role_id');
            if ($role_id_session == 1) redirect('admin');
            elseif ($role_id_session == 2) redirect('user');
            elseif ($role_id_session == 4) redirect('monitoring');
            else redirect('auth/blocked');
            exit;
        }
        // Pengecekan is_active bisa ditambahkan di sini jika Petugas bisa dinonaktifkan
        if ($this->session->userdata('is_active') == 0) {
           $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Akun Petugas Anda tidak aktif. Hubungi Administrator.</div>');
           // Arahkan ke logout agar session bersih dan tidak loop jika auth/blocked juga cek _check_auth
           $current_controller = $this->router->fetch_class();
           $current_method = $this->router->fetch_method();
           // Hindari loop redirect jika sudah di halaman auth atau logout
            if (!($current_controller == 'auth' || ($current_controller == 'petugas' && $current_method == 'logout'))) {
                 redirect('auth/logout');
                 exit;
            }
        }
    }

    public function index() // Dashboard Petugas
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Dashboard Petugas';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $petugas_id = $data['user']['id']; // Asumsi user.id adalah id_user di tabel petugas

        $this->db->where('petugas', $petugas_id);
        $this->db->where('status', '1'); 
        $data['jumlah_tugas_lhp'] = $this->db->count_all_results('user_permohonan');

        $this->db->where('id_petugas_pemeriksa', $petugas_id);
        $data['jumlah_lhp_selesai'] = $this->db->count_all_results('lhp');

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('petugas/dashboard_petugas_view', $data);
        $this->load->view('templates/footer');
    }

    public function daftar_pemeriksaan()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Pemeriksaan Ditugaskan';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $petugas_id = $data['user']['id'];

        $this->db->select('up.*, upr.NamaPers, u_pemohon.name as nama_pemohon');
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left'); // Untuk nama pemohon
        $this->db->where('up.petugas', $petugas_id); // Hanya permohonan yang ditugaskan ke petugas ini
        $this->db->where('up.status', '1'); // Status '1' = Penunjukan Pemeriksa (siap direkam LHP)
        $this->db->order_by('up.TglSuratTugas DESC, up.WaktuPenunjukanPetugas DESC');
        $data['daftar_tugas'] = $this->db->get()->result_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('petugas/daftar_pemeriksaan_view', $data); // Buat view ini
        $this->load->view('templates/footer');
    }

    public function rekam_lhp($id_permohonan = 0)
    {
        if ($id_permohonan == 0) {
            redirect('petugas/daftar_pemeriksaan');
            return;
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Perekaman Laporan Hasil Pemeriksaan (LHP)';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $petugas_id = $data['user']['id'];

        // Ambil data permohonan yang akan direkam LHP-nya
        // Pastikan permohonan ini memang ditugaskan ke petugas yang login dan statusnya sesuai
        $this->db->select('up.*, upr.NamaPers, upr.npwp, u_pemohon.name as nama_pemohon');
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $this->db->where('up.id', $id_permohonan);
        $this->db->where('up.petugas', $petugas_id);
        $this->db->where('up.status', '1'); // Hanya bisa rekam LHP jika statusnya 'Penunjukan Pemeriksa'
        $permohonan = $this->db->get()->row_array();

        if (!$permohonan) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan, tidak ditugaskan kepada Anda, atau sudah diproses LHP-nya.</div>');
            redirect('petugas/daftar_pemeriksaan');
            return;
        }
        $data['permohonan'] = $permohonan;

        // Cek apakah LHP sudah pernah direkam untuk permohonan ini
        $existing_lhp = $this->db->get_where('lhp', ['id_permohonan' => $id_permohonan])->row_array();
        if ($existing_lhp) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">LHP untuk permohonan ini sudah pernah direkam. Anda bisa mengeditnya jika diperlukan (fitur edit belum dibuat).</div>');
            // redirect('petugas/edit_lhp/' . $existing_lhp['id']); // Jika ada fitur edit LHP
            redirect('petugas/daftar_pemeriksaan');
            return;
        }

        // Validasi form LHP
        $this->form_validation->set_rules('tanggal_lhp', 'Tanggal LHP', 'required');
        $this->form_validation->set_rules('catatan_pemeriksaan', 'Catatan Hasil Pemeriksaan', 'required|trim');
        $this->form_validation->set_rules('jumlah_barang_benar', 'Jumlah Barang Sesuai/Benar', 'required|numeric|greater_than_equal_to[0]');
        // Validasi untuk file dokumentasi foto (bisa dibuat wajib atau opsional)
        // Jika wajib dan tidak ada file, form_validation->run() akan false jika ada rule callback.
        if (empty($_FILES['file_dokumentasi_foto']['name'])) {
             // $this->form_validation->set_rules('file_dokumentasi_foto', 'Dokumentasi Foto', 'required'); // Ini akan error jika file kosong
        }

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('petugas/form_rekam_lhp_view', $data); // Buat view ini
            $this->load->view('templates/footer');
        } else {
            $nama_file_dokumentasi = null;
            // Proses Upload File Dokumentasi Foto jika ada
            if (isset($_FILES['file_dokumentasi_foto']) && $_FILES['file_dokumentasi_foto']['error'] != UPLOAD_ERR_NO_FILE) {
                $upload_dir_doc = 'uploads/dokumentasi_lhp/';
                $upload_path_doc = FCPATH . $upload_dir_doc;
                if (!is_dir($upload_path_doc)) { @mkdir($upload_path_doc, 0777, true); }

                if (!is_writable($upload_path_doc)) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload error: Direktori Dokumentasi LHP tidak writable.</div>');
                    redirect('petugas/rekam_lhp/' . $id_permohonan);
                    return;
                }

                $config_doc['upload_path']   = $upload_path_doc;
                $config_doc['allowed_types'] = 'jpg|png|jpeg'; // Hanya gambar
                $config_doc['max_size']      = '2048'; // 2MB
                $config_doc['encrypt_name']  = TRUE;

                $this->upload->initialize($config_doc, TRUE); // Reset config untuk upload ini

                if ($this->upload->do_upload('file_dokumentasi_foto')) {
                    $nama_file_dokumentasi = $this->upload->data('file_name');
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload File Dokumentasi Foto Gagal: ' . $this->upload->display_errors('', '') . '</div>');
                    redirect('petugas/rekam_lhp/' . $id_permohonan);
                    return;
                }
            }
            // Jika file dokumentasi foto wajib:
            // elseif (empty($nama_file_dokumentasi)) {
            //    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">File Dokumentasi Foto wajib diupload.</div>');
            //    redirect('petugas/rekam_lhp/' . $id_permohonan);
            //    return;
            // }


            $data_lhp = [
                'id_permohonan' => $id_permohonan,
                'id_petugas_pemeriksa' => $petugas_id,
                'tanggal_lhp' => $this->input->post('tanggal_lhp'),
                'catatan_pemeriksaan' => $this->input->post('catatan_pemeriksaan'),
                'JumlahBenar' => (int)$this->input->post('jumlah_barang_benar'),
                'file_dokumentasi_foto' => $nama_file_dokumentasi,
                'waktu_rekam_lhp' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('lhp', $data_lhp);
            $id_lhp_baru = $this->db->insert_id();

            if ($id_lhp_baru) {
                // Update status permohonan menjadi 'LHP Direkam'
                $this->db->where('id', $id_permohonan);
                $this->db->update('user_permohonan', ['status' => '2']); // Status '2' = LHP Direkam

                // Logika Pemotongan Kuota (dipindahkan ke Admin::prosesSurat setelah LHP disetujui)
                // Jika pemotongan dilakukan langsung oleh petugas setelah rekam LHP:
                /*
                $jumlah_barang_disetujui_lhp = (int)$this->input->post('jumlah_barang_benar');
                if ($jumlah_barang_disetujui_lhp > 0) {
                    $perusahaan_pemohon = $this->db->get_where('user_perusahaan', ['id_pers' => $permohonan['id_pers']])->row_array();
                    if ($perusahaan_pemohon && isset($perusahaan_pemohon['remaining_quota'])) {
                        $sisa_kuota_lama = (int)$perusahaan_pemohon['remaining_quota'];
                        $sisa_kuota_baru = $sisa_kuota_lama - $jumlah_barang_disetujui_lhp;
                        if ($sisa_kuota_baru < 0) { $sisa_kuota_baru = 0; }
                        $this->db->where('id_pers', $permohonan['id_pers']);
                        $this->db->update('user_perusahaan', ['remaining_quota' => $sisa_kuota_baru]);
                        log_message('info', 'Quota updated by Petugas for id_pers: ' . $permohonan['id_pers'] . '. LHP Used: ' . $jumlah_barang_disetujui_lhp . '. Remaining: ' . $sisa_kuota_baru);
                    }
                }
                */

                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">LHP berhasil direkam untuk permohonan ID ' . $id_permohonan . '. Status permohonan diubah menjadi "LHP Direkam".</div>');
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal menyimpan data LHP. Silakan coba lagi.</div>');
            }
            redirect('petugas/daftar_pemeriksaan');
        }
    }

    // Tambahkan method untuk force_change_password_page jika diperlukan
    // Di controllers/User.php (atau Petugas.php)
    public function force_change_password_page()
    {
        // Pastikan user login dan memang statusnya force_change_password = 1 dan role-nya Petugas
        if (!$this->session->userdata('email') || 
            $this->session->userdata('force_change_password') != 1 ||
            $this->session->userdata('role_id') != 3) { // Pastikan ini untuk role Petugas
            
            // Jika tidak memenuhi syarat, arahkan ke dashboard Petugas (jika sudah login) atau ke auth
            if ($this->session->userdata('role_id') == 3) {
                redirect('petugas/index'); 
            } else {
                redirect('auth/logout'); // Logout paksa jika role tidak sesuai
            }
            return;
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Wajib Ganti Password (Petugas)';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->form_validation->set_rules('new_password', 'Password Baru', 'required|trim|min_length[6]|matches[confirm_new_password]', [
            'min_length' => 'Password minimal 6 karakter.',
            'matches'    => 'Konfirmasi password tidak cocok.'
        ]);
        $this->form_validation->set_rules('confirm_new_password', 'Konfirmasi Password Baru', 'required|trim');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data); 
            $this->load->view('templates/topbar', $data);
            $this->load->view('petugas/form_force_change_password', $data); // Buat view ini
            $this->load->view('templates/footer');
        } else {
            $new_password_hash = password_hash($this->input->post('new_password'), PASSWORD_DEFAULT);
            $user_id = $data['user']['id'];

            $update_data_db = [
                'password' => $new_password_hash,
                'force_change_password' => 0 // Set kembali ke 0 di database
            ];

            $this->db->where('id', $user_id);
            $this->db->update('user', $update_data_db);

            // Update juga data 'force_change_password' di session agar tidak redirect lagi
            $this->session->set_userdata('force_change_password', 0);

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Password Anda telah berhasil diubah. Selamat datang di dashboard Anda.</div>');
            redirect('petugas/index'); // Arahkan ke dashboard Petugas
        }
    }
    
    public function edit_profil()
    {
        // Pengecekan otentikasi dan otorisasi khusus untuk method ini
        $this->_check_auth_petugas(); // Panggil di awal method jika tidak di __construct untuk method ini

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Profil Saya';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $user_id = $data['user']['id'];

        // Ambil data detail petugas dari tabel 'petugas'
        // Pastikan tabel 'petugas' memiliki kolom 'id_user' yang merujuk ke 'user.id'
        if ($this->db->field_exists('id_user', 'petugas')) {
            $data['petugas_detail'] = $this->db->get_where('petugas', ['id_user' => $user_id])->row_array();
        } else {
            $data['petugas_detail'] = null; // Atau handle error jika tabel/kolom tidak ada
            log_message('error', 'Kolom id_user tidak ditemukan di tabel petugas untuk edit_profil Petugas.');
        }

        // Validasi hanya untuk upload foto profil baru
        // Tidak ada validasi untuk nama, NIP, atau jabatan karena tidak bisa diubah oleh petugas
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['name'] != '' && $_FILES['profile_image']['error'] != UPLOAD_ERR_NO_FILE) {
            // Aturan validasi file bisa dibuat lebih spesifik jika perlu (misal callback)
            // Untuk sekarang, kita akan handle langsung di proses upload
        }

        if ($this->input->method() === 'post') { // Cek jika form disubmit
            // Hanya proses jika ada file yang diupload
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] != UPLOAD_ERR_NO_FILE) {
                
                $upload_dir_profile = 'uploads/profile_images/'; // Ganti nama folder jika perlu, misal 'profile_petugas'
                $upload_path_profile = FCPATH . $upload_dir_profile;
                if (!is_dir($upload_path_profile)) { 
                    if (!@mkdir($upload_path_profile, 0777, true)) {
                        $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal membuat direktori upload foto profil.</div>');
                        redirect('petugas/edit_profil');
                        return;
                    }
                }

                if (!is_writable($upload_path_profile)) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload error: Direktori foto profil tidak writable.</div>');
                    redirect('petugas/edit_profil');
                    return;
                }

                $config_profile['upload_path']   = $upload_path_profile;
                $config_profile['allowed_types'] = 'jpg|png|jpeg|gif';
                $config_profile['max_size']      = '2048'; // 2MB
                $config_profile['max_width']     = '1024';
                $config_profile['max_height']    = '1024';
                $config_profile['encrypt_name']  = TRUE;
                
                $this->upload->initialize($config_profile, TRUE); 

                if ($this->upload->do_upload('profile_image')) {
                    $old_image = $data['user']['image'];
                    if ($old_image != 'default.jpg' && !empty($old_image) && file_exists($upload_path_profile . $old_image)) {
                        @unlink($upload_path_profile . $old_image);
                    }
                    $new_image_name = $this->upload->data('file_name');
                    
                    // Update foto profil di tabel user
                    $this->db->where('id', $user_id);
                    $this->db->update('user', ['image' => $new_image_name]);

                    // Update session jika Anda menyimpan nama file gambar di session untuk topbar
                    $this->session->set_userdata('user_image', $new_image_name); // Ganti 'user_image' dengan key session Anda

                    $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Foto profil berhasil diupdate.</div>');
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload Foto Profil Gagal: ' . $this->upload->display_errors('', '') . '</div>');
                }
                redirect('petugas/edit_profil'); // Redirect untuk refresh data dan pesan
                return;
            } else {
                // Tidak ada file yang diupload, mungkin hanya refresh halaman atau submit tanpa file
                // Anda bisa tambahkan pesan jika perlu, atau biarkan saja
                // $this->session->set_flashdata('message', '<div class="alert alert-info" role="alert">Tidak ada foto profil yang dipilih untuk diupload.</div>');
            }
        }

        // Ambil ulang data user setelah kemungkinan update foto untuk ditampilkan di view
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('petugas/form_edit_profil_petugas', $data); // Buat view ini
        $this->load->view('templates/footer');
    }
}