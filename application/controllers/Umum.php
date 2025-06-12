<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Umum extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_loggedin();
        $this->load->helper('repack_helper');
    }

    public function index()
    {
        $data['menu'] = $this->db->get_where('user_menu')->result_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['permohonan'] = $this->db->get_where('user_permohonan',)->result_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Status Permohonan';

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('umum/permohonan-masuk', $data);
        $this->load->view('templates/footer');
    }

    public function printPdf($id)
    {
        $user = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $permohonan = $this->db->get_where('user_permohonan', ['id' => $id])->row_array();
        $user_perusahaan = $this->db->get_where('user_perusahaan', ['id_pers' => $permohonan['id_pers']])->row_array();
        $data = array(
            'user' => $user,
            'permohonan' => $permohonan,
            'user_perusahaan' => $user_perusahaan,
        );

        $this->load->view('user/FormPermohonan', $data);
    }
}