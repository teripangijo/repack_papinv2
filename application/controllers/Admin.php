<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_loggedin(); // Helper untuk otentikasi
        if ($this->session->userdata('role_id') != 1) { // Hanya Admin (role_id 1) yang boleh akses
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Access Denied! You are not authorized to access this page.</div>');
            redirect('auth/blocked');
            exit; 
        }
        $this->load->helper(array('form', 'url', 'repack_helper', 'download')); //
        $this->load->library('form_validation'); //
        $this->load->library('upload'); //
        $this->load->library('session'); //
        if (!isset($this->db)) {
            $this->load->database(); //
        }
    }

    public function edit_profil()
    {
        // Tidak perlu _check_auth_petugas() karena otentikasi admin sudah ada di __construct()

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Profil Saya';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $user_id = $data['user']['id'];

        // Bagian untuk 'petugas_detail' tidak diperlukan untuk Admin, kecuali Admin juga memiliki entri detail di tabel lain.
        // Jika Admin hanya menggunakan tabel 'user', maka baris di bawah ini bisa dihilangkan.
        // Jika ada tabel detail khusus admin, misalnya 'admin_details', Anda bisa menambahkannya di sini.
        // Untuk saat ini, kita asumsikan tidak ada tabel detail tambahan.

        if ($this->input->method() === 'post') {
            // Proses update nama atau data lainnya (jika ada di form)
            $update_data_user = [];
            $name_input = $this->input->post('name', true); // Ambil input nama dari form

            // Hanya update nama jika berbeda dan tidak kosong
            if (!empty($name_input) && $name_input !== $data['user']['name']) {
                $update_data_user['name'] = htmlspecialchars($name_input);
            }
            
            // Tambahkan validasi jika NIP/Email diubah (mirip dengan edit_user)
            $current_login_identifier = $data['user']['email'];
            $new_login_identifier = $this->input->post('login_identifier', true); // Field di form harus 'login_identifier'

            if (!empty($new_login_identifier) && $new_login_identifier !== $current_login_identifier) {
                // Admin (role_id 1) biasanya menggunakan email
                // Anda bisa menambahkan validasi is_unique jika memang diizinkan untuk diubah
                $this->form_validation->set_rules('login_identifier', 'Email Login', 'trim|required|valid_email|is_unique[user.email.id.'.$user_id.']');
                if ($this->form_validation->run() == TRUE) {
                     $update_data_user['email'] = htmlspecialchars($new_login_identifier);
                } else {
                    // $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">' . validation_errors() . '</div>');
                    // redirect('admin/edit_profil');
                    // return;
                }
            }


            // Jika ada data yang akan diupdate (selain foto)
            if (!empty($update_data_user)) {
                $this->db->where('id', $user_id);
                $this->db->update('user', $update_data_user);
                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Profil berhasil diupdate.</div>');
                 // Update session jika nama atau email (login identifier) berubah
                if (isset($update_data_user['name'])) {
                    $this->session->set_userdata('name', $update_data_user['name']);
                }
                if (isset($update_data_user['email'])) {
                    $this->session->set_userdata('email', $update_data_user['email']);
                }
            }


            // Proses upload foto profil (jika ada file yang diupload)
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] != UPLOAD_ERR_NO_FILE) {
                $upload_dir_profile = 'uploads/profile_images/';
                $upload_path_profile = FCPATH . $upload_dir_profile;

                if (!is_dir($upload_path_profile)) {
                    if (!@mkdir($upload_path_profile, 0777, true)) {
                        $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal membuat direktori upload foto profil.</div>');
                        redirect('admin/edit_profil');
                        return;
                    }
                }

                if (!is_writable($upload_path_profile)) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload error: Direktori foto profil tidak writable.</div>');
                    redirect('admin/edit_profil');
                    return;
                }

                $config_profile['upload_path']   = $upload_path_profile;
                $config_profile['allowed_types'] = 'jpg|png|jpeg|gif';
                $config_profile['max_size']      = '2048'; // 2MB
                $config_profile['max_width']     = '1024';
                $config_profile['max_height']    = '1024';
                $config_profile['encrypt_name']  = TRUE;

                // Inisialisasi library upload dengan konfigurasi
                // Nama instance 'profile_upload' untuk menghindari konflik jika ada upload lain
                $this->load->library('upload', $config_profile, 'profile_upload');
                $this->profile_upload->initialize($config_profile);


                if ($this->profile_upload->do_upload('profile_image')) {
                    $old_image = $data['user']['image'];
                    if ($old_image != 'default.jpg' && !empty($old_image) && file_exists($upload_path_profile . $old_image)) {
                        @unlink($upload_path_profile . $old_image);
                    }

                    $new_image_name = $this->profile_upload->data('file_name');
                    $this->db->where('id', $user_id);
                    $this->db->update('user', ['image' => $new_image_name]);

                    // Update session user_image
                    $this->session->set_userdata('user_image', $new_image_name);
                    // Tambahkan atau gabungkan dengan flashdata sebelumnya
                    $current_flash = $this->session->flashdata('message');
                    $this->session->set_flashdata('message', ($current_flash ? $current_flash . '<br>' : '') . '<div class="alert alert-success" role="alert">Foto profil berhasil diupdate.</div>');

                } else {
                     // Tambahkan atau gabungkan dengan flashdata sebelumnya
                    $current_flash = $this->session->flashdata('message');
                    $this->session->set_flashdata('message', ($current_flash ? $current_flash . '<br>' : '') .'<div class="alert alert-danger" role="alert">Upload Foto Profil Gagal: ' . $this->profile_upload->display_errors('', '') . '</div>');
                }
            }
            // Redirect setelah semua proses POST selesai
            redirect('admin/edit_profil');
            return; // Penting untuk menghentikan eksekusi lebih lanjut setelah redirect
        }

        // Load ulang data user untuk ditampilkan di view setelah potensi update atau jika bukan POST request
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        // Pastikan Anda membuat view ini: application/views/admin/form_edit_profil_admin.php
        $this->load->view('admin/form_edit_profil_admin', $data);
        $this->load->view('templates/footer');
    }

    public function index()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Admin Dashboard';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        
        $data['total_users'] = $this->db->where_in('role_id', [2,3,4])->count_all_results('user'); // Pengguna Jasa, Petugas, Monitoring
        $data['pending_permohonan'] = $this->db->where_in('status', ['0', '1', '2', '5'])->count_all_results('user_permohonan');
        $data['pending_kuota_requests'] = $this->db->where('status', 'pending')->count_all_results('user_pengajuan_kuota');

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/index', $data);
        $this->load->view('templates/footer');
    }

    // Di application/controllers/Admin.php

    public function monitoring_kuota()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Monitoring Kuota Perusahaan (per Jenis Barang)';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        // Query untuk mengambil data perusahaan dan agregat kuota barang mereka
        $this->db->select('
            up.id_pers,
            up.NamaPers,
            u.email as user_email,
            (SELECT GROUP_CONCAT(DISTINCT ukb.nomor_skep_asal SEPARATOR ", ")
            FROM user_kuota_barang ukb
            WHERE ukb.id_pers = up.id_pers AND ukb.status_kuota_barang = "active"
            ) as list_skep_aktif,
            (SELECT SUM(ukb.initial_quota_barang)
            FROM user_kuota_barang ukb
            WHERE ukb.id_pers = up.id_pers
            ) as total_initial_kuota_barang,
            (SELECT SUM(ukb.remaining_quota_barang)
            FROM user_kuota_barang ukb
            WHERE ukb.id_pers = up.id_pers
            ) as total_remaining_kuota_barang
        ');
        $this->db->from('user_perusahaan up');
        $this->db->join('user u', 'up.id_pers = u.id', 'left');
    
        $this->db->order_by('up.NamaPers', 'ASC');
        $data['monitoring_data'] = $this->db->get()->result_array();

        log_message('debug', 'ADMIN MONITORING KUOTA - Query: ' . $this->db->last_query());
        log_message('debug', 'ADMIN MONITORING KUOTA - Data: ' . print_r($data['monitoring_data'], true));

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/monitoring_kuota_view', $data);
        $this->load->view('templates/footer');
    }

    public function role()
    {
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Role Management';
        $data['role'] = $this->db->get('user_role')->result_array();

        $this->form_validation->set_rules('role', 'Role', 'required|trim|is_unique[user_role.role]');
        if($this->form_validation->run() == false){
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/role', $data);
            $this->load->view('templates/footer');
        } else {
            $this->db->insert('user_role', ['role' => $this->input->post('role')]);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">New role added!</div>');
            redirect('admin/role');
        }
    }

    public function roleAccess($role_id)
    {
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Role Access Management';
        $data['role'] = $this->db->get_where('user_role', ['id' => $role_id])->row_array();
        
        if(!$data['role']){
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Role tidak ditemukan.</div>');
            redirect('admin/role');
        }
        
        $data['menu'] = $this->db->get('user_menu')->result_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/role-access', $data);
        $this->load->view('templates/footer');
    }

    public function changeaccess()
    {
        $menu_id = $this->input->post('menuId');
        $role_id = $this->input->post('roleId');

        $data = [
            'role_id' => $role_id,
            'menu_id' => $menu_id
        ];
        $result = $this->db->get_where('user_access_menu', $data);

        if ($result->num_rows() < 1) {
            $this->db->insert('user_access_menu', $data);
        } else {
            $this->db->delete('user_access_menu', $data);
        }
        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Access Changed!</div>');
    }

    public function permohonanMasuk()
    {
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Permohonan Impor';

        $this->db->select(
            'up.id, up.nomorSurat, up.TglSurat, up.time_stamp, up.status, ' .
            'upr.NamaPers, ' .
            'u_pemohon.name as nama_pengaju, ' .
            'u_real_petugas.name as nama_petugas_assigned' // Ambil nama petugas dari tabel user
        );
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $this->db->join('petugas p_assigned', 'up.petugas = p_assigned.id', 'left'); // up.petugas = petugas.id
        $this->db->join('user u_real_petugas', 'p_assigned.id_user = u_real_petugas.id', 'left'); // petugas.id_user = user.id
        
        $this->db->order_by("
            CASE up.status
                WHEN '0' THEN 1 -- Baru Masuk
                WHEN '5' THEN 2 -- Diproses Admin (sebelum tunjuk petugas)
                WHEN '1' THEN 3 -- Penunjukan Pemeriksa
                WHEN '2' THEN 4 -- LHP Direkam
                ELSE 5          -- Selesai (Disetujui/Ditolak) atau status lain
            END ASC, up.time_stamp DESC");
        $data['permohonan'] = $this->db->get()->result_array();
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/permohonan-masuk', $data); 
        $this->load->view('templates/footer');
    }

    private function _get_upload_config($upload_path, $allowed_types, $max_size_kb, $max_width = null, $max_height = null) 
    {
        log_message('debug', "ADMIN Controller: _get_upload_config() called. Path: {$upload_path}, Types: {$allowed_types}, Size: {$max_size_kb}KB");
        if (!is_dir($upload_path)) {
            log_message('debug', 'ADMIN Controller: _get_upload_config() - Upload path does not exist: ' . $upload_path);
            if (!@mkdir($upload_path, 0777, true)) {
                log_message('error', 'ADMIN Controller: _get_upload_config() - Gagal membuat direktori upload: ' . $upload_path . ' - Periksa izin parent direktori.');
                return false;
            }
            log_message('debug', 'ADMIN Controller: _get_upload_config() - Direktori upload berhasil dibuat: ' . $upload_path);
        }
        if (!is_writable($upload_path)) {
            log_message('error', 'ADMIN Controller: _get_upload_config() - Direktori upload tidak writable: ' . $upload_path . ' - Periksa izin (chown www-data:www-data dan chmod 775).');
            return false;
        }

        $config['upload_path']   = $upload_path;
        $config['allowed_types'] = $allowed_types;
        $config['max_size']      = $max_size_kb;
        if ($max_width) $config['max_width'] = $max_width;
        if ($max_height) $config['max_height'] = $max_height;
        $config['encrypt_name']  = TRUE;
        log_message('debug', 'ADMIN Controller: _get_upload_config() - Config created: ' . print_r($config, true));
        return $config;
    }

    public function prosesSurat($id_permohonan = 0)
    {
        // ... (bagian awal method: ambil data admin, permohonan, lhp, validasi awal tetap sama) ...
        $admin_user = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['user'] = $admin_user;
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Proses Finalisasi Permohonan Impor';

        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) { /* ... redirect ... */ return; }

        $this->db->select('up.*, upr.NamaPers, upr.npwp, upr.alamat, upr.NoSkep'); // Ambil juga file_surat_keputusan yang sudah ada
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->where('up.id', $id_permohonan);
        $data['permohonan'] = $this->db->get()->row_array();

        if (!$data['permohonan']) { /* ... redirect ... */ return; }
        
        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $data['permohonan']['id_pers']])->row_array();
        if (!$data['user_perusahaan']) {
            $data['user_perusahaan'] = ['NamaPers' => 'N/A', 'alamat' => 'N/A', 'NoSkep' => 'N/A', 'npwp' => 'N/A'];
        }


        $data['lhp'] = $this->db->get_where('lhp', ['id_permohonan' => $id_permohonan])->row_array();
        if (!$data['lhp'] || $data['permohonan']['status'] != '2' || empty($data['lhp']['NoLHP']) || empty($data['lhp']['TglLHP'])) {
            // ... (redirect jika LHP belum lengkap) ...
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">LHP belum lengkap atau status permohonan (ID '.htmlspecialchars($id_permohonan).') tidak valid untuk finalisasi.</div>');
            redirect('admin/detail_permohonan_admin/' . $id_permohonan);
            return;
        }
        
        // Validasi form
        $this->form_validation->set_rules('status_final', 'Status Final Permohonan', 'required|in_list[3,4]');
        $this->form_validation->set_rules('nomorSetuju', 'Nomor Surat Persetujuan/Penolakan', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('tgl_S', 'Tanggal Surat Persetujuan/Penolakan', 'trim|required');
        $this->form_validation->set_rules('link', 'Link Surat Keputusan (Opsional)', 'trim|callback__valid_url_format_check'); // Pastikan callback _valid_url_format_check ada

        // Validasi kondisional
        if ($this->input->post('status_final') == '4') { // Jika ditolak
            $this->form_validation->set_rules('catatan_penolakan', 'Catatan Penolakan', 'trim|required');
        } elseif ($this->input->post('status_final') == '3') { // Jika disetujui
            // Jika belum ada file SK lama DAN tidak ada file baru yang diupload
            if (empty($data['permohonan']['file_surat_keputusan']) && (!isset($_FILES['file_surat_keputusan']) || $_FILES['file_surat_keputusan']['error'] == UPLOAD_ERR_NO_FILE)) {
                $this->form_validation->set_rules('file_surat_keputusan', 'File Surat Persetujuan Pengeluaran', 'required');
            }
            // Jika ada file baru yang diupload, validasi
            if (isset($_FILES['file_surat_keputusan']) && $_FILES['file_surat_keputusan']['error'] != UPLOAD_ERR_NO_FILE) {
                $this->form_validation->set_rules('file_surat_keputusan', 'File Surat Persetujuan Pengeluaran', 'callback_admin_check_file_sk_upload'); // Buat callback ini
            }
        }


        if ($this->form_validation->run() == false) {
            log_message('debug', 'ADMIN PROSES SURAT - Form validation failed. Errors: ' . validation_errors());
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/prosesSurat', $data);
            $this->load->view('templates/footer');
        } else {
            log_message('debug', 'ADMIN PROSES SURAT - Form validation success. Processing data...');
            $status_final_permohonan = $this->input->post('status_final');
            $nomor_surat_keputusan = $this->input->post('nomorSetuju');
            $tanggal_surat_keputusan = $this->input->post('tgl_S');
            $catatan_penolakan_input = $this->input->post('catatan_penolakan');

            $data_update_permohonan = [
                'nomorSetuju'   => $nomor_surat_keputusan,
                'tgl_S'         => !empty($tanggal_surat_keputusan) ? $tanggal_surat_keputusan : null,
                'link'          => $this->input->post('link'),
                // 'nomorND'       => null, // Hapus atau set null
                // 'tgl_ND'        => null, // Hapus atau set null
                // 'linkND'        => null, // Hapus atau set null
                'catatan_penolakan' => ($status_final_permohonan == '4') ? $catatan_penolakan_input : null, // Hanya simpan jika ditolak
                'time_selesai'  => date("Y-m-d H:i:s"),
                'status'        => $status_final_permohonan,
                // 'diproses_oleh_id_admin' => $admin_user['id'] 
            ];

            // Handle Upload File Surat Keputusan (jika status disetujui dan ada file diupload)
            $nama_file_sk_baru = $data['permohonan']['file_surat_keputusan']; // Default ke file lama (jika ada)

            if ($status_final_permohonan == '3' && isset($_FILES['file_surat_keputusan']) && $_FILES['file_surat_keputusan']['error'] != UPLOAD_ERR_NO_FILE) {
                $upload_dir_sk = './uploads/sk_penyelesaian/'; // Pastikan direktori ini ada dan writable
                // Gunakan method _get_upload_config Anda
                $config_sk = $this->_get_upload_config($upload_dir_sk, 'pdf|jpg|png|jpeg', 2048); 

                if (!$config_sk) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Konfigurasi direktori upload SK gagal.</div>');
                    redirect('admin/prosesSurat/' . $id_permohonan); return;
                }

                $this->load->library('upload', $config_sk, 'sk_penyelesaian_upload');
                $this->sk_penyelesaian_upload->initialize($config_sk);

                if ($this->sk_penyelesaian_upload->do_upload('file_surat_keputusan')) {
                    // Hapus file SK lama jika ada dan upload baru berhasil
                    if (!empty($data['permohonan']['file_surat_keputusan']) && file_exists($upload_dir_sk . $data['permohonan']['file_surat_keputusan'])) {
                        @unlink($upload_dir_sk . $data['permohonan']['file_surat_keputusan']);
                    }
                    $nama_file_sk_baru = $this->sk_penyelesaian_upload->data('file_name');
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload File Surat Keputusan Gagal: ' . $this->sk_penyelesaian_upload->display_errors('', '') . '</div>');
                    // Reload view dengan data yang sudah ada
                    $this->load->view('templates/header', $data);
                    $this->load->view('templates/sidebar', $data);
                    $this->load->view('templates/topbar', $data);
                    $this->load->view('admin/prosesSurat', $data);
                    $this->load->view('templates/footer');
                    return;
                }
            }
            // Hanya update nama file jika statusnya disetujui, jika ditolak mungkin tidak perlu file SK
            if ($status_final_permohonan == '3') {
                $data_update_permohonan['file_surat_keputusan'] = $nama_file_sk_baru;
            } else {
                // Jika ditolak, dan ada file SK lama, mungkin Anda ingin menghapusnya atau mengosongkan field
                if (!empty($data['permohonan']['file_surat_keputusan']) && file_exists('./uploads/sk_penyelesaian/' . $data['permohonan']['file_surat_keputusan'])) {
                    @unlink('./uploads/sk_penyelesaian/' . $data['permohonan']['file_surat_keputusan']);
                }
                $data_update_permohonan['file_surat_keputusan'] = null;
            }


            $this->db->where('id', $id_permohonan);
            $this->db->update('user_permohonan', $data_update_permohonan);
            // ... (Logika pemotongan kuota jika disetujui, tetap sama) ...
            if ($status_final_permohonan == '3' && isset($data['lhp']['JumlahBenar']) && $data['lhp']['JumlahBenar'] > 0) {
                // ... (kode pemotongan kuota barang Anda) ...
            }

            $pesan_status_akhir = ($status_final_permohonan == '3') ? 'Disetujui' : 'Ditolak';
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Status permohonan ID '.htmlspecialchars($id_permohonan).' telah berhasil diproses menjadi "'. $pesan_status_akhir .'"!</div>');
            redirect('admin/permohonanMasuk');
        }
    }

    public function admin_check_file_sk_upload($str) 
    {
        $field_name = 'file_surat_keputusan';

        // Jika tidak ada file baru yang diupload SAAT EDIT, ini valid jika file lama sudah ada.
        // Namun, untuk prosesSurat, jika status 'approved' dan BELUM ADA file lama, maka file baru WAJIB.
        // Kondisi ini sudah dihandle di set_rules kondisional.
        // Callback ini hanya akan dipanggil jika file di-submit.
        if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] == UPLOAD_ERR_NO_FILE) {
            // Ini seharusnya sudah ditangani oleh 'required' kondisional di set_rules
            // Tapi jika dipanggil dan tidak ada file, anggap error
            $this->form_validation->set_message('admin_check_file_sk_upload', 'Kolom {field} wajib diisi.');
            return FALSE;
        }

        $config_rules = $this->_get_upload_config('./uploads/dummy/', 'pdf|jpg|png|jpeg', 2048);
        if (!$config_rules) { /* error config */ return FALSE; }

        $file = $_FILES[$field_name];
        $allowed_types_arr = explode('|', $config_rules['allowed_types']);
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types_arr)) {
            $this->form_validation->set_message('admin_check_file_sk_upload', "Tipe file {field} tidak valid (Hanya ".str_replace('|',', ',$config_rules['allowed_types']).").");
            return FALSE;
        }
        if ($file['size'] > ($config_rules['max_size'] * 1024)) {
            $this->form_validation->set_message('admin_check_file_sk_upload', "Ukuran file {field} melebihi ".$config_rules['max_size']."KB.");
            return FALSE;
        }
        return TRUE;
    }


    public function _valid_url_format_check($str)
    {
        if (empty($str)) { // Jika tidak ada input, anggap valid
            return TRUE;
        }
        // Validasi format URL menggunakan filter_var
        if (filter_var($str, FILTER_VALIDATE_URL)) {
            return TRUE;
        } else {
            $this->form_validation->set_message('_valid_url_format_check', '{field} harus berisi URL yang valid (contoh: http://example.com).');
            return FALSE;
        }
    }

    private function _log_perubahan_kuota(
        $id_pers_param,
        $jenis_transaksi_param,
        $jumlah_param,
        $kuota_sebelum_param,
        $kuota_sesudah_param,
        $keterangan_param,
        $id_referensi_param = null,
        $tipe_referensi_param = null,
        $dicatat_oleh_user_id_param = null,
        $nama_barang_terkait_param = null,
        $id_kuota_barang_ref_param = null
    ) {
        $log_data = [
            'id_pers'                 => $id_pers_param,
            'nama_barang_terkait'     => $nama_barang_terkait_param,
            'id_kuota_barang_referensi'=> $id_kuota_barang_ref_param,
            'jenis_transaksi'         => $jenis_transaksi_param,
            'jumlah_perubahan'        => $jumlah_param,
            'sisa_kuota_sebelum'      => $kuota_sebelum_param,
            'sisa_kuota_setelah'      => $kuota_sesudah_param,
            'keterangan'              => $keterangan_param,
            'id_referensi_transaksi'  => $id_referensi_param,
            'tipe_referensi'          => $tipe_referensi_param,
            'dicatat_oleh_user_id'    => $dicatat_oleh_user_id_param,
            'tanggal_transaksi'       => date('Y-m-d H:i:s')
        ];

        // Tambahkan baris ini untuk menyimpan ke database
        if (!empty($log_data['id_pers']) && !empty($log_data['nama_barang_terkait'])) { // Pastikan data penting ada
            $this->db->insert('log_kuota_perusahaan', $log_data);
            // Anda bisa tambahkan logging tambahan di sini jika insert gagal untuk debugging
            // if ($this->db->affected_rows() > 0) {
            //     log_message('debug', 'Log kuota berhasil disimpan: ' . print_r($log_data, true));
            // } else {
            //     log_message('error', 'Gagal menyimpan log kuota. Data: ' . print_r($log_data, true) . ' Error: ' . $this->db->error()['message']);
            // }
        } else {
            log_message('warning', 'Data log kuota tidak lengkap, tidak disimpan: ' . print_r($log_data, true));
        }
    }

    public function penunjukanPetugas($id_permohonan)
    {
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Penunjukan Petugas Pemeriksa';

        // Ambil data permohonan yang akan diproses
        $this->db->select('up.*, upr.NamaPers'); // Ambil juga kolom 'petugas', 'NoSuratTugas', 'TglSuratTugas', 'FileSuratTugas' untuk pre-fill form jika diedit
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->where('up.id', $id_permohonan);
        $permohonan = $this->db->get()->row_array();

        if (!$permohonan) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan!</div>');
            redirect('admin/permohonanMasuk');
            return;
        }
        $data['permohonan'] = $permohonan;

        if ($permohonan['status'] == '0' && $this->input->method() !== 'post') {
            $this->db->where('id', $id_permohonan);
            $this->db->update('user_permohonan', ['status' => '5']);
            $permohonan['status'] = '5'; // Update variabel lokal
            $data['permohonan']['status'] = '5'; // Update data yang dikirim ke view
            $this->session->set_flashdata('message_transient', '<div class="alert alert-info" role="alert">Status permohonan ID ' . htmlspecialchars($id_permohonan) . ' telah diubah menjadi "Diproses Admin". Lanjutkan dengan menunjuk petugas.</div>');
        }

        // Ambil daftar petugas dari tabel 'petugas'
        $data['list_petugas'] = $this->db->order_by('Nama', 'ASC')->get('petugas')->result_array();
        if (empty($data['list_petugas'])) {
            log_message('warning', 'Tidak ada data petugas ditemukan di tabel petugas.');
        }

        // Validasi form
        $this->form_validation->set_rules('petugas_id', 'Petugas Pemeriksa', 'required|numeric');
        $this->form_validation->set_rules('nomor_surat_tugas', 'Nomor Surat Tugas', 'required|trim');
        $this->form_validation->set_rules('tanggal_surat_tugas', 'Tanggal Surat Tugas', 'required');


        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/form_penunjukan_petugas', $data);
            $this->load->view('templates/footer');
        } else {
            // Proses data form
            $update_data = [
                'petugas' => $this->input->post('petugas_id'), // Ambil langsung dari 'petugas_id'
                'NoSuratTugas' => $this->input->post('nomor_surat_tugas'),
                'TglSuratTugas' => $this->input->post('tanggal_surat_tugas'),
                'status' => '1', // Status '1' = Penunjukan Pemeriksa
                'WaktuPenunjukanPetugas' => date('Y-m-d H:i:s')
            ];

            $nama_file_surat_tugas = $permohonan['FileSuratTugas'] ?? null;

            if (isset($_FILES['file_surat_tugas']) && $_FILES['file_surat_tugas']['error'] != UPLOAD_ERR_NO_FILE) {
                $upload_dir_st = './uploads/surat_tugas/';
                if (!is_dir($upload_dir_st)) {
                    if (!@mkdir($upload_dir_st, 0777, true)) {
                        $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal membuat direktori upload Surat Tugas.</div>');
                        redirect('admin/penunjukanPetugas/' . $id_permohonan);
                        return;
                    }
                }

                if (!is_writable($upload_dir_st)) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload error: Direktori Surat Tugas tidak writable. Path: '.$upload_dir_st.'</div>');
                    redirect('admin/penunjukanPetugas/' . $id_permohonan);
                    return;
                }

                $config_st['upload_path']   = $upload_dir_st;
                $config_st['allowed_types'] = 'pdf|jpg|png|jpeg|doc|docx';
                $config_st['max_size']      = '2048'; // 2MB
                $config_st['encrypt_name']  = TRUE;

                // Cek apakah file sudah ada sebelumnya
                $this->load->library('upload', $config_st, 'st_upload');
                $this->st_upload->initialize($config_st);

                if ($this->st_upload->do_upload('file_surat_tugas')) {
                    // Hapus file lama jika ada dan file baru berhasil diupload
                    if (!empty($permohonan['FileSuratTugas']) && file_exists($upload_dir_st . $permohonan['FileSuratTugas'])) {
                       @unlink($upload_dir_st . $permohonan['FileSuratTugas']);
                    }
                    $nama_file_surat_tugas = $this->st_upload->data('file_name');
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload File Surat Tugas Gagal: ' . $this->st_upload->display_errors('', '') . '</div>');
                    redirect('admin/penunjukanPetugas/' . $id_permohonan);
                    return;
                }
            }
            $update_data['FileSuratTugas'] = $nama_file_surat_tugas;


            // Update data permohonan
            $this->db->where('id', $id_permohonan);
            $this->db->update('user_permohonan', $update_data);

            // Logging untuk verifikasi
            $updated_permohonan = $this->db->get_where('user_permohonan', ['id' => $id_permohonan])->row_array();
            log_message('debug', 'PENUNJUKAN PETUGAS - Data Permohonan Setelah Update: ' . print_r($updated_permohonan, true));
            log_message('debug', 'PENUNJUKAN PETUGAS - Nilai petugas_id yang di-POST: ' . $this->input->post('petugas_id'));


            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Petugas pemeriksa berhasil ditunjuk untuk permohonan ID ' . htmlspecialchars($id_permohonan) . '. Status diubah menjadi "Penunjukan Pemeriksa".</div>');
            redirect('admin/permohonanMasuk');
        }
    }

    public function daftar_pengajuan_kuota()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Pengajuan Kuota';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->db->select('upk.*, upr.NamaPers, u.email as user_email');
        $this->db->from('user_pengajuan_kuota upk');
        $this->db->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $this->db->join('user u', 'upk.id_pers = u.id', 'left');
        $this->db->order_by('FIELD(upk.status, "pending") DESC, upk.submission_date DESC');
        $data['pengajuan_kuota'] = $this->db->get()->result_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/daftar_pengajuan_kuota', $data);
        $this->load->view('templates/footer');
    }

    public function proses_pengajuan_kuota($id_pengajuan)
    {
        log_message('debug', 'ADMIN PROSES PENGAJUAN KUOTA - Method dipanggil untuk id_pengajuan: ' . $id_pengajuan);
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Proses Pengajuan Kuota';
        $admin_user = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['user'] = $admin_user;

        // Ambil data pengajuan, termasuk nama_barang_kuota dan data perusahaan sebelum ada perubahan kuota
        $this->db->select('upk.*, upr.NamaPers, upr.initial_quota as initial_quota_umum_sebelum, upr.remaining_quota as remaining_quota_umum_sebelum, u.email as user_email');
        $this->db->from('user_pengajuan_kuota upk');
        $this->db->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $this->db->join('user u', 'upk.id_pers = u.id', 'left');
        $this->db->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $this->db->get()->row_array();
        log_message('debug', 'ADMIN PROSES PENGAJUAN KUOTA - Data pengajuan yang diambil: ' . print_r($data['pengajuan'], true));

        if (!$data['pengajuan'] || ($data['pengajuan']['status'] != 'pending' && $data['pengajuan']['status'] != 'diproses')) {
            $pesan_error_awal = 'Pengajuan kuota tidak ditemukan atau statusnya tidak memungkinkan untuk diproses (Status saat ini: ' . ($data['pengajuan']['status'] ?? 'Tidak Diketahui') . '). Hanya status "pending" atau "diproses" yang bisa dilanjutkan.';
            log_message('error', 'ADMIN PROSES PENGAJUAN KUOTA - Validasi awal gagal: ' . $pesan_error_awal);
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">' . $pesan_error_awal . '</div>');
            redirect('admin/daftar_pengajuan_kuota');
            return;
        }

        // Aturan validasi form
        $this->form_validation->set_rules('status_pengajuan', 'Status Pengajuan', 'required|in_list[approved,rejected,diproses]');
        if ($this->input->post('status_pengajuan') == 'approved') {
            $this->form_validation->set_rules('approved_quota', 'Kuota Disetujui', 'trim|required|numeric|greater_than[0]');
            $this->form_validation->set_rules('nomor_sk_petugas', 'Nomor Surat Keputusan', 'trim|required|max_length[100]');
            $this->form_validation->set_rules('tanggal_sk_petugas', 'Tanggal Surat Keputusan', 'trim|required');
        } else {
            $this->form_validation->set_rules('approved_quota', 'Kuota Disetujui', 'trim|numeric');
            $this->form_validation->set_rules('nomor_sk_petugas', 'Nomor Surat Keputusan', 'trim|max_length[100]');
            $this->form_validation->set_rules('tanggal_sk_petugas', 'Tanggal Surat Keputusan', 'trim');
        }
        $this->form_validation->set_rules('admin_notes', 'Catatan Admin', 'trim');
        // Tambahkan validasi untuk file_sk_petugas jika wajib saat approved dan belum ada file lama
        if ($this->input->post('status_pengajuan') == 'approved' && empty($data['pengajuan']['file_sk_petugas']) && empty($_FILES['file_sk_petugas']['name'])) {
            $this->form_validation->set_rules('file_sk_petugas', 'File SK Petugas', 'required');
        }


        if ($this->form_validation->run() == false) {
            log_message('debug', 'ADMIN PROSES PENGAJUAN KUOTA - Validasi Form Gagal. Errors: ' . validation_errors() . ' POST Data: ' . print_r($this->input->post(), true));
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/proses_pengajuan_kuota_form', $data);
            $this->load->view('templates/footer', $data);
        } else {
            log_message('debug', 'ADMIN PROSES PENGAJUAN KUOTA - Validasi Form Sukses. Memproses data...');
            $status_pengajuan = $this->input->post('status_pengajuan');
            $approved_quota_input = ($status_pengajuan == 'approved') ? (float)$this->input->post('approved_quota') : 0;
            $nomor_sk_petugas = $this->input->post('nomor_sk_petugas');
            $tanggal_sk_petugas = $this->input->post('tanggal_sk_petugas'); // Ambil tanggal SK
            $admin_notes = $this->input->post('admin_notes');

            $data_update_pengajuan = [
                'status' => $status_pengajuan,
                'admin_notes' => $admin_notes,
                'processed_date' => date('Y-m-d H:i:s'),
                'nomor_sk_petugas' => $nomor_sk_petugas,
                'tanggal_sk_petugas' => !empty($tanggal_sk_petugas) ? $tanggal_sk_petugas : null, // Simpan tanggal SK (jika ada kolomnya)
                'approved_quota' => $approved_quota_input
            ];

            // Proses Upload File SK
            $nama_file_sk = $data['pengajuan']['file_sk_petugas'] ?? null;
            if (($status_pengajuan == 'approved' || $status_pengajuan == 'rejected') && isset($_FILES['file_sk_petugas']) && $_FILES['file_sk_petugas']['error'] != UPLOAD_ERR_NO_FILE) {
                $upload_dir_sk = './uploads/sk_kuota/';
                if (!is_dir($upload_dir_sk)) { @mkdir($upload_dir_sk, 0777, true); }
                if (!is_writable($upload_dir_sk)) { /* ... error handling ... */ redirect('admin/proses_pengajuan_kuota/' . $id_pengajuan); return; }
                
                $config_sk['upload_path']   = $upload_dir_sk;
                $config_sk['allowed_types'] = 'pdf|jpg|png|jpeg';
                $config_sk['max_size']      = '2048';
                $config_sk['encrypt_name']  = TRUE;
                $this->load->library('upload', $config_sk, 'sk_upload_instance'); // Gunakan nama instance berbeda
                $this->sk_upload_instance->initialize($config_sk);

                if ($this->sk_upload_instance->do_upload('file_sk_petugas')) {
                    if (!empty($data['pengajuan']['file_sk_petugas']) && file_exists($upload_dir_sk . $data['pengajuan']['file_sk_petugas'])) {
                        @unlink($upload_dir_sk . $data['pengajuan']['file_sk_petugas']);
                    }
                    $nama_file_sk = $this->sk_upload_instance->data('file_name');
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">File SK Upload Error: ' . $this->sk_upload_instance->display_errors('', '') . '</div>');
                    redirect('admin/proses_pengajuan_kuota/' . $id_pengajuan); return;
                }
            }
            $data_update_pengajuan['file_sk_petugas'] = $nama_file_sk;

            // Update tabel user_pengajuan_kuota
            $this->db->where('id', $id_pengajuan);
            $this->db->update('user_pengajuan_kuota', $data_update_pengajuan);
            log_message('debug', 'ADMIN PROSES PENGAJUAN KUOTA - user_pengajuan_kuota diupdate. Affected: ' . $this->db->affected_rows());

            if ($status_pengajuan == 'approved' && $approved_quota_input > 0) {
                $id_pers_terkait = $data['pengajuan']['id_pers'];
                $nama_barang_diajukan = $data['pengajuan']['nama_barang_kuota']; // Harus ada di $data['pengajuan']

                if ($id_pers_terkait && !empty($nama_barang_diajukan)) {
                    $sisa_kuota_umum_sebelum_tambah = (float)($data['pengajuan']['remaining_quota_umum_sebelum'] ?? 0);

                    $data_kuota_barang = [
                        'id_pers' => $id_pers_terkait,
                        'id_pengajuan_kuota' => $id_pengajuan,
                        'nama_barang' => $nama_barang_diajukan,
                        'initial_quota_barang' => $approved_quota_input,
                        'remaining_quota_barang' => $approved_quota_input,
                        'nomor_skep_asal' => $nomor_sk_petugas,
                        'tanggal_skep_asal' => !empty($tanggal_sk_petugas) ? $tanggal_sk_petugas : null,
                        'status_kuota_barang' => 'active',
                        'dicatat_oleh_user_id' => $admin_user['id'],
                        'waktu_pencatatan' => date('Y-m-d H:i:s')
                    ];
                    $this->db->insert('user_kuota_barang', $data_kuota_barang);
                    $id_kuota_barang_baru = $this->db->insert_id();
                    log_message('info', 'ADMIN PROSES PENGAJUAN KUOTA - Data kuota barang baru disimpan. ID: ' . $id_kuota_barang_baru . ' untuk barang: ' . $nama_barang_diajukan);

                    if ($id_kuota_barang_baru) {
                        $this->_log_perubahan_kuota(
                            $id_pers_terkait, 'penambahan', $approved_quota_input,
                            0, // Kuota barang spesifik ini sebelumnya adalah 0 karena ini entri baru
                            $approved_quota_input, // Sisa sesudah = jumlah yang baru ditambahkan
                            'Persetujuan Pengajuan Kuota. Barang: ' . $nama_barang_diajukan . '. No. SK: ' . ($nomor_sk_petugas ?: '-'),
                            $id_pengajuan, 'pengajuan_kuota_disetujui', $admin_user['id'],
                            $nama_barang_diajukan, $id_kuota_barang_baru
                        );
                    }
                } else {
                    log_message('error', 'ADMIN PROSES PENGAJUAN KUOTA - Gagal menambah kuota barang: id_pers atau nama_barang_kuota kosong. ID Pers: ' . $id_pers_terkait . ', Nama Barang: ' . $nama_barang_diajukan);
                }
            }

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Pengajuan kuota telah berhasil diproses!</div>');
            redirect('admin/daftar_pengajuan_kuota');
        }
    }

    public function print_pengajuan_kuota($id_pengajuan)
    {
        $data['title'] = 'Detail Proses Pengajuan Kuota';
        $data['user_login'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->db->select('upk.*, upr.NamaPers, upr.npwp AS npwp_perusahaan, upr.alamat as alamat_perusahaan, upr.pic, upr.jabatanPic, u.email AS user_email, u.name AS user_name_pengaju, u.image AS logo_perusahaan_file');
        $this->db->from('user_pengajuan_kuota upk');
        $this->db->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $this->db->join('user u', 'upk.id_pers = u.id', 'left');
        $this->db->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $this->db->get()->row_array();

        if (!$data['pengajuan']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data pengajuan kuota tidak ditemukan.</div>');
            redirect('admin/daftar_pengajuan_kuota');
            return;
        }

        // Data untuk view cetak
        $data['user'] = $this->db->get_where('user', ['id' => $data['pengajuan']['id_pers']])->row_array(); // Data user pengaju
        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $data['pengajuan']['id_pers']])->row_array();

        $this->load->view('user/FormPengajuanKuota_print', $data);
    }

    public function detailPengajuanKuotaAdmin($id_pengajuan)
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Proses Pengajuan Kuota';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->db->select('upk.*, upr.NamaPers, upr.npwp AS npwp_perusahaan, upr.alamat as alamat_perusahaan, upr.pic, upr.jabatanPic, u.email AS user_email_pemohon, u.name AS nama_pemohon');
        $this->db->from('user_pengajuan_kuota upk');
        $this->db->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $this->db->join('user u', 'upk.id_pers = u.id', 'left'); // Asumsi id_pers di user_pengajuan_kuota merujuk ke id user pemohon
        $this->db->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $this->db->get()->row_array();

        if (!$data['pengajuan']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data pengajuan kuota tidak ditemukan.</div>');
            redirect('admin/daftar_pengajuan_kuota'); // Atau ke halaman error yang sesuai
            return;
        }


        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/detail_pengajuan_kuota_view', $data); // Load view baru untuk Admin
        $this->load->view('templates/footer');
    }

    public function download_sk_kuota_admin($id_pengajuan)
    {
        $this->load->helper('download'); // Pastikan helper download di-load
        $pengajuan = $this->db->get_where('user_pengajuan_kuota', ['id' => $id_pengajuan])->row_array();

        if ($pengajuan && !empty($pengajuan['file_sk_petugas'])) {
            $file_name = $pengajuan['file_sk_petugas'];
            $file_path = FCPATH . 'uploads/sk_kuota/' . $file_name;

            if (file_exists($file_path)) {
                force_download($file_path, NULL);
            } else {
                log_message('error', 'Admin: File SK Kuota tidak ditemukan di path: ' . $file_path . ' untuk id_pengajuan: ' . $id_pengajuan);
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">File Surat Keputusan tidak ditemukan di server.</div>');
                redirect('admin/daftar_pengajuan_kuota');
            }
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Surat Keputusan belum tersedia untuk pengajuan ini.</div>');
            redirect('admin/daftar_pengajuan_kuota');
        }
    }

    public function manajemen_user()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Manajemen User';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        // Ambil semua user kecuali admin yang sedang login
        $this->db->select('u.*, ur.role as role_name');
        $this->db->from('user u');
        $this->db->join('user_role ur', 'u.role_id = ur.id', 'left');
        // $this->db->where('u.id !=', $data['user']['id']);
        $this->db->order_by('u.name', 'ASC');
        $data['users_list'] = $this->db->get()->result_array();

        // Ambil daftar role untuk dropdown di form tambah/edit (jika diperlukan)
        $data['roles'] = $this->db->get('user_role')->result_array();


        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/manajemen_user_view', $data);
        $this->load->view('templates/footer');
    }

    
    public function tambah_user($role_id_to_add = 0)
    {
        $data['title'] = 'Returnable Package';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        if ($role_id_to_add == 0 || !is_numeric($role_id_to_add)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Role ID untuk user baru tidak valid.</div>');
            redirect('admin/manajemen_user');
            return;
        }
        
        $data['target_role_info'] = $this->db->get_where('user_role', ['id' => $role_id_to_add])->row_array();

        if (!$data['target_role_info']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Role target tidak ditemukan.</div>');
            redirect('admin/manajemen_user');
            return;
        }
        
        // Jangan izinkan admin menambahkan admin lain (role_id 1) dari form ini
        if ($role_id_to_add == 1) {
             $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Penambahan user dengan role Admin tidak diizinkan melalui form ini.</div>');
             redirect('admin/manajemen_user');
             return;
        }

        $data['subtitle'] = 'Tambah User Baru: ' . htmlspecialchars($data['target_role_info']['role']);
        $data['role_id_to_add'] = $role_id_to_add; 

        // Aturan validasi dasar
        $this->form_validation->set_rules('name', 'Nama Lengkap', 'required|trim');
        $this->form_validation->set_rules('password', 'Password Awal', 'required|trim|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Konfirmasi Password Awal', 'required|trim|matches[password]');

        // Aturan validasi kondisional untuk login identifier
        $login_identifier_label = '';
        $login_identifier_rules = 'required|trim';
        $data['login_identifier_type_is_email'] = false; // Flag untuk view

        if ($role_id_to_add == 2) { // Pengguna Jasa
            $login_identifier_label = 'Email';
            $login_identifier_rules .= '|valid_email|is_unique[user.email]';
            $data['login_identifier_type_is_email'] = true;
        } elseif (in_array($role_id_to_add, [3, 4, 5])) { // Petugas (3), Monitoring (4), Petugas Administrasi (5)
            if ($role_id_to_add == 3) {
                $login_identifier_label = 'NIP Petugas';
                $login_identifier_rules .= '|numeric'; // Assuming NIP is numeric for Petugas
            } elseif ($role_id_to_add == 4) {
                $login_identifier_label = 'NIP Monitoring';
                $login_identifier_rules .= '|numeric'; // Assuming NIP is numeric for Monitoring
            } else { // role_id == 5 (Petugas Administrasi)
                $login_identifier_label = 'NIP Petugas Administrasi'; // MODIFIED for clarity
                // Add numeric validation if NIPs for Petugas Administrasi are always numbers
                // For example: $login_identifier_rules .= '|numeric';
            }
            $login_identifier_rules .= '|is_unique[user.email]'; // NIP will be stored in the 'email' column
        } else {
            // For role custom lain jika ada
            $login_identifier_label = 'Login Identifier (Email/Username)';
            $login_identifier_rules .= '|is_unique[user.email]';
            $data['login_identifier_type_is_email'] = true; // Asumsi defaultnya email
        }
        $this->form_validation->set_rules('login_identifier', $login_identifier_label, $login_identifier_rules, [
            'is_unique' => $login_identifier_label . ' ini sudah terdaftar.',
            'numeric'   => $login_identifier_label . ' harus berupa angka.',
            'valid_email'=> $login_identifier_label . ' tidak valid.'
        ]);
        $data['login_identifier_label_view'] = $login_identifier_label; // Kirim label ke view
        
        // Aturan validasi kondisional untuk field spesifik role
        if ($role_id_to_add == 3) { // Petugas
            $this->form_validation->set_rules('jabatan_petugas', 'Jabatan Petugas', 'trim|required');
        }
        // Untuk role Monitoring (4) dan Petugas Administrasi (5), kita asumsikan tidak ada field tambahan di form ini.
        // Jika ada, tambahkan rules di sini:
        // elseif ($role_id_to_add == 5) { // Petugas Administrasi
        //    $this->form_validation->set_rules('field_petugas_admin', 'Field Khusus Petugas Admin', 'trim|required');
        // }

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/form_tambah_user_view', $data); // Menggunakan view generik
            $this->load->view('templates/footer');
        } else {
            $login_identifier_input = $this->input->post('login_identifier');
            $force_change_pass = 1; // Default wajib ganti password

            // Untuk role Monitoring dan Petugas Administrasi, password tidak dipaksa ganti
            if (in_array($role_id_to_add, [4, 5])) {
                $force_change_pass = 0;
            }

            $user_data_to_insert = [
                'name' => htmlspecialchars($this->input->post('name', true)),
                'email' => htmlspecialchars($login_identifier_input, true),
                'image' => 'default.jpg',
                'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                'role_id' => $role_id_to_add,
                'is_active' => 1,
                'force_change_password' => $force_change_pass,
                'date_created' => time()
            ];
            $this->db->insert('user', $user_data_to_insert);
            $new_user_id = $this->db->insert_id();

            if ($new_user_id) {
                // Jika role adalah Petugas, simpan juga ke tabel 'petugas'
                if ($role_id_to_add == 3) {
                    $petugas_data_to_insert = [
                        'id_user' => $new_user_id,
                        'Nama' => $user_data_to_insert['name'],
                        'NIP' => $login_identifier_input,
                        'Jabatan' => htmlspecialchars($this->input->post('jabatan_petugas', true))
                    ];
                    $this->db->insert('petugas', $petugas_data_to_insert);
                }
                // Tidak ada tabel detail untuk Monitoring atau Petugas Administrasi dari form ini

                $pesan_sukses = 'User ' . htmlspecialchars($data['target_role_info']['role']) . ' baru, ' . htmlspecialchars($user_data_to_insert['name']) . ', berhasil ditambahkan.';
                if ($force_change_pass == 1) {
                    $pesan_sukses .= ' User wajib mengganti password saat login pertama.';
                }
                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">' . $pesan_sukses . '</div>');
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal menambahkan user baru. Silakan coba lagi.</div>');
            }
            redirect('admin/manajemen_user');
        }
    }


    public function ganti_password_user($target_user_id = 0)
    {
        if ($target_user_id == 0) {
            redirect('admin/manajemen_user');
            return;
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Ganti Password User';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(); // Admin yang login

        $data['target_user'] = $this->db->get_where('user', ['id' => $target_user_id])->row_array();

        if (!$data['target_user'] || $data['target_user']['role_id'] == 1) { // Tidak bisa ganti password admin lain atau jika user tidak ada
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">User tidak ditemukan atau Anda tidak dapat mengganti password user ini.</div>');
            redirect('admin/manajemen_user');
            return;
        }

        $this->form_validation->set_rules('new_password', 'Password Baru', 'required|trim|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Konfirmasi Password Baru', 'required|trim|matches[new_password]');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/form_ganti_password_user', $data);
            $this->load->view('templates/footer');
        } else {
            $new_password_hash = password_hash($this->input->post('new_password'), PASSWORD_DEFAULT);
            $update_data = [
                'password' => $new_password_hash,
                'force_change_password' => 1 // Wajibkan user ganti password setelah ini
            ];

            $this->db->where('id', $target_user_id);
            $this->db->update('user', $update_data);

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Password untuk user ' . htmlspecialchars($data['target_user']['name']) . ' berhasil diubah. User tersebut wajib mengganti passwordnya saat login berikutnya.</div>');
            redirect('admin/manajemen_user');
        }
    }

    public function edit_user($target_user_id = 0)
    {
        if ($target_user_id == 0 || !is_numeric($target_user_id)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">User ID tidak valid.</div>');
            redirect('admin/manajemen_user');
            return;
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Data User';
        $admin_logged_in = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['user'] = $admin_logged_in; // Data admin yang login (untuk header, sidebar, dll)

        $data['target_user_data'] = $this->db->get_where('user', ['id' => $target_user_id])->row_array();

        if (!$data['target_user_data']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">User yang akan diedit tidak ditemukan (ID: '.htmlspecialchars($target_user_id).').</div>');
            redirect('admin/manajemen_user');
            return;
        }

        $is_editing_main_admin = ($data['target_user_data']['id'] == 1); // Asumsi Admin utama memiliki ID = 1
        
        $target_role_id_for_check = $this->input->post('role_id') ? (int)$this->input->post('role_id') : (int)$data['target_user_data']['role_id'];
        $is_target_petugas_or_monitoring = in_array($target_role_id_for_check, [3, 4]); // Asumsi Role Petugas = 3, Monitoring = 4

        $data['roles_list'] = $this->db->get('user_role')->result_array();

        // Validasi Form
        $this->form_validation->set_rules('name', 'Nama Lengkap', 'required|trim');

        $original_login_identifier = $data['target_user_data']['email']; // Kolom 'email' di DB bisa berisi email atau NIP
        $input_login_identifier = $this->input->post('login_identifier'); // Nama field input dari form

        // Membangun aturan validasi untuk login_identifier (Email/NIP)
        $login_identifier_rules = 'required|trim';
        $login_identifier_label = '';

        if ($is_target_petugas_or_monitoring) {
            $login_identifier_label = 'NIP';
            $login_identifier_rules .= '|numeric'; // NIP harus numerik
            if ($input_login_identifier !== null && $input_login_identifier != $original_login_identifier) {
                $login_identifier_rules .= '|is_unique[user.email]';
            }
        } else { // Untuk Admin dan Pengguna Jasa
            $login_identifier_label = 'Email';
            $login_identifier_rules .= '|valid_email';
            if ($input_login_identifier !== null && $input_login_identifier != $original_login_identifier) {
                $login_identifier_rules .= '|is_unique[user.email]';
            }
        }
        // Hanya set rules jika ada input (untuk menghindari error saat halaman pertama kali load)
        if ($this->input->post('login_identifier') !== null) {
            $this->form_validation->set_rules('login_identifier', $login_identifier_label, $login_identifier_rules, [
                'is_unique' => $login_identifier_label . ' ini sudah terdaftar.',
                'numeric'   => $login_identifier_label . ' harus berupa angka.',
                'valid_email' => $login_identifier_label . ' tidak valid.'
            ]);
        }


        if (!$is_editing_main_admin) {
            $this->form_validation->set_rules('role_id', 'Role', 'required|numeric');
            $this->form_validation->set_rules('is_active', 'Status Aktif', 'required|in_list[0,1]');
        }
        
        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/form_edit_user', $data); 
            $this->load->view('templates/footer');
        } else {
            $update_data_user = [
                'name' => htmlspecialchars($this->input->post('name', true)),
                'email' => htmlspecialchars($input_login_identifier, true), 
            ];

            if (!$is_editing_main_admin) {
                $update_data_user['role_id'] = (int)$this->input->post('role_id');
                $update_data_user['is_active'] = (int)$this->input->post('is_active');
            } else {
                $update_data_user['role_id'] = (int)$data['target_user_data']['role_id']; 
                $update_data_user['is_active'] = (int)$data['target_user_data']['is_active']; 
            }

            $this->db->where('id', $target_user_id);
            $this->db->update('user', $update_data_user);

            $new_role_id = (int)($this->input->post('role_id') ?? $data['target_user_data']['role_id']);

            // Jika role baru atau role saat ini adalah Petugas (ID 3)
            // dan pastikan tabel 'petugas' memiliki kolom 'id_user' sebagai foreign key ke 'user.id'
            if ($new_role_id == 3) { 
                if ($this->db->field_exists('id_user', 'petugas')) {
                    $petugas_detail = $this->db->get_where('petugas', ['id_user' => $target_user_id])->row_array();
                    $data_petugas_update = [
                        'Nama' => $update_data_user['name'],
                        'NIP' => $update_data_user['email'], // NIP diambil dari user.email (yang berisi NIP)
                        'Jabatan' => $this->input->post('jabatan_petugas_edit')
                    ];

                    if ($petugas_detail) { 
                        $this->db->where('id_user', $target_user_id);
                        $this->db->update('petugas', $data_petugas_update);
                    } else { 
                        $data_petugas_update['id_user'] = $target_user_id;
                        $this->db->insert('petugas', $data_petugas_update);
                    }
                } else {
                    log_message('error', 'Kolom id_user tidak ditemukan di tabel petugas saat mencoba update/insert data petugas untuk user ID: ' . $target_user_id);
                    // Anda bisa set flashdata error di sini jika perlu
                    // $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Data user berhasil diupdate, tetapi detail petugas tidak dapat diproses karena struktur tabel petugas tidak sesuai (missing id_user).</div>');
                }
            }
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Data user '.htmlspecialchars($update_data_user['name']).' berhasil diupdate.</div>');
            redirect('admin/manajemen_user');
        }
    }

    public function histori_kuota_perusahaan($id_pers = 0)
    {
        log_message('debug', 'ADMIN HISTORI KUOTA - Method dipanggil dengan id_pers: ' . $id_pers); //

        if ($id_pers == 0 || !is_numeric($id_pers)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Perusahaan tidak valid.</div>'); //
            redirect('admin/monitoring_kuota');
            return;
        }

        $data['title'] = 'Returnable Package'; //
        $data['subtitle'] = 'Histori & Detail Kuota Perusahaan'; //
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(); //

        // Ambil data perusahaan umum (tetap dimuat di awal)
        $this->db->select('up.id_pers, up.NamaPers, up.npwp, u.email as email_kontak, u.name as nama_kontak_user'); //
        $this->db->from('user_perusahaan up'); //
        $this->db->join('user u', 'up.id_pers = u.id', 'left'); //
        $this->db->where('up.id_pers', $id_pers); //
        $data['perusahaan'] = $this->db->get()->row_array(); //
        log_message('debug', 'ADMIN HISTORI KUOTA - Data Perusahaan: ' . print_r($data['perusahaan'], true)); //

        if (!$data['perusahaan']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data perusahaan tidak ditemukan untuk ID: ' . $id_pers . '</div>'); //
            redirect('admin/monitoring_kuota');
            return;
        }
        $data['id_pers_untuk_histori'] = $id_pers; //
        // Data untuk rincian kuota barang dan log transaksi akan dimuat via AJAX
        $this->load->view('templates/header', $data); //
        $this->load->view('templates/sidebar', $data); //
        $this->load->view('templates/topbar', $data); //
        $this->load->view('admin/histori_kuota_perusahaan_view', $data); //
        $this->load->view('templates/footer'); //
    }

    // Method untuk mengambil data rincian kuota barang via AJAX
    public function ajax_get_rincian_kuota_barang($id_pers = 0)
    {
        // Pastikan ini adalah AJAX request jika perlu
        // if (!$this->input->is_ajax_request()) {
        //    exit('No direct script access allowed');
        // }

        if ($id_pers == 0 || !is_numeric($id_pers)) {
            $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(['error' => 'ID Perusahaan tidak valid.']));
            return;
        }

        $this->db->select('ukb.*'); // Ambil semua kolom dari user_kuota_barang
        $this->db->from('user_kuota_barang ukb'); //
        $this->db->where('ukb.id_pers', $id_pers); //
        $this->db->order_by('ukb.nama_barang ASC, ukb.waktu_pencatatan DESC'); //
        $rincian_kuota = $this->db->get()->result_array(); //

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(['data' => $rincian_kuota]));
    }

    // Method untuk mengambil log transaksi kuota barang via AJAX
    public function ajax_get_log_transaksi_kuota($id_pers = 0)
    {
        if ($id_pers == 0 || !is_numeric($id_pers)) {
             $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(['error' => 'ID Perusahaan tidak valid.']));
            return;
        }

        $this->db->select('lk.*, u_admin.name as nama_pencatat'); //
        $this->db->from('log_kuota_perusahaan lk'); //
        $this->db->join('user u_admin', 'lk.dicatat_oleh_user_id = u_admin.id', 'left'); //
        $this->db->where('lk.id_pers', $id_pers); //
        $this->db->order_by('lk.tanggal_transaksi', 'DESC'); //
        $this->db->order_by('lk.id_log', 'DESC'); //
        $log_transaksi = $this->db->get()->result_array(); //
        
        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(['data' => $log_transaksi]));
    }

    public function detail_permohonan_admin($id_permohonan = 0)
    {
        // Aktifkan logging di awal method untuk memastikan method ini terpanggil
        log_message('debug', 'DETAIL PERMOHONAN ADMIN - Method dipanggil dengan id_permohonan: ' . $id_permohonan);

        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            log_message('error', 'DETAIL PERMOHONAN ADMIN - ID Permohonan tidak valid: ' . $id_permohonan);
            redirect('admin/permohonanMasuk');
            return;
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Permohonan Impor ID: ' . htmlspecialchars($id_permohonan);
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        // 1. Ambil data permohonan utama
        $this->db->select('up.*, up.file_bc_manifest, upr.NamaPers, upr.npwp, u_pemohon.name as nama_pengaju_permohonan, u_pemohon.email as email_pengaju_permohonan, u_petugas.name as nama_petugas_pemeriksa');
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        // Asumsi: user_permohonan.petugas adalah ID dari tabel 'petugas', dan 'petugas.id_user' adalah FK ke 'user.id'
        $this->db->join('petugas p', 'up.petugas = p.id', 'left'); // Sesuaikan jika 'up.petugas' merujuk langsung ke user.id
        $this->db->join('user u_petugas', 'p.id_user = u_petugas.id', 'left'); // Jika 'up.petugas' adalah ID user, join ini tidak perlu melalui tabel 'petugas'
        $this->db->where('up.id', $id_permohonan);
        $data['permohonan_detail'] = $this->db->get()->row_array();

        log_message('debug', 'DETAIL PERMOHONAN ADMIN - Query Permohonan: ' . $this->db->last_query());
        log_message('debug', 'DETAIL PERMOHONAN ADMIN - Data Permohonan: ' . print_r($data['permohonan_detail'], true));


        if (!$data['permohonan_detail']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data permohonan dengan ID ' . htmlspecialchars($id_permohonan) . ' tidak ditemukan.</div>');
            log_message('error', 'DETAIL PERMOHONAN ADMIN - Data permohonan tidak ditemukan untuk ID: ' . $id_permohonan);
            redirect('admin/permohonanMasuk');
            return;
        }

        // 2. Ambil data LHP (jika ada)
        $data['lhp_detail'] = $this->db->get_where('lhp', ['id_permohonan' => $id_permohonan])->row_array();
        log_message('debug', 'DETAIL PERMOHONAN ADMIN - Data LHP: ' . print_r($data['lhp_detail'], true));

        // 3. Ambil data barang yang diajukan

        // Load view
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/detail_permohonan_admin_view', $data);
        $this->load->view('templates/footer');
    }

    // Di dalam class Admin extends CI_Controller

    public function hapus_permohonan($id_permohonan = 0)
    {
        // Pastikan hanya admin yang bisa akses (sudah ada di constructor)
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid untuk dihapus.</div>');
            redirect('admin/permohonanMasuk');
            return;
        }

        // Ambil data permohonan untuk validasi dan mungkin untuk menghapus file terkait
        $permohonan = $this->db->get_where('user_permohonan', ['id' => $id_permohonan])->row_array();

        if (!$permohonan) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan dengan ID '.htmlspecialchars($id_permohonan).' tidak ditemukan.</div>');
            redirect('admin/permohonanMasuk');
            return;
        }

        // Logika tambahan: Hanya izinkan hapus untuk status tertentu jika perlu
        // Misalnya, jangan izinkan hapus jika sudah "Selesai (Disetujui)"
        // if ($permohonan['status'] == '3' || $permohonan['status'] == '4') {
        //     $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Permohonan yang sudah selesai tidak dapat dihapus.</div>');
        //     redirect('admin/permohonanMasuk');
        //     return;
        // }

        // Hapus file terkait jika ada (misalnya file BC 1.1 Manifest)
        if (!empty($permohonan['file_bc_manifest']) && file_exists('./uploads/bc_manifest/' . $permohonan['file_bc_manifest'])) {
            if (@unlink('./uploads/bc_manifest/' . $permohonan['file_bc_manifest'])) {
                log_message('info', 'File BC Manifest ' . $permohonan['file_bc_manifest'] . ' berhasil dihapus untuk permohonan ID: ' . $id_permohonan);
            } else {
                log_message('error', 'Gagal menghapus file BC Manifest ' . $permohonan['file_bc_manifest'] . ' untuk permohonan ID: ' . $id_permohonan);
                // Anda bisa memilih untuk menghentikan proses atau melanjutkan penghapusan data DB
            }
        }
        // Tambahkan penghapusan file lain jika ada (misal file surat tugas, file LHP, dll, tergantung kebijakan bisnis)

        // Hapus data terkait di tabel lain (misalnya LHP jika ada, atau data penunjukan petugas jika tidak on delete cascade)
        // $this->db->where('id_permohonan', $id_permohonan)->delete('lhp');
        // Hati-hati dengan relasi data!

        // Hapus permohonan utama
        $this->db->where('id', $id_permohonan);
        if ($this->db->delete('user_permohonan')) {
            log_message('info', 'Permohonan ID ' . $id_permohonan . ' berhasil dihapus oleh Admin ID: ' . $this->session->userdata('user_id')); // Asumsi user_id admin ada di sesi
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Permohonan dengan ID Aju '.htmlspecialchars($id_permohonan).' berhasil dihapus.</div>');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal menghapus permohonan. Silakan coba lagi.</div>');
        }
        redirect('admin/permohonanMasuk');
    }

    public function edit_permohonan($id_permohonan = 0)
    {
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            redirect('admin/permohonanMasuk');
            return;
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Permohonan (Admin)';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(); // Admin yang login

        $permohonan = $this->db->select('up.*, upr.NamaPers as NamaPerusahaanPemohon') // Ambil nama perusahaan untuk info
                            ->from('user_permohonan up')
                            ->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left')
                            ->where('up.id', $id_permohonan)
                            ->get()->row_array();

        if (!$permohonan) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan.</div>');
            redirect('admin/permohonanMasuk');
            return;
        }

        // Logika pembatasan edit berdasarkan status (sesuaikan dengan kebutuhan admin)
        // Admin mungkin memiliki lebih banyak kelonggaran untuk mengedit dibandingkan user
        // Misalnya, admin masih bisa edit walau sudah ada penunjukan petugas (status 1)
        // if (!in_array($permohonan['status'], ['0', '5', '1'])) { // Contoh: Hanya status ini yang boleh diedit admin
        //     $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Permohonan ini tidak dapat diedit lagi (Status: '.htmlspecialchars(status_permohonan_text($permohonan['status'])).').</div>');
        //     redirect('admin/detail_permohonan_admin/' . $id_permohonan);
        //     return;
        // }
        $data['permohonan_edit'] = $permohonan;
        // Untuk data perusahaan (jika perlu ditampilkan di form edit admin)
        $data['user_perusahaan_pemohon'] = $this->db->get_where('user_perusahaan', ['id_pers' => $permohonan['id_pers']])->row_array();


        // Ambil daftar kuota barang milik perusahaan pemohon (bukan admin)
        // Ini penting agar admin mengedit berdasarkan kuota perusahaan yang bersangkutan
        $id_user_pemohon = $permohonan['id_pers']; // ID user (perusahaan) yang mengajukan
        $this->db->select('id_kuota_barang, nama_barang, remaining_quota_barang, nomor_skep_asal');
        $this->db->from('user_kuota_barang');
        $this->db->where('id_pers', $id_user_pemohon);
        $this->db->group_start();
        $this->db->where('remaining_quota_barang >', 0);
        if (isset($permohonan['id_kuota_barang_digunakan'])) {
            $this->db->or_where('id_kuota_barang', $permohonan['id_kuota_barang_digunakan']);
        }
        $this->db->group_end();
        $this->db->where('status_kuota_barang', 'active');
        $this->db->order_by('nama_barang', 'ASC');
        $data['list_barang_berkuota'] = $this->db->get()->result_array();

        // Aturan Validasi Form (Admin mungkin bisa mengedit lebih banyak field atau memiliki aturan berbeda)
        // Untuk contoh ini, kita gunakan aturan yang mirip dengan user, tapi Anda bisa sesuaikan
        $this->form_validation->set_rules('nomorSurat', 'Nomor Surat', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('TglSurat', 'Tanggal Surat', 'trim|required');
        // ... (tambahkan semua aturan validasi yang relevan untuk admin) ...
        $this->form_validation->set_rules('NamaBarang', 'Nama Barang', 'trim|required'); // Dari hidden input
        $this->form_validation->set_rules('id_kuota_barang_selected', 'ID Kuota Barang', 'trim|required|numeric'); // Dari hidden input
        $this->form_validation->set_rules('JumlahBarang', 'Jumlah Barang', 'trim|required|numeric|greater_than[0]');


        // Validasi untuk file_bc_manifest (jika admin mengupload file baru)
        if (isset($_FILES['file_bc_manifest_admin_edit']) && $_FILES['file_bc_manifest_admin_edit']['error'] != UPLOAD_ERR_NO_FILE) {
            $this->form_validation->set_rules('file_bc_manifest_admin_edit', 'File BC 1.1 / Manifest (Baru)', 'callback_admin_check_file_bc_manifest_upload');
        }

        if ($this->form_validation->run() == false) {
            // Load view form edit untuk admin
            // Anda mungkin perlu membuat view baru: application/views/admin/form_edit_permohonan_admin.php
            // yang mirip dengan views/user/edit_permohonan_form.php tapi disesuaikan untuk admin
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/form_edit_permohonan_admin', $data); // Buat view ini
            $this->load->view('templates/footer');
        } else {
            // Proses update data (mirip dengan User::editpermohonan)
            $id_kuota_barang_dipilih = (int)$this->input->post('id_kuota_barang_selected');
            $nama_barang_input_form = $this->input->post('NamaBarang');
            $jumlah_barang_dimohon = (float)$this->input->post('JumlahBarang');

            // Validasi ulang kuota (penting!)
            $kuota_valid_db = $this->db->get_where('user_kuota_barang', [
                'id_kuota_barang' => $id_kuota_barang_dipilih,
                'id_pers' => $id_user_pemohon, // Gunakan ID user pemohon
                'status_kuota_barang' => 'active'
            ])->row_array();

            if (!$kuota_valid_db) { /* ... error dan redirect ... */ }
            if ($kuota_valid_db['nama_barang'] != $nama_barang_input_form) { /* ... error dan redirect ... */ }
            
            $sisa_kuota_efektif_untuk_validasi = (float)$kuota_valid_db['remaining_quota_barang'];
            if ($permohonan['id_kuota_barang_digunakan'] == $id_kuota_barang_dipilih) {
                $sisa_kuota_efektif_untuk_validasi += (float)$permohonan['JumlahBarang'];
            }
            if ($jumlah_barang_dimohon > $sisa_kuota_efektif_untuk_validasi) { /* ... error dan redirect ... */ }

            // Handle upload file baru oleh admin
            $nama_file_bc_manifest_update = $permohonan['file_bc_manifest']; // Default pakai yang lama
            if (isset($_FILES['file_bc_manifest_admin_edit']) && $_FILES['file_bc_manifest_admin_edit']['error'] != UPLOAD_ERR_NO_FILE) {
                $config_upload_bc = $this->_get_upload_config_admin('./uploads/bc_manifest/', 'pdf', 2048); // Mungkin perlu method _get_upload_config_admin() jika path/logika berbeda
                if (!$config_upload_bc) { /* ... error dan redirect ... */ }
                
                // Gunakan instance upload yang berbeda untuk admin jika perlu
                // $this->load->library('upload', $config_upload_bc, 'admin_bc_upload');
                // $this->admin_bc_upload->initialize($config_upload_bc);
                // if ($this->admin_bc_upload->do_upload('file_bc_manifest_admin_edit')) { ... }

                $this->upload->initialize($config_upload_bc, TRUE); // Asumsi instance global bisa di-reset
                if ($this->upload->do_upload('file_bc_manifest_admin_edit')) {
                    if (!empty($permohonan['file_bc_manifest']) && file_exists('./uploads/bc_manifest/' . $permohonan['file_bc_manifest'])) {
                        @unlink('./uploads/bc_manifest/' . $permohonan['file_bc_manifest']);
                    }
                    $nama_file_bc_manifest_update = $this->upload->data('file_name');
                } else {
                    // ... handle error upload dan reload view form edit admin ...
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload File BC 1.1 Gagal: ' . $this->upload->display_errors('', '') . '</div>');
                    // Re-populate data dan load view lagi
                    $this->load->view('templates/header', $data);
                    // ... load view lainnya ...
                    $this->load->view('admin/form_edit_permohonan_admin', $data);
                    $this->load->view('templates/footer');
                    return;
                }
            }

            $data_update = [
                'nomorSurat'    => $this->input->post('nomorSurat'),
                'TglSurat'      => $this->input->post('TglSurat'),
                // ... field lain yang boleh diedit admin ...
                'NamaBarang'    => $nama_barang_input_form,
                'JumlahBarang'  => $jumlah_barang_dimohon,
                'id_kuota_barang_digunakan' => $id_kuota_barang_dipilih,
                'NoSkep'        => $kuota_valid_db['nomor_skep_asal'], // Update NoSkep jika kuota barang berubah
                'file_bc_manifest' => $nama_file_bc_manifest_update,
                // 'diedit_oleh_admin_id' => $data['user']['id'], // Catat admin yang mengedit
                // 'waktu_edit_admin' => date('Y-m-d H:i:s')
            ];

            $this->db->where('id', $id_permohonan);
            // Admin bisa mengedit permohonan milik siapa saja, jadi tidak perlu where id_pers admin
            $this->db->update('user_permohonan', $data_update);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Permohonan berhasil diupdate oleh Admin.</div>');
            redirect('admin/detail_permohonan_admin/' . $id_permohonan);
        }
    }

    public function admin_check_file_bc_manifest_upload($str)
    {
        // Anda bisa menggunakan logika yang sama dengan User::check_file_bc_manifest_upload_edit
        // atau membuat logika khusus jika ada perbedaan
        $field_name = 'file_bc_manifest_admin_edit';
        if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] == UPLOAD_ERR_NO_FILE) {
            // Jika admin mengedit dan tidak upload file baru, ini valid
            return TRUE;
        }
        // Jika ada file baru, validasi seperti biasa
        $config_upload_rules = $this->_get_upload_config_admin('./uploads/dummy_path_for_rules/', 'pdf', 2048);
        $file = $_FILES[$field_name];
        // ... (logika validasi tipe dan ukuran) ...
        $allowed_extensions_str = $config_upload_rules['allowed_types'];
        $allowed_extensions_arr = explode('|', $allowed_extensions_str);
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions_arr)) {
            $this->form_validation->set_message('admin_check_file_bc_manifest_upload', "Tipe file {field} tidak diizinkan (Hanya PDF).");
            return FALSE;
        }
        $max_size_bytes = $config_upload_rules['max_size'] * 1024;
        if ($file['size'] > $max_size_bytes) {
            $this->form_validation->set_message('admin_check_file_bc_manifest_upload', "Ukuran file {field} melebihi batas.");
            return FALSE;
        }
        return TRUE;
    }

} // End class Admin