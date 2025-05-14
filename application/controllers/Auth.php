<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        // Load database library jika belum di autoload
        // $this->load->database(); 
    }

    // ... (method index, _redirect_user_by_role, bypass, _login, registration, logout, blocked tetap sama) ...
    public function index()
    {
        if ($this->session->userdata('email')) {
            $this->_redirect_user_by_role($this->session->userdata('is_active')); // Gunakan status dari session
        }

        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email'); 
        $this->form_validation->set_rules('password', 'Password', 'trim|required'); 

        if ($this->form_validation->run() == false) {
            $data['title'] = "REPACK Login";
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/login');
            $this->load->view('templates/auth_footer');
        } else {
            $this->_login();
        }
    }

    private function _redirect_user_by_role($is_active = 1) 
    {
        $role_id = $this->session->userdata('role_id');

        if ($is_active == 0 && $role_id == 2) {
            $this->session->set_flashdata('message', '<div class="alert alert-info" role="alert">Your account is not yet active. Please complete your company profile to activate it.</div>');
            redirect('user/edit'); 
            return; 
        }

        if ($role_id == 1) {
            redirect('admin');
        } elseif ($role_id == 2) {
            redirect('user'); 
        } elseif ($role_id == 3) {
            redirect('monitoring');
        } elseif ($role_id == 4) {
            redirect('umum');
        } else {
            // Jika role tidak dikenal atau tidak ada, kembali ke login
            $this->session->sess_destroy(); // Hancurkan session yang mungkin tidak valid
            redirect('auth/login');
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
            $this->_redirect_user_by_role($user['is_active']); 
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Bypass failed: User not found!</div>');
            redirect('auth');
        }
    }


    private function _login()
    {
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        // Pastikan database sudah di-load
        if (!isset($this->db)) {
             $this->load->database();
        }

        $user = $this->db->get_where('user', ['email' => $email])->row_array();

        if ($user) {
            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Set data session
                $data_session = [
                    'id'        => $user['id'], 
                    'email'     => $user['email'],
                    'role_id'   => $user['role_id'],
                    'nama'      => $user['name'],
                    'is_active' => $user['is_active']
                ];
                $this->session->set_userdata($data_session);

                // Redirect berdasarkan role dan status aktif
                $this->_redirect_user_by_role($user['is_active']);

            } else {
                // Password salah
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Wrong password!</div>');
                redirect('auth');
            }
        } else {
            // Email tidak terdaftar
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Email is not registered!</div>');
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
            // Pastikan database sudah di-load
            if (!isset($this->db)) {
                 $this->load->database();
            }
        
            $data_insert = [ 
                'name' => htmlspecialchars($this->input->post('name', true)),
                'email' => htmlspecialchars($this->input->post('email', true)),
                'image' => 'default.jpg', 
                'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT), 
                'role_id' => 2, 
                'is_active' => 0, 
                'date_created' => time()
            ];            
            $this->db->insert('user', $data_insert);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Account registered successfully! Please login to complete your company profile and activate your account.</div>');
            redirect('auth'); 
        }
    }


    public function logout()
    {
        $this->session->unset_userdata('id'); 
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('role_id');
        $this->session->unset_userdata('nama');
        $this->session->unset_userdata('is_active');

        $this->session->sess_destroy(); // Hancurkan session

        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">You have been logged out!</div>');
        redirect('auth');
    }

    public function blocked()
    {
        $data['title'] = 'Access Denied'; 
        $data['subtitle'] = 'Access Blocked'; // Tetap ada jika view memerlukannya
        
        // Pastikan database sudah di-load jika query diperlukan
        if ($this->session->userdata('email') && !isset($this->db)) {
             $this->load->database();
        }
        
        if ($this->session->userdata('email')) {
            $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        } else {
            $data['user'] = null; 
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
