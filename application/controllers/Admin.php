<?php
defined('BASEPATH') or exit('No direct script access allowed');
use PragmaRX\Google2FA\Google2FA;

class Admin extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
         is_loggedin(); 

        $email_user_login = $this->session->userdata('email');
        if (!$email_user_login) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Sesi Anda tidak valid. Silakan login kembali.</div>');
            redirect('auth/logout');
            exit;
        }

        $user_data_from_db = $this->db->get_where('user', ['email' => $email_user_login])->row_array();
        
        if (!$user_data_from_db) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">User tidak ditemukan. Sesi Anda telah diakhiri.</div>');
            redirect('auth/logout');
            exit;
        }

        if ($user_data_from_db['role_id'] != 1) { 
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Access Denied! You are not authorized to access this page.</div>');
            redirect('auth/blocked');
            exit; 
        }

        $this->load->helper(array('form', 'url', 'repack_helper', 'download')); 
        $this->load->library('form_validation'); 
        $this->load->library('upload'); 
        $this->load->library('session'); 
        if (!isset($this->db)) {
            $this->load->database(); 
        }
    }

    public function setup_mfa()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Setup Multi-Factor Authentication';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        
        if (empty($data['user']['google2fa_secret'])) {
            $secretKey = $google2fa->generateSecretKey();
            $this->db->where('id', $data['user']['id']);
            $this->db->update('user', ['google2fa_secret' => $secretKey]);
        } else {
            $secretKey = $data['user']['google2fa_secret'];
        }

        $companyName = 'Repack Papin';
        $userEmail = $data['user']['email'];

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            $companyName,
            $userEmail,
            $secretKey
        );

        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(400),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new \BaconQrCode\Writer($renderer);
        $qrCodeImage = $writer->writeString($qrCodeUrl);

        $qrCodeDataUri = 'data:image/svg+xml;base64,' . base64_encode($qrCodeImage);

        $data['qr_code_data_uri'] = $qrCodeDataUri;
        $data['secret_key'] = $secretKey;

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/mfa_setup', $data);
        $this->load->view('templates/footer');
    }

    public function verify_mfa()
    {
        $userId = $this->session->userdata('user_id');
        $user = $this->db->get_where('user', ['id' => $userId])->row_array();
        $secret = $user['google2fa_secret'];

        $oneTimePassword = $this->input->post('one_time_password');

        $google2fa = new \PragmaRX\Google2FA\Google2FA();

        $window = 4;
        $isValid = $google2fa->verifyKey($secret, $oneTimePassword);

        if ($isValid) {
            $this->db->where('id', $userId);
            $this->db->update('user', ['is_mfa_enabled' => 1]);

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Autentikasi Dua Faktor (MFA) berhasil diaktifkan!</div>');
            redirect('admin/edit_profil');
        } else {
            $this->session->set_flashdata('error', 'Kode verifikasi salah. Silakan coba lagi.');
            redirect('admin/setup_mfa');
        }
    }

    public function reset_mfa()
    {
        // Ambil ID user yang sedang login dari sesi
        $user_id = $this->session->userdata('user_id');

        // Update database untuk menonaktifkan MFA dan menghapus secret key yang lama
        $this->db->where('id', $user_id);
        $this->db->update('user', [
            'is_mfa_enabled' => 0,          // Set status MFA menjadi TIDAK AKTIF
            'google2fa_secret' => NULL    // Hapus secret key agar yang baru dibuat
        ]);

        // Beri pesan kepada pengguna bahwa MFA telah dinonaktifkan
        $this->session->set_flashdata('message', '<div class="alert alert-info" role="alert">MFA Anda telah dinonaktifkan. Silakan lakukan pengaturan ulang untuk mengaktifkannya kembali.</div>');

        // Arahkan pengguna ke halaman setup MFA
        redirect('admin/setup_mfa');
    }

    public function edit_profil()
    {

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Profil Saya';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $user_id = $data['user']['id'];


        if ($this->input->method() === 'post') {
            $update_data_user = [];
            $name_input = $this->input->post('name', true); 

            if (!empty($name_input) && $name_input !== $data['user']['name']) {
                $update_data_user['name'] = htmlspecialchars($name_input);
            }
            
            $current_login_identifier = $data['user']['email'];
            $new_login_identifier = $this->input->post('login_identifier', true); 

            if (!empty($new_login_identifier) && $new_login_identifier !== $current_login_identifier) {
                $this->form_validation->set_rules('login_identifier', 'Email Login', 'trim|required|valid_email|is_unique[user.email.id.'.$user_id.']');
                if ($this->form_validation->run() == TRUE) {
                     $update_data_user['email'] = htmlspecialchars($new_login_identifier);
                } else {
                }
            }


            if (!empty($update_data_user)) {
                $this->db->where('id', $user_id);
                $this->db->update('user', $update_data_user);
                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Profil berhasil diupdate.</div>');
                if (isset($update_data_user['name'])) {
                    $this->session->set_userdata('name', $update_data_user['name']);
                }
                if (isset($update_data_user['email'])) {
                    $this->session->set_userdata('email', $update_data_user['email']);
                }
            }


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
                $config_profile['max_size']      = '2048'; 
                $config_profile['max_width']     = '1024';
                $config_profile['max_height']    = '1024';
                $config_profile['encrypt_name']  = TRUE;

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

                    $this->session->set_userdata('user_image', $new_image_name);
                    $current_flash = $this->session->flashdata('message');
                    $this->session->set_flashdata('message', ($current_flash ? $current_flash . '<br>' : '') . '<div class="alert alert-success" role="alert">Foto profil berhasil diupdate.</div>');

                } else {
                    $current_flash = $this->session->flashdata('message');
                    $this->session->set_flashdata('message', ($current_flash ? $current_flash . '<br>' : '') .'<div class="alert alert-danger" role="alert">Upload Foto Profil Gagal: ' . $this->profile_upload->display_errors('', '') . '</div>');
                }
            }
            redirect('admin/edit_profil');
            return; 
        }

        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/form_edit_profil_admin', $data);
        $this->load->view('templates/footer');
    }

    public function index()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Admin Dashboard';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        
        $data['total_users'] = $this->db->where_in('role_id', [2,3,4])->count_all_results('user'); 
        $data['pending_permohonan'] = $this->db->where_in('status', ['0', '1', '2', '5'])->count_all_results('user_permohonan');
        $data['pending_kuota_requests'] = $this->db->where('status', 'pending')->count_all_results('user_pengajuan_kuota');

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/index', $data);
        $this->load->view('templates/footer');
    }


    public function monitoring_kuota()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Monitoring Kuota Perusahaan (per Jenis Barang)';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

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

    public function delete_user($user_id = 0)
    {
        if ($user_id == 0 || !is_numeric($user_id)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">User ID tidak valid.</div>');
            redirect('admin/manajemen_user');
            return;
        }

        if ($user_id == $this->session->userdata('user_id')) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Anda tidak dapat menghapus akun Anda sendiri.</div>');
            redirect('admin/manajemen_user');
            return;
        }

        if ($user_id == 1) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Akun Super Admin (ID 1) tidak dapat dihapus.</div>');
            redirect('admin/manajemen_user');
            return;
        }

        $user_to_delete = $this->db->get_where('user', ['id' => $user_id])->row_array();

        if (!$user_to_delete) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">User tidak ditemukan.</div>');
            redirect('admin/manajemen_user');
            return;
        }

        $this->db->trans_start();

        if ($user_to_delete['role_id'] == 3) {
            $this->db->delete('petugas', ['id_user' => $user_id]);
        }

        $this->db->delete('user', ['id' => $user_id]);
        
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal menghapus user karena terjadi kesalahan database.</div>');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">User ' . htmlspecialchars($user_to_delete['name']) . ' berhasil dihapus.</div>');
        }

        redirect('admin/manajemen_user');
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
            'u_real_petugas.name as nama_petugas_assigned' 
        );
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $this->db->join('petugas p_assigned', 'up.petugas = p_assigned.id', 'left'); 
        $this->db->join('user u_real_petugas', 'p_assigned.id_user = u_real_petugas.id', 'left'); 
        
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
        $admin_user = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['user'] = $admin_user;
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Proses Finalisasi Permohonan Impor';

        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {  return; }

        $this->db->select('up.*, upr.NamaPers, upr.npwp, upr.alamat, upr.NoSkep'); 
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->where('up.id', $id_permohonan);
        $data['permohonan'] = $this->db->get()->row_array();

        if (!$data['permohonan']) {  return; }
        
        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $data['permohonan']['id_pers']])->row_array();
        if (!$data['user_perusahaan']) {
            $data['user_perusahaan'] = ['NamaPers' => 'N/A', 'alamat' => 'N/A', 'NoSkep' => 'N/A', 'npwp' => 'N/A'];
        }


        $data['lhp'] = $this->db->get_where('lhp', ['id_permohonan' => $id_permohonan])->row_array();
        if (!$data['lhp'] || $data['permohonan']['status'] != '2' || empty($data['lhp']['NoLHP']) || empty($data['lhp']['TglLHP'])) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">LHP belum lengkap atau status permohonan (ID '.htmlspecialchars($id_permohonan).') tidak valid untuk finalisasi.</div>');
            redirect('admin/detail_permohonan_admin/' . $id_permohonan);
            return;
        }
        
        $this->form_validation->set_rules('status_final', 'Status Final Permohonan', 'required|in_list[3,4]');
        $this->form_validation->set_rules('nomorSetuju', 'Nomor Surat Persetujuan/Penolakan', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('tgl_S', 'Tanggal Surat Persetujuan/Penolakan', 'trim|required');
        $this->form_validation->set_rules('link', 'Link Surat Keputusan (Opsional)', 'trim|callback__valid_url_format_check'); 

        if ($this->input->post('status_final') == '4') { 
            $this->form_validation->set_rules('catatan_penolakan', 'Catatan Penolakan', 'trim|required');
        } elseif ($this->input->post('status_final') == '3') { 
            if (empty($data['permohonan']['file_surat_keputusan']) && (!isset($_FILES['file_surat_keputusan']) || $_FILES['file_surat_keputusan']['error'] == UPLOAD_ERR_NO_FILE)) {
                $this->form_validation->set_rules('file_surat_keputusan', 'File Surat Persetujuan Pengeluaran', 'required');
            }
            if (isset($_FILES['file_surat_keputusan']) && $_FILES['file_surat_keputusan']['error'] != UPLOAD_ERR_NO_FILE) {
                $this->form_validation->set_rules('file_surat_keputusan', 'File Surat Persetujuan Pengeluaran', 'callback_admin_check_file_sk_upload'); 
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
                'catatan_penolakan' => ($status_final_permohonan == '4') ? $catatan_penolakan_input : null, 
                'time_selesai'  => date("Y-m-d H:i:s"),
                'status'        => $status_final_permohonan,

            ];

            $nama_file_sk_baru = $data['permohonan']['file_surat_keputusan']; 

            if ($status_final_permohonan == '3' && isset($_FILES['file_surat_keputusan']) && $_FILES['file_surat_keputusan']['error'] != UPLOAD_ERR_NO_FILE) {
                $upload_dir_sk = './uploads/sk_penyelesaian/'; 
                $config_sk = $this->_get_upload_config($upload_dir_sk, 'pdf|jpg|png|jpeg', 2048); 

                if (!$config_sk) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Konfigurasi direktori upload SK gagal.</div>');
                    redirect('admin/prosesSurat/' . $id_permohonan); return;
                }

                $this->load->library('upload', $config_sk, 'sk_penyelesaian_upload');
                $this->sk_penyelesaian_upload->initialize($config_sk);

                if ($this->sk_penyelesaian_upload->do_upload('file_surat_keputusan')) {
                    if (!empty($data['permohonan']['file_surat_keputusan']) && file_exists($upload_dir_sk . $data['permohonan']['file_surat_keputusan'])) {
                        @unlink($upload_dir_sk . $data['permohonan']['file_surat_keputusan']);
                    }
                    $nama_file_sk_baru = $this->sk_penyelesaian_upload->data('file_name');
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload File Surat Keputusan Gagal: ' . $this->sk_penyelesaian_upload->display_errors('', '') . '</div>');
                    $this->load->view('templates/header', $data);
                    $this->load->view('templates/sidebar', $data);
                    $this->load->view('templates/topbar', $data);
                    $this->load->view('admin/prosesSurat', $data);
                    $this->load->view('templates/footer');
                    return;
                }
            }
            if ($status_final_permohonan == '3') {
                $data_update_permohonan['file_surat_keputusan'] = $nama_file_sk_baru;
            } else {
                if (!empty($data['permohonan']['file_surat_keputusan']) && file_exists('./uploads/sk_penyelesaian/' . $data['permohonan']['file_surat_keputusan'])) {
                    @unlink('./uploads/sk_penyelesaian/' . $data['permohonan']['file_surat_keputusan']);
                }
                $data_update_permohonan['file_surat_keputusan'] = null;
            }


            $this->db->where('id', $id_permohonan);
            $this->db->update('user_permohonan', $data_update_permohonan);
            if ($status_final_permohonan == '3' && isset($data['lhp']['JumlahBenar']) && $data['lhp']['JumlahBenar'] > 0) {
    
                $jumlah_dipotong = (float)$data['lhp']['JumlahBenar'];
                $id_kuota_barang_terpakai = $data['permohonan']['id_kuota_barang_digunakan'];
                $id_perusahaan = $data['permohonan']['id_pers'];

                if ($id_kuota_barang_terpakai) {
                    $this->db->trans_start();

                    $kuota_barang_saat_ini = $this->db->get_where('user_kuota_barang', ['id_kuota_barang' => $id_kuota_barang_terpakai])->row_array();
                    
                    if ($kuota_barang_saat_ini) {
                        $kuota_sebelum = (float)$kuota_barang_saat_ini['remaining_quota_barang'];
                        $kuota_sesudah = $kuota_sebelum - $jumlah_dipotong;

                        $this->db->where('id_kuota_barang', $id_kuota_barang_terpakai);
                        $this->db->set('remaining_quota_barang', 'remaining_quota_barang - ' . $this->db->escape($jumlah_dipotong), FALSE);
                        $this->db->update('user_kuota_barang');

                        $keterangan_log = 'Pemotongan kuota dari persetujuan impor. No. Surat: ' . ($data_update_permohonan['nomorSetuju'] ?? '-');
                        $this->_log_perubahan_kuota(
                            $id_perusahaan,
                            'pengurangan',
                            $jumlah_dipotong,
                            $kuota_sebelum,
                            $kuota_sesudah,
                            $keterangan_log,
                            $id_permohonan, 
                            'permohonan_impor_disetujui', 
                            $admin_user['id'], 
                            $kuota_barang_saat_ini['nama_barang'], 
                            $id_kuota_barang_terpakai 
                        );
                    }
                    
                    $this->db->trans_complete();
                }
            }

            $pesan_status_akhir = ($status_final_permohonan == '3') ? 'Disetujui' : 'Ditolak';
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Status permohonan ID '.htmlspecialchars($id_permohonan).' telah berhasil diproses menjadi "'. $pesan_status_akhir .'"!</div>');
            redirect('admin/permohonanMasuk');
        }
    }

    public function admin_check_file_sk_upload($str) 
    {
        $field_name = 'file_surat_keputusan';

        if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] == UPLOAD_ERR_NO_FILE) {
            $this->form_validation->set_message('admin_check_file_sk_upload', 'Kolom {field} wajib diisi.');
            return FALSE;
        }

        $config_rules = $this->_get_upload_config('./uploads/dummy/', 'pdf|jpg|png|jpeg', 2048);
        if (!$config_rules) {  return FALSE; }

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
        if (empty($str)) { 
            return TRUE;
        }
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

        if (!empty($log_data['id_pers']) && !empty($log_data['nama_barang_terkait'])) { 
            $this->db->insert('log_kuota_perusahaan', $log_data);
        } else {
            log_message('error', 'Data log kuota tidak lengkap, tidak disimpan: ' . print_r($log_data, true));
        }
    }

    public function penunjukanPetugas($id_permohonan)
    {
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Penunjukan Petugas Pemeriksa';

        $this->db->select('up.*, upr.NamaPers'); 
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
            $permohonan['status'] = '5'; 
            $data['permohonan']['status'] = '5'; 
            $this->session->set_flashdata('message_transient', '<div class="alert alert-info" role="alert">Status permohonan ID ' . htmlspecialchars($id_permohonan) . ' telah diubah menjadi "Diproses Admin". Lanjutkan dengan menunjuk petugas.</div>');
        }

        $data['list_petugas'] = $this->db->order_by('Nama', 'ASC')->get('petugas')->result_array();
        if (empty($data['list_petugas'])) {
            log_message('error', 'Tidak ada data petugas ditemukan di tabel petugas.');
        }

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
            $update_data = [
                'petugas' => $this->input->post('petugas_id'), 
                'NoSuratTugas' => $this->input->post('nomor_surat_tugas'),
                'TglSuratTugas' => $this->input->post('tanggal_surat_tugas'),
                'status' => '1', 
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
                $config_st['max_size']      = '2048'; 
                $config_st['encrypt_name']  = TRUE;

                $this->load->library('upload', $config_st, 'st_upload');
                $this->st_upload->initialize($config_st);

                if ($this->st_upload->do_upload('file_surat_tugas')) {
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


            $this->db->where('id', $id_permohonan);
            $this->db->update('user_permohonan', $update_data);

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
            $tanggal_sk_petugas = $this->input->post('tanggal_sk_petugas'); 
            $admin_notes = $this->input->post('admin_notes');

            $data_update_pengajuan = [
                'status' => $status_pengajuan,
                'admin_notes' => $admin_notes,
                'processed_date' => date('Y-m-d H:i:s'),
                'nomor_sk_petugas' => $nomor_sk_petugas,
                'tanggal_sk_petugas' => !empty($tanggal_sk_petugas) ? $tanggal_sk_petugas : null, 
                'approved_quota' => $approved_quota_input
            ];

            $nama_file_sk = $data['pengajuan']['file_sk_petugas'] ?? null;
            if (($status_pengajuan == 'approved' || $status_pengajuan == 'rejected') && isset($_FILES['file_sk_petugas']) && $_FILES['file_sk_petugas']['error'] != UPLOAD_ERR_NO_FILE) {
                $upload_dir_sk = './uploads/sk_kuota/';
                if (!is_dir($upload_dir_sk)) { @mkdir($upload_dir_sk, 0777, true); }
                if (!is_writable($upload_dir_sk)) {  redirect('admin/proses_pengajuan_kuota/' . $id_pengajuan); return; }
                
                $config_sk['upload_path']   = $upload_dir_sk;
                $config_sk['allowed_types'] = 'pdf|jpg|png|jpeg';
                $config_sk['max_size']      = '2048';
                $config_sk['encrypt_name']  = TRUE;
                $this->load->library('upload', $config_sk, 'sk_upload_instance'); 
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

            $this->db->where('id', $id_pengajuan);
            $this->db->update('user_pengajuan_kuota', $data_update_pengajuan);
            log_message('debug', 'ADMIN PROSES PENGAJUAN KUOTA - user_pengajuan_kuota diupdate. Affected: ' . $this->db->affected_rows());

            if ($status_pengajuan == 'approved' && $approved_quota_input > 0) {
                $id_pers_terkait = $data['pengajuan']['id_pers'];
                $nama_barang_diajukan = $data['pengajuan']['nama_barang_kuota']; 

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
                            0, 
                            $approved_quota_input, 
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

        $data['user'] = $this->db->get_where('user', ['id' => $data['pengajuan']['id_pers']])->row_array(); 
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
        $this->db->join('user u', 'upk.id_pers = u.id', 'left'); 
        $this->db->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $this->db->get()->row_array();

        if (!$data['pengajuan']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data pengajuan kuota tidak ditemukan.</div>');
            redirect('admin/daftar_pengajuan_kuota');
            return;
        }


        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/detail_pengajuan_kuota_view', $data); 
        $this->load->view('templates/footer');
    }

    public function download_sk_kuota_admin($id_pengajuan)
    {
        $this->load->helper('download');
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

        $this->db->select('u.*, ur.role as role_name');
        $this->db->from('user u');
        $this->db->join('user_role ur', 'u.role_id = ur.id', 'left');
        $this->db->order_by('u.name', 'ASC');
        $data['users_list'] = $this->db->get()->result_array();

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
        
        if ($role_id_to_add == 1) {
             $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Penambahan user dengan role Admin tidak diizinkan melalui form ini.</div>');
             redirect('admin/manajemen_user');
             return;
        }

        $data['subtitle'] = 'Tambah User Baru: ' . htmlspecialchars($data['target_role_info']['role']);
        $data['role_id_to_add'] = $role_id_to_add; 

        $this->form_validation->set_rules('name', 'Nama Lengkap', 'required|trim');
        $this->form_validation->set_rules('password', 'Password Awal', 'required|trim|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Konfirmasi Password Awal', 'required|trim|matches[password]');

        $login_identifier_label = '';
        $login_identifier_rules = 'required|trim';
        $data['login_identifier_type_is_email'] = false; 

        if ($role_id_to_add == 2) { 
            $login_identifier_label = 'Email';
            $login_identifier_rules .= '|valid_email|is_unique[user.email]';
            $data['login_identifier_type_is_email'] = true;
        } elseif (in_array($role_id_to_add, [3, 4, 5])) { 
            if ($role_id_to_add == 3) {
                $login_identifier_label = 'NIP Petugas';
                $login_identifier_rules .= '|numeric';
            } elseif ($role_id_to_add == 4) {
                $login_identifier_label = 'NIP Monitoring';
                $login_identifier_rules .= '|numeric';
            } else { 
                $login_identifier_label = 'NIP Petugas Administrasi';
            }
            $login_identifier_rules .= '|is_unique[user.email]';
        } else {
            $login_identifier_label = 'Login Identifier (Email/Username)';
            $login_identifier_rules .= '|is_unique[user.email]';
            $data['login_identifier_type_is_email'] = true; 
        }
        $this->form_validation->set_rules('login_identifier', $login_identifier_label, $login_identifier_rules, [
            'is_unique' => $login_identifier_label . ' ini sudah terdaftar.',
            'numeric'   => $login_identifier_label . ' harus berupa angka.',
            'valid_email'=> $login_identifier_label . ' tidak valid.'
        ]);
        $data['login_identifier_label_view'] = $login_identifier_label; 
        
        if ($role_id_to_add == 3) { 
            $this->form_validation->set_rules('jabatan_petugas', 'Jabatan Petugas', 'trim|required');
        }

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/form_tambah_user_view', $data); 
            $this->load->view('templates/footer');
        } else {
            $login_identifier_input = $this->input->post('login_identifier');
            $force_change_pass = 1; 

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
                if ($role_id_to_add == 3) {
                    $petugas_data_to_insert = [
                        'id_user' => $new_user_id,
                        'Nama' => $user_data_to_insert['name'],
                        'NIP' => $login_identifier_input,
                        'Jabatan' => htmlspecialchars($this->input->post('jabatan_petugas', true))
                    ];
                    $this->db->insert('petugas', $petugas_data_to_insert);
                }

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
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(); 

        $data['target_user'] = $this->db->get_where('user', ['id' => $target_user_id])->row_array();

        if (!$data['target_user'] || $data['target_user']['role_id'] == 1) { 
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
                'force_change_password' => 1 
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
        $data['user'] = $admin_logged_in; 

        $data['target_user_data'] = $this->db->get_where('user', ['id' => $target_user_id])->row_array();

        if (!$data['target_user_data']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">User yang akan diedit tidak ditemukan (ID: '.htmlspecialchars($target_user_id).').</div>');
            redirect('admin/manajemen_user');
            return;
        }

        $is_editing_main_admin = ($data['target_user_data']['id'] == 1); 
        
        $target_role_id_for_check = $this->input->post('role_id') ? (int)$this->input->post('role_id') : (int)$data['target_user_data']['role_id'];
        $is_target_petugas_or_monitoring = in_array($target_role_id_for_check, [3, 4]); 

        $data['roles_list'] = $this->db->get('user_role')->result_array();

        $this->form_validation->set_rules('name', 'Nama Lengkap', 'required|trim');

        $original_login_identifier = $data['target_user_data']['email']; 
        $input_login_identifier = $this->input->post('login_identifier'); 

        $login_identifier_rules = 'required|trim';
        $login_identifier_label = '';

        if ($is_target_petugas_or_monitoring) {
            $login_identifier_label = 'NIP';
            $login_identifier_rules .= '|numeric'; 
            if ($input_login_identifier !== null && $input_login_identifier != $original_login_identifier) {
                $login_identifier_rules .= '|is_unique[user.email]';
            }
        } else { 
            $login_identifier_label = 'Email';
            $login_identifier_rules .= '|valid_email';
            if ($input_login_identifier !== null && $input_login_identifier != $original_login_identifier) {
                $login_identifier_rules .= '|is_unique[user.email]';
            }
        }
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

            if ($new_role_id == 3) { 
                if ($this->db->field_exists('id_user', 'petugas')) {
                    $petugas_detail = $this->db->get_where('petugas', ['id_user' => $target_user_id])->row_array();
                    $data_petugas_update = [
                        'Nama' => $update_data_user['name'],
                        'NIP' => $update_data_user['email'], 
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
                }
            }
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Data user '.htmlspecialchars($update_data_user['name']).' berhasil diupdate.</div>');
            redirect('admin/manajemen_user');
        }
    }

    public function histori_kuota_perusahaan($id_pers = 0)
    {
        log_message('debug', 'ADMIN HISTORI KUOTA - Method dipanggil dengan id_pers: ' . $id_pers); 

        if ($id_pers == 0 || !is_numeric($id_pers)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Perusahaan tidak valid.</div>'); 
            redirect('admin/monitoring_kuota');
            return;
        }

        $data['title'] = 'Returnable Package'; 
        $data['subtitle'] = 'Histori & Detail Kuota Perusahaan'; 
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(); 

        $this->db->select('up.id_pers, up.NamaPers, up.npwp, u.email as email_kontak, u.name as nama_kontak_user'); 
        $this->db->from('user_perusahaan up'); 
        $this->db->join('user u', 'up.id_pers = u.id', 'left'); 
        $this->db->where('up.id_pers', $id_pers); 
        $data['perusahaan'] = $this->db->get()->row_array(); 
        log_message('debug', 'ADMIN HISTORI KUOTA - Data Perusahaan: ' . print_r($data['perusahaan'], true)); 

        if (!$data['perusahaan']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data perusahaan tidak ditemukan untuk ID: ' . $id_pers . '</div>'); 
            redirect('admin/monitoring_kuota');
            return;
        }
        $data['id_pers_untuk_histori'] = $id_pers; 
        $this->load->view('templates/header', $data); 
        $this->load->view('templates/sidebar', $data); 
        $this->load->view('templates/topbar', $data); 
        $this->load->view('admin/histori_kuota_perusahaan_view', $data); 
        $this->load->view('templates/footer'); 
    }

    public function ajax_get_rincian_kuota_barang($id_pers = 0)
    {

        if ($id_pers == 0 || !is_numeric($id_pers)) {
            $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(['error' => 'ID Perusahaan tidak valid.']));
            return;
        }

        $this->db->select('ukb.*'); 
        $this->db->from('user_kuota_barang ukb'); 
        $this->db->where('ukb.id_pers', $id_pers); 
        $this->db->order_by('ukb.nama_barang ASC, ukb.waktu_pencatatan DESC'); 
        $rincian_kuota = $this->db->get()->result_array(); 

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(['data' => $rincian_kuota]));
    }

    public function ajax_get_log_transaksi_kuota($id_pers = 0)
    {
        if ($id_pers == 0 || !is_numeric($id_pers)) {
             $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(['error' => 'ID Perusahaan tidak valid.']));
            return;
        }

        $this->db->select('lk.*, u_admin.name as nama_pencatat'); 
        $this->db->from('log_kuota_perusahaan lk'); 
        $this->db->join('user u_admin', 'lk.dicatat_oleh_user_id = u_admin.id', 'left'); 
        $this->db->where('lk.id_pers', $id_pers); 
        $this->db->order_by('lk.tanggal_transaksi', 'DESC'); 
        $this->db->order_by('lk.id_log', 'DESC'); 
        $log_transaksi = $this->db->get()->result_array(); 
        
        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(['data' => $log_transaksi]));
    }

    public function detail_permohonan_admin($id_permohonan = 0)
    {
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

        $this->db->select('up.*, up.file_bc_manifest, upr.NamaPers, upr.npwp, u_pemohon.name as nama_pengaju_permohonan, u_pemohon.email as email_pengaju_permohonan, u_petugas.name as nama_petugas_pemeriksa');
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $this->db->join('petugas p', 'up.petugas = p.id', 'left'); 
        $this->db->join('user u_petugas', 'p.id_user = u_petugas.id', 'left'); 
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

        $data['lhp_detail'] = $this->db->get_where('lhp', ['id_permohonan' => $id_permohonan])->row_array();
        log_message('debug', 'DETAIL PERMOHONAN ADMIN - Data LHP: ' . print_r($data['lhp_detail'], true));


        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/detail_permohonan_admin_view', $data);
        $this->load->view('templates/footer');
    }


    public function hapus_permohonan($id_permohonan = 0)
    {
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid untuk dihapus.</div>');
            redirect('admin/permohonanMasuk');
            return;
        }

        $permohonan = $this->db->get_where('user_permohonan', ['id' => $id_permohonan])->row_array();

        if (!$permohonan) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan dengan ID '.htmlspecialchars($id_permohonan).' tidak ditemukan.</div>');
            redirect('admin/permohonanMasuk');
            return;
        }


        if (!empty($permohonan['file_bc_manifest']) && file_exists('./uploads/bc_manifest/' . $permohonan['file_bc_manifest'])) {
            if (@unlink('./uploads/bc_manifest/' . $permohonan['file_bc_manifest'])) {
                log_message('info', 'File BC Manifest ' . $permohonan['file_bc_manifest'] . ' berhasil dihapus untuk permohonan ID: ' . $id_permohonan);
            } else {
                log_message('error', 'Gagal menghapus file BC Manifest ' . $permohonan['file_bc_manifest'] . ' untuk permohonan ID: ' . $id_permohonan);
            }
        }


        $this->db->where('id', $id_permohonan);
        if ($this->db->delete('user_permohonan')) {
            log_message('info', 'Permohonan ID ' . $id_permohonan . ' berhasil dihapus oleh Admin ID: ' . $this->session->userdata('user_id')); 
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
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(); 

        $permohonan = $this->db->select('up.*, upr.NamaPers as NamaPerusahaanPemohon') 
                            ->from('user_permohonan up')
                            ->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left')
                            ->where('up.id', $id_permohonan)
                            ->get()->row_array();

        if (!$permohonan) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan.</div>');
            redirect('admin/permohonanMasuk');
            return;
        }

        $data['permohonan_edit'] = $permohonan;
        $data['user_perusahaan_pemohon'] = $this->db->get_where('user_perusahaan', ['id_pers' => $permohonan['id_pers']])->row_array();


        $id_user_pemohon = $permohonan['id_pers']; 
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

        $this->form_validation->set_rules('nomorSurat', 'Nomor Surat', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('TglSurat', 'Tanggal Surat', 'trim|required');
        $this->form_validation->set_rules('NamaBarang', 'Nama Barang', 'trim|required'); 
        $this->form_validation->set_rules('id_kuota_barang_selected', 'ID Kuota Barang', 'trim|required|numeric'); 
        $this->form_validation->set_rules('JumlahBarang', 'Jumlah Barang', 'trim|required|numeric|greater_than[0]');


        if (isset($_FILES['file_bc_manifest_admin_edit']) && $_FILES['file_bc_manifest_admin_edit']['error'] != UPLOAD_ERR_NO_FILE) {
            $this->form_validation->set_rules('file_bc_manifest_admin_edit', 'File BC 1.1 / Manifest (Baru)', 'callback_admin_check_file_bc_manifest_upload');
        }

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/form_edit_permohonan_admin', $data); 
            $this->load->view('templates/footer');
        } else {
            $id_kuota_barang_dipilih = (int)$this->input->post('id_kuota_barang_selected');
            $nama_barang_input_form = $this->input->post('NamaBarang');
            $jumlah_barang_dimohon = (float)$this->input->post('JumlahBarang');

            $kuota_valid_db = $this->db->get_where('user_kuota_barang', [
                'id_kuota_barang' => $id_kuota_barang_dipilih,
                'id_pers' => $id_user_pemohon, 
                'status_kuota_barang' => 'active'
            ])->row_array();

            if (!$kuota_valid_db) {  }
            if ($kuota_valid_db['nama_barang'] != $nama_barang_input_form) {  }
            
            $sisa_kuota_efektif_untuk_validasi = (float)$kuota_valid_db['remaining_quota_barang'];
            if ($permohonan['id_kuota_barang_digunakan'] == $id_kuota_barang_dipilih) {
                $sisa_kuota_efektif_untuk_validasi += (float)$permohonan['JumlahBarang'];
            }
            if ($jumlah_barang_dimohon > $sisa_kuota_efektif_untuk_validasi) {  }

            $nama_file_bc_manifest_update = $permohonan['file_bc_manifest']; 
            if (isset($_FILES['file_bc_manifest_admin_edit']) && $_FILES['file_bc_manifest_admin_edit']['error'] != UPLOAD_ERR_NO_FILE) {
                $config_upload_bc = $this->_get_upload_config_admin('./uploads/bc_manifest/', 'pdf', 2048); 
                if (!$config_upload_bc) {  }
                
                $this->upload->initialize($config_upload_bc, TRUE); 
                if ($this->upload->do_upload('file_bc_manifest_admin_edit')) {
                    if (!empty($permohonan['file_bc_manifest']) && file_exists('./uploads/bc_manifest/' . $permohonan['file_bc_manifest'])) {
                        @unlink('./uploads/bc_manifest/' . $permohonan['file_bc_manifest']);
                    }
                    $nama_file_bc_manifest_update = $this->upload->data('file_name');
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload File BC 1.1 Gagal: ' . $this->upload->display_errors('', '') . '</div>');
                    $this->load->view('templates/header', $data);
                    $this->load->view('admin/form_edit_permohonan_admin', $data);
                    $this->load->view('templates/footer');
                    return;
                }
            }

            $data_update = [
                'nomorSurat'    => $this->input->post('nomorSurat'),
                'TglSurat'      => $this->input->post('TglSurat'),
                'NamaBarang'    => $nama_barang_input_form,
                'JumlahBarang'  => $jumlah_barang_dimohon,
                'id_kuota_barang_digunakan' => $id_kuota_barang_dipilih,
                'NoSkep'        => $kuota_valid_db['nomor_skep_asal'], 
                'file_bc_manifest' => $nama_file_bc_manifest_update,
            ];

            $this->db->where('id', $id_permohonan);
            $this->db->update('user_permohonan', $data_update);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Permohonan berhasil diupdate oleh Admin.</div>');
            redirect('admin/detail_permohonan_admin/' . $id_permohonan);
        }
    }

    public function admin_check_file_bc_manifest_upload($str)
    {
        $field_name = 'file_bc_manifest_admin_edit';
        if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] == UPLOAD_ERR_NO_FILE) {
            return TRUE;
        }
        $config_upload_rules = $this->_get_upload_config_admin('./uploads/dummy_path_for_rules/', 'pdf', 2048);
        $file = $_FILES[$field_name];
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

    public function tolak_permohonan_awal($id_permohonan = 0)
    {
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            redirect('admin/permohonanMasuk');
            return;
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Formulir Penolakan Permohonan';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->db->select('up.id, up.nomorSurat, upr.NamaPers, up.status');
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->where('up.id', $id_permohonan);
        $data['permohonan'] = $this->db->get()->row_array();

        if (!$data['permohonan'] || $data['permohonan']['status'] != '0') {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Permohonan ini tidak ditemukan atau statusnya bukan "Baru Masuk" sehingga tidak bisa ditolak langsung.</div>');
            redirect('admin/permohonanMasuk');
            return;
        }

        $this->form_validation->set_rules('alasan_penolakan', 'Alasan Penolakan', 'trim|required');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/form_tolak_permohonan_view', $data); 
            $this->load->view('templates/footer');
        } else {
            $alasan_penolakan = $this->input->post('alasan_penolakan', true);

            $update_data = [
                'status' => '6', 
                'catatan_penolakan' => $alasan_penolakan,
                'time_selesai' => date('Y-m-d H:i:s') 
            ];

            $this->db->where('id', $id_permohonan);
            $this->db->update('user_permohonan', $update_data);

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Permohonan ID ' . htmlspecialchars($id_permohonan) . ' berhasil ditolak.</div>');
            redirect('admin/permohonanMasuk');
        }
    }

}