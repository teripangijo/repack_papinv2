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

    public function edit() // Method untuk menampilkan dan memproses form edit profil & perusahaan
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Profil & Perusahaan';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $id_user_login = $data['user']['id'];

        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $id_user_login])->row_array();
        $is_activating = empty($data['user_perusahaan']); // True jika profil perusahaan belum ada (aktivasi)
        $data['is_activating'] = $is_activating; // Kirim ke view

        // Ambil daftar kuota per barang untuk ditampilkan jika profil sudah ada
        if (!$is_activating) {
            $this->db->select('nama_barang, initial_quota_barang, remaining_quota_barang, nomor_skep_asal, tanggal_skep_asal, status_kuota_barang');
            $this->db->from('user_kuota_barang');
            $this->db->where('id_pers', $id_user_login);
            $this->db->order_by('nama_barang', 'ASC');
            $data['daftar_kuota_barang_user'] = $this->db->get()->result_array();
        } else {
            $data['daftar_kuota_barang_user'] = [];
        }

        // --- Aturan Validasi Form ---
        $this->form_validation->set_rules('NamaPers', 'Nama Perusahaan', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('npwp', 'NPWP', 'trim|required|regex_match[/^[0-9]{2}\.[0-9]{3}\.[0-9]{3}\.[0-9]{1}-[0-9]{3}\.[0-9]{3}$/]', ['regex_match' => 'Format NPWP tidak valid. Contoh: 00.000.000.0-000.000']);
        $this->form_validation->set_rules('alamat', 'Alamat Perusahaan', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('telp', 'Nomor Telepon Perusahaan', 'trim|required|numeric|max_length[15]');
        $this->form_validation->set_rules('pic', 'Nama PIC', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('jabatanPic', 'Jabatan PIC', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('NoSkepFasilitas', 'No. SKEP Fasilitas Umum', 'trim|max_length[100]'); // Opsional

        // Validasi untuk input kuota awal hanya saat aktivasi
        if ($is_activating) {
            // Hanya validasi jika salah satu field kuota awal diisi, untuk mengindikasikan user ingin input
            if ($this->input->post('initial_skep_no') || $this->input->post('initial_skep_tgl') || $this->input->post('initial_nama_barang') || $this->input->post('initial_kuota_jumlah')) {
                $this->form_validation->set_rules('initial_skep_no', 'Nomor SKEP Kuota Awal', 'trim|required|max_length[100]');
                $this->form_validation->set_rules('initial_skep_tgl', 'Tanggal SKEP Kuota Awal', 'trim|required');
                $this->form_validation->set_rules('initial_nama_barang', 'Nama Barang Kuota Awal', 'trim|required|max_length[100]');
                $this->form_validation->set_rules('initial_kuota_jumlah', 'Jumlah Kuota Awal', 'trim|required|numeric|greater_than[0]');
            }
        }

        // Validasi file
        if ($is_activating || (isset($_FILES['ttd']) && $_FILES['ttd']['error'] != UPLOAD_ERR_NO_FILE)) {
            // Jika aktivasi, TTD wajib. Jika edit, TTD wajib diupload jika field file dipilih.
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
            $data['upload_error'] = $this->session->flashdata('upload_error_detail'); // Gunakan nama flashdata spesifik
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/edit-profile', $data);
            $this->load->view('templates/footer', $data);
        } else {
            // --- Persiapan Nama File ---
            $nama_file_ttd = $data['user_perusahaan']['ttd'] ?? null;
            $nama_file_profile_image = $data['user']['image'] ?? 'default.jpg';
            $nama_file_skep_fasilitas = $data['user_perusahaan']['FileSkepFasilitas'] ?? null;
            $nama_file_initial_skep = null; // Untuk SKEP kuota awal barang

            // --- Proses Upload TTD ---
            if (isset($_FILES['ttd']) && $_FILES['ttd']['error'] != UPLOAD_ERR_NO_FILE) {
                $config_ttd = $this->_get_upload_config('./uploads/ttd/', 'jpg|png|jpeg|pdf', 1024); // 1MB
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

            // --- Proses Upload Gambar Profil (Logo Perusahaan) ---
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] != UPLOAD_ERR_NO_FILE) {
                $config_profile = $this->_get_upload_config('./uploads/profile_images/', 'jpg|png|jpeg|gif', 1024, 1024, 1024); // Path diubah
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

            // --- Proses Upload File SKEP Fasilitas ---
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

            // --- Proses Upload File SKEP Kuota Awal (hanya saat aktivasi dan jika diisi) ---
            if ($is_activating && isset($_FILES['initial_skep_file']) && $_FILES['initial_skep_file']['error'] != UPLOAD_ERR_NO_FILE) {
                $config_skep_i = $this->_get_upload_config('./uploads/skep_awal_user/', 'pdf|jpg|jpeg|png', 2048); // Buat folder ini
                $this->upload->initialize($config_skep_i, TRUE);
                if ($this->upload->do_upload('initial_skep_file')) {
                    $nama_file_initial_skep = $this->upload->data('file_name');
                } else {
                    $this->session->set_flashdata('upload_error_detail', $this->upload->display_errors());
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload File SKEP Kuota Awal Gagal: ' . $this->upload->display_errors('', '') . '</div>');
                    redirect('user/edit'); return;
                }
            }


            // --- Update Data User (Hanya Gambar Profil/Logo) ---
            $data_user_update = [];
            if ($nama_file_profile_image !== null && $nama_file_profile_image != $data['user']['image']) {
                $data_user_update['image'] = $nama_file_profile_image;
            }
            if (!empty($data_user_update)) {
                 $this->db->where('id', $id_user_login);
                 $this->db->update('user', $data_user_update);
                 // Update session jika gambar berubah
                 if(isset($data_user_update['image'])) $this->session->set_userdata('image', $data_user_update['image']);
            }

            // --- Update/Insert Data Perusahaan ---
            $data_perusahaan = [
                'NamaPers' => $this->input->post('NamaPers'),
                'npwp' => $this->input->post('npwp'),
                'alamat' => $this->input->post('alamat'),
                'telp' => $this->input->post('telp'),
                'pic' => $this->input->post('pic'),
                'jabatanPic' => $this->input->post('jabatanPic'),
                'NoSkepFasilitas' => $this->input->post('NoSkepFasilitas') ?: null, // Simpan null jika kosong
            ];
             if ($nama_file_ttd !== null) {
                 $data_perusahaan['ttd'] = $nama_file_ttd;
             }
             if ($nama_file_skep_fasilitas !== null) {
                 $data_perusahaan['FileSkepFasilitas'] = $nama_file_skep_fasilitas;
             }

            if ($is_activating) {
                $data_perusahaan['id_pers'] = $id_user_login; // Primary key = user_id
                // 'initial_quota' dan 'remaining_quota' umum tidak diisi dari sini lagi
                $this->db->insert('user_perusahaan', $data_perusahaan);

                // Proses SKEP dan Kuota Awal Barang yang diinput user
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
                        // 'file_skep_kuota_awal' => $nama_file_initial_skep, // TAMBAHKAN KOLOM INI DI user_kuota_barang JIKA PERLU
                        'dicatat_oleh_user_id' => $id_user_login,
                        'waktu_pencatatan' => date('Y-m-d H:i:s')
                    ];
                    if ($nama_file_initial_skep) { // Hanya tambahkan jika file diupload
                        // $data_kuota_awal_barang['file_skep_kuota_awal'] = $nama_file_initial_skep;
                    }
                    $this->db->insert('user_kuota_barang', $data_kuota_awal_barang);
                    log_message('info', 'KUOTA AWAL BARANG dicatat saat aktivasi untuk user: ' . $id_user_login . ', barang: ' . $initial_nama_barang . ', jumlah: ' . $initial_kuota_jumlah);

                    // PEMANGGILAN LOG DARI USER CONTROLLER DIKOMENTARI KARENA METHOD ADA DI ADMIN
                    // Anda perlu membuat mekanisme logging yang bisa diakses dari sini, misal via Helper/Library.
                    /*
                    $this->_log_perubahan_kuota_user_side( // Buat method ini jika perlu
                        $id_user_login, 'penambahan', $initial_kuota_jumlah, 0, $initial_kuota_jumlah,
                        'Input kuota awal oleh pengguna saat aktivasi. SKEP: ' . $initial_skep_no . ' Barang: ' . $initial_nama_barang,
                        $this->db->insert_id(), 'input_kuota_awal_user', $id_user_login
                    );
                    */
                }

                $this->db->where('id', $id_user_login);
                $this->db->update('user', ['is_active' => 1]);
                $this->session->set_userdata('is_active', 1);
                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Profil perusahaan berhasil disimpan dan akun Anda telah diaktifkan! Anda sekarang dapat mengajukan kuota atau membuat permohonan.</div>');
                redirect('user/index');
            } else { // Update data perusahaan yang sudah ada
                // Untuk field 'quota' lama, kita tidak mengupdatenya lagi di user_perusahaan
                // karena sudah digantikan oleh sistem kuota per barang.
                $this->db->where('id_pers', $id_user_login);
                $this->db->update('user_perusahaan', $data_perusahaan);

                // Cek apakah ada perubahan sebelum menampilkan pesan
                $perubahan_terdeteksi = false;
                if (!empty($data_user_update)) $perubahan_terdeteksi = true;
                if ($nama_file_ttd !== null && (!isset($data['user_perusahaan']['ttd']) || $nama_file_ttd !== $data['user_perusahaan']['ttd'])) $perubahan_terdeteksi = true;
                if ($nama_file_skep_fasilitas !== null && (!isset($data['user_perusahaan']['FileSkepFasilitas']) || $nama_file_skep_fasilitas !== $data['user_perusahaan']['FileSkepFasilitas'])) $perubahan_terdeteksi = true;
                // Cek perubahan pada field perusahaan lainnya
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

    // Helper function untuk konfigurasi upload (DRY principle)
    private function _get_upload_config($upload_path, $allowed_types, $max_size_kb, $max_width = null, $max_height = null) {
        if (!is_dir($upload_path)) {
            if (!@mkdir($upload_path, 0777, true)) {
                // Gagal membuat direktori, bisa throw exception atau return false/array error
                log_message('error', 'Gagal membuat direktori upload: ' . $upload_path);
                return false; // Indikasi error
            }
        }
        if (!is_writable($upload_path)) {
            log_message('error', 'Direktori upload tidak writable: ' . $upload_path);
            return false; // Indikasi error
        }

        $config['upload_path']   = $upload_path;
        $config['allowed_types'] = $allowed_types;
        $config['max_size']      = $max_size_kb; // Dalam kilobytes
        if ($max_width) $config['max_width'] = $max_width;
        if ($max_height) $config['max_height'] = $max_height;
        $config['encrypt_name']  = TRUE;
        return $config;
    }

    public function file_check($str, $field)
    {
        // $is_activating diambil dari data yang sudah di-load sebelumnya
        $user_id_for_file_check = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row()->id;
        $user_perusahaan_for_file_check = $this->db->get_where('user_perusahaan', ['id_pers' => $user_id_for_file_check])->row_array();
        $is_activating_for_file_check = empty($user_perusahaan_for_file_check);

        $config = [];
        $error_field_name = '';

        switch ($field) {
            case 'ttd':
                $config = ['allowed_types' => ['image/jpeg', 'image/png', 'application/pdf', 'image/pjpeg'], 'max_size' => 1024 * 1024, 'error_name' => 'Tanda Tangan PIC', 'allowed_str' => 'jpg, png, pdf'];
                // Wajib jika aktivasi
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
                // Opsional, jadi tidak perlu cek wajib di sini jika tidak ada file. Controller akan menangani.
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

    public function permohonan_impor_kembali() // Atau nama method Anda, misal: permohonan_impor_kembali()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Permohonan Impor Kembali'; // Sesuaikan judul
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $id_user_login = $data['user']['id']; // ID user yang login

        // Ambil data perusahaan (ini sudah ada di kode Anda)
        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $id_user_login])->row_array();

        // Jika data perusahaan belum lengkap, redirect ke edit profil (sudah ada di kode Anda)
        if (empty($data['user_perusahaan']) && $data['user']['is_active'] == 1) {
             $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Mohon lengkapi profil perusahaan Anda terlebih dahulu di menu Edit Profil.</div>');
             redirect('user/edit');
             return;
        }
        // Jika akun belum aktif dan perusahaan belum ada, juga redirect
        if ($data['user']['is_active'] == 0 && empty($data['user_perusahaan'])) {
             $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Akun Anda belum aktif. Mohon lengkapi profil perusahaan Anda untuk aktivasi.</div>');
             redirect('user/edit');
             return;
        }


        // --- LOGIKA PENGAMBILAN NO SKEP ---
        $nomor_skep_valid = null;

        // Prioritas 1: Ambil dari profil perusahaan (user_perusahaan.NoSkep)
        if (isset($data['user_perusahaan']['NoSkep']) && !empty(trim($data['user_perusahaan']['NoSkep']))) {
            $nomor_skep_valid = trim($data['user_perusahaan']['NoSkep']);
            log_message('debug', 'SKEP ditemukan dari profil perusahaan: ' . $nomor_skep_valid);
        } else {
            // Prioritas 2: Ambil dari pengajuan kuota terakhir yang disetujui
            $this->db->select('nomor_sk_petugas');
            $this->db->from('user_pengajuan_kuota');
            $this->db->where('id_pers', $id_user_login); // Pengajuan kuota milik perusahaan ini
            $this->db->where('status', 'approved');     // Yang statusnya disetujui
            $this->db->where('nomor_sk_petugas IS NOT NULL');
            $this->db->where("nomor_sk_petugas != ''");
            $this->db->order_by('processed_date', 'DESC'); // Ambil yang terbaru
            $this->db->limit(1);
            $pengajuan_kuota_terakhir = $this->db->get()->row_array();

            if ($pengajuan_kuota_terakhir && isset($pengajuan_kuota_terakhir['nomor_sk_petugas']) && !empty(trim($pengajuan_kuota_terakhir['nomor_sk_petugas']))) {
                $nomor_skep_valid = trim($pengajuan_kuota_terakhir['nomor_sk_petugas']);
                log_message('debug', 'SKEP ditemukan dari pengajuan kuota terakhir: ' . $nomor_skep_valid);
            }
        }
        $data['nomor_skep_otomatis'] = $nomor_skep_valid;
        // ------------------------------------

        // Jika tidak ada SKEP valid dari kedua sumber, cegah pengajuan
        if ($nomor_skep_valid === null) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Tidak ditemukan No. SKEP yang valid (baik dari profil perusahaan maupun dari histori pengajuan kuota yang disetujui). Anda tidak dapat membuat Permohonan Impor Kembali saat ini.</div>');
            // Arahkan ke halaman di mana user bisa mengupdate SKEP atau mengajukan kuota
            // Misalnya, ke dashboard atau daftar pengajuan kuota
            redirect('user/index'); // Atau 'user/daftar_pengajuan_kuota_user'
            return;
        }


        // Aturan validasi form (seperti yang sudah ada di kode Anda)
        $this->form_validation->set_rules('nomorSurat', 'Nomor Surat', 'trim|required');
        $this->form_validation->set_rules('TglSurat', 'Tanggal Surat', 'trim|required');
        $this->form_validation->set_rules('Perihal', 'Perihal', 'trim|required');
        $this->form_validation->set_rules('NamaBarang', 'Nama Barang', 'trim|required');
        $this->form_validation->set_rules('JumlahBarang', 'Jumlah Barang', 'trim|required|numeric|greater_than[0]');
        $this->form_validation->set_rules('NegaraAsal', 'Negara Asal', 'trim|required');
        $this->form_validation->set_rules('NamaKapal', 'Nama Kapal', 'trim|required');
        $this->form_validation->set_rules('noVoyage', 'Nomor Voyage', 'trim|required');
        // No. SKEP sekarang diambil otomatis, jadi tidak perlu divalidasi sebagai input
        // $this->form_validation->set_rules('NoSkep', 'No. SKEP', 'trim|required');
        $this->form_validation->set_rules('TglKedatangan', 'Tanggal Perkiraan Kedatangan', 'trim|required');
        $this->form_validation->set_rules('TglBongkar', 'Tanggal Perkiraan Bongkar', 'trim|required');
        $this->form_validation->set_rules('lokasi', 'Lokasi Bongkar', 'trim|required');


        if ($this->form_validation->run() == false) {
            log_message('debug', 'Form Permohonan Impor Validasi Gagal. Errors: ' . validation_errors());
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/permohonan_impor_kembali_form', $data); // atau nama view Anda: 'user/form_permohonan_impor_kembali_view.php'
            $this->load->view('templates/footer');
        } else {
            // Proses penyimpanan data permohonan
            $time = time();
            $timenow = date("Y-m-d H:i:s", $time);
            $data_insert = [
                'NamaPers'      => $data['user_perusahaan']['NamaPers'],
                'alamat'        => $data['user_perusahaan']['alamat'],
                'nomorSurat'    => $this->input->post('nomorSurat'),
                'TglSurat'      => $this->input->post('TglSurat'),
                'Perihal'       => $this->input->post('Perihal'),
                'NamaBarang'    => $this->input->post('NamaBarang'),
                'JumlahBarang'  => $this->input->post('JumlahBarang'),
                'NegaraAsal'    => $this->input->post('NegaraAsal'),
                'NamaKapal'     => $this->input->post('NamaKapal'),
                'noVoyage'      => $this->input->post('noVoyage'),
                'NoSkep'        => $nomor_skep_valid, // Simpan No. SKEP yang valid
                'TglKedatangan' => $this->input->post('TglKedatangan'),
                'TglBongkar'    => $this->input->post('TglBongkar'),
                'lokasi'        => $this->input->post('lokasi'),
                'id_pers'       => $id_user_login,
                'time_stamp'    => $timenow,
                'status'        => '0'
            ];
            $this->db->insert('user_permohonan', $data_insert);

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Permohonan Impor Kembali Telah Disimpan dan akan segera diproses.</div>');
            redirect('user/daftarPermohonan'); // atau ke halaman konfirmasi/dashboard
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
        $data['subtitle'] = 'Pengajuan Penambahan Kuota';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $id_user_login = $data['user']['id'];

        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $id_user_login])->row_array();

        if(empty($data['user_perusahaan'])) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Mohon lengkapi profil perusahaan Anda terlebih dahulu di menu "Edit Profil & Perusahaan" sebelum mengajukan kuota.</div>');
            redirect('user/edit');
            return;
        }
        if ($data['user']['is_active'] == 0) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Akun Anda belum aktif. Tidak dapat mengajukan kuota. Mohon lengkapi profil perusahaan Anda jika belum, atau hubungi Administrator.</div>');
        redirect('user/edit');
            return;
        }

        // Aturan validasi form (nama field di form harus sama dengan parameter pertama set_rules)
        $this->form_validation->set_rules('nomor_surat_pengajuan', 'Nomor Surat Pengajuan', 'trim|required');
        $this->form_validation->set_rules('tanggal_surat_pengajuan', 'Tanggal Surat Pengajuan', 'trim|required');
        $this->form_validation->set_rules('perihal_pengajuan', 'Perihal Surat Pengajuan', 'trim|required');
        $this->form_validation->set_rules('nama_barang_kuota', 'Nama/Jenis Barang untuk Kuota', 'trim|required'); // Nama barang spesifik
        $this->form_validation->set_rules('requested_quota', 'Jumlah Kuota Diajukan', 'trim|required|numeric|greater_than[0]');
        $this->form_validation->set_rules('reason', 'Alasan Pengajuan', 'trim|required');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/pengajuan_kuota_form', $data); // View ini sudah ada
            $this->load->view('templates/footer', $data);
        } else {
            // ... (logika upload file jika ada) ...
            $nama_file_lampiran = null; // Isi jika ada upload

            // Data yang disimpan tetap sama, nama_barang_kuota akan jadi kunci
            $data_pengajuan = [
                'id_pers'                   => $id_user_login,
                'nomor_surat_pengajuan'     => $this->input->post('nomor_surat_pengajuan'),
                'tanggal_surat_pengajuan'   => $this->input->post('tanggal_surat_pengajuan'),
                'perihal_pengajuan'         => $this->input->post('perihal_pengajuan'),
                'nama_barang_kuota'         => $this->input->post('nama_barang_kuota'), // Penting untuk menyimpan jenis barang
                'requested_quota'           => $this->input->post('requested_quota'),
                'reason'                    => $this->input->post('reason'),
                // 'file_lampiran_user'     => $nama_file_lampiran, // Jika ada kolomnya
                'submission_date'           => date('Y-m-d H:i:s'),
                'status'                    => 'pending'
            ];

            $this->db->insert('user_pengajuan_kuota', $data_pengajuan);

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Pengajuan kuota Anda untuk barang "'.htmlspecialchars($this->input->post('nama_barang_kuota')).'" telah berhasil dikirim.</div>');
            redirect('user/daftar_pengajuan_kuota_user');
        }
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
