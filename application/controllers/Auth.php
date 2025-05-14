<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('session'); // Pastikan session di-load
        $this->load->helper('url');    // Pastikan helper url di-load
        // Load database library jika belum di autoload
        if (!isset($this->db)) {
             $this->load->database();
        }
    }

    public function index() // Halaman Login
    {
        if ($this->session->userdata('email')) { // 'email' di session tetap digunakan sebagai identifier unik setelah login
            $this->_redirect_user_by_role($this->session->userdata('is_active'));
        }

        // Validasi menggunakan nama input field baru 'login_identifier'
        $this->form_validation->set_rules('login_identifier', 'Email / NIP', 'trim|required', [
            'required' => 'Kolom Email / NIP wajib diisi.'
        ]);
        $this->form_validation->set_rules('password', 'Password', 'trim|required', [
            'required' => 'Kolom Password wajib diisi.'
        ]);

        if ($this->form_validation->run() == false) {
            $data['title'] = "REPACK Login";
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/login'); // Pastikan view login.php menggunakan name="login_identifier"
            $this->load->view('templates/auth_footer');
        } else {
            // Jika validasi sukses, panggil private method _login
            $this->_login();
        }
    }

    private function _redirect_user_by_role($is_active = 1)
    {
        $role_id = $this->session->userdata('role_id');
        $user_id = $this->session->userdata('id'); // Ambil user_id untuk query force_change_password

        // Cek ulang force_change_password di sini juga, untuk kasus jika user sudah login tapi belum ganti password
        // dan mencoba akses halaman lain. Ini lebih aman ditangani di __construct masing-masing controller role.
        // Namun, untuk redirect awal setelah login, pengecekan di _login() sudah cukup.

        // Logika untuk pengguna jasa yang belum aktif (is_active = 0)
        if ($role_id == 2 && $is_active == 0) {
            $this->session->set_flashdata('message', '<div class="alert alert-info" role="alert">Akun Anda belum aktif. Silakan lengkapi profil perusahaan Anda untuk aktivasi.</div>');
            redirect('user/edit'); // Arahkan ke halaman edit profil
            return;
        }
        
        // Jika user aktif atau bukan pengguna jasa yang belum aktif
        if ($role_id == 1) { // Admin
            redirect('admin');
        } elseif ($role_id == 2) { // Pengguna Jasa (yang sudah aktif)
            redirect('user');
        } elseif ($role_id == 3) { // Petugas
            redirect('petugas');
        } elseif ($role_id == 4) { // Monitoring
            redirect('monitoring');
        } else {
            // Jika role tidak dikenal atau tidak ada, hancurkan session dan kembali ke login
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Role tidak dikenal. Silakan login kembali.</div>');
            $this->session->sess_destroy();
            redirect('auth');
        }
    }

    public function bypass($id_or_email) 
    {
        // Pastikan database sudah di-load
        if (!isset($this->db)) {
             $this->load->database();
        }
        
        $user = $this->db->get_where('user', ['email' => $id_or_email])->row_array();

        if ($user) {
            $data_session = [ 
                'id' => $user['id'], 
                'email' => $user['email'],
                'role_id' => $user['role_id'],
                'nama' => $user['name'], 
                'is_active' => $user['is_active'] 
            ];
            $this->session->set_userdata($data_session);
            if ($user['force_change_password'] == 1) {
                $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Untuk keamanan, Anda wajib mengganti password Anda.</div>');
                // Arahkan ke halaman ganti password khusus untuk role tersebut
                if ($user['role_id'] == 2) { // Pengguna Jasa
                    redirect('user/force_change_password_page'); // Buat method dan view ini di User.php
                } elseif ($user['role_id'] == 3) { // Petugas
                    redirect('petugas/force_change_password_page'); // Buat method dan view ini di Petugas.php
                } else {
                    // Untuk role lain, mungkin langsung ke dashboard mereka jika tidak ada force change
                    $this->_redirect_user_by_role($user['is_active']);
                }
                return; // Hentikan eksekusi lebih lanjut
            } else {
                $this->_redirect_user_by_role($user['is_active']);
        }
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Bypass failed: User not found!</div>');
            redirect('auth');
        }
    }


    private function _login()
    {
        $login_identifier = $this->input->post('login_identifier');
        $password = $this->input->post('password');

        // Cari user berdasarkan login_identifier di kolom 'email'
        // karena NIP untuk Petugas/Monitoring disimpan di kolom 'email'
        $user = $this->db->get_where('user', ['email' => $login_identifier])->row_array();

        if ($user) {
            // User ditemukan, cek status aktif (kecuali jika role_id 2 dan is_active 0, yang akan dihandle _redirect_user_by_role)
            // if ($user['is_active'] == 1 || ($user['role_id'] == 2 && $user['is_active'] == 0) ) { // Logika ini lebih cocok di _redirect_user_by_role
                // Verifikasi password
                if (password_verify($password, $user['password'])) {
                    $data_session = [
                        'id'        => $user['id'],
                        'email'     => $user['email'], // Tetap simpan 'email' (yang berisi NIP atau email asli) sebagai identifier di session
                        'role_id'   => $user['role_id'],
                        'nama'      => $user['name'], // Ganti 'name' menjadi 'nama' jika itu yang Anda gunakan di session
                        'is_active' => $user['is_active'],
                        'force_change_password' => $user['force_change_password'] ?? 0 // Ambil status force_change_password
                    ];
                    $this->session->set_userdata($data_session);

                    // Cek apakah user wajib ganti password
                    if ($user['force_change_password'] == 1) {
                        $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Untuk keamanan, Anda wajib mengganti password Anda.</div>');
                        if ($user['role_id'] == 2) { // Pengguna Jasa
                            redirect('user/force_change_password_page'); // Buat method ini di User.php
                        } elseif ($user['role_id'] == 3) { // Petugas
                            redirect('petugas/force_change_password_page'); // Buat method ini di Petugas.php
                        } elseif ($user['role_id'] == 4) { // Monitoring
                            redirect('monitoring/force_change_password_page'); // Buat method ini di Monitoring.php
                        } else {
                            // Untuk Admin atau role lain yang mungkin tidak ada halaman force change password khusus
                            // Anda bisa arahkan ke halaman ganti password umum atau dashboard
                            $this->_redirect_user_by_role($user['is_active']);
                        }
                    } else {
                        $this->_redirect_user_by_role($user['is_active']);
                    }
                } else {
                    // Password salah
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Password salah!</div>');
                    redirect('auth');
                }
            // } else {
                // Akun tidak aktif (selain kasus Pengguna Jasa yang belum aktivasi profil)
                // $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Akun ini belum aktif atau dinonaktifkan.</div>');
                // redirect('auth');
            // }
        } else {
            // Email atau NIP tidak terdaftar
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Email atau NIP tidak terdaftar!</div>');
            redirect('auth');
        }
    }

    public function registration()
    {
        if ($this->session->userdata('email')) {
            $this->_redirect_user_by_role($this->session->userdata('is_active'));
        }

        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]', [
            'is_unique' => 'This email has already been registered!'
        ]);
        $this->form_validation->set_rules('password', 'Password', 'required|trim|min_length[3]|matches[password2]', [
            'matches' => 'Passwords don\'t match!',
            'min_length' => 'Password too short! (Minimum 3 characters)'
        ]);
        $this->form_validation->set_rules('password2', 'Repeat Password', 'required|trim|matches[password]');

        if ($this->form_validation->run() == false) {
            $data['title'] = "REPACK Registration";
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/registration');
            $this->load->view('templates/auth_footer');
        } else {
            if (!isset($this->db)) {
                 $this->load->database();
            }
        
            $data_insert = [
                'name' => htmlspecialchars($this->input->post('name', true)),
                'email' => htmlspecialchars($this->input->post('email', true)), // Registrasi Pengguna Jasa tetap pakai email
                'image' => 'default.jpg',
                'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                'role_id' => 2, // Default role untuk registrasi adalah Pengguna Jasa
                'is_active' => 0, // Awalnya tidak aktif, perlu lengkapi profil
                'force_change_password' => 0, // Tidak perlu force ganti password saat registrasi awal
                'date_created' => time()
            ];            
            $this->db->insert('user', $data_insert);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Akun berhasil diregistrasi! Silakan login untuk melengkapi profil perusahaan dan mengaktifkan akun Anda.</div>');
            redirect('auth');
        }
    }


    public function logout()
    {
        // Hancurkan semua data session
        $this->session->sess_destroy();
        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Anda telah berhasil logout!</div>');
        redirect('auth'); // Arahkan ke halaman login
    }

    public function blocked()
    {
        $data['title'] = 'Access Denied';
        $data['subtitle'] = 'Access Blocked';
        
        if ($this->session->userdata('email') && !isset($this->db)) {
             $this->load->database();
        }
        
        if ($this->session->userdata('email')) {
            $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        } else {
            // Jika tidak ada session email, mungkin user belum login atau session sudah destroy
            // Set $data['user'] ke array kosong atau null untuk menghindari error di view jika view mengharapkannya
            $data['user'] = null; // atau array()
        }
        $this->load->view('templates/auth_header', $data);
        $this->load->view('auth/blocked', $data);
        $this->load->view('templates/auth_footer');
    }


    public function changepass($id_from_url = null) 
    {
        if (!$this->session->userdata('email')) {
            redirect('auth');
        }
        
        // Pastikan database sudah di-load
        if (!isset($this->db)) {
             $this->load->database();
        }

        $logged_in_user_id = $this->session->userdata('id'); 

        $actual_user_id = null;
        if ($this->session->userdata('role_id') == 1 && $id_from_url !== null && is_numeric($id_from_url)) {
            $actual_user_id = $id_from_url;
        } elseif ($logged_in_user_id !== null) {
            $actual_user_id = $logged_in_user_id;
        }

        if ($actual_user_id === null) {
             $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">User ID not specified or session invalid for password change.</div>'); 
             $this->_redirect_user_by_role($this->session->userdata('is_active'));
             return;
        }

        $data['user_for_pass_change'] = $this->db->get_where('user', ['id' => $actual_user_id])->row_array();

        if (!$data['user_for_pass_change']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">User not found for password change (ID: '.$actual_user_id.').</div>');
            $this->_redirect_user_by_role($this->session->userdata('is_active'));
            return;
        }

        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        // === TAMBAHKAN DEFINISI TITLE & SUBTITLE ===
        $data['title'] = "Change Password";
        $data['subtitle'] = "Change Password"; // Atau sesuaikan jika perlu
        // ==========================================

        $this->form_validation->set_rules('password', 'New Password', 'required|trim|min_length[3]|matches[password2]', [
            'matches' => 'Passwords don\'t match!',
            'min_length' => 'Password too short! (Minimum 3 characters)'
        ]);
        $this->form_validation->set_rules('password2', 'Repeat New Password', 'required|trim|matches[password]');

        if ($this->form_validation->run() == false) {
            // Gunakan template utama karena user sudah login
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data); // Sidebar memerlukan $subtitle
            $this->load->view('templates/topbar', $data);
            $this->load->view('auth/changepass', $data); 
            $this->load->view('templates/footer');
        } else {
            $new_password_hash = password_hash($this->input->post('password'), PASSWORD_DEFAULT);

            $this->db->where('id', $actual_user_id);
            $this->db->update('user', ['password' => $new_password_hash]);

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Password changed successfully!</div>');
            $this->_redirect_user_by_role($this->session->userdata('is_active'));
        }
    }

    public function home()
    {
        if (!$this->session->userdata('email')) {
            redirect('auth');
        } else {
            $this->_redirect_user_by_role($this->session->userdata('is_active'));
        }
    }

    public function berhasil()
    {
        if (!$this->session->userdata('email')) {
            redirect('auth');
        } else {
            $this->_redirect_user_by_role($this->session->userdata('is_active'));
        }
    }

} // End class Auth
