<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller 
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
    }
    public function index()
    {
        $this->form_validation->set_rules('email', 'Email', 'trim|required');
        $this->form_validation->set_rules('password', 'password', 'trim|required');
        if($this->form_validation->run() == false){
            $data['title'] = "REPACK Login";
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/login');
            $this->load->view('templates/auth_footer');      
        } else {
            // proses login
            $this->_login();
        }
    }
    
    public function bypass($id)
    {
        $email = $id;
        $user = $this->db->get_where('user', ['email' => $email])->row_array();
        $data = [
            'email' => $user['email'],
            'role_id' => $user['role_id'],
            'nama' => $user['name']
        ];
        $this->session->set_userdata($data);

        if ($user['role_id'] == 1) {
            redirect('admin');
        } elseif ($user['role_id'] == 2) {
            redirect('user');
        } elseif ($user['role_id'] == 3) {
            redirect('monitoring');
        } elseif ($user['role_id'] == 4) {
            redirect('umum');
        }
    }

    private function _login()
    {
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        $user = $this->db->get_where('user', ['email' => $email])->row_array();
        
        if($user){
            //user ada
                if(password_verify($password , $user['password'])){
                    $data = [
                        'email' => $user['email'],
                        'role_id' => $user['role_id'],
                        'nama' => $user['name']
                    ];
                    $this->session->set_userdata($data);

                    if($user['role_id'] == 1){
                        redirect('admin');
                    } elseif ($user['role_id'] == 2) {
                        redirect('user');
                    } elseif ($user['role_id'] == 3) {
                        redirect('monitoring');
                    } elseif ($user['role_id'] == 4) {
                        redirect('umum');
                    }
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Wrong password!</div>');
                    redirect("auth");
                }
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">User not found, please create new account!</div>');
            redirect("auth");
        }
    }

    public function registration()
    {
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|is_unique[user.email]',[
            'is_unique' => 'The email has already registered'
        ]);
        $this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[3]|matches[password2]', [
            'matches' => 'Password not match',
            'min_length' => 'Password too short'
        ]);
        $this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password1]');

        if($this->form_validation->run() == false) {
            $data['title'] = "REPACK Registration";
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/registration');
            $this->load->view('templates/auth_footer');
        } else {
            $data = [
                'name' => htmlspecialchars($this->input->post('name', true)), 
                'email' => htmlspecialchars($this->input->post('email', true)),
                'image' => 'default.png',
                'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
                'role_id' => 2,
                'is_active' => 0,
                'date_created' => time()
            ];

            $is_active = 1;
            // echo $data;
            $this->db->insert('user', $data);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Account has been activated!, Please Login</div>');
            redirect('auth');
        }
    }

    public function logout()
    {
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('role_id');
        $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Logged out!</div>');
        redirect('auth');
    }

    public function blocked()
    {
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Access Blocked';
        $this->load->view('templates/header', $data);
        $this->load->view('auth/blocked', $data);
        $this->load->view('templates/footer');
    }

    public function changepass($id)
    {
        $data['user'] = $this->db->get_where('user', ['id' => $id])->row_array();
        $this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[3]|matches[password2]', [
            'matches' => 'Password not match',
            'min_length' => 'Password too short'
        ]);
        $this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password1]');

        if ($this->form_validation->run() == false) {
            $data['title'] = "REPACK Registration";
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/changepass', $data);
            $this->load->view('templates/auth_footer');
        } else {
            $password = [
                'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
            ];

            $is_active = 1;
            // var_dump($password);
            $this->db->where('id',$id);
            $this->db->update('user',$password);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Password berhasil diubah</div>');
            redirect('auth/berhasil');
        }
    }

    public function home()
    {
        $user = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();


        if ($user['role_id'] == 1) {
            redirect('admin');
        } elseif ($user['role_id'] == 2) {
            redirect('user');
        } elseif ($user['role_id'] == 3) {
            redirect('monitoring');
        } elseif ($user['role_id'] == 4) {
            redirect('umum');
        }
    }

    public function berhasil()
    {
        $user = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Password berhasil diubah</div>');


        if ($user['role_id'] == 1) {
            redirect('admin');
        } elseif ($user['role_id'] == 2) {
            redirect('user');
        } elseif ($user['role_id'] == 3) {
            redirect('monitoring');
        } elseif ($user['role_id'] == 4) {
            redirect('umum');
        }
    }
}