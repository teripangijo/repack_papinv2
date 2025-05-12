<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Pastikan user sudah login dan adalah admin (role_id = 1)
        // Helper is_loggedin() Anda mungkin sudah menangani ini.
        // Jika belum, tambahkan pengecekan role di sini.
        is_loggedin(); 
        if ($this->session->userdata('role_id') != 1) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Access Denied! You are not authorized to access this page.</div>');
            redirect('auth/blocked'); // atau redirect ke halaman login/user dashboard
        }

        $this->load->helper(array('form', 'url', 'repack_helper')); // repack_helper jika masih digunakan
        $this->load->library('form_validation');
        $this->load->library('upload'); // Jika masih ada fungsi upload di Admin.php
        // Load database jika belum di autoload
        if (!isset($this->db)) {
            $this->load->database();
        }
    }

    public function index()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Admin Dashboard';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        
        // Data untuk dashboard admin (contoh)
        $data['total_users'] = $this->db->count_all('user');
        $data['pending_permohonan'] = $this->db->where_in('status', ['0', '1', '2'])->count_all_results('user_permohonan');
        $data['pending_kuota_requests'] = $this->db->where('status', 'pending')->count_all_results('user_pengajuan_kuota');


        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data); // Pastikan sidebar admin dimuat
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/index', $data); // View untuk dashboard admin
        $this->load->view('templates/footer');
    }

    public function role()
    {
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
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Role Access Management';
        $data['role'] = $this->db->get_where('user_role', ['id' => $role_id])->row_array();
        
        // $this->db->where('id !=', 1); // Jika ada menu yang tidak boleh diakses admin (misal menu Admin itu sendiri)
        $data['menu'] = $this->db->get('user_menu')->result_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/role-access', $data);
        $this->load->view('templates/footer');
    }

    public function changeaccess()
    {
        $menu_id = $this->input->post('menuId');
        $role_id = $this->input->post('roleId');

        $data = [
            'role_id' => $role_id,
            'menu_id' => $menu_id
        ];
        $result = $this->db->get_where('user_access_menu', $data);

        if ($result->num_rows() < 1) {
            $this->db->insert('user_access_menu', $data);
        } else {
            $this->db->delete('user_access_menu', $data);
        }
        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Access Changed!</div>');
        // Tidak perlu redirect di sini, AJAX akan handle refresh atau update UI jika perlu
    }

    // --- METHOD-METHOD UNTUK PERMOHONAN IMPOR KEMBALI (DARI KODE LAMA ANDA) ---
    public function permohonanMasuk()
    {
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Permohonan Masuk';

        // Ambil semua permohonan dengan join ke user_perusahaan untuk nama perusahaan
        $this->db->select('up.*, upr.NamaPers, u.name as nama_pengaju, ptgs.Nama as nama_petugas_assigned');
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('user u', 'upr.id_pers = u.id', 'left'); // Asumsi id_pers di user_perusahaan adalah user.id
        $this->db->join('petugas ptgs', 'up.petugas = ptgs.id', 'left'); // Untuk nama petugas yang ditugaskan
        $this->db->order_by('up.time_stamp', 'DESC');
        $data['permohonan'] = $this->db->get()->result_array();
        
        // Data petugas untuk dropdown (jika ada fitur assign petugas)
        $data['list_petugas'] = $this->db->get('petugas')->result_array();


        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/permohonan-masuk', $data); // View untuk daftar permohonan admin
        $this->load->view('templates/footer');
    }
    
    // Method proses, gantipetugas, prosesLHP, editLHP, prosesSurat, cetakLHP, dll.
    // akan SAYA ASUMSIKAN ada di sini dari kode Anda sebelumnya.
    // PENTING: Modifikasi akan ada di method yang menandakan permohonan SELESAI (misal prosesSurat)

    public function prosesSurat($id_permohonan) // $id adalah id_permohonan
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Penyelesaian Permohonan & LHP';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        
        $this->db->select('up.*, upr.NamaPers, upr.remaining_quota, u.email as email_pemohon');
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('user u', 'upr.id_pers = u.id', 'left');
        $this->db->where('up.id', $id_permohonan);
        $data['permohonan'] = $this->db->get()->row_array();

        if (!$data['permohonan']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan!</div>');
            redirect('admin/permohonanMasuk');
        }

        // Ambil data LHP jika ada
        $data['lhp'] = $this->db->get_where('lhp', ['id_permohonan' => $id_permohonan])->row_array();

        // Validasi form untuk input data surat persetujuan/penolakan
        $this->form_validation->set_rules('nomorSetuju', 'Nomor Surat Keputusan', 'trim|required');
        $this->form_validation->set_rules('tgl_S', 'Tanggal Surat Keputusan', 'trim|required');
        // ... (validasi lain jika perlu, misal nomor ND, link, dll.) ...
        $this->form_validation->set_rules('status_final', 'Status Final Permohonan', 'required'); // Input baru untuk status (disetujui/ditolak)

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/proses_surat_lhp', $data); // View baru untuk form ini
            $this->load->view('templates/footer');
        } else {
            $status_final_permohonan = $this->input->post('status_final'); // Misal '3' untuk disetujui, '4' untuk ditolak

            $data_update_permohonan = [
                'nomorSetuju' => $this->input->post('nomorSetuju'),
                'tgl_S' => $this->input->post('tgl_S'),
                'nomorND' => $this->input->post('nomorND'), // Jika ada
                'tgl_ND' => $this->input->post('tgl_ND'),   // Jika ada
                'link' => $this->input->post('link'),       // Jika ada
                'linkND' => $this->input->post('linkND'),   // Jika ada
                'time_selesai' => date("Y-m-d H:i:s"),
                'status' => $status_final_permohonan 
            ];

            $this->db->where('id', $id_permohonan);
            $this->db->update('user_permohonan', $data_update_permohonan);

            // --- LOGIKA PEMOTONGAN KUOTA ---
            // Hanya potong kuota jika statusnya disetujui ('3') dan ada LHP yang valid
            if ($status_final_permohonan == '3' && $data['lhp'] && isset($data['lhp']['JumlahBenar'])) { 
                $id_pers_terkait = $data['permohonan']['id_pers'];
                $jumlah_barang_disetujui = (int)$data['lhp']['JumlahBenar']; 

                if ($jumlah_barang_disetujui > 0) {
                    $perusahaan = $this->db->get_where('user_perusahaan', ['id_pers' => $id_pers_terkait])->row_array();
                    if ($perusahaan && isset($perusahaan['remaining_quota'])) {
                        $sisa_kuota_lama = (int)$perusahaan['remaining_quota'];
                        $sisa_kuota_baru = $sisa_kuota_lama - $jumlah_barang_disetujui;

                        if ($sisa_kuota_baru < 0) {
                            $sisa_kuota_baru = 0; 
                        }
                        $this->db->where('id_pers', $id_pers_terkait);
                        $this->db->update('user_perusahaan', ['remaining_quota' => $sisa_kuota_baru]);
                        log_message('info', 'Quota updated for id_pers: ' . $id_pers_terkait . '. Used: ' . $jumlah_barang_disetujui . '. Remaining: ' . $sisa_kuota_baru);
                    }
                }
            }
            // --- AKHIR LOGIKA PEMOTONGAN KUOTA ---

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Status permohonan dan LHP telah berhasil diproses!</div>');
            redirect('admin/permohonanMasuk');
        }
    }


    // --- METHOD-METHOD BARU UNTUK MANAJEMEN PENGAJUAN KUOTA ---
    public function daftar_pengajuan_kuota()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Pengajuan Kuota';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->db->select('upk.*, upr.NamaPers, u.email as user_email');
        $this->db->from('user_pengajuan_kuota upk');
        $this->db->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $this->db->join('user u', 'upk.id_pers = u.id', 'left'); // Asumsi id_pers adalah user_id
        $this->db->order_by('upk.submission_date', 'DESC');
        $data['pengajuan_kuota'] = $this->db->get()->result_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/daftar_pengajuan_kuota', $data); // View baru
        $this->load->view('templates/footer');
    }

    public function proses_pengajuan_kuota($id_pengajuan)
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Proses Pengajuan Kuota';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->db->select('upk.*, upr.NamaPers, upr.initial_quota, upr.remaining_quota, u.email as user_email');
        $this->db->from('user_pengajuan_kuota upk');
        $this->db->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $this->db->join('user u', 'upk.id_pers = u.id', 'left');
        $this->db->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $this->db->get()->row_array();

        if (!$data['pengajuan'] || $data['pengajuan']['status'] != 'pending') {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Pengajuan kuota tidak ditemukan atau sudah diproses.</div>');
            redirect('admin/daftar_pengajuan_kuota');
        }

        $this->form_validation->set_rules('status_pengajuan', 'Status Pengajuan', 'required|in_list[approved,rejected]');
        // Hanya validasi approved_quota jika statusnya 'approved'
        if ($this->input->post('status_pengajuan') == 'approved') {
            $this->form_validation->set_rules('approved_quota', 'Kuota Disetujui', 'trim|required|numeric|greater_than[0]');
        } else {
            $this->form_validation->set_rules('approved_quota', 'Kuota Disetujui', 'trim|numeric'); 
        }
        $this->form_validation->set_rules('admin_notes', 'Catatan Admin', 'trim');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/proses_pengajuan_kuota_form', $data); // View baru
            $this->load->view('templates/footer');
        } else {
            $status_pengajuan = $this->input->post('status_pengajuan');
            $approved_quota = (int)$this->input->post('approved_quota');
            $admin_notes = $this->input->post('admin_notes');

            $data_update_pengajuan = [
                'status' => $status_pengajuan,
                'admin_notes' => $admin_notes,
                'processed_date' => date('Y-m-d H:i:s')
            ];

            if ($status_pengajuan == 'approved') {
                // Tidak perlu cek $approved_quota <= 0 karena sudah ditangani validasi
                $data_update_pengajuan['approved_quota'] = $approved_quota;

                // Update tabel user_perusahaan
                $perusahaan = $data['pengajuan']; 
                if ($perusahaan && isset($perusahaan['initial_quota']) && isset($perusahaan['remaining_quota'])) {
                    $new_initial_quota = (int)$perusahaan['initial_quota'] + $approved_quota;
                    $new_remaining_quota = (int)$perusahaan['remaining_quota'] + $approved_quota;

                    $this->db->where('id_pers', $perusahaan['id_pers']);
                    $this->db->update('user_perusahaan', [
                        'initial_quota' => $new_initial_quota,
                        'remaining_quota' => $new_remaining_quota
                    ]);
                } else {
                     $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal update kuota perusahaan: data perusahaan tidak lengkap.</div>');
                     redirect('admin/daftar_pengajuan_kuota');
                     return;
                }
            } else { // Jika 'rejected'
                 $data_update_pengajuan['approved_quota'] = 0; // Atau NULL
            }

            $this->db->where('id', $id_pengajuan);
            $this->db->update('user_pengajuan_kuota', $data_update_pengajuan);

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Pengajuan kuota telah berhasil diproses!</div>');
            redirect('admin/daftar_pengajuan_kuota');
        }
    }

    // Method-method upload lama Anda (jika masih relevan dan tidak bentrok dengan upload di User.php)
    // Contoh: proses, gantipetugas, prosesLHP, editLHP, cetakLHP, dll.
    // Anda perlu memindahkan atau mengintegrasikan logika dari method-method tersebut
    // yang berkaitan dengan pemrosesan permohonan ke dalam alur yang baru.
    // Misalnya, fungsi untuk assign petugas, rekam LHP, dll.

    // public function upload() { ... }
    // public function uploadproses($id) { ... }
    // public function prosesuploadnpwp($id) { ... }
    // public function prosesuploadskep($id) { ... }
    // public function prosesuploadttd($id) { ... }

} // End class Admin
