<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_loggedin(); 
        if ($this->session->userdata('role_id') != 1) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Access Denied! You are not authorized to access this page.</div>');
            redirect('auth/blocked'); 
        }
        $this->load->helper(array('form', 'url', 'repack_helper')); 
        $this->load->library('form_validation');
        $this->load->library('upload'); 
        if (!isset($this->db)) {
            $this->load->database();
        }
    }

    public function index()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Admin Dashboard';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        
        $data['total_users'] = $this->db->count_all('user');
        $data['pending_permohonan'] = $this->db->where_in('status', ['0', '1', '2'])->count_all_results('user_permohonan');
        $data['pending_kuota_requests'] = $this->db->where('status', 'pending')->count_all_results('user_pengajuan_kuota');

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data); 
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/index', $data); 
        $this->load->view('templates/footer');
    }

    public function role()
    {
        // ... (kode role tetap sama) ...
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Role Management';
        $data['role'] = $this->db->get('user_role')->result_array();

        $this->form_validation->set_rules('role', 'Role', 'required|trim');
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
        // ... (kode roleAccess tetap sama) ...
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Role Access Management';
        $data['role'] = $this->db->get_where('user_role', ['id' => $role_id])->row_array();
        $data['menu'] = $this->db->get('user_menu')->result_array();
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/role-access', $data);
        $this->load->view('templates/footer');
    }

    public function changeaccess()
    {
        // ... (kode changeaccess tetap sama) ...
        $menu_id = $this->input->post('menuId');
        $role_id = $this->input->post('roleId');
        $data = ['role_id' => $role_id, 'menu_id' => $menu_id];
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
        // ... (kode permohonanMasuk tetap sama) ...
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Permohonan Masuk';
        $this->db->select('up.*, upr.NamaPers, u.name as nama_pengaju, ptgs.Nama as nama_petugas_assigned');
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('user u', 'upr.id_pers = u.id', 'left'); 
        $this->db->join('petugas ptgs', 'up.petugas = ptgs.id', 'left'); 
        $this->db->order_by('up.time_stamp', 'DESC');
        $data['permohonan'] = $this->db->get()->result_array();
        $data['list_petugas'] = $this->db->get('petugas')->result_array();
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/permohonan-masuk', $data); 
        $this->load->view('templates/footer');
    }
    
    public function prosesSurat($id_permohonan) 
    {
        // ... (kode prosesSurat tetap sama, termasuk logika pemotongan kuota) ...
         $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Penyelesaian Permohonan & LHP';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->db->select('up.*, upr.NamaPers, upr.remaining_quota, upr.npwp, u.email as email_pemohon'); 
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('user u', 'upr.id_pers = u.id', 'left');
        $this->db->where('up.id', $id_permohonan);
        $data['permohonan'] = $this->db->get()->row_array();
        if (!$data['permohonan']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan!</div>');
            redirect('admin/permohonanMasuk');
        }
        $data['lhp'] = $this->db->get_where('lhp', ['id_permohonan' => $id_permohonan])->row_array();
        $this->form_validation->set_rules('nomorSetuju', 'Nomor Surat Keputusan', 'trim|required');
        $this->form_validation->set_rules('tgl_S', 'Tanggal Surat Keputusan', 'trim|required');
        $this->form_validation->set_rules('status_final', 'Status Final Permohonan', 'required'); 
        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/proses_surat_lhp', $data); 
            $this->load->view('templates/footer');
        } else {
            $status_final_permohonan = $this->input->post('status_final'); 
            $data_update_permohonan = [
                'nomorSetuju' => $this->input->post('nomorSetuju'),
                'tgl_S' => $this->input->post('tgl_S'),
                'nomorND' => $this->input->post('nomorND'), 
                'tgl_ND' => $this->input->post('tgl_ND'),   
                'link' => $this->input->post('link'),       
                'linkND' => $this->input->post('linkND'),   
                'time_selesai' => date("Y-m-d H:i:s"),
                'status' => $status_final_permohonan 
            ];
            $this->db->where('id', $id_permohonan);
            $this->db->update('user_permohonan', $data_update_permohonan);
            if ($status_final_permohonan == '3' && $data['lhp'] && isset($data['lhp']['JumlahBenar'])) { 
                $id_pers_terkait = $data['permohonan']['id_pers'];
                $jumlah_barang_disetujui = (int)$data['lhp']['JumlahBenar']; 
                if ($jumlah_barang_disetujui > 0) {
                    $perusahaan = $this->db->get_where('user_perusahaan', ['id_pers' => $id_pers_terkait])->row_array();
                    if ($perusahaan && isset($perusahaan['remaining_quota'])) {
                        $sisa_kuota_lama = (int)$perusahaan['remaining_quota'];
                        $sisa_kuota_baru = $sisa_kuota_lama - $jumlah_barang_disetujui;
                        if ($sisa_kuota_baru < 0) { $sisa_kuota_baru = 0; }
                        $this->db->where('id_pers', $id_pers_terkait);
                        $this->db->update('user_perusahaan', ['remaining_quota' => $sisa_kuota_baru]);
                        log_message('info', 'Quota updated for id_pers: ' . $id_pers_terkait . '. Used: ' . $jumlah_barang_disetujui . '. Remaining: ' . $sisa_kuota_baru);
                    }
                }
            }
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Status permohonan dan LHP telah berhasil diproses!</div>');
            redirect('admin/permohonanMasuk');
        }
    }

    // METHOD BARU UNTUK MONITORING KUOTA
    public function monitoring_kuota()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Monitoring Kuota Perusahaan';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        // Ambil semua data perusahaan beserta data user (untuk nama jika id_pers = user.id)
        $this->db->select('upr.id_pers, upr.NamaPers, upr.initial_quota, upr.remaining_quota, u.name as user_name, u.email as user_email');
        $this->db->from('user_perusahaan upr');
        $this->db->join('user u', 'upr.id_pers = u.id', 'left'); // Asumsi id_pers di user_perusahaan adalah user.id
        $this->db->order_by('upr.NamaPers', 'ASC');
        $perusahaan_list = $this->db->get()->result_array();

        $data['monitoring_data'] = [];
        if ($perusahaan_list) {
            foreach ($perusahaan_list as $perusahaan) {
                // Untuk setiap perusahaan, cari KEP terakhir (nomor_sk_petugas dari pengajuan kuota terakhir yang 'approved' atau 'selesai')
                $this->db->select('nomor_sk_petugas');
                $this->db->from('user_pengajuan_kuota');
                $this->db->where('id_pers', $perusahaan['id_pers']);
                $this->db->where_in('status', ['approved', 'selesai']); // Hanya yang disetujui atau selesai
                $this->db->order_by('processed_date', 'DESC'); // Ambil yang terbaru diproses
                $this->db->limit(1);
                $latest_kep = $this->db->get()->row_array();

                $perusahaan['kep_terakhir'] = $latest_kep ? $latest_kep['nomor_sk_petugas'] : '-';
                $data['monitoring_data'][] = $perusahaan;
            }
        }

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data); 
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/monitoring_kuota_view', $data); // View baru
        $this->load->view('templates/footer');
    }

    public function daftar_pengajuan_kuota()
    {
        // ... (kode daftar_pengajuan_kuota tetap sama) ...
    }

    public function proses_pengajuan_kuota($id_pengajuan)
    {
        // ... (kode proses_pengajuan_kuota tetap sama) ...
    }

    public function print_pengajuan_kuota($id_pengajuan)
    {
        // ... (kode print_pengajuan_kuota tetap sama) ...
    }
    
    // Anda mungkin memiliki method lain seperti manajemen_user, prosesLHP, dll.
    // Pastikan untuk membuatnya atau mengintegrasikannya.

} // End class Admin
