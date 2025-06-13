<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('email')) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Sesi Anda telah berakhir. Silakan login kembali.</div>');
            redirect('auth');
            exit;
        }

        $user = $this->db->get_where('user', ['id' => $this->session->userdata('user_id')])->row_array();
        if (!$user) {
            $this->session->sess_destroy();
            redirect('auth');
            exit;
        }

        $roles_wajib_mfa = [1, 2, 3, 4, 5];

        if (in_array($user['role_id'], $roles_wajib_mfa) && !$user['is_mfa_enabled']) {
            $current_controller = $this->router->fetch_class();
            $current_method = $this->router->fetch_method();
            
            if ($current_method != 'setup_mfa' && $current_method != 'verify_mfa' && $current_method != 'logout') {
                $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Untuk keamanan, Anda wajib mengaktifkan Multi-Factor Authentication (MFA).</div>');
                redirect($current_controller . '/setup_mfa');
                exit;
            }
            return;
        }

        if ($user['is_mfa_enabled'] && $this->session->userdata('mfa_verified') !== true) {
            redirect('auth/verify_mfa_login');
            exit;
        }
    }
}