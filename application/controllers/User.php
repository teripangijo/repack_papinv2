<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('upload');
        $this->load->helper('url');
        $this->load->helper('form');
        // Pastikan database sudah di-load jika belum di autoload
        if (!isset($this->db)) {
             $this->load->database();
        }
        $this->_check_auth();
    }

    // Fungsi helper untuk mengecek otentikasi dan status aktif
    private function _check_auth()
    {
        // Cek apakah user sudah login
        if (!$this->session->userdata('email')) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Please login to continue.</div>');
            redirect('auth');
        }

        // Cek status aktif, kecuali untuk method 'edit' dan 'index' (dashboard) jika user belum aktif
        $user_is_active = $this->session->userdata('is_active'); 
        $current_method = $this->router->fetch_method(); 

        if ($user_is_active == 0 && !in_array($current_method, ['edit', 'index']) ) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Your account is not yet active. Please complete your company profile.</div>');
            redirect('user/edit'); // Arahkan ke edit untuk aktivasi
        }
        
        // Pengecekan role, hanya user biasa (role_id = 2) yang boleh akses controller User
        if ($this->session->userdata('role_id') != 2) { 
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Access Denied! You are not authorized to access this page.</div>');
            if ($this->session->userdata('role_id') == 1) {
                redirect('admin'); // Admin diarahkan ke dashboard admin
            } else {
                redirect('auth/blocked'); // Role lain diblokir
            }
        }
    }

    public function index() // Ini adalah Dashboard
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Dashboard'; 
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        
        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $data['user']['id']])->row_array();
        
        $data['kuota_awal'] = 0;
        $data['sisa_kuota'] = 0;
        $data['total_kuota_terpakai'] = 0;


        if ($data['user_perusahaan']) {
            $this->db->select('id, nomorSurat, TglSurat, NamaBarang, JumlahBarang, status, time_stamp');
            $data['kuota_awal'] = $data['user_perusahaan']['initial_quota'] ?? 0;
            $data['sisa_kuota'] = $data['user_perusahaan']['remaining_quota'] ?? 0;
            $data['total_kuota_terpakai'] = ($data['kuota_awal'] - $data['sisa_kuota']);
            $this->db->where('id_pers', $data['user']['id']);
            $this->db->order_by('time_stamp', 'DESC');
            $this->db->limit(5); 
            $data['recent_permohonan'] = $this->db->get('user_permohonan')->result_array();
        } else {
            $data['recent_permohonan'] = [];
        }

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/dashboard', $data); 
        $this->load->view('templates/footer');
    }

    public function edit()
    {
        // echo "CONTROLLER User->edit() DIEKSEKUSI PADA " . date('Y-m-d H:i:s');
        // die();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Profile & Perusahaan'; 
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $id = $data['user']['id']; 

        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $id])->row_array();

        $this->form_validation->set_rules('NamaPers', 'Nama Perusahaan', 'trim|required');
        $this->form_validation->set_rules('npwp', 'NPWP', 'trim|required');
        $this->form_validation->set_rules('alamat', 'Alamat', 'trim|required');
        $this->form_validation->set_rules('telp', 'Telp', 'trim|required');
        $this->form_validation->set_rules('pic', 'PIC', 'trim|required');
        $this->form_validation->set_rules('jabatanPic', 'Jabatan PIC', 'trim|required');
        
        if (empty($data['user_perusahaan'])) { 
            $this->form_validation->set_rules('quota', 'Kuota Awal', 'trim|required|numeric|greater_than_equal_to[0]',
                ['greater_than_equal_to' => 'The {field} must be a number greater than or equal to 0.']
            );
        } else {
            $this->form_validation->set_rules('quota', 'Kuota Awal', 'trim|numeric|greater_than_equal_to[0]',
                ['greater_than_equal_to' => 'The {field} must be a number greater than or equal to 0.']
            ); 
        }

        if (empty($data['user_perusahaan']) || (isset($_FILES['ttd']) && $_FILES['ttd']['error'] != UPLOAD_ERR_NO_FILE)) {
            $this->form_validation->set_rules('ttd', 'Tanda Tangan', 'callback_file_check[ttd]');
        }
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] != UPLOAD_ERR_NO_FILE) {
             $this->form_validation->set_rules('profile_image', 'Profile Image/Logo', 'callback_file_check[profile_image]');
        }
        
        if ($this->form_validation->run() == false) {
            $data['upload_error'] = $this->session->flashdata('upload_error'); 
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/edit-profile', $data); 
            $this->load->view('templates/footer');
        } else {
            $nama_file_ttd = null;
            $nama_file_profile_image = null; 
            $is_activating = empty($data['user_perusahaan']);

            // --- Proses Upload TTD ---
            if (isset($_FILES['ttd']) && $_FILES['ttd']['error'] != UPLOAD_ERR_NO_FILE) {
                $upload_dir_relative_ttd = 'uploads/ttd/'; 
                $upload_path_absolute_ttd = FCPATH . $upload_dir_relative_ttd;
                if (!is_dir($upload_path_absolute_ttd)) { @mkdir($upload_path_absolute_ttd, 0777, true); }
                if (!is_writable($upload_path_absolute_ttd)) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload error: TTD directory is not writable.</div>');
                    redirect('user/edit'); return;
                }
                $config_ttd['upload_path']   = $upload_path_absolute_ttd;
                $config_ttd['allowed_types'] = 'jpg|png|jpeg|pdf';
                $config_ttd['max_size']      = '1024';
                $config_ttd['encrypt_name']  = TRUE;
                $this->upload->initialize($config_ttd, TRUE); 
                if ($this->upload->do_upload('ttd')) {
                    if (!$is_activating && !empty($data['user_perusahaan']['ttd']) && file_exists($config_ttd['upload_path'] . $data['user_perusahaan']['ttd'])) {
                        @unlink($config_ttd['upload_path'] . $data['user_perusahaan']['ttd']);
                    }
                    $nama_file_ttd = $this->upload->data('file_name');
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">TTD Upload Error: ' . $this->upload->display_errors('', '') . '</div>');
                    redirect('user/edit'); return;
                }
            } else {
                if (!$is_activating && !empty($data['user_perusahaan']['ttd'])) {
                     $nama_file_ttd = $data['user_perusahaan']['ttd'];
                }
            }

            // --- Proses Upload Gambar Profil (Logo Perusahaan) ---
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] != UPLOAD_ERR_NO_FILE) {
                $upload_dir_relative_profile = 'uploads/kop/'; 
                $upload_path_absolute_profile = FCPATH . $upload_dir_relative_profile;
                if (!is_dir($upload_path_absolute_profile)) { @mkdir($upload_path_absolute_profile, 0777, true); }
                if (!is_writable($upload_path_absolute_profile)) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload error: Profile image/logo directory is not writable.</div>');
                    redirect('user/edit'); return;
                }
                $config_profile['upload_path']   = $upload_path_absolute_profile;
                $config_profile['allowed_types'] = 'jpg|png|jpeg|gif'; 
                $config_profile['max_size']      = '1024'; 
                $config_profile['max_width']     = '1024'; 
                $config_profile['max_height']    = '1024';
                $config_profile['encrypt_name']  = TRUE; 
                $this->upload->initialize($config_profile, TRUE); 
                if ($this->upload->do_upload('profile_image')) {
                    $old_image = $data['user']['image'];
                    if ($old_image != 'default.jpg' && file_exists($config_profile['upload_path'] . $old_image)) {
                        @unlink($config_profile['upload_path'] . $old_image);
                    }
                    $nama_file_profile_image = $this->upload->data('file_name');
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Profile Image/Logo Upload Error: ' . $this->upload->display_errors('', '') . '</div>');
                    redirect('user/edit'); return;
                }
            } else {
                $nama_file_profile_image = $data['user']['image'];
            }

            $data_user_update = [];
            if ($nama_file_profile_image !== null && $nama_file_profile_image != $data['user']['image']) { 
                $data_user_update['image'] = $nama_file_profile_image; 
            }
            if (!empty($data_user_update)) {
                 $this->db->where('id', $id);
                 $this->db->update('user', $data_user_update);
            }

            $data_perusahaan = [
                'NamaPers' => $this->input->post('NamaPers'),
                'npwp' => $this->input->post('npwp'),
                'alamat' => $this->input->post('alamat'),
                'telp' => $this->input->post('telp'),
                'pic' => $this->input->post('pic'),
                'jabatanPic' => $this->input->post('jabatanPic'),
                'NoSkep' => $this->input->post('NoSkep'),
                'id_pers' => $id
            ];
             if ($nama_file_ttd !== null) {
                 $data_perusahaan['ttd'] = $nama_file_ttd; 
             }

            if ($is_activating) { 
                $input_quota = (int)$this->input->post('quota');
                $data_perusahaan['initial_quota'] = $input_quota;
                $data_perusahaan['remaining_quota'] = $input_quota;
                $this->db->insert('user_perusahaan', $data_perusahaan);
                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Company profile saved and account has been activated! Welcome to your Dashboard.</div>');
                $is_active_data = ['is_active' => 1];
                $this->db->where('id', $id);
                $this->db->update('user', $is_active_data);
                $this->session->set_userdata('is_active', 1);
                redirect('user/index'); 
            } else {
                if ($this->input->post('quota') !== null && is_numeric($this->input->post('quota'))) {
                    // Jika ada input 'quota' saat edit, Anda bisa memutuskan apa yang akan dilakukan.
                    // Untuk saat ini, kita tidak mengubah initial_quota atau remaining_quota di sini.
                    // $data_perusahaan['quota'] = (int)$this->input->post('quota'); // Jika ada kolom 'quota' umum
                }
                $this->db->where('id_pers', $id);
                $this->db->update('user_perusahaan', $data_perusahaan);
                 if (empty($data_user_update) && ($nama_file_ttd === null || (isset($data['user_perusahaan']['ttd']) && $nama_file_ttd === $data['user_perusahaan']['ttd']))) { 
                     $this->session->set_flashdata('message', '<div class="alert alert-info" role="alert">No changes detected in profile or company data.</div>');
                 } else {
                     $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Profile and company data have been updated successfully!</div>');
                 }
                redirect('user/index'); 
            }
        }
    }

    public function file_check($str, $field)
    {
        if ($field == 'ttd') {
            $allowed_mime_type_arr = ['image/jpeg', 'image/png', 'application/pdf', 'image/pjpeg'];
            $max_size = 1048576; // 1MB
            $error_field_name = 'Tanda Tangan';
            $allowed_types_str = 'jpg, png, pdf';
        } elseif ($field == 'profile_image') { 
            $allowed_mime_type_arr = ['image/jpeg', 'image/png', 'image/gif', 'image/pjpeg'];
            $max_size = 1048576; // 1MB
            $error_field_name = 'Profile Image/Logo';
            $allowed_types_str = 'jpg, png, gif';
        } else {
            $this->form_validation->set_message('file_check', 'Invalid field specified for file check.');
            return false;
        }

        if (isset($_FILES[$field]) && $_FILES[$field]['error'] != UPLOAD_ERR_NO_FILE) {
            if (function_exists('mime_content_type')) {
                $mime = mime_content_type($_FILES[$field]['tmp_name']);
                if (!in_array($mime, $allowed_mime_type_arr)) {
                    $this->form_validation->set_message('file_check', "The file type you are attempting to upload is not allowed for {$error_field_name} (Only {$allowed_types_str}). Detected: {$mime}");
                    return false;
                }
            } else {
                 $ext_arr = explode('|', str_replace(['jpeg','pjpeg'], 'jpg', $allowed_types_str));
                 $file_ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
                 if (!in_array($file_ext, $ext_arr)) {
                     $this->form_validation->set_message('file_check', "The file extension you are attempting to upload is not allowed for {$error_field_name} (Only {$allowed_types_str}).");
                     return false;
                 }
            }
            if ($_FILES[$field]['size'] > $max_size) {
                 $this->form_validation->set_message('file_check', "The file you are attempting to upload is larger than the permitted size (".($max_size/1024)."KB) for {$error_field_name}.");
                 return false;
            }
            return true; 
        } else {
            // Jika file tidak diupload
            if ($field == 'ttd') {
                // Cek apakah ini aktivasi (ttd wajib) atau edit (ttd tidak wajib)
                $user_id_from_session = $this->session->userdata('id');
                if (!$user_id_from_session) {
                    $this->form_validation->set_message('file_check', 'User session not found for TTD validation.');
                    return false; 
                }
                $perusahaan_data = $this->db->get_where('user_perusahaan', ['id_pers' => $user_id_from_session])->row_array();
                if (empty($perusahaan_data)) { // Jika data perusahaan belum ada (proses aktivasi)
                    $this->form_validation->set_message('file_check', 'The Tanda Tangan field is required for account activation.');
                    return false; // Wajib saat aktivasi
                } else {
                    return true; // Tidak wajib saat edit (jika tidak ada file baru)
                }
            } elseif ($field == 'profile_image') {
                // Gambar profil/logo tidak wajib saat edit (jika tidak ada file baru)
                return true;
            } else {
                 return true; // Field lain mungkin tidak wajib
            }
        }
    }

    public function permohonan_impor_kembali() 
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Permohonan Impor Kembali'; 
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $id = $data['user']['id'];
        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $id])->row_array();

        if(empty($data['user_perusahaan'])) {
             $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Please complete your company profile first in the Edit Profile menu before submitting a request.</div>');
             redirect('user/edit'); 
             return;
        }
        if (!isset($data['user_perusahaan']['remaining_quota']) || $data['user_perusahaan']['remaining_quota'] <= 0) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Your remaining quota is insufficient. Please submit a quota request.</div>');
            redirect('user/pengajuan_kuota'); 
            return;
        }

        $this->form_validation->set_rules('nomorSurat', 'Nomor Surat', 'trim|required');
        $this->form_validation->set_rules('TglSurat', 'Tanggal Surat', 'trim|required');
        $this->form_validation->set_rules('Perihal', 'Perihal', 'trim|required');
        $this->form_validation->set_rules('NamaBarang', 'Nama Barang', 'trim|required');
        $this->form_validation->set_rules('JumlahBarang', 'Jumlah Barang', 'trim|required|numeric|callback_check_quota['.(isset($data['user_perusahaan']['remaining_quota']) ? $data['user_perusahaan']['remaining_quota'] : 0).']');
        $this->form_validation->set_rules('NegaraAsal', 'Negara Asal', 'trim|required');
        $this->form_validation->set_rules('NamaKapal', 'Nama Kapal', 'trim|required');
        $this->form_validation->set_rules('noVoyage', 'Nomor Voyage', 'trim|required');
        $this->form_validation->set_rules('TglKedatangan', 'Tanggal Kedatangan', 'trim|required');
        $this->form_validation->set_rules('TglBongkar', 'Tanggal Bongkar', 'trim|required');
        $this->form_validation->set_rules('lokasi', 'Lokasi Bongkar', 'trim|required');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/permohonan_impor_kembali_form', $data); 
            $this->load->view('templates/footer');
        } else {
            $time = time();
            $timenow = date("Y-m-d H:i:s", $time);
            $jumlah_barang_dimohon = (int)$this->input->post('JumlahBarang');
            
            $data_insert = [
                'NamaPers' => $data['user_perusahaan']['NamaPers'],
                'alamat' => $data['user_perusahaan']['alamat'],
                'nomorSurat' => $this->input->post('nomorSurat'),
                'TglSurat' => $this->input->post('TglSurat'),
                'Perihal' => $this->input->post('Perihal'),
                'NamaBarang' => $this->input->post('NamaBarang'),
                'JumlahBarang' => $jumlah_barang_dimohon,
                'NegaraAsal' => $this->input->post('NegaraAsal'),
                'NamaKapal' => $this->input->post('NamaKapal'),
                'noVoyage' => $this->input->post('noVoyage'),
                'NoSkep' => isset($data['user_perusahaan']['NoSkep']) ? $data['user_perusahaan']['NoSkep'] : null,
                'TglKedatangan' => $this->input->post('TglKedatangan'),
                'TglBongkar' => $this->input->post('TglBongkar'),
                'lokasi' => $this->input->post('lokasi'),
                'id_pers' => $id,
                'time_stamp' => $timenow,
                'status' => '0'
            ];
            $this->db->insert('user_permohonan', $data_insert);

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Permohonan Impor Kembali Telah Disimpan.</div>');
            redirect('user/daftarPermohonan');
        }
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
        $data['subtitle'] = 'Pengajuan Kuota';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $data['user']['id']])->row_array();

        if(empty($data['user_perusahaan'])) {
             $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Please complete your company profile first in the Edit Profile menu before submitting a quota request.</div>');
             redirect('user/edit');
             return;
        }

        $this->form_validation->set_rules('nomor_surat_pengajuan', 'Nomor Surat Pengajuan', 'trim|required');
        $this->form_validation->set_rules('tanggal_surat_pengajuan', 'Tanggal Surat Pengajuan', 'trim|required');
        $this->form_validation->set_rules('perihal_pengajuan', 'Perihal Surat Pengajuan', 'trim|required');
        $this->form_validation->set_rules('nama_barang_kuota', 'Nama/Jenis Barang', 'trim|required');
        $this->form_validation->set_rules('requested_quota', 'Jumlah Kuota Diajukan', 'trim|required|numeric|greater_than[0]');
        $this->form_validation->set_rules('reason', 'Alasan Pengajuan', 'trim|required');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/pengajuan_kuota_form', $data); 
            $this->load->view('templates/footer');
        } else {
            $data_pengajuan = [
                'id_pers' => $data['user']['id'],
                'nomor_surat_pengajuan' => $this->input->post('nomor_surat_pengajuan'),
                'tanggal_surat_pengajuan' => $this->input->post('tanggal_surat_pengajuan'),
                'perihal_pengajuan' => $this->input->post('perihal_pengajuan'),
                'nama_barang_kuota' => $this->input->post('nama_barang_kuota'),
                'requested_quota' => $this->input->post('requested_quota'),
                'reason' => $this->input->post('reason'),
                'submission_date' => date('Y-m-d H:i:s'),
                'status' => 'pending' 
            ];
            $this->db->insert('user_pengajuan_kuota', $data_pengajuan); 

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Pengajuan kuota Anda telah berhasil dikirim dan akan diproses oleh administrator.</div>');
            redirect('user/index'); 
        }
    }

    public function daftar_pengajuan_kuota()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Pengajuan Kuota Saya';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        
        $this->db->where('id_pers', $data['user']['id']);
        $this->db->order_by('submission_date', 'DESC');
        $data['daftar_pengajuan'] = $this->db->get('user_pengajuan_kuota')->result_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/daftar_pengajuan_kuota_view', $data); 
        $this->load->view('templates/footer');
    }

    public function print_bukti_pengajuan_kuota($id_pengajuan)
    {
        $data['title'] = 'Bukti Pengajuan Kuota';
        $data['user_login'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->db->select('upk.*, upr.NamaPers, upr.npwp AS npwp_perusahaan, upr.alamat as alamat_perusahaan, upr.pic, upr.jabatanPic, upr.ttd as ttd_perusahaan_pic, u.email AS user_email, u.name AS user_name_pengaju, u.image AS logo_perusahaan_file');
        $this->db->from('user_pengajuan_kuota upk');
        $this->db->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $this->db->join('user u', 'upk.id_pers = u.id', 'left'); 
        $this->db->where('upk.id', $id_pengajuan);
        $this->db->where('upk.id_pers', $data['user_login']['id']); 
        $data['pengajuan'] = $this->db->get()->row_array();

        if (!$data['pengajuan']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data pengajuan kuota tidak ditemukan atau Anda tidak berhak mengaksesnya.</div>');
            redirect('user/daftar_pengajuan_kuota'); 
            return;
        }
        
        $data['user'] = $data['user_login']; 
        $data['user_perusahaan'] = [ 
            'NamaPers' => $data['pengajuan']['NamaPers'],
            'npwp' => $data['pengajuan']['npwp_perusahaan'],
            'alamat' => $data['pengajuan']['alamat_perusahaan'],
            'pic' => $data['pengajuan']['pic'],
            'jabatanPic' => $data['pengajuan']['jabatanPic'],
            'ttd' => $data['pengajuan']['ttd_perusahaan_pic']
        ];

        $this->load->view('user/FormPengajuanKuota_print', $data); 
    }


    public function daftarPermohonan()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Permohonan Impor Kembali'; 
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        
        $this->db->select('up.*, p.Nama AS nama_petugas'); 
        $this->db->from('user_permohonan up');
        $this->db->join('petugas p', 'up.petugas = p.id', 'left'); 
        $this->db->where('up.id_pers', $data['user']['id']);
        $this->db->order_by('up.time_stamp', 'DESC'); 
        $data['permohonan'] = $this->db->get()->result_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/daftar-permohonan', $data);
        $this->load->view('templates/footer');
    }

    public function printPdf($id_permohonan)
    {
        $user = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $permohonan = $this->db->get_where('user_permohonan', ['id' => $id_permohonan, 'id_pers' => $user['id']])->row_array(); 

        if (!$permohonan) {
             $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan atau Anda tidak berhak mengaksesnya.</div>');
             redirect('user/daftarPermohonan');
             return;
        }

        $user_perusahaan = $this->db->get_where('user_perusahaan', ['id_pers' => $permohonan['id_pers']])->row_array();
        $data = array(
            'user' => $user, 
            'permohonan' => $permohonan,
            'user_perusahaan' => $user_perusahaan, 
        );
        $this->load->view('user/FormPermohonan', $data); 
    }

    public function editpermohonan($id_permohonan)
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Permohonan Impor Kembali'; 
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $id_user = $data['user']['id'];

        $permohonan = $this->db->get_where('user_permohonan', ['id' => $id_permohonan, 'id_pers' => $id_user])->row_array();

        if (!$permohonan) {
             $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan atau Anda tidak berhak mengeditnya.</div>');
             redirect('user/daftarPermohonan');
             return;
        }
        if ($permohonan['status'] != '0') {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Permohonan ini sudah diproses dan tidak dapat diedit lagi.</div>');
            redirect('user/daftarPermohonan');
            return;
        }

        $data['permohonan'] = $permohonan; 
        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $id_user])->row_array();

        $this->form_validation->set_rules('nomorSurat', 'Nomor Surat', 'trim|required');
        $this->form_validation->set_rules('TglSurat', 'Tanggal Surat', 'trim|required');
        $this->form_validation->set_rules('Perihal', 'Perihal', 'trim|required');
        $this->form_validation->set_rules('NamaBarang', 'Nama Barang', 'trim|required');
        $this->form_validation->set_rules('JumlahBarang', 'Jumlah Barang', 'trim|required|numeric');
        $this->form_validation->set_rules('NegaraAsal', 'Negara Asal', 'trim|required');
        $this->form_validation->set_rules('NamaKapal', 'Nama Kapal', 'trim|required');
        $this->form_validation->set_rules('noVoyage', 'Nomor Voyage', 'trim|required');
        $this->form_validation->set_rules('TglKedatangan', 'Tanggal Kedatangan', 'trim|required');
        $this->form_validation->set_rules('TglBongkar', 'Tanggal Bongkar', 'trim|required');
        $this->form_validation->set_rules('lokasi', 'Lokasi Bongkar', 'trim|required');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/edit-permohonan', $data); 
            $this->load->view('templates/footer');
        } else {
            $time = time();
            $timenow = date("Y-m-d H:i:s", $time);
            $data_update = [
                'nomorSurat' => $this->input->post('nomorSurat'),
                'TglSurat' => $this->input->post('TglSurat'),
                'Perihal' => $this->input->post('Perihal'),
                'NamaBarang' => $this->input->post('NamaBarang'),
                'JumlahBarang' => $this->input->post('JumlahBarang'),
                'NegaraAsal' => $this->input->post('NegaraAsal'),
                'NamaKapal' => $this->input->post('NamaKapal'),
                'noVoyage' => $this->input->post('noVoyage'),
                'TglKedatangan' => $this->input->post('TglKedatangan'),
                'TglBongkar' => $this->input->post('TglBongkar'),
                'lokasi' => $this->input->post('lokasi'),
                'time_stamp_update' => $timenow 
            ];

            $this->db->where('id', $id_permohonan);
            $this->db->update('user_permohonan', $data_update);

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Permohonan Telah Diubah.</div>');
            redirect('user/daftarPermohonan');
        }
    }

    public function force_change_password_page()
    {
        // Pastikan user login dan memang harus ganti password
        if (!$this->session->userdata('email') || $this->session->userdata('force_change_password') != 1) {
            redirect('user/index'); // Atau ke halaman default role mereka
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
            $this->load->view('user/form_force_change_password', $data); // Buat view ini
            $this->load->view('templates/footer');
        } else {
            $new_password_hash = password_hash($this->input->post('new_password'), PASSWORD_DEFAULT);
            $update_data = [
                'password' => $new_password_hash,
                'force_change_password' => 0 // Set kembali ke 0 setelah berhasil ganti
            ];

            $this->db->where('id', $data['user']['id']);
            $this->db->update('user', $update_data);

            // Update session juga
            $this->session->set_userdata('force_change_password', 0);

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Password Anda telah berhasil diubah. Silakan lanjutkan.</div>');
            redirect('user/index'); // Arahkan ke dashboard mereka
        }
    }

    // Di application/controllers/User.php
public function tes_layout()
{
    $data['title'] = 'Tes Layout';
    $data['subtitle'] = 'Halaman Uji Coba Template';
    // Data user minimal untuk diteruskan ke template
    $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
    if (empty($data['user'])) { // Jika tidak ada session, buat array user kosong agar tidak error di template
        $data['user'] = ['name' => 'Guest', 'image' => 'default.jpg', 'role_id' => 0, 'role_name' => 'Guest'];
    }


    log_message('debug', 'TES LAYOUT - Memulai load view header.');
    $this->load->view('templates/header', $data);

    // Buat view tes_konten.php
    log_message('debug', 'TES LAYOUT - Memulai load view tes_konten.');
    $this->load->view('user/tes_konten', $data); // View konten yang SANGAT sederhana

    log_message('debug', 'TES LAYOUT - Memulai load view footer.');
    $this->load->view('templates/footer', $data);
    log_message('debug', 'TES LAYOUT - Semua view selesai di-load.');
}

} // End class User
