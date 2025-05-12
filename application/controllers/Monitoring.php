<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Monitoring extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_loggedin();
        $this->load->helper('repack_helper');
    }

    public function index()
    {
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['permohonanMasuk'] = $this->db->get_where('user_permohonan',['status' => '0'])->result();
        $data['permohonanProses1'] = $this->db->get_where('user_permohonan', ['status' => '1'])->result();
        $data['permohonanProses2'] = $this->db->get_where('user_permohonan', ['status' => '2'])->result();
        $data['permohonanSelesai'] = $this->db->get_where('user_permohonan', ['status' => '3'])->result();
        $data['Masuk'] =count($data['permohonanMasuk']);
        $data['Proses1'] =count($data['permohonanProses1']);
        $data['Proses2'] = count($data['permohonanProses2']);
        $data['Proses'] = $data['Proses1'] + $data['Proses2'];
        $data['Selesai'] = count($data['permohonanSelesai']);
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Dashboard';
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('monitoring/index', $data);
        $this->load->view('templates/footer');
    }

    public function permohonan()
    {
        $data['menu'] = $this->db->get_where('user_menu')->result_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['permohonan'] = $this->db->get_where('user_permohonan',)->result_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Status Permohonan';

            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('monitoring/permohonan-masuk', $data);
            $this->load->view('templates/footer');
    }

    public function perusahaan()
    {
        $data['menu'] = $this->db->get_where('user_menu')->result_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['perusahaan'] = $this->db->get_where('user_perusahaan',)->result_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Data Perusahaan Terdaftar';

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('monitoring/perusahaan', $data);
        $this->load->view('templates/footer');
    }
}