<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monitoring extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        
        $this->load->library('session');
        $this->load->helper('url');
        if (!isset($this->db)) {
             $this->load->database();
        }
        $this->_check_auth_monitoring(); 
    }

    private function _check_auth_monitoring()
    {
        if (!$this->session->userdata('email')) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Please login to continue.</div>');
            redirect('auth');
            exit;
        }
        if ($this->session->userdata('role_id') != 4) { 
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Access Denied! You are not authorized.</div>');
            
            $role_id_session = $this->session->userdata('role_id');
            if ($role_id_session == 1) redirect('admin');
            elseif ($role_id_session == 2) redirect('user');
            elseif ($role_id_session == 3) redirect('petugas');
            else redirect('auth/blocked');
            exit;
        }
        
    }

    public function index() 
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Dashboard Monitoring';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        
        
        $data['total_pengajuan_kuota'] = $this->db->count_all_results('user_pengajuan_kuota');
        $data['total_permohonan_impor'] = $this->db->count_all_results('user_permohonan');
        $data['permohonan_kuota_pending'] = $this->db->where('status', 'pending')->count_all_results('user_pengajuan_kuota');
        $data['permohonan_impor_baru'] = $this->db->where_in('status', ['0','5'])->count_all_results('user_permohonan');


        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data); 
        $this->load->view('templates/topbar', $data);
        $this->load->view('monitoring/dashboard_monitoring_view', $data); 
        $this->load->view('templates/footer');
    }

    public function pengajuan_kuota()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Pantauan Data Pengajuan Kuota';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->db->select('upk.*, upr.NamaPers, u_pemohon.name as nama_pengaju_kuota');
        $this->db->from('user_pengajuan_kuota upk');
        $this->db->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $this->db->join('user u_pemohon', 'upk.id_pers = u_pemohon.id', 'left'); 
        $this->db->order_by('upk.submission_date', 'DESC');
        $data['daftar_pengajuan_kuota'] = $this->db->get()->result_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('monitoring/daftar_pengajuan_kuota_view', $data); 
        $this->load->view('templates/footer');
    }

    public function permohonan_impor()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Pantauan Data Permohonan Impor';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->db->select('up.*, upr.NamaPers, u_pemohon.name as nama_pemohon_impor, p_petugas.Nama as nama_petugas_pemeriksa');
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left'); 
        $this->db->join('petugas p_petugas', 'up.petugas = p_petugas.id', 'left'); 
        $this->db->order_by('up.time_stamp', 'DESC');
        $data['daftar_permohonan_impor'] = $this->db->get()->result_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('monitoring/daftar_permohonan_impor_view', $data); 
        $this->load->view('templates/footer');
    }

    
    public function detail_pengajuan_kuota($id_pengajuan)
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Pantauan Pengajuan Kuota';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->db->select('upk.*, upr.NamaPers, u_pemohon.name as nama_pengaju_kuota');
        $this->db->from('user_pengajuan_kuota upk');
        $this->db->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $this->db->join('user u_pemohon', 'upk.id_pers = u_pemohon.id', 'left');
        $this->db->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $this->db->get()->row_array();

        if (!$data['pengajuan']) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Data pengajuan kuota tidak ditemukan.</div>');
            redirect('monitoring/pengajuan_kuota');
            return;
        }

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('monitoring/detail_pengajuan_kuota_view', $data); 
        $this->load->view('templates/footer');
    }

    public function detail_permohonan_impor($id_permohonan)
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Pantauan Permohonan Impor';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->db->select('up.*, upr.NamaPers, u_pemohon.name as nama_pemohon_impor, p_petugas.Nama as nama_petugas_pemeriksa, lhp.JumlahBenar as lhp_jumlah_benar, lhp.catatan_pemeriksaan as lhp_catatan, lhp.file_dokumentasi_foto as lhp_file_foto, lhp.tanggal_lhp');
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $this->db->join('petugas p_petugas', 'up.petugas = p_petugas.id', 'left');
        $this->db->join('lhp', 'up.id = lhp.id_permohonan', 'left'); 
        $this->db->where('up.id', $id_permohonan);
        $data['permohonan'] = $this->db->get()->row_array();

        if (!$data['permohonan']) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Data permohonan impor tidak ditemukan.</div>');
            redirect('monitoring/permohonan_impor');
            return;
        }

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('monitoring/detail_permohonan_impor_view', $data); 
        $this->load->view('templates/footer');
    }
}