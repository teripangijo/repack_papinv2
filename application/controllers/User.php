<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->_check_auth();

        $this->load->library('form_validation');
        $this->load->library('upload');
        $this->load->helper('url');
        $this->load->helper('form');
        
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

        $qrCodeUrl = $google2fa->getQRCodeUrl($companyName, $userEmail, $secretKey);

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
        $this->load->view('user/mfa_setup', $data);
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

            $this->session->set_userdata('mfa_verified', true);

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Autentikasi Dua Faktor (MFA) berhasil diaktifkan!</div>');
            redirect('user/index');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Kode verifikasi salah. Silakan coba lagi.</div>');
            redirect('user/setup_mfa');
        }
    }

    public function reset_mfa()
    {
        $user_id = $this->session->userdata('user_id');

        $this->db->where('id', $user_id);
        $this->db->update('user', [
            'is_mfa_enabled' => 0,
            'google2fa_secret' => NULL
        ]);

        $this->session->unset_userdata('mfa_verified');

        $this->session->set_flashdata('message', '<div class="alert alert-info" role="alert">MFA Anda telah dinonaktifkan. Silakan lakukan pengaturan ulang.</div>');
        redirect('user/setup_mfa');
    }

    
    private function _check_auth()
    {
        if (!$this->session->userdata('email')) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Mohon login untuk melanjutkan.</div>');
            redirect('auth');
            exit;
        }
        if ($this->session->userdata('role_id') != 2) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Anda tidak diotorisasi untuk mengakses halaman ini.</div>');
            if ($this->session->userdata('role_id') == 1) redirect('admin');
            elseif ($this->session->userdata('role_id') == 3) redirect('petugas');
            elseif ($this->session->userdata('role_id') == 4) redirect('monitoring');
            else redirect('auth/blocked');
            exit;
        }
        $user_is_active = $this->session->userdata('is_active');
        $current_method = $this->router->fetch_method();
        $allowed_inactive_methods = ['edit', 'logout', 'ganti_password'];

        if ($user_is_active == 0 && !in_array($current_method, $allowed_inactive_methods) ) {
            if (!($this->router->fetch_class() == 'user' && $current_method == 'edit')) {
                $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Akun Anda belum aktif. Mohon lengkapi profil perusahaan Anda.</div>');
                redirect('user/edit');
                exit;
            }
        }
    }

    public function index() 
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Dashboard Pengguna Jasa'; 
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $id_user_login = $data['user']['id'];

        
        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $id_user_login])->row_array();

        
        $data['total_kuota_awal_disetujui_barang'] = 0;
        $data['total_sisa_kuota_barang'] = 0;
        $data['total_kuota_terpakai_barang'] = 0;
        $data['daftar_kuota_per_barang'] = []; 

        if ($data['user_perusahaan']) { 
            
            $this->db->select('
                SUM(initial_quota_barang) as total_initial,
                SUM(remaining_quota_barang) as total_remaining
            ');
            $this->db->from('user_kuota_barang');
            $this->db->where('id_pers', $id_user_login);
            
            $agregat_kuota = $this->db->get()->row_array();

            if ($agregat_kuota) {
                $data['total_kuota_awal_disetujui_barang'] = (float)($agregat_kuota['total_initial'] ?? 0);
                $data['total_sisa_kuota_barang'] = (float)($agregat_kuota['total_remaining'] ?? 0);
                $data['total_kuota_terpakai_barang'] = $data['total_kuota_awal_disetujui_barang'] - $data['total_sisa_kuota_barang'];
            }
            log_message('debug', 'USER DASHBOARD - Agregat Kuota Barang: ' . print_r($agregat_kuota, true));

            
            $this->db->select('nama_barang, initial_quota_barang, remaining_quota_barang, nomor_skep_asal');
            $this->db->from('user_kuota_barang');
            $this->db->where('id_pers', $id_user_login);
            $this->db->where('status_kuota_barang', 'active'); 
            $this->db->order_by('nama_barang', 'ASC');
            $data['daftar_kuota_per_barang'] = $this->db->get()->result_array();


            
            $this->db->select('id, nomorSurat, TglSurat, NamaBarang, JumlahBarang, status, time_stamp');
            $this->db->from('user_permohonan');
            $this->db->where('id_pers', $id_user_login);
            $this->db->order_by('time_stamp', 'DESC');
            $this->db->limit(5);
            $data['recent_permohonan'] = $this->db->get()->result_array();
        } else {
            $data['recent_permohonan'] = [];
            
            if ($data['user']['is_active'] == 1) { 
                $this->session->set_flashdata('message_dashboard', '<div class="alert alert-info" role="alert">Selamat datang! Mohon lengkapi profil perusahaan Anda di menu "Edit Profile & Perusahaan" untuk dapat menggunakan semua fitur.</div>');
            }
        }

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/dashboard', $data); 
        $this->load->view('templates/footer', $data);
    }

    public function edit() 
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Profil & Perusahaan';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $id_user_login = $data['user']['id'];

        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $id_user_login])->row_array();
        $is_activating = empty($data['user_perusahaan']); 
        $data['is_activating'] = $is_activating; 

        
        if (!$is_activating) {
            $this->db->select('nama_barang, initial_quota_barang, remaining_quota_barang, nomor_skep_asal, tanggal_skep_asal, status_kuota_barang');
            $this->db->from('user_kuota_barang');
            $this->db->where('id_pers', $id_user_login);
            $this->db->order_by('nama_barang', 'ASC');
            $data['daftar_kuota_barang_user'] = $this->db->get()->result_array();
        } else {
            $data['daftar_kuota_barang_user'] = [];
        }

        
        $this->form_validation->set_rules('NamaPers', 'Nama Perusahaan', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('npwp', 'NPWP', 'trim|required|regex_match[/^[0-9]{2}\.[0-9]{3}\.[0-9]{3}\.[0-9]{1}-[0-9]{3}\.[0-9]{3}$/]', ['regex_match' => 'Format NPWP tidak valid. Contoh: 00.000.000.0-000.000']);
        $this->form_validation->set_rules('alamat', 'Alamat Perusahaan', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('telp', 'Nomor Telepon Perusahaan', 'trim|required|numeric|max_length[15]');
        $this->form_validation->set_rules('pic', 'Nama PIC', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('jabatanPic', 'Jabatan PIC', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('NoSkepFasilitas', 'No. SKEP Fasilitas Umum', 'trim|max_length[100]'); 

        
        if ($is_activating) {
            
            if ($this->input->post('initial_skep_no') || $this->input->post('initial_skep_tgl') || $this->input->post('initial_nama_barang') || $this->input->post('initial_kuota_jumlah')) {
                $this->form_validation->set_rules('initial_skep_no', 'Nomor SKEP Kuota Awal', 'trim|required|max_length[100]');
                $this->form_validation->set_rules('initial_skep_tgl', 'Tanggal SKEP Kuota Awal', 'trim|required');
                $this->form_validation->set_rules('initial_nama_barang', 'Nama Barang Kuota Awal', 'trim|required|max_length[100]');
                $this->form_validation->set_rules('initial_kuota_jumlah', 'Jumlah Kuota Awal', 'trim|required|numeric|greater_than[0]');
            }
        }

        
        if ($is_activating || (isset($_FILES['ttd']) && $_FILES['ttd']['error'] != UPLOAD_ERR_NO_FILE)) {
            
            $this->form_validation->set_rules('ttd', 'Tanda Tangan PIC', 'callback_file_check[ttd]');
        }
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] != UPLOAD_ERR_NO_FILE) {
             $this->form_validation->set_rules('profile_image', 'Gambar Profil/Logo', 'callback_file_check[profile_image]');
        }
        if (isset($_FILES['file_skep_fasilitas']) && $_FILES['file_skep_fasilitas']['error'] != UPLOAD_ERR_NO_FILE) {
            $this->form_validation->set_rules('file_skep_fasilitas', 'File SKEP Fasilitas', 'callback_file_check[file_skep_fasilitas]');
        }
        if ($is_activating && isset($_FILES['initial_skep_file']) && $_FILES['initial_skep_file']['error'] != UPLOAD_ERR_NO_FILE) {
            $this->form_validation->set_rules('initial_skep_file', 'File SKEP Kuota Awal', 'callback_file_check[initial_skep_file]');
        }


        if ($this->form_validation->run() == false) {
            $data['upload_error'] = $this->session->flashdata('upload_error_detail'); 
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/edit-profile', $data);
            $this->load->view('templates/footer', $data);
        } else {
            
            $nama_file_ttd = $data['user_perusahaan']['ttd'] ?? null;
            $nama_file_profile_image = $data['user']['image'] ?? 'default.jpg';
            $nama_file_skep_fasilitas = $data['user_perusahaan']['FileSkepFasilitas'] ?? null;
            $nama_file_initial_skep = null; 

            
            if (isset($_FILES['ttd']) && $_FILES['ttd']['error'] != UPLOAD_ERR_NO_FILE) {
                $config_ttd = $this->_get_upload_config('./uploads/ttd/', 'jpg|png|jpeg|pdf', 1024); 
                $this->upload->initialize($config_ttd, TRUE);
                if ($this->upload->do_upload('ttd')) {
                    if (!$is_activating && !empty($data['user_perusahaan']['ttd']) && file_exists($config_ttd['upload_path'] . $data['user_perusahaan']['ttd'])) {
                        @unlink($config_ttd['upload_path'] . $data['user_perusahaan']['ttd']);
                    }
                    $nama_file_ttd = $this->upload->data('file_name');
                } else {
                    $this->session->set_flashdata('upload_error_detail', $this->upload->display_errors());
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload TTD Gagal: ' . $this->upload->display_errors('', '') . '</div>');
                    redirect('user/edit'); return;
                }
            }

            
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] != UPLOAD_ERR_NO_FILE) {
                $config_profile = $this->_get_upload_config('./uploads/profile_images/', 'jpg|png|jpeg|gif', 1024, 1024, 1024); 
                $this->upload->initialize($config_profile, TRUE);
                if ($this->upload->do_upload('profile_image')) {
                    $old_image = $data['user']['image'];
                    if ($old_image != 'default.jpg' && !empty($old_image) && file_exists($config_profile['upload_path'] . $old_image)) {
                        @unlink($config_profile['upload_path'] . $old_image);
                    }
                    $nama_file_profile_image = $this->upload->data('file_name');
                } else {
                    $this->session->set_flashdata('upload_error_detail', $this->upload->display_errors());
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload Gambar Profil Gagal: ' . $this->upload->display_errors('', '') . '</div>');
                    redirect('user/edit'); return;
                }
            }

            
            if (isset($_FILES['file_skep_fasilitas']) && $_FILES['file_skep_fasilitas']['error'] != UPLOAD_ERR_NO_FILE) {
                $config_skep_f = $this->_get_upload_config('./uploads/skep_fasilitas/', 'pdf|jpg|jpeg|png', 2048);
                $this->upload->initialize($config_skep_f, TRUE);
                if ($this->upload->do_upload('file_skep_fasilitas')) {
                    if (!empty($data['user_perusahaan']['FileSkepFasilitas']) && file_exists($config_skep_f['upload_path'] . $data['user_perusahaan']['FileSkepFasilitas'])) {
                        @unlink($config_skep_f['upload_path'] . $data['user_perusahaan']['FileSkepFasilitas']);
                    }
                    $nama_file_skep_fasilitas = $this->upload->data('file_name');
                } else {
                    $this->session->set_flashdata('upload_error_detail', $this->upload->display_errors());
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload File SKEP Fasilitas Gagal: ' . $this->upload->display_errors('', '') . '</div>');
                    redirect('user/edit'); return;
                }
            }

            
            if ($is_activating && isset($_FILES['initial_skep_file']) && $_FILES['initial_skep_file']['error'] != UPLOAD_ERR_NO_FILE) {
                $config_skep_i = $this->_get_upload_config('./uploads/skep_awal_user/', 'pdf|jpg|jpeg|png', 2048); 
                $this->upload->initialize($config_skep_i, TRUE);
                if ($this->upload->do_upload('initial_skep_file')) {
                    $nama_file_initial_skep = $this->upload->data('file_name');
                } else {
                    $this->session->set_flashdata('upload_error_detail', $this->upload->display_errors());
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload File SKEP Kuota Awal Gagal: ' . $this->upload->display_errors('', '') . '</div>');
                    redirect('user/edit'); return;
                }
            }


            
            $data_user_update = [];
            if ($nama_file_profile_image !== null && $nama_file_profile_image != $data['user']['image']) {
                $data_user_update['image'] = $nama_file_profile_image;
            }
            if (!empty($data_user_update)) {
                 $this->db->where('id', $id_user_login);
                 $this->db->update('user', $data_user_update);
                 
                 if(isset($data_user_update['image'])) $this->session->set_userdata('image', $data_user_update['image']);
            }

            
            $data_perusahaan = [
                'NamaPers' => $this->input->post('NamaPers'),
                'npwp' => $this->input->post('npwp'),
                'alamat' => $this->input->post('alamat'),
                'telp' => $this->input->post('telp'),
                'pic' => $this->input->post('pic'),
                'jabatanPic' => $this->input->post('jabatanPic'),
                'NoSkepFasilitas' => $this->input->post('NoSkepFasilitas') ?: null, 
            ];
             if ($nama_file_ttd !== null) {
                 $data_perusahaan['ttd'] = $nama_file_ttd;
             }
             if ($nama_file_skep_fasilitas !== null) {
                 $data_perusahaan['FileSkepFasilitas'] = $nama_file_skep_fasilitas;
             }

            if ($is_activating) {
                $data_perusahaan['id_pers'] = $id_user_login; 
                
                $this->db->insert('user_perusahaan', $data_perusahaan);

                
                $initial_skep_no = trim($this->input->post('initial_skep_no'));
                $initial_nama_barang = trim($this->input->post('initial_nama_barang'));
                $initial_kuota_jumlah = (float)$this->input->post('initial_kuota_jumlah');

                if (!empty($initial_skep_no) && !empty($initial_nama_barang) && $initial_kuota_jumlah > 0) {
                    $data_kuota_awal_barang = [
                        'id_pers' => $id_user_login,
                        'nama_barang' => $initial_nama_barang,
                        'initial_quota_barang' => $initial_kuota_jumlah,
                        'remaining_quota_barang' => $initial_kuota_jumlah,
                        'nomor_skep_asal' => $initial_skep_no,
                        'tanggal_skep_asal' => $this->input->post('initial_skep_tgl'),
                        'status_kuota_barang' => 'active',
                        
                        'dicatat_oleh_user_id' => $id_user_login,
                        'waktu_pencatatan' => date('Y-m-d H:i:s')
                    ];
                    if ($nama_file_initial_skep) { 
                        
                    }
                    $this->db->insert('user_kuota_barang', $data_kuota_awal_barang);
                    log_message('info', 'KUOTA AWAL BARANG dicatat saat aktivasi untuk user: ' . $id_user_login . ', barang: ' . $initial_nama_barang . ', jumlah: ' . $initial_kuota_jumlah);

                    
                    
                    
                }

                $this->db->where('id', $id_user_login);
                $this->db->update('user', ['is_active' => 1]);
                $this->session->set_userdata('is_active', 1);
                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Profil perusahaan berhasil disimpan dan akun Anda telah diaktifkan! Anda sekarang dapat mengajukan kuota atau membuat permohonan.</div>');
                redirect('user/index');
            } else { 
                
                
                $this->db->where('id_pers', $id_user_login);
                $this->db->update('user_perusahaan', $data_perusahaan);

                
                $perubahan_terdeteksi = false;
                if (!empty($data_user_update)) $perubahan_terdeteksi = true;
                if ($nama_file_ttd !== null && (!isset($data['user_perusahaan']['ttd']) || $nama_file_ttd !== $data['user_perusahaan']['ttd'])) $perubahan_terdeteksi = true;
                if ($nama_file_skep_fasilitas !== null && (!isset($data['user_perusahaan']['FileSkepFasilitas']) || $nama_file_skep_fasilitas !== $data['user_perusahaan']['FileSkepFasilitas'])) $perubahan_terdeteksi = true;
                
                foreach ($data_perusahaan as $key => $value) {
                    if ($value !== ($data['user_perusahaan'][$key] ?? null)) {
                        $perubahan_terdeteksi = true;
                        break;
                    }
                }

                 if ($perubahan_terdeteksi) {
                     $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Profil dan data perusahaan berhasil diperbarui!</div>');
                 } else {
                     $this->session->set_flashdata('message', '<div class="alert alert-info" role="alert">Tidak ada perubahan data yang terdeteksi.</div>');
                 }
                redirect('user/index');
            }
        }
    }

    
    private function _get_upload_config($upload_path, $allowed_types, $max_size_kb, $max_width = null, $max_height = null) {
        if (!is_dir($upload_path)) {
            if (!@mkdir($upload_path, 0777, true)) {
                
                log_message('error', 'Gagal membuat direktori upload: ' . $upload_path);
                return false; 
            }
        }
        if (!is_writable($upload_path)) {
            log_message('error', 'Direktori upload tidak writable: ' . $upload_path);
            return false; 
        }

        $config['upload_path']   = $upload_path;
        $config['allowed_types'] = $allowed_types;
        $config['max_size']      = $max_size_kb; 
        if ($max_width) $config['max_width'] = $max_width;
        if ($max_height) $config['max_height'] = $max_height;
        $config['encrypt_name']  = TRUE;
        return $config;
    }

    public function file_check($str, $field)
    {
        
        $user_id_for_file_check = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row()->id;
        $user_perusahaan_for_file_check = $this->db->get_where('user_perusahaan', ['id_pers' => $user_id_for_file_check])->row_array();
        $is_activating_for_file_check = empty($user_perusahaan_for_file_check);

        $config = [];
        $error_field_name = '';

        switch ($field) {
            case 'ttd':
                $config = ['allowed_types' => ['image/jpeg', 'image/png', 'application/pdf', 'image/pjpeg'], 'max_size' => 1024 * 1024, 'error_name' => 'Tanda Tangan PIC', 'allowed_str' => 'jpg, png, pdf'];
                
                if ($is_activating_for_file_check && (!isset($_FILES[$field]) || $_FILES[$field]['error'] == UPLOAD_ERR_NO_FILE)) {
                    $this->form_validation->set_message('file_check', '{field} wajib diupload saat aktivasi akun.');
                    return FALSE;
                }
                break;
            case 'profile_image':
                $config = ['allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/pjpeg'], 'max_size' => 1024 * 1024, 'error_name' => 'Gambar Profil/Logo', 'allowed_str' => 'jpg, png, gif'];
                break;
            case 'file_skep_fasilitas':
                $config = ['allowed_types' => ['application/pdf', 'image/jpeg', 'image/png', 'image/pjpeg'], 'max_size' => 2048 * 1024, 'error_name' => 'File SKEP Fasilitas', 'allowed_str' => 'pdf, jpg, png'];
                break;
            case 'initial_skep_file':
                $config = ['allowed_types' => ['application/pdf', 'image/jpeg', 'image/png', 'image/pjpeg'], 'max_size' => 2048 * 1024, 'error_name' => 'File SKEP Kuota Awal', 'allowed_str' => 'pdf, jpg, png'];
                
                break;
            default:
                $this->form_validation->set_message('file_check', 'Field file tidak dikenal untuk validasi.');
                return FALSE;
        }

        if (isset($_FILES[$field]) && $_FILES[$field]['error'] != UPLOAD_ERR_NO_FILE) {
            if (function_exists('mime_content_type')) {
                $mime = mime_content_type($_FILES[$field]['tmp_name']);
                if (!in_array($mime, $config['allowed_types'])) {
                    $this->form_validation->set_message('file_check', "Tipe file untuk {field} tidak diizinkan (Hanya {$config['allowed_str']}). Terdeteksi: {$mime}");
                    return FALSE;
                }
            } else {
                 $ext_arr = explode(', ', str_replace(['jpeg','pjpeg'], 'jpg', $config['allowed_str']));
                 $file_ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
                 if (!in_array($file_ext, $ext_arr)) {
                     $this->form_validation->set_message('file_check', "Ekstensi file untuk {field} tidak diizinkan (Hanya {$config['allowed_str']}).");
                     return FALSE;
                 }
            }
            if ($_FILES[$field]['size'] > $config['max_size']) {
                 $this->form_validation->set_message('file_check', "Ukuran file untuk {field} melebihi batas (".($config['max_size']/1024/1024)."MB).");
                 return FALSE;
            }
        }
        return TRUE;
    }

    public function permohonan_impor_kembali()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Permohonan Impor Kembali';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $id_user_login = $data['user']['id']; 

        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $id_user_login])->row_array();

        if (empty($data['user_perusahaan']) || $data['user']['is_active'] == 0) {
             $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Mohon lengkapi profil perusahaan Anda dan pastikan akun aktif sebelum membuat permohonan.</div>');
             redirect('user/edit');
             return;
        }
        
        $this->db->select('id_kuota_barang, nama_barang, remaining_quota_barang, nomor_skep_asal, tanggal_skep_asal');
        $this->db->from('user_kuota_barang');
        $this->db->where('id_pers', $id_user_login);
        $this->db->where('remaining_quota_barang >', 0);
        $this->db->where('status_kuota_barang', 'active');
        $this->db->order_by('nama_barang ASC, tanggal_skep_asal DESC'); 
        $data['list_barang_berkuota'] = $this->db->get()->result_array();

        log_message('debug', 'PERMOHONAN BARU - ID User: ' . $id_user_login . ', Data User Perusahaan: ' . print_r($data['user_perusahaan'], true));
        log_message('debug', 'PERMOHONAN BARU - Query List Barang Berkuota: ' . $this->db->last_query());
        log_message('debug', 'PERMOHONAN BARU - Data List Barang Berkuota: ' . print_r($data['list_barang_berkuota'], true));
        
        $this->form_validation->set_rules('nomorSurat', 'Nomor Surat Pengajuan', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('TglSurat', 'Tanggal Surat', 'trim|required');
        $this->form_validation->set_rules('Perihal', 'Perihal Surat', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('id_kuota_barang_selected', 'Pilihan Kuota Barang', 'trim|required|numeric'); 
        $this->form_validation->set_rules('NamaBarang', 'Nama/Jenis Barang', 'trim|required'); 
        $this->form_validation->set_rules('JumlahBarang', 'Jumlah Barang Diajukan', 'trim|required|numeric|greater_than[0]|max_length[10]');
        $this->form_validation->set_rules('NegaraAsal', 'Negara Asal Barang', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('NamaKapal', 'Nama Kapal/Sarana Pengangkut', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('noVoyage', 'No. Voyage/Flight', 'trim|required|max_length[50]');
        $this->form_validation->set_rules('TglKedatangan', 'Tanggal Perkiraan Kedatangan', 'trim|required');
        $this->form_validation->set_rules('TglBongkar', 'Tanggal Perkiraan Bongkar', 'trim|required');
        $this->form_validation->set_rules('lokasi', 'Lokasi Bongkar', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('file_bc_manifest', 'File BC 1.1 / Manifest', 'callback_check_file_bc_manifest_upload');

        if ($this->form_validation->run() == false) {
            log_message('debug', 'PERMOHONAN BARU - Validasi form GAGAL. Errors: ' . validation_errors());
            if (empty($data['list_barang_berkuota']) && $this->input->method() !== 'post') {
                 $this->session->set_flashdata('message_form_permohonan', '<div class="alert alert-warning" role="alert">Anda tidak memiliki kuota aktif untuk barang apapun saat ini. Tidak dapat membuat permohonan impor kembali. Silakan ajukan kuota terlebih dahulu.</div>');
            }
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/permohonan_impor_kembali_form', $data); 
            $this->load->view('templates/footer', $data);
        } else {
            log_message('debug', 'PERMOHONAN BARU - Validasi form SUKSES. Memulai proses data.');
            $id_kuota_barang_dipilih = (int)$this->input->post('id_kuota_barang_selected');
            $nama_barang_input_form = $this->input->post('NamaBarang'); 
            $jumlah_barang_dimohon = (float)$this->input->post('JumlahBarang');

            log_message('debug', 'PERMOHONAN BARU - POST Data: id_kuota_barang=' . $id_kuota_barang_dipilih . ', nama_barang_form=' . $nama_barang_input_form . ', jumlah_dimohon=' . $jumlah_barang_dimohon);

            $kuota_valid_db = $this->db->get_where('user_kuota_barang', [
                'id_kuota_barang' => $id_kuota_barang_dipilih,
                'id_pers' => $id_user_login,
                'status_kuota_barang' => 'active'
            ])->row_array();

            if (!$kuota_valid_db) {
                log_message('error', 'PERMOHONAN BARU - Kuota barang (ID: '.$id_kuota_barang_dipilih.') tidak ditemukan/tidak aktif untuk user ID: ' . $id_user_login);
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Kuota barang yang dipilih tidak valid atau tidak aktif. Silakan pilih kembali.</div>');
                redirect('user/permohonan_impor_kembali'); return;
            }
            if ($kuota_valid_db['nama_barang'] != $nama_barang_input_form) {
                log_message('error', 'PERMOHONAN BARU - Nama barang form ('.$nama_barang_input_form.') != nama barang DB ('.$kuota_valid_db['nama_barang'].') untuk ID kuota: '.$id_kuota_barang_dipilih);
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Terjadi ketidaksesuaian data barang. Silakan coba lagi.</div>');
                redirect('user/permohonan_impor_kembali'); return;
            }
            if ($jumlah_barang_dimohon > (float)$kuota_valid_db['remaining_quota_barang']) {
                log_message('error', 'PERMOHONAN BARU - Jumlah dimohon ('.$jumlah_barang_dimohon.') > sisa kuota ('.$kuota_valid_db['remaining_quota_barang'].') barang: '.$nama_barang_input_form);
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Jumlah barang (' . $jumlah_barang_dimohon . ') melebihi sisa kuota (' . (float)$kuota_valid_db['remaining_quota_barang'] . ') untuk ' . htmlspecialchars($nama_barang_input_form) . '.</div>');
                redirect('user/permohonan_impor_kembali'); return;
            }
            $nomor_skep_final = $kuota_valid_db['nomor_skep_asal'];

            log_message('debug', 'PERMOHONAN BARU - Memulai blok upload. _FILES[file_bc_manifest]: ' . print_r($_FILES['file_bc_manifest'] ?? 'TIDAK ADA', true));
            $nama_file_bc_manifest = null; 

            $config_upload_bc = $this->_get_upload_config('./uploads/bc_manifest/', 'pdf', 2048); 

            if (!$config_upload_bc) {
                log_message('error', 'PERMOHONAN BARU - Gagal mendapatkan konfigurasi upload.');
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Konfigurasi direktori upload BC 1.1/Manifest gagal. Hubungi administrator.</div>');
                redirect('user/permohonan_impor_kembali'); return;
            }
            
            $this->upload->initialize($config_upload_bc, TRUE); 
            if ($this->upload->do_upload('file_bc_manifest')) { 
                $upload_data_bc = $this->upload->data();
                $nama_file_bc_manifest = $upload_data_bc['file_name'];
                log_message('info', 'PERMOHONAN BARU - UPLOAD BC MANIFEST SUKSES: ' . $nama_file_bc_manifest . ' | Full data: ' . print_r($upload_data_bc, true));
            } else {
                $upload_errors = $this->upload->display_errors('', '');
                log_message('error', 'PERMOHONAN BARU - UPLOAD BC MANIFEST GAGAL SAAT PENYIMPANAN: ' . $upload_errors . ' _FILES data: ' . print_r($_FILES['file_bc_manifest'], true));
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload File BC 1.1 / Manifest Gagal Saat Penyimpanan: ' . $upload_errors . '</div>');
                
                $data['list_barang_berkuota'] = $this->db->select('id_kuota_barang, nama_barang, remaining_quota_barang, nomor_skep_asal, tanggal_skep_asal')
                                                    ->from('user_kuota_barang')
                                                    ->where('id_pers', $id_user_login)
                                                    ->where('remaining_quota_barang >', 0)
                                                    ->where('status_kuota_barang', 'active')
                                                    ->order_by('nama_barang ASC, tanggal_skep_asal DESC')
                                                    ->get()->result_array();
                $this->load->view('templates/header', $data);
                $this->load->view('templates/sidebar', $data);
                $this->load->view('templates/topbar', $data);
                $this->load->view('user/permohonan_impor_kembali_form', $data);
                $this->load->view('templates/footer', $data);
                return; 
            }

            log_message('debug', 'PERMOHONAN BARU - Nilai $nama_file_bc_manifest sebelum insert: ' . ($nama_file_bc_manifest ?? 'NULL (INI MASALAH JIKA FILE DIUPLOAD)'));

            $data_insert = [
                'NamaPers'      => $data['user_perusahaan']['NamaPers'],
                'alamat'        => $data['user_perusahaan']['alamat'],
                'nomorSurat'    => $this->input->post('nomorSurat'),
                'TglSurat'      => $this->input->post('TglSurat'),
                'Perihal'       => $this->input->post('Perihal'),
                'NamaBarang'    => $nama_barang_input_form, 
                'JumlahBarang'  => $jumlah_barang_dimohon,
                'NegaraAsal'    => $this->input->post('NegaraAsal'),
                'NamaKapal'     => $this->input->post('NamaKapal'),
                'noVoyage'      => $this->input->post('noVoyage'),
                'NoSkep'        => $nomor_skep_final,
                'file_bc_manifest' => $nama_file_bc_manifest, 
                'id_kuota_barang_digunakan' => $id_kuota_barang_dipilih, 
                'TglKedatangan' => $this->input->post('TglKedatangan'),
                'TglBongkar'    => $this->input->post('TglBongkar'),
                'lokasi'        => $this->input->post('lokasi'),
                'id_pers'       => $id_user_login,
                'time_stamp'    => date('Y-m-d H:i:s'),
                'status'        => '0' 
            ];
            
            log_message('debug', 'PERMOHONAN BARU - Data yang akan diinsert ke user_permohonan: ' . print_r($data_insert, true));

            if ($this->db->insert('user_permohonan', $data_insert)) {
                $id_permohonan_baru = $this->db->insert_id();
                log_message('info', 'PERMOHONAN BARU - BERHASIL insert. ID Permohonan Baru: ' . $id_permohonan_baru . '. File BC Manifest Tersimpan: ' . ($nama_file_bc_manifest ?? 'TIDAK ADA FILE TERSIMPAN'));
                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Permohonan Impor Kembali untuk barang "'.htmlspecialchars($nama_barang_input_form).'" telah berhasil diajukan.</div>');
                redirect('user/daftarPermohonan');
            } else {
                $db_error = $this->db->error();
                log_message('error', 'PERMOHONAN BARU - GAGAL insert ke database. Error: Code ' . $db_error['code'] . ' - ' . $db_error['message'] . ' Data: ' . print_r($data_insert, true));
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal menyimpan permohonan ke database. Error: ' . $db_error['message'] . '</div>');
                $this->load->view('templates/header', $data);
                $this->load->view('templates/sidebar', $data);
                $this->load->view('templates/topbar', $data);
                $this->load->view('user/permohonan_impor_kembali_form', $data);
                $this->load->view('templates/footer', $data);
            }
        }
    }

    public function check_file_bc_manifest_upload($str)
    {
        $field_name = 'file_bc_manifest'; 
        log_message('debug', 'CALLBACK check_file_bc_manifest_upload - _FILES content: ' . print_r($_FILES, true));
        if (isset($_FILES[$field_name])) {
            log_message('debug', 'CALLBACK check_file_bc_manifest_upload - _FILES['.$field_name.'] error code: ' . $_FILES[$field_name]['error']);
        } else {
            log_message('debug', 'CALLBACK check_file_bc_manifest_upload - _FILES['.$field_name.'] IS NOT SET.');
        }
        $config_upload_rules = $this->_get_upload_config('./uploads/dummy_path_for_rules/', 'pdf', 2048); 

        if (!$config_upload_rules || !is_array($config_upload_rules)) {
            $this->form_validation->set_message('check_file_bc_manifest_upload', 'Kesalahan konfigurasi upload internal.');
            return FALSE;
        }

        if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] == UPLOAD_ERR_NO_FILE) {
            $this->form_validation->set_message('check_file_bc_manifest_upload', 'Kolom {field} wajib diisi (File BC 1.1 / Manifest).');
            return FALSE;
        }

        $file = $_FILES[$field_name];
        $allowed_extensions_str = $config_upload_rules['allowed_types'] ?? 'pdf';
        $allowed_extensions_arr = explode('|', $allowed_extensions_str);
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions_arr)) {
            $this->form_validation->set_message('check_file_bc_manifest_upload', "Tipe file untuk {field} tidak diizinkan (Hanya PDF). Ekstensi terdeteksi: .{$file_extension}");
            return FALSE;
        }

        $max_size_kb = $config_upload_rules['max_size'] ?? 2048;
        $max_size_bytes = $max_size_kb * 1024;
        if ($file['size'] > $max_size_bytes) {
            $this->form_validation->set_message('check_file_bc_manifest_upload', "Ukuran file untuk {field} melebihi batas (" . $max_size_kb . "KB). Ukuran file Anda: " . round($file['size'] / 1024) . "KB");
            return FALSE;
        }
        
        return TRUE;
    }

    public function check_quota($requested_amount, $remaining_quota_param)
    {
        $remaining_quota = (int)$remaining_quota_param;
        if ((int)$requested_amount > $remaining_quota) {
            $this->form_validation->set_message('check_quota', 'The requested amount ({field}) of ' . $requested_amount . ' exceeds your remaining quota (' . $remaining_quota . ').');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function pengajuan_kuota()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Pengajuan Penetapan/Penambahan Kuota';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $id_user_login = $data['user']['id']; 

        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $id_user_login])->row_array();

        if (empty($data['user_perusahaan'])) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Mohon lengkapi profil perusahaan Anda terlebih dahulu di menu "Edit Profil & Perusahaan" sebelum mengajukan kuota.</div>');
            redirect('user/edit'); 
            return;
        }
        if ($data['user']['is_active'] == 0) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Akun Anda belum aktif. Tidak dapat mengajukan kuota. Mohon lengkapi profil perusahaan Anda jika belum, atau hubungi Administrator.</div>');
            redirect('user/edit'); 
            return;
        }

        $this->db->select_sum('initial_quota_barang', 'total_initial_kuota_all_barang');
        $this->db->select_sum('remaining_quota_barang', 'total_remaining_kuota_all_barang');
        $this->db->where('id_pers', $id_user_login); 
        $agregat_kuota = $this->db->get('user_kuota_barang')->row_array();
        $data['total_kuota_awal_semua_barang'] = $agregat_kuota['total_initial_kuota_all_barang'] ?? 0;
        $data['total_sisa_kuota_semua_barang'] = $agregat_kuota['total_remaining_kuota_all_barang'] ?? 0;
        $this->form_validation->set_rules('nomor_surat_pengajuan', 'Nomor Surat Pengajuan', 'trim|required|max_length[100]');
        
        $this->form_validation->set_rules('tanggal_surat_pengajuan', 'Tanggal Surat Pengajuan', 'trim|required');
        $this->form_validation->set_rules('perihal_pengajuan', 'Perihal Surat Pengajuan', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('nama_barang_kuota', 'Nama/Jenis Barang untuk Kuota', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('requested_quota', 'Jumlah Kuota Diajukan', 'trim|required|numeric|greater_than[0]|max_length[10]');
        $this->form_validation->set_rules('reason', 'Alasan Pengajuan', 'trim|required');


        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/pengajuan_kuota_form', $data); 
            $this->load->view('templates/footer'); 
        } else {
            
            $nama_file_lampiran = null;
            if (isset($_FILES['file_lampiran_pengajuan']) && $_FILES['file_lampiran_pengajuan']['error'] != UPLOAD_ERR_NO_FILE) {
                $upload_dir_lampiran = './uploads/lampiran_kuota/';
                if (!is_dir($upload_dir_lampiran)) {
                    if (!@mkdir($upload_dir_lampiran, 0777, true)) {
                        $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal membuat direktori upload lampiran.</div>');
                        redirect('user/pengajuan_kuota');
                        return;
                    }
                }
                $config_lampiran['upload_path']   = $upload_dir_lampiran;
                $config_lampiran['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png';
                $config_lampiran['max_size']      = '2048'; 
                $config_lampiran['encrypt_name']  = TRUE;
                $this->load->library('upload', $config_lampiran, 'lampiran_kuota_upload');
                $this->lampiran_kuota_upload->initialize($config_lampiran);

                if ($this->lampiran_kuota_upload->do_upload('file_lampiran_pengajuan')) {
                    $nama_file_lampiran = $this->lampiran_kuota_upload->data('file_name');
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload File Lampiran Gagal: ' . $this->lampiran_kuota_upload->display_errors('', '') . '</div>');
                    redirect('user/pengajuan_kuota');
                    return;
                }
            }

            $data_pengajuan = [
                'id_pers'                   => $id_user_login,
                'nomor_surat_pengajuan'     => $this->input->post('nomor_surat_pengajuan'),
                'tanggal_surat_pengajuan'   => $this->input->post('tanggal_surat_pengajuan'),
                'perihal_pengajuan'         => $this->input->post('perihal_pengajuan'),
                'nama_barang_kuota'         => $this->input->post('nama_barang_kuota'),
                'requested_quota'           => (float)$this->input->post('requested_quota'),
                'reason'                    => $this->input->post('reason'),
                'submission_date'           => date('Y-m-d H:i:s'),
                'status'                    => 'pending'
            ];

            if ($nama_file_lampiran !== null) {
                
                $data_pengajuan['file_lampiran_user'] = $nama_file_lampiran;
            }

            $this->db->insert('user_pengajuan_kuota', $data_pengajuan);

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Pengajuan kuota Anda untuk barang "'.htmlspecialchars($this->input->post('nama_barang_kuota')).'" telah berhasil dikirim dan akan diproses.</div>');
            redirect('user/daftar_pengajuan_kuota');
        }
    }

    public function daftar_pengajuan_kuota()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Pengajuan Kuota Saya';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $id_user_login = $data['user']['id'];

        $this->db->select('id, nomor_surat_pengajuan, tanggal_surat_pengajuan, perihal_pengajuan, nama_barang_kuota, requested_quota, status, submission_date, processed_date, admin_notes, nomor_sk_petugas, file_sk_petugas, approved_quota');
        $this->db->from('user_pengajuan_kuota');
        $this->db->where('id_pers', $id_user_login); 
        $this->db->order_by('submission_date', 'DESC'); 
        $data['daftar_pengajuan'] = $this->db->get()->result_array();

        log_message('debug', 'USER DAFTAR PENGAJUAN KUOTA - User ID: ' . $id_user_login);
        log_message('debug', 'USER DAFTAR PENGAJUAN KUOTA - Query: ' . $this->db->last_query());
        log_message('debug', 'USER DAFTAR PENGAJUAN KUOTA - Jumlah Data: ' . count($data['daftar_pengajuan']));

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data); 
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/daftar_pengajuan_kuota_view', $data); 
        $this->load->view('templates/footer', $data);
    }

    public function print_bukti_pengajuan_kuota($id_pengajuan = 0)
    {
        if ($id_pengajuan == 0 || !is_numeric($id_pengajuan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Pengajuan Kuota tidak valid.</div>');
            redirect('user/daftar_pengajuan_kuota_user');
            return;
        }
        $user_login = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        if (!$user_login) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Sesi tidak valid. Silakan login kembali.</div>');
            redirect('auth');
            return;
        }
        $data['user'] = $user_login; 
        $this->db->select('upk.*, upr.NamaPers, upr.alamat as alamat_perusahaan, upr.npwp as npwp_perusahaan, upr.pic, upr.jabatanPic, upr.ttd as file_ttd_pic, upr.telp as telp_perusahaan');
        $this->db->from('user_pengajuan_kuota upk');
        $this->db->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $this->db->where('upk.id', $id_pengajuan);
        $this->db->where('upk.id_pers', $user_login['id']); 
        $pengajuan = $this->db->get()->row_array();

        if (!$pengajuan) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data pengajuan kuota tidak ditemukan atau Anda tidak berhak mengaksesnya.</div>');
            redirect('user/daftar_pengajuan_kuota_user');
            return;
        }
        $data['pengajuan'] = $pengajuan;
        $data['user_perusahaan'] = [
            'NamaPers'   => $pengajuan['NamaPers'] ?? null,
            'alamat'     => $pengajuan['alamat_perusahaan'] ?? null,
            'npwp'       => $pengajuan['npwp_perusahaan'] ?? null,
            'telp'       => $pengajuan['telp_perusahaan'] ?? null, 
            'pic'        => $pengajuan['pic'] ?? null,
            'jabatanPic' => $pengajuan['jabatanPic'] ?? null,
            'ttd'        => $pengajuan['file_ttd_pic'] ?? null 
        ];

        log_message('debug', 'PRINT PENGAJUAN KUOTA - Data User Login: ' . print_r($data['user'], true));
        log_message('debug', 'PRINT PENGAJUAN KUOTA - Data Pengajuan: ' . print_r($data['pengajuan'], true));
        log_message('debug', 'PRINT PENGAJUAN KUOTA - Data User Perusahaan (untuk view): ' . print_r($data['user_perusahaan'], true));
        
        $this->load->view('user/FormPengajuanKuota_print', $data);
    }

    public function daftarPermohonan()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Permohonan Impor Kembali';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->db->select(
            'up.*, ' . 
            'p.Nama AS nama_petugas_pemeriksa, ' . 
            'up.NamaBarang, ' . 
            'lhp.JumlahBenar'   
        );
        $this->db->from('user_permohonan up');
        $this->db->join('petugas p', 'up.petugas = p.id', 'left'); 
        $this->db->join('lhp', 'lhp.id_permohonan = up.id', 'left'); 
        $this->db->where('up.id_pers', $data['user']['id']); 
        $this->db->order_by('up.time_stamp', 'DESC');
        $data['permohonan'] = $this->db->get()->result_array();
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/daftar-permohonan', $data); 
        $this->load->view('templates/footer');
    }

    public function detailPermohonan($id_permohonan = 0)
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Permohonan Impor Kembali';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $user_id = $data['user']['id'];

        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            redirect('user/daftarPermohonan');
            return;
        }

        $this->db->select(
            'up.*, ' .
            'upr.NamaPers, upr.npwp AS npwp_perusahaan, upr.alamat AS alamat_perusahaan, upr.NoSkep AS NoSkep_perusahaan, ' .
            'petugas_pemeriksa.Nama AS nama_petugas_pemeriksa, ' 
            
        );
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('petugas petugas_pemeriksa', 'up.petugas = petugas_pemeriksa.id', 'left');
        
        $this->db->where('up.id', $id_permohonan);
        $this->db->where('up.id_pers', $user_id); 
        $data['permohonan_detail'] = $this->db->get()->row_array();

        if (!$data['permohonan_detail']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data permohonan tidak ditemukan atau Anda tidak memiliki akses. ID: ' . htmlspecialchars($id_permohonan) . '</div>');
            redirect('user/daftarPermohonan');
            return;
        }
        $data['lhp_detail'] = $this->db->get_where('lhp', ['id_permohonan' => $id_permohonan])->row_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/detail_permohonan_view', $data); 
        $this->load->view('templates/footer');
    }

    public function printPdf($id_permohonan) 
    {   
        if (empty($id_permohonan) || !is_numeric($id_permohonan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            redirect('user/daftarPermohonan');
            return;
        }
   
        $user_login = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        if (!$user_login) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Sesi tidak valid. Silakan login kembali.</div>');
            redirect('auth');
            return;
        }
        $data['user'] = $user_login;

        $this->db->select('up.*, upr.NamaPers, upr.alamat as alamat_perusahaan, upr.npwp as npwp_perusahaan, upr.pic, upr.jabatanPic, upr.ttd as file_ttd_pic_perusahaan, upr.telp as telp_perusahaan');
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->where('up.id', $id_permohonan);
        $this->db->where('up.id_pers', $user_login['id']); 
        $permohonan_data_lengkap = $this->db->get()->row_array();

        if (!$permohonan_data_lengkap) {
             $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan atau Anda tidak berhak mengaksesnya.</div>');
             redirect('user/daftarPermohonan');
             return;
        }

        $data['permohonan'] = $permohonan_data_lengkap;      
        $data['user_perusahaan'] = [
            'NamaPers'   => $permohonan_data_lengkap['NamaPers'] ?? null, 
            'alamat'     => $permohonan_data_lengkap['alamat_perusahaan'] ?? null,
            'npwp'       => $permohonan_data_lengkap['npwp_perusahaan'] ?? null,
            'telp'       => $permohonan_data_lengkap['telp_perusahaan'] ?? null,
            'pic'        => $permohonan_data_lengkap['pic'] ?? null,
            'jabatanPic' => $permohonan_data_lengkap['jabatanPic'] ?? null,
            'ttd'        => $permohonan_data_lengkap['file_ttd_pic_perusahaan'] ?? null
            
            
        ];
      
        log_message('debug', 'PRINT PDF PERMOHONAN - Data User Login: ' . print_r($data['user'], true));
        log_message('debug', 'PRINT PDF PERMOHONAN - Data Permohonan Lengkap (termasuk NoSkep dari permohonan): ' . print_r($data['permohonan'], true));
        log_message('debug', 'PRINT PDF PERMOHONAN - Data User Perusahaan (untuk kop/ttd): ' . print_r($data['user_perusahaan'], true));
        
        $this->load->view('user/FormPermohonan', $data);
    }

    public function editpermohonan($id_permohonan = 0)
    {
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            redirect('user/daftarPermohonan');
            return;
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Permohonan Impor Kembali';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $id_user_login = $data['user']['id'];

        
        $permohonan = $this->db->get_where('user_permohonan', ['id' => $id_permohonan, 'id_pers' => $id_user_login])->row_array();

        if (!$permohonan) {
             $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan atau Anda tidak berhak mengeditnya.</div>');
             redirect('user/daftarPermohonan');
             return;
        }
        
        if ($permohonan['status'] != '0') {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Permohonan ini sudah diproses (Status: '.htmlspecialchars($permohonan['status']).') dan tidak dapat diedit lagi.</div>');
            redirect('user/daftarPermohonan');
            return;
        }
        $data['permohonan_edit'] = $permohonan; 

        
        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $id_user_login])->row_array();

        
        $this->db->select('id_kuota_barang, nama_barang, remaining_quota_barang, nomor_skep_asal');
        $this->db->from('user_kuota_barang');
        $this->db->where('id_pers', $id_user_login);
        
        $this->db->group_start();
        $this->db->where('remaining_quota_barang >', 0);
        if (isset($permohonan['id_kuota_barang_digunakan'])) {
            $this->db->or_where('id_kuota_barang', $permohonan['id_kuota_barang_digunakan']);
        }
        $this->db->group_end();
        $this->db->where('status_kuota_barang', 'active');
        $this->db->order_by('nama_barang', 'ASC');
        $data['list_barang_berkuota'] = $this->db->get()->result_array();
        log_message('debug', 'USER EDIT PERMOHONAN - List Barang Berkuota: ' . print_r($data['list_barang_berkuota'], true));

        $this->form_validation->set_rules('nomorSurat', 'Nomor Surat', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('TglSurat', 'Tanggal Surat', 'trim|required');
        $this->form_validation->set_rules('Perihal', 'Perihal', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('id_kuota_barang_selected', 'Pilihan Kuota Barang', 'trim|required|numeric');
        $this->form_validation->set_rules('NamaBarang', 'Nama/Jenis Barang', 'trim|required'); 
        $this->form_validation->set_rules('JumlahBarang', 'Jumlah Barang', 'trim|required|numeric|greater_than[0]|max_length[10]');
        $this->form_validation->set_rules('NegaraAsal', 'Negara Asal', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('NamaKapal', 'Nama Kapal', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('noVoyage', 'Nomor Voyage', 'trim|required|max_length[50]');
        $this->form_validation->set_rules('TglKedatangan', 'Tanggal Kedatangan', 'trim|required');
        $this->form_validation->set_rules('TglBongkar', 'Tanggal Bongkar', 'trim|required');
        $this->form_validation->set_rules('lokasi', 'Lokasi Bongkar', 'trim|required|max_length[100]');

        if (isset($_FILES['file_bc_manifest_edit']) && $_FILES['file_bc_manifest_edit']['error'] != UPLOAD_ERR_NO_FILE) {
            $this->form_validation->set_rules('file_bc_manifest_edit', 'File BC 1.1 / Manifest (Baru)', 'callback_check_file_bc_manifest_upload_edit');
        }

        if ($this->form_validation->run() == false) {
            
            $data['id_permohonan_form_action'] = $id_permohonan;
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/edit_permohonan_form', $data); 
            $this->load->view('templates/footer');
        } else {
            $id_kuota_barang_dipilih = (int)$this->input->post('id_kuota_barang_selected');
            $nama_barang_input_form = $this->input->post('NamaBarang');
            $jumlah_barang_dimohon = (float)$this->input->post('JumlahBarang');

            
            $kuota_valid_db = $this->db->get_where('user_kuota_barang', [
                'id_kuota_barang' => $id_kuota_barang_dipilih,
                'id_pers' => $id_user_login,
                'status_kuota_barang' => 'active'
            ])->row_array();

            if (!$kuota_valid_db) {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Kuota barang yang dipilih tidak valid atau tidak aktif.</div>');
                redirect('user/editpermohonan/' . $id_permohonan); return;
            }
            if ($kuota_valid_db['nama_barang'] != $nama_barang_input_form) {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Terjadi ketidaksesuaian data barang. Silakan coba lagi.</div>');
                redirect('user/editpermohonan/' . $id_permohonan); return;
            }
            
            $sisa_kuota_efektif_untuk_validasi = (float)$kuota_valid_db['remaining_quota_barang'];
            if ($permohonan['id_kuota_barang_digunakan'] == $id_kuota_barang_dipilih) { 
                $sisa_kuota_efektif_untuk_validasi += (float)$permohonan['JumlahBarang'];
            }

            if ($jumlah_barang_dimohon > $sisa_kuota_efektif_untuk_validasi) {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Jumlah barang yang Anda ajukan (' . $jumlah_barang_dimohon . ' unit) melebihi sisa kuota yang tersedia (efektif: ' . $sisa_kuota_efektif_untuk_validasi . ' unit) untuk ' . htmlspecialchars($nama_barang_input_form) . '.</div>');
                redirect('user/editpermohonan/' . $id_permohonan); return;
            }
            $nomor_skep_final = $kuota_valid_db['nomor_skep_asal'];

            $data_update = [
                'nomorSurat'    => $this->input->post('nomorSurat'),
                'TglSurat'      => $this->input->post('TglSurat'),
                'Perihal'       => $this->input->post('Perihal'),
                'NamaBarang'    => $nama_barang_input_form, 
                'JumlahBarang'  => $jumlah_barang_dimohon,
                'NegaraAsal'    => $this->input->post('NegaraAsal'),
                'NamaKapal'     => $this->input->post('NamaKapal'),
                'noVoyage'      => $this->input->post('noVoyage'),
                'NoSkep'        => $nomor_skep_final,
                'id_kuota_barang_digunakan' => $id_kuota_barang_dipilih, 
                'TglKedatangan' => $this->input->post('TglKedatangan'),
                'TglBongkar'    => $this->input->post('TglBongkar'),
                'lokasi'        => $this->input->post('lokasi'),
                'time_stamp_update' => date('Y-m-d H:i:s') 
            ];

            $this->db->where('id', $id_permohonan);
            $this->db->where('id_pers', $id_user_login); 
            $this->db->update('user_permohonan', $data_update);
            log_message('info', 'USER EDIT PERMOHONAN - Permohonan ID: ' . $id_permohonan . ' berhasil diupdate.');
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Permohonan Impor Kembali berhasil diubah.</div>');
            redirect('user/daftarPermohonan');
        }
    }

    public function force_change_password_page()
    {
        
        if (!$this->session->userdata('email') || $this->session->userdata('force_change_password') != 1) {
            redirect('user/index'); 
            return;
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Wajib Ganti Password';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->form_validation->set_rules('new_password', 'Password Baru', 'required|trim|min_length[6]|matches[confirm_new_password]');
        $this->form_validation->set_rules('confirm_new_password', 'Konfirmasi Password Baru', 'required|trim');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/form_force_change_password', $data); 
            $this->load->view('templates/footer');
        } else {
            $new_password_hash = password_hash($this->input->post('new_password'), PASSWORD_DEFAULT);
            $update_data = [
                'password' => $new_password_hash,
                'force_change_password' => 0 
            ];

            $this->db->where('id', $data['user']['id']);
            $this->db->update('user', $update_data);
            $this->session->set_userdata('force_change_password', 0);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Password Anda telah berhasil diubah. Silakan lanjutkan.</div>');
            redirect('user/index'); 
        }
    }

    public function tes_layout()
    {
        $data['title'] = 'Tes Layout';
        $data['subtitle'] = 'Halaman Uji Coba Template';
        
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        if (empty($data['user'])) { 
            $data['user'] = ['name' => 'Guest', 'image' => 'default.jpg', 'role_id' => 0, 'role_name' => 'Guest'];
        }


        log_message('debug', 'TES LAYOUT - Memulai load view header.');
        $this->load->view('templates/header', $data);

        
        log_message('debug', 'TES LAYOUT - Memulai load view tes_konten.');
        $this->load->view('user/tes_konten', $data); 

        log_message('debug', 'TES LAYOUT - Memulai load view footer.');
        $this->load->view('templates/footer', $data);
        log_message('debug', 'TES LAYOUT - Semua view selesai di-load.');
    }

    public function hapus_permohonan_impor($id_permohonan = 0)
    {
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            redirect('user/daftarPermohonan');
            return;
        }

        $id_user_login = $this->session->userdata('id'); 

        $permohonan = $this->db->get_where('user_permohonan', [
            'id' => $id_permohonan,
            'id_pers' => $id_user_login 
        ])->row_array();

        if (!$permohonan) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan atau Anda tidak berhak menghapusnya.</div>');
            redirect('user/daftarPermohonan');
            return;
        }

        $deletable_statuses = ['0', '5']; 
        if (!in_array($permohonan['status'], $deletable_statuses)) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Permohonan ini sudah dalam proses dan tidak dapat dihapus lagi.</div>');
            redirect('user/daftarPermohonan');
            return;
        }

        $file_bc_manifest_path = './uploads/bc_manifest/' . $permohonan['file_bc_manifest'];
        if (!empty($permohonan['file_bc_manifest']) && file_exists($file_bc_manifest_path)) {
            if (@unlink($file_bc_manifest_path)) {
                log_message('info', 'User (ID: '.$id_user_login.') menghapus file BC Manifest: ' . $permohonan['file_bc_manifest'] . ' untuk permohonan ID: ' . $id_permohonan);
            } else {
                log_message('error', 'User (ID: '.$id_user_login.') GAGAL menghapus file BC Manifest: ' . $permohonan['file_bc_manifest'] . ' untuk permohonan ID: ' . $id_permohonan);
            }
        }

        $this->db->where('id', $id_permohonan);
        $this->db->where('id_pers', $id_user_login); 
        if ($this->db->delete('user_permohonan')) {
            log_message('info', 'User (ID: '.$id_user_login.') menghapus permohonan impor ID: ' . $id_permohonan);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Permohonan Impor Kembali (ID Aju: '.htmlspecialchars($id_permohonan).') berhasil dihapus.</div>');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal menghapus permohonan impor. Silakan coba lagi.</div>');
        }
        redirect('user/daftarPermohonan');
    }

    public function hapus_pengajuan_kuota($id_pengajuan = 0)
    {
        if ($id_pengajuan == 0 || !is_numeric($id_pengajuan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Pengajuan Kuota tidak valid.</div>');
            redirect('user/daftar_pengajuan_kuota');
            return;
        }

        $id_user_login = $this->session->userdata('id'); 

        $pengajuan = $this->db->get_where('user_pengajuan_kuota', [
            'id' => $id_pengajuan,
            'id_pers' => $id_user_login 
        ])->row_array();

        if (!$pengajuan) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Pengajuan kuota tidak ditemukan atau Anda tidak berhak menghapusnya.</div>');
            redirect('user/daftar_pengajuan_kuota');
            return;
        }

        $deletable_statuses = ['pending'];
        if (!in_array($pengajuan['status'], $deletable_statuses)) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Pengajuan kuota ini sudah dalam proses (Status: '.htmlspecialchars($pengajuan['status']).') dan tidak dapat dihapus lagi.</div>');
            redirect('user/daftar_pengajuan_kuota');
            return;
        }

        $file_lampiran_path = './uploads/lampiran_kuota/' . $pengajuan['file_lampiran_user'];
        if (!empty($pengajuan['file_lampiran_user']) && file_exists($file_lampiran_path)) {
            if (@unlink($file_lampiran_path)) {
                log_message('info', 'User (ID: '.$id_user_login.') menghapus file lampiran kuota: ' . $pengajuan['file_lampiran_user'] . ' untuk pengajuan ID: ' . $id_pengajuan);
            } else {
                log_message('error', 'User (ID: '.$id_user_login.') GAGAL menghapus file lampiran kuota: ' . $pengajuan['file_lampiran_user'] . ' untuk pengajuan ID: ' . $id_pengajuan);
            }
        }

        $this->db->where('id', $id_pengajuan);
        $this->db->where('id_pers', $id_user_login); 
        if ($this->db->delete('user_pengajuan_kuota')) {
            log_message('info', 'User (ID: '.$id_user_login.') menghapus pengajuan kuota ID: ' . $id_pengajuan);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Pengajuan kuota (ID: '.htmlspecialchars($id_pengajuan).') berhasil dihapus.</div>');
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal menghapus pengajuan kuota. Silakan coba lagi.</div>');
        }
        redirect('user/daftar_pengajuan_kuota');
    }

    public function check_file_bc_manifest_upload_edit($str)
    {
        $field_name = 'file_bc_manifest_edit'; 

        if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] == UPLOAD_ERR_NO_FILE) {
            return TRUE; 
        }

        $config_upload_rules = $this->_get_upload_config('./uploads/dummy_path_for_rules/', 'pdf', 2048);
        if (!$config_upload_rules || !is_array($config_upload_rules)) {
            $this->form_validation->set_message('check_file_bc_manifest_upload_edit', 'Kesalahan konfigurasi upload internal.');
            return FALSE;
        }

        $file = $_FILES[$field_name];
        $allowed_extensions_str = $config_upload_rules['allowed_types'] ?? 'pdf';
        $allowed_extensions_arr = explode('|', $allowed_extensions_str);
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions_arr)) {
            $this->form_validation->set_message('check_file_bc_manifest_upload_edit', "Tipe file {field} tidak diizinkan (Hanya PDF). Ekstensi terdeteksi: .{$file_extension}");
            return FALSE;
        }

        $max_size_kb = $config_upload_rules['max_size'] ?? 2048;
        $max_size_bytes = $max_size_kb * 1024;
        if ($file['size'] > $max_size_bytes) {
            $this->form_validation->set_message('check_file_bc_manifest_upload_edit', "Ukuran file {field} melebihi batas (".$max_size_kb."KB).");
            return FALSE;
        }
        return TRUE;
    }

} 
