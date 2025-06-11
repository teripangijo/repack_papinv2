<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Petugas_administrasi extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Helper 'is_loggedin()' mungkin sudah melakukan pengecekan sesi email.
        // Jika belum, _check_auth_pa() akan menanganinya.
        // is_loggedin(); 

        // Load library dan helper yang umum digunakan
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->library('upload');
        $this->load->helper(array('form', 'url', 'repack_helper', 'download')); 
        
        if (!isset($this->db)) {
            $this->load->database();
        }
        
        // Pengecekan otentikasi dan otorisasi khusus untuk Petugas Administrasi
        $excluded_methods = ['logout']; 
        $current_method = $this->router->fetch_method();

        if (!in_array($current_method, $excluded_methods)) {
            $this->_check_auth_petugas_administrasi();
        } 
        elseif (!$this->session->userdata('email') && $current_method != 'logout') {
             $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Sesi tidak valid atau telah berakhir. Silakan login kembali.</div>');
             redirect('auth');
             exit;
        }
        log_message('debug', 'Petugas_administrasi Class Initialized. Method: ' . $this->router->fetch_method());
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
                // Assuming Petugas Administrasi uses email, role_id 5
                // For other roles, adjust validation as needed.
                $this->form_validation->set_rules('login_identifier', 'Email Login', 'trim|required|valid_email|is_unique[user.email.id.'.$user_id.']');
                if ($this->form_validation->run() == TRUE) { // Check validation for this rule specifically if other rules exist
                     $update_data_user['email'] = htmlspecialchars($new_login_identifier);
                } else {
                    // $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">' . validation_errors() . '</div>');
                    // redirect('petugas_administrasi/edit_profil'); // Adjusted redirect
                    // return;
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
                        redirect('petugas_administrasi/edit_profil'); // Adjusted redirect
                        return;
                    }
                }

                if (!is_writable($upload_path_profile)) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload error: Direktori foto profil tidak writable.</div>');
                    redirect('petugas_administrasi/edit_profil'); // Adjusted redirect
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
            redirect('petugas_administrasi/edit_profil'); // Adjusted redirect
            return; 
        }

        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        // Pastikan Anda membuat view ini: application/views/petugas_administrasi/form_edit_profil_admin.php (Adjusted Comment)
        $this->load->view('petugas_administrasi/form_edit_profil_admin', $data);
        $this->load->view('templates/footer');
    }

    private function _check_auth_petugas_administrasi()
    {
        log_message('debug', 'Petugas_administrasi: _check_auth_petugas_administrasi() called. Email session: ' . ($this->session->userdata('email') ?? 'NULL'));
        if (!$this->session->userdata('email')) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Mohon login untuk melanjutkan.</div>');
            redirect('auth');
            exit;
        }
        
        $role_id_session = $this->session->userdata('role_id');
        log_message('debug', 'Petugas_administrasi: _check_auth_petugas_administrasi() - Role ID: ' . ($role_id_session ?? 'NULL'));

        if ($role_id_session != 5) { // Role ID 5 untuk Petugas Administrasi
            log_message('warning', 'Petugas_administrasi: _check_auth_petugas_administrasi() - Akses ditolak, role ID tidak sesuai: ' . $role_id_session);
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Akses Ditolak! Anda tidak diotorisasi untuk mengakses halaman ini.</div>');
            
            if ($role_id_session == 1) redirect('admin');
            elseif ($role_id_session == 2) redirect('user');
            elseif ($role_id_session == 3) redirect('petugas');
            elseif ($role_id_session == 4) redirect('monitoring');
            else redirect('auth/blocked'); 
            exit;
        }
        log_message('debug', 'Petugas_administrasi: _check_auth_petugas_administrasi() passed.');
    }
    
    public function index()
    {
        log_message('debug', 'Petugas_administrasi: index() called.');
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Dashboard Petugas Administrasi';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        
        $data['pending_permohonan'] = $this->db->where_in('status', ['0', '1', '2', '5'])->count_all_results('user_permohonan');
        $data['pending_kuota_requests'] = $this->db->where('status', 'pending')->count_all_results('user_pengajuan_kuota');

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('petugas_administrasi/index', $data); 
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

        log_message('debug', 'PETUGAS_ADMINISTRASI MONITORING KUOTA - Query: ' . $this->db->last_query()); // Adjusted Log
        log_message('debug', 'PETUGAS_ADMINISTRASI MONITORING KUOTA - Data: ' . print_r($data['monitoring_data'], true)); // Adjusted Log

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        // Consider if this view should be specific to petugas_administrasi
        $this->load->view('petugas_administrasi/monitoring_kuota_view', $data); // Adjusted View Path
        $this->load->view('templates/footer');
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
                WHEN '0' THEN 1 
                WHEN '5' THEN 2 
                WHEN '1' THEN 3 
                WHEN '2' THEN 4 
                ELSE 5          
            END ASC, up.time_stamp DESC");
        $data['permohonan'] = $this->db->get()->result_array();
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('petugas_administrasi/permohonan-masuk', $data); // Adjusted View Path
        $this->load->view('templates/footer');
    }

    private function _get_upload_config($upload_path, $allowed_types, $max_size_kb, $max_width = null, $max_height = null) 
    {
        log_message('debug', "Petugas_administrasi Controller: _get_upload_config() called. Path: {$upload_path}, Types: {$allowed_types}, Size: {$max_size_kb}KB"); // Adjusted Log
        if (!is_dir($upload_path)) {
            log_message('debug', 'Petugas_administrasi Controller: _get_upload_config() - Upload path does not exist: ' . $upload_path); // Adjusted Log
            if (!@mkdir($upload_path, 0777, true)) {
                log_message('error', 'Petugas_administrasi Controller: _get_upload_config() - Gagal membuat direktori upload: ' . $upload_path . ' - Periksa izin parent direktori.'); // Adjusted Log
                return false;
            }
            log_message('debug', 'Petugas_administrasi Controller: _get_upload_config() - Direktori upload berhasil dibuat: ' . $upload_path); // Adjusted Log
        }
        if (!is_writable($upload_path)) {
            log_message('error', 'Petugas_administrasi Controller: _get_upload_config() - Direktori upload tidak writable: ' . $upload_path . ' - Periksa izin (chown www-data:www-data dan chmod 775).'); // Adjusted Log
            return false;
        }

        $config['upload_path']   = $upload_path;
        $config['allowed_types'] = $allowed_types;
        $config['max_size']      = $max_size_kb;
        if ($max_width) $config['max_width'] = $max_width;
        if ($max_height) $config['max_height'] = $max_height;
        $config['encrypt_name']  = TRUE;
        log_message('debug', 'Petugas_administrasi Controller: _get_upload_config() - Config created: ' . print_r($config, true)); // Adjusted Log
        return $config;
    }

    public function prosesSurat($id_permohonan = 0)
    {
        $admin_user = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(); // Renaming to $pa_user would be clearer
        $data['user'] = $admin_user; // $data['petugas_administrasi_user']
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Proses Finalisasi Permohonan Impor';

        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) { 
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            redirect('petugas_administrasi/permohonanMasuk'); // Adjusted redirect
            return; 
        }

        $this->db->select('up.*, upr.NamaPers, upr.npwp, upr.alamat, upr.NoSkep');
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->where('up.id', $id_permohonan);
        $data['permohonan'] = $this->db->get()->row_array();

        if (!$data['permohonan']) { 
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan.</div>');
            redirect('petugas_administrasi/permohonanMasuk'); // Adjusted redirect
            return; 
        }
        
        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $data['permohonan']['id_pers']])->row_array();
        if (!$data['user_perusahaan']) {
            $data['user_perusahaan'] = ['NamaPers' => 'N/A', 'alamat' => 'N/A', 'NoSkep' => 'N/A', 'npwp' => 'N/A'];
        }

        $data['lhp'] = $this->db->get_where('lhp', ['id_permohonan' => $id_permohonan])->row_array();
        if (!$data['lhp'] || $data['permohonan']['status'] != '2' || empty($data['lhp']['NoLHP']) || empty($data['lhp']['TglLHP'])) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">LHP belum lengkap atau status permohonan (ID '.htmlspecialchars($id_permohonan).') tidak valid untuk finalisasi.</div>');
            redirect('petugas_administrasi/detail_permohonan_admin/' . $id_permohonan); // Adjusted redirect (assuming detail_permohonan_admin is used by PA)
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
                $this->form_validation->set_rules('file_surat_keputusan', 'File Surat Persetujuan Pengeluaran', 'callback_petugas_administrasi_check_file_sk_upload'); // Adjusted callback name
            }
        }

        if ($this->form_validation->run() == false) {
            log_message('debug', 'PETUGAS_ADMINISTRASI PROSES SURAT - Form validation failed. Errors: ' . validation_errors()); // Adjusted Log
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('petugas_administrasi/prosesSurat', $data); // Adjusted View Path
            $this->load->view('templates/footer');
        } else {
            log_message('debug', 'PETUGAS_ADMINISTRASI PROSES SURAT - Form validation success. Processing data...'); // Adjusted Log
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
                // 'diproses_oleh_id_pa' => $admin_user['id'] // Or $pa_user['id']
            ];

            $nama_file_sk_baru = $data['permohonan']['file_surat_keputusan']; 

            if ($status_final_permohonan == '3' && isset($_FILES['file_surat_keputusan']) && $_FILES['file_surat_keputusan']['error'] != UPLOAD_ERR_NO_FILE) {
                $upload_dir_sk = './uploads/sk_penyelesaian/'; 
                $config_sk = $this->_get_upload_config($upload_dir_sk, 'pdf|jpg|png|jpeg', 2048); 

                if (!$config_sk) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Konfigurasi direktori upload SK gagal.</div>');
                    redirect('petugas_administrasi/prosesSurat/' . $id_permohonan); return; // Adjusted redirect
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
                    $this->load->view('petugas_administrasi/prosesSurat', $data); // Adjusted View Path
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
    
                // Ambil data yang diperlukan untuk pemotongan dan logging
                $jumlah_dipotong = (float)$data['lhp']['JumlahBenar'];
                $id_kuota_barang_terpakai = $data['permohonan']['id_kuota_barang_digunakan'];
                $id_perusahaan = $data['permohonan']['id_pers'];

                // Pastikan ID Kuota Barang ada sebelum melanjutkan
                if ($id_kuota_barang_terpakai) {
                    // Mulai transaksi database untuk memastikan integritas data
                    $this->db->trans_start();

                    // 1. Ambil data kuota saat ini untuk logging
                    $kuota_barang_saat_ini = $this->db->get_where('user_kuota_barang', ['id_kuota_barang' => $id_kuota_barang_terpakai])->row_array();
                    
                    if ($kuota_barang_saat_ini) {
                        $kuota_sebelum = (float)$kuota_barang_saat_ini['remaining_quota_barang'];
                        $kuota_sesudah = $kuota_sebelum - $jumlah_dipotong;

                        // 2. Update sisa kuota di tabel user_kuota_barang
                        $this->db->where('id_kuota_barang', $id_kuota_barang_terpakai);
                        $this->db->set('remaining_quota_barang', 'remaining_quota_barang - ' . $this->db->escape($jumlah_dipotong), FALSE);
                        $this->db->update('user_kuota_barang');

                        // 3. Catat transaksi ke dalam log
                        $keterangan_log = 'Pemotongan kuota dari persetujuan impor. No. Surat: ' . ($data_update_permohonan['nomorSetuju'] ?? '-');
                        $this->_log_perubahan_kuota(
                            $id_perusahaan,
                            'pengurangan',
                            $jumlah_dipotong,
                            $kuota_sebelum,
                            $kuota_sesudah,
                            $keterangan_log,
                            $id_permohonan, // id_referensi_transaksi
                            'permohonan_impor_disetujui', // tipe_referensi
                            $admin_user['id'], // dicatat_oleh_user_id
                            $kuota_barang_saat_ini['nama_barang'], // nama_barang_terkait
                            $id_kuota_barang_terpakai // id_kuota_barang_referensi
                        );
                    }
                    
                    // Selesaikan transaksi
                    $this->db->trans_complete();
                }
            }

            $pesan_status_akhir = ($status_final_permohonan == '3') ? 'Disetujui' : 'Ditolak';
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Status permohonan ID '.htmlspecialchars($id_permohonan).' telah berhasil diproses menjadi "'. $pesan_status_akhir .'"!</div>');
            redirect('petugas_administrasi/permohonanMasuk'); // Adjusted redirect
        }
    }

    // Adjusted callback name for consistency
    public function petugas_administrasi_check_file_sk_upload($str) 
    {
        $field_name = 'file_surat_keputusan';
        if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] == UPLOAD_ERR_NO_FILE) {
            $this->form_validation->set_message('petugas_administrasi_check_file_sk_upload', 'Kolom {field} wajib diisi.');
            return FALSE;
        }

        $config_rules = $this->_get_upload_config('./uploads/dummy/', 'pdf|jpg|png|jpeg', 2048);
        if (!$config_rules) { 
            $this->form_validation->set_message('petugas_administrasi_check_file_sk_upload', 'Konfigurasi upload gagal.');
            return FALSE; 
        }

        $file = $_FILES[$field_name];
        $allowed_types_arr = explode('|', $config_rules['allowed_types']);
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types_arr)) {
            $this->form_validation->set_message('petugas_administrasi_check_file_sk_upload', "Tipe file {field} tidak valid (Hanya ".str_replace('|',', ',$config_rules['allowed_types']).").");
            return FALSE;
        }
        if ($file['size'] > ($config_rules['max_size'] * 1024)) {
            $this->form_validation->set_message('petugas_administrasi_check_file_sk_upload', "Ukuran file {field} melebihi ".$config_rules['max_size']."KB.");
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
            redirect('petugas_administrasi/permohonanMasuk'); // Adjusted redirect
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
            log_message('warning', 'Tidak ada data petugas ditemukan di tabel petugas.');
        }

        $this->form_validation->set_rules('petugas_id', 'Petugas Pemeriksa', 'required|numeric');
        $this->form_validation->set_rules('nomor_surat_tugas', 'Nomor Surat Tugas', 'required|trim');
        $this->form_validation->set_rules('tanggal_surat_tugas', 'Tanggal Surat Tugas', 'required');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('petugas_administrasi/form_penunjukan_petugas', $data); // Adjusted View Path
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
                        redirect('petugas_administrasi/penunjukanPetugas/' . $id_permohonan); // Adjusted redirect
                        return;
                    }
                }

                if (!is_writable($upload_dir_st)) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload error: Direktori Surat Tugas tidak writable. Path: '.$upload_dir_st.'</div>');
                    redirect('petugas_administrasi/penunjukanPetugas/' . $id_permohonan); // Adjusted redirect
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
                    redirect('petugas_administrasi/penunjukanPetugas/' . $id_permohonan); // Adjusted redirect
                    return;
                }
            }
            $update_data['FileSuratTugas'] = $nama_file_surat_tugas;

            $this->db->where('id', $id_permohonan);
            $this->db->update('user_permohonan', $update_data);

            $updated_permohonan = $this->db->get_where('user_permohonan', ['id' => $id_permohonan])->row_array();
            log_message('debug', 'PENUNJUKAN PETUGAS (PA) - Data Permohonan Setelah Update: ' . print_r($updated_permohonan, true)); // Adjusted Log
            log_message('debug', 'PENUNJUKAN PETUGAS (PA) - Nilai petugas_id yang di-POST: ' . $this->input->post('petugas_id')); // Adjusted Log

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Petugas pemeriksa berhasil ditunjuk untuk permohonan ID ' . htmlspecialchars($id_permohonan) . '. Status diubah menjadi "Penunjukan Pemeriksa".</div>');
            redirect('petugas_administrasi/permohonanMasuk'); // Adjusted redirect
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
        $this->load->view('petugas_administrasi/daftar_pengajuan_kuota', $data); // Adjusted View Path
        $this->load->view('templates/footer');
    }

    public function proses_pengajuan_kuota($id_pengajuan)
    {
        log_message('debug', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Method dipanggil untuk id_pengajuan: ' . $id_pengajuan); // Adjusted Log
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Proses Pengajuan Kuota';
        $pa_user = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(); // Changed var name
        $data['user'] = $pa_user;

        $this->db->select('upk.*, upr.NamaPers, upr.initial_quota as initial_quota_umum_sebelum, upr.remaining_quota as remaining_quota_umum_sebelum, u.email as user_email');
        $this->db->from('user_pengajuan_kuota upk');
        $this->db->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $this->db->join('user u', 'upk.id_pers = u.id', 'left');
        $this->db->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $this->db->get()->row_array();
        log_message('debug', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Data pengajuan yang diambil: ' . print_r($data['pengajuan'], true)); // Adjusted Log

        if (!$data['pengajuan'] || ($data['pengajuan']['status'] != 'pending' && $data['pengajuan']['status'] != 'diproses')) {
            $pesan_error_awal = 'Pengajuan kuota tidak ditemukan atau statusnya tidak memungkinkan untuk diproses (Status saat ini: ' . ($data['pengajuan']['status'] ?? 'Tidak Diketahui') . '). Hanya status "pending" atau "diproses" yang bisa dilanjutkan.';
            log_message('error', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Validasi awal gagal: ' . $pesan_error_awal); // Adjusted Log
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">' . $pesan_error_awal . '</div>');
            redirect('petugas_administrasi/daftar_pengajuan_kuota'); // Adjusted redirect
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
        $this->form_validation->set_rules('admin_notes', 'Catatan Petugas Administrasi', 'trim'); // Adjusted Label
        if ($this->input->post('status_pengajuan') == 'approved' && empty($data['pengajuan']['file_sk_petugas']) && empty($_FILES['file_sk_petugas']['name'])) {
            $this->form_validation->set_rules('file_sk_petugas', 'File SK Petugas', 'required');
        }

        if ($this->form_validation->run() == false) {
            log_message('debug', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Validasi Form Gagal. Errors: ' . validation_errors() . ' POST Data: ' . print_r($this->input->post(), true)); // Adjusted Log
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('petugas_administrasi/proses_pengajuan_kuota_form', $data);
            $this->load->view('templates/footer', $data); // Should be $this->load->view('templates/footer'); only
        } else {
            log_message('debug', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Validasi Form Sukses. Memproses data...'); // Adjusted Log
            $status_pengajuan = $this->input->post('status_pengajuan');
            $approved_quota_input = ($status_pengajuan == 'approved') ? (float)$this->input->post('approved_quota') : 0;
            $nomor_sk_petugas = $this->input->post('nomor_sk_petugas');
            $tanggal_sk_petugas = $this->input->post('tanggal_sk_petugas'); 
            $admin_notes = $this->input->post('admin_notes'); // Consider renaming to pa_notes

            $data_update_pengajuan = [
                'status' => $status_pengajuan,
                'admin_notes' => $admin_notes, // Consider 'pa_notes' in DB
                'processed_date' => date('Y-m-d H:i:s'),
                'nomor_sk_petugas' => $nomor_sk_petugas,
                'tanggal_sk_petugas' => !empty($tanggal_sk_petugas) ? $tanggal_sk_petugas : null, 
                'approved_quota' => $approved_quota_input
            ];

            $nama_file_sk = $data['pengajuan']['file_sk_petugas'] ?? null;
            if (($status_pengajuan == 'approved' || $status_pengajuan == 'rejected') && isset($_FILES['file_sk_petugas']) && $_FILES['file_sk_petugas']['error'] != UPLOAD_ERR_NO_FILE) {
                $upload_dir_sk = './uploads/sk_kuota/';
                if (!is_dir($upload_dir_sk)) { @mkdir($upload_dir_sk, 0777, true); }
                if (!is_writable($upload_dir_sk)) { 
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Error: Direktori upload SK Kuota tidak writable.</div>');
                    redirect('petugas_administrasi/proses_pengajuan_kuota/' . $id_pengajuan); // Adjusted redirect
                    return; 
                }
                
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
                    redirect('petugas_administrasi/proses_pengajuan_kuota/' . $id_pengajuan); return; // Adjusted redirect
                }
            }
            $data_update_pengajuan['file_sk_petugas'] = $nama_file_sk;

            $this->db->where('id', $id_pengajuan);
            $this->db->update('user_pengajuan_kuota', $data_update_pengajuan);
            log_message('debug', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - user_pengajuan_kuota diupdate. Affected: ' . $this->db->affected_rows()); // Adjusted Log

            if ($status_pengajuan == 'approved' && $approved_quota_input > 0) {
                $id_pers_terkait = $data['pengajuan']['id_pers'];
                $nama_barang_diajukan = $data['pengajuan']['nama_barang_kuota']; 

                if ($id_pers_terkait && !empty($nama_barang_diajukan)) {
                    // $sisa_kuota_umum_sebelum_tambah = (float)($data['pengajuan']['remaining_quota_umum_sebelum'] ?? 0); // This logic might be complex and depends on overall quota strategy

                    $data_kuota_barang = [
                        'id_pers' => $id_pers_terkait,
                        'id_pengajuan_kuota' => $id_pengajuan,
                        'nama_barang' => $nama_barang_diajukan,
                        'initial_quota_barang' => $approved_quota_input,
                        'remaining_quota_barang' => $approved_quota_input,
                        'nomor_skep_asal' => $nomor_sk_petugas,
                        'tanggal_skep_asal' => !empty($tanggal_sk_petugas) ? $tanggal_sk_petugas : null,
                        'status_kuota_barang' => 'active',
                        'dicatat_oleh_user_id' => $pa_user['id'], // Changed var name
                        'waktu_pencatatan' => date('Y-m-d H:i:s')
                    ];
                    $this->db->insert('user_kuota_barang', $data_kuota_barang);
                    $id_kuota_barang_baru = $this->db->insert_id();
                    log_message('info', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Data kuota barang baru disimpan. ID: ' . $id_kuota_barang_baru . ' untuk barang: ' . $nama_barang_diajukan); // Adjusted Log

                    if ($id_kuota_barang_baru) {
                        $this->_log_perubahan_kuota(
                            $id_pers_terkait, 'penambahan', $approved_quota_input,
                            0, 
                            $approved_quota_input, 
                            'Persetujuan Pengajuan Kuota. Barang: ' . $nama_barang_diajukan . '. No. SK: ' . ($nomor_sk_petugas ?: '-'),
                            $id_pengajuan, 'pengajuan_kuota_disetujui', $pa_user['id'], // Changed var name
                            $nama_barang_diajukan, $id_kuota_barang_baru
                        );
                    }
                } else {
                    log_message('error', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Gagal menambah kuota barang: id_pers atau nama_barang_kuota kosong. ID Pers: ' . $id_pers_terkait . ', Nama Barang: ' . $nama_barang_diajukan); // Adjusted Log
                }
            }

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Pengajuan kuota telah berhasil diproses!</div>');
            redirect('petugas_administrasi/daftar_pengajuan_kuota'); // Adjusted redirect
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
            redirect('petugas_administrasi/daftar_pengajuan_kuota'); // Adjusted redirect
            return;
        }

        $data['user'] = $this->db->get_where('user', ['id' => $data['pengajuan']['id_pers']])->row_array(); 
        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $data['pengajuan']['id_pers']])->row_array();

        // This view path seems to indicate a shared user view, which might be fine.
        $this->load->view('user/FormPengajuanKuota_print', $data);
    }

    public function detailPengajuanKuotaAdmin($id_pengajuan) // Consider renaming to detailPengajuanKuota
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
            redirect('petugas_administrasi/daftar_pengajuan_kuota'); // Adjusted redirect
            return;
        }

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('petugas_administrasi/detail_pengajuan_kuota_view', $data); // Adjusted View Path
        $this->load->view('templates/footer');
    }

    public function download_sk_kuota_admin($id_pengajuan) // Consider renaming to download_sk_kuota
    {
        $this->load->helper('download'); 
        $pengajuan = $this->db->get_where('user_pengajuan_kuota', ['id' => $id_pengajuan])->row_array();

        if ($pengajuan && !empty($pengajuan['file_sk_petugas'])) {
            $file_name = $pengajuan['file_sk_petugas'];
            $file_path = FCPATH . 'uploads/sk_kuota/' . $file_name;

            if (file_exists($file_path)) {
                force_download($file_path, NULL);
            } else {
                log_message('error', 'Petugas_administrasi: File SK Kuota tidak ditemukan di path: ' . $file_path . ' untuk id_pengajuan: ' . $id_pengajuan); // Adjusted Log
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">File Surat Keputusan tidak ditemukan di server.</div>');
                redirect('petugas_administrasi/daftar_pengajuan_kuota'); // Adjusted redirect
            }
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Surat Keputusan belum tersedia untuk pengajuan ini.</div>');
            redirect('petugas_administrasi/daftar_pengajuan_kuota'); // Adjusted redirect
        }
    }
    
    public function histori_kuota_perusahaan($id_pers = 0)
    {
        log_message('debug', 'PETUGAS_ADMINISTRASI HISTORI KUOTA - Method dipanggil dengan id_pers: ' . $id_pers); // Adjusted Log

        if ($id_pers == 0 || !is_numeric($id_pers)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Perusahaan tidak valid.</div>');
            redirect('petugas_administrasi/monitoring_kuota'); // Adjusted redirect
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
        log_message('debug', 'PETUGAS_ADMINISTRASI HISTORI KUOTA - Data Perusahaan: ' . print_r($data['perusahaan'], true)); // Adjusted Log

        if (!$data['perusahaan']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data perusahaan tidak ditemukan untuk ID: ' . $id_pers . '</div>');
            redirect('petugas_administrasi/monitoring_kuota'); // Adjusted redirect
            return;
        }
        $data['id_pers_untuk_histori'] = $id_pers;

        $this->db->select('ukb.*'); 
        $this->db->from('user_kuota_barang ukb');
        $this->db->where('ukb.id_pers', $id_pers);
        $this->db->order_by('ukb.nama_barang ASC, ukb.waktu_pencatatan DESC');
        $data['daftar_kuota_barang_perusahaan'] = $this->db->get()->result_array();
        log_message('debug', 'PETUGAS_ADMINISTRASI HISTORI KUOTA - Query Daftar Kuota Barang: ' . $this->db->last_query()); // Adjusted Log
        log_message('debug', 'PETUGAS_ADMINISTRASI HISTORI KUOTA - Data Daftar Kuota Barang: ' . print_r($data['daftar_kuota_barang_perusahaan'], true)); // Adjusted Log

        $this->db->select('lk.*, u_admin.name as nama_pencatat'); // Consider u_pa.name
        $this->db->from('log_kuota_perusahaan lk');
        $this->db->join('user u_admin', 'lk.dicatat_oleh_user_id = u_admin.id', 'left'); // u_pa
        $this->db->where('lk.id_pers', $id_pers);
        $this->db->order_by('lk.tanggal_transaksi', 'DESC');
        $this->db->order_by('lk.id_log', 'DESC');
        $data['histori_kuota_transaksi'] = $this->db->get()->result_array(); 
        log_message('debug', 'PETUGAS_ADMINISTRASI HISTORI KUOTA - Query Log Transaksi: ' . $this->db->last_query()); // Adjusted Log
        log_message('debug', 'PETUGAS_ADMINISTRASI HISTORI KUOTA - Data Log Transaksi: ' . print_r($data['histori_kuota_transaksi'], true)); // Adjusted Log

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('petugas_administrasi/histori_kuota_perusahaan_view', $data); // Adjusted View Path
        $this->load->view('templates/footer');
    }

    public function detail_permohonan_admin($id_permohonan = 0) // Consider renaming if not exclusively for admin-level details
    {
        log_message('debug', 'PETUGAS_ADMINISTRASI DETAIL PERMOHONAN - Method dipanggil dengan id_permohonan: ' . $id_permohonan); // Adjusted Log

        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            log_message('error', 'PETUGAS_ADMINISTRASI DETAIL PERMOHONAN - ID Permohonan tidak valid: ' . $id_permohonan); // Adjusted Log
            redirect('petugas_administrasi/permohonanMasuk'); // Adjusted redirect
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

        log_message('debug', 'PETUGAS_ADMINISTRASI DETAIL PERMOHONAN - Query Permohonan: ' . $this->db->last_query()); // Adjusted Log
        log_message('debug', 'PETUGAS_ADMINISTRASI DETAIL PERMOHONAN - Data Permohonan: ' . print_r($data['permohonan_detail'], true)); // Adjusted Log

        if (!$data['permohonan_detail']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data permohonan dengan ID ' . htmlspecialchars($id_permohonan) . ' tidak ditemukan.</div>');
            log_message('error', 'PETUGAS_ADMINISTRASI DETAIL PERMOHONAN - Data permohonan tidak ditemukan untuk ID: ' . $id_permohonan); // Adjusted Log
            redirect('petugas_administrasi/permohonanMasuk'); // Adjusted redirect
            return;
        }

        $data['lhp_detail'] = $this->db->get_where('lhp', ['id_permohonan' => $id_permohonan])->row_array();
        log_message('debug', 'PETUGAS_ADMINISTRASI DETAIL PERMOHONAN - Data LHP: ' . print_r($data['lhp_detail'], true)); // Adjusted Log

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('petugas_administrasi/detail_permohonan_view', $data); // Adjusted View path & name
        $this->load->view('templates/footer');
    }

    public function hapus_permohonan($id_permohonan = 0)
    {
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid untuk dihapus.</div>');
            redirect('petugas_administrasi/permohonanMasuk'); // Adjusted redirect
            return;
        }

        $permohonan = $this->db->get_where('user_permohonan', ['id' => $id_permohonan])->row_array();

        if (!$permohonan) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan dengan ID '.htmlspecialchars($id_permohonan).' tidak ditemukan.</div>');
            redirect('petugas_administrasi/permohonanMasuk'); // Adjusted redirect
            return;
        }

        if (!empty($permohonan['file_bc_manifest']) && file_exists('./uploads/bc_manifest/' . $permohonan['file_bc_manifest'])) {
            if (@unlink('./uploads/bc_manifest/' . $permohonan['file_bc_manifest'])) {
                log_message('info', 'File BC Manifest ' . $permohonan['file_bc_manifest'] . ' berhasil dihapus untuk permohonan ID: ' . $id_permohonan . ' oleh Petugas Administrasi ID: ' . $this->session->userdata('user_id')); // Adjusted Log
            } else {
                log_message('error', 'Gagal menghapus file BC Manifest ' . $permohonan['file_bc_manifest'] . ' untuk permohonan ID: ' . $id_permohonan);
            }
        }

        $this->db->where('id', $id_permohonan);
        if ($this->db->delete('user_permohonan')) {
            log_message('info', 'Permohonan ID ' . $id_permohonan . ' berhasil dihapus oleh Petugas Administrasi ID: ' . $this->session->userdata('user_id')); // Adjusted Log
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Permohonan dengan ID Aju '.htmlspecialchars($id_permohonan).' berhasil dihapus.</div>');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal menghapus permohonan. Silakan coba lagi.</div>');
        }
        redirect('petugas_administrasi/permohonanMasuk'); // Adjusted redirect
    }

    public function edit_permohonan($id_permohonan = 0)
    {
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            redirect('petugas_administrasi/permohonanMasuk'); // Adjusted redirect
            return;
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Permohonan (Petugas Administrasi)'; // Adjusted Subtitle
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(); 

        $permohonan = $this->db->select('up.*, upr.NamaPers as NamaPerusahaanPemohon') 
                            ->from('user_permohonan up')
                            ->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left')
                            ->where('up.id', $id_permohonan)
                            ->get()->row_array();

        if (!$permohonan) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan.</div>');
            redirect('petugas_administrasi/permohonanMasuk'); // Adjusted redirect
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

        if (isset($_FILES['file_bc_manifest_pa_edit']) && $_FILES['file_bc_manifest_pa_edit']['error'] != UPLOAD_ERR_NO_FILE) { // Adjusted field name suggestion
            $this->form_validation->set_rules('file_bc_manifest_pa_edit', 'File BC 1.1 / Manifest (Baru)', 'callback_pa_check_file_bc_manifest_upload'); // Adjusted callback name
        }

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('petugas_administrasi/form_edit_permohonan', $data); // Adjusted View Path
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

            if (!$kuota_valid_db) { 
                 $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data kuota barang tidak valid.</div>');
                 redirect('petugas_administrasi/edit_permohonan/' . $id_permohonan); return; // Adjusted Redirect
            }
            if ($kuota_valid_db['nama_barang'] != $nama_barang_input_form) { 
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Nama barang tidak sesuai dengan kuota yang dipilih.</div>');
                redirect('petugas_administrasi/edit_permohonan/' . $id_permohonan); return; // Adjusted Redirect
            }
            
            $sisa_kuota_efektif_untuk_validasi = (float)$kuota_valid_db['remaining_quota_barang'];
            if ($permohonan['id_kuota_barang_digunakan'] == $id_kuota_barang_dipilih) {
                $sisa_kuota_efektif_untuk_validasi += (float)$permohonan['JumlahBarang'];
            }
            if ($jumlah_barang_dimohon > $sisa_kuota_efektif_untuk_validasi) { 
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Jumlah barang dimohon melebihi sisa kuota efektif.</div>');
                redirect('petugas_administrasi/edit_permohonan/' . $id_permohonan); return; // Adjusted Redirect
            }

            $nama_file_bc_manifest_update = $permohonan['file_bc_manifest']; 
            if (isset($_FILES['file_bc_manifest_pa_edit']) && $_FILES['file_bc_manifest_pa_edit']['error'] != UPLOAD_ERR_NO_FILE) { // Adjusted field name
                $config_upload_bc = $this->_get_upload_config('./uploads/bc_manifest/', 'pdf', 2048); // Using existing _get_upload_config
                if (!$config_upload_bc) { 
                     $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Konfigurasi upload file BC gagal.</div>');
                     redirect('petugas_administrasi/edit_permohonan/' . $id_permohonan); return; // Adjusted Redirect
                }
                
                $this->upload->initialize($config_upload_bc, TRUE); 
                if ($this->upload->do_upload('file_bc_manifest_pa_edit')) { // Adjusted field name
                    if (!empty($permohonan['file_bc_manifest']) && file_exists('./uploads/bc_manifest/' . $permohonan['file_bc_manifest'])) {
                        @unlink('./uploads/bc_manifest/' . $permohonan['file_bc_manifest']);
                    }
                    $nama_file_bc_manifest_update = $this->upload->data('file_name');
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload File BC 1.1 Gagal: ' . $this->upload->display_errors('', '') . '</div>');
                    $this->load->view('templates/header', $data);
                    $this->load->view('templates/sidebar', $data);
                    $this->load->view('templates/topbar', $data);
                    $this->load->view('petugas_administrasi/form_edit_permohonan', $data); // Adjusted View Path
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
                // 'diedit_oleh_pa_id' => $data['user']['id'], 
                // 'waktu_edit_pa' => date('Y-m-d H:i:s')
            ];

            $this->db->where('id', $id_permohonan);
            $this->db->update('user_permohonan', $data_update);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Permohonan berhasil diupdate oleh Petugas Administrasi.</div>');
            redirect('petugas_administrasi/detail_permohonan_admin/' . $id_permohonan); // Adjusted redirect (or a PA specific detail view)
        }
    }

    // Adjusted callback name for consistency
    public function pa_check_file_bc_manifest_upload($str) 
    {
        $field_name = 'file_bc_manifest_pa_edit'; // Adjusted field name suggestion
        if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] == UPLOAD_ERR_NO_FILE) {
            return TRUE;
        }
        $config_upload_rules = $this->_get_upload_config('./uploads/dummy_path_for_rules/', 'pdf', 2048); // Using existing _get_upload_config
         if (!$config_upload_rules) { 
            $this->form_validation->set_message('pa_check_file_bc_manifest_upload', 'Konfigurasi upload gagal (internal).');
            return FALSE; 
        }
        $file = $_FILES[$field_name];
        $allowed_extensions_str = $config_upload_rules['allowed_types'];
        $allowed_extensions_arr = explode('|', $allowed_extensions_str);
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions_arr)) {
            $this->form_validation->set_message('pa_check_file_bc_manifest_upload', "Tipe file {field} tidak diizinkan (Hanya PDF).");
            return FALSE;
        }
        $max_size_bytes = $config_upload_rules['max_size'] * 1024;
        if ($file['size'] > $max_size_bytes) {
            $this->form_validation->set_message('pa_check_file_bc_manifest_upload', "Ukuran file {field} melebihi batas (" . $config_upload_rules['max_size'] . "KB).");
            return FALSE;
        }
        return TRUE;
    }

} // End class Petugas_administrasi