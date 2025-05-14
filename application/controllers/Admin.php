<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_loggedin();
        if ($this->session->userdata('role_id') != 1) { // Hanya Admin (role_id 1) yang boleh akses
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Access Denied! You are not authorized to access this page.</div>');
            redirect('auth/blocked');
        }
        $this->load->helper(array('form', 'url', 'repack_helper'));
        $this->load->library('form_validation');
        $this->load->library('upload');
        // if (!isset($this->db)) { // Cek ini biasanya tidak perlu jika autoload database sudah benar
        //     $this->load->database();
        // }
        // Sebaiknya load model jika ada query kompleks, tapi untuk contoh ini kita langsung pakai $this->db
    }

    

    public function index()
    {
        // ... (kode method index yang sudah ada) ...
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Admin Dashboard';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $data['total_users'] = $this->db->where('role_id !=', 1)->count_all_results('user'); // Hitung pengguna jasa saja
        $data['pending_permohonan'] = $this->db->where_in('status', ['0', '1', '2'])->count_all_results('user_permohonan');
        $data['pending_kuota_requests'] = $this->db->where('status', 'pending')->count_all_results('user_pengajuan_kuota');

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/index', $data);
        $this->load->view('templates/footer');
    }

    // ... (method role, roleAccess, changeaccess, permohonanMasuk, prosesSurat, dll. yang sudah ada) ...


    // --- TAMBAHKAN METHOD INI ---
    public function monitoring_kuota() // atau monitoring_kuota jika Anda prefer snake_case untuk URL
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Monitoring Kuota Perusahaan';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        // Query untuk mengambil data monitoring kuota
        // Asumsi: Anda ingin menampilkan data dari tabel 'user_perusahaan' dan 'user'
        $this->db->select('up.*, u.email as user_email, upk_approved.nomor_sk_petugas as kep_terakhir');
        $this->db->from('user_perusahaan up');
        $this->db->join('user u', 'up.id_pers = u.id', 'left'); // Bergabung dengan tabel user untuk email

        // Subquery untuk mendapatkan nomor KEP Kuota terakhir yang disetujui untuk setiap perusahaan
        // Ini adalah contoh, Anda mungkin perlu menyesuaikan berdasarkan bagaimana Anda menyimpan data nomor KEP terakhir.
        // Alternatif lain adalah membuat kolom khusus di tabel user_perusahaan untuk menyimpan KEP terakhir.
        $this->db->join('(SELECT id_pers, MAX(nomor_sk_petugas) as nomor_sk_petugas FROM user_pengajuan_kuota WHERE status = "approved" GROUP BY id_pers) upk_approved',
                        'up.id_pers = upk_approved.id_pers', 'left');

        $this->db->order_by('up.NamaPers', 'ASC');
        $data['monitoring_data'] = $this->db->get()->result_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/monitoring_kuota_view', $data); // Load view yang sudah Anda buat
        $this->load->view('templates/footer');
    }
    // --- END METHOD BARU ---


    public function role()
    {
    // ... (kode method role yang sudah ada) ...
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Role Management';
        $data['role'] = $this->db->get('user_role')->result_array();

        $this->form_validation->set_rules('role', 'Role', 'required|trim|is_unique[user_role.role]');
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
        // ... (kode method roleAccess yang sudah ada) ...
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
        // ... (kode method changeaccess yang sudah ada) ...
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
    }

    public function permohonanMasuk()
    {
        // ... (kode method permohonanMasuk yang sudah ada) ...
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Permohonan Impor'; // Disesuaikan

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
        // ... (kode method prosesSurat yang sudah ada) ...
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Penyelesaian Permohonan Impor'; // Disesuaikan
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
        $this->form_validation->set_rules('status_final', 'Status Final Permohonan', 'required|in_list[3,4]'); // 3=Selesai (Disetujui), 4=Selesai (Ditolak)

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

            // Logika Pemotongan Kuota
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
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Status permohonan telah berhasil diproses!</div>');
            redirect('admin/permohonanMasuk');
        }
    }

    public function daftar_pengajuan_kuota()
    {
        // ... (kode method daftar_pengajuan_kuota yang sudah ada) ...
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Pengajuan Kuota';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->db->select('upk.*, upr.NamaPers, u.email as user_email');
        $this->db->from('user_pengajuan_kuota upk');
        $this->db->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $this->db->join('user u', 'upk.id_pers = u.id', 'left');
        $this->db->order_by('FIELD(upk.status, "pending") DESC, upk.submission_date DESC');
        $data['pengajuan_kuota'] = $this->db->get()->result_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/daftar_pengajuan_kuota', $data);
        $this->load->view('templates/footer');
    }

    public function proses_pengajuan_kuota($id_pengajuan)
    {
        // ... (kode method proses_pengajuan_kuota yang sudah ada) ...
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
            return; // Tambahkan return setelah redirect
        }

        $this->form_validation->set_rules('status_pengajuan', 'Status Pengajuan', 'required|in_list[approved,rejected,diproses]'); // Tambahkan 'diproses'
        if ($this->input->post('status_pengajuan') == 'approved') {
            $this->form_validation->set_rules('approved_quota', 'Kuota Disetujui', 'trim|required|numeric|greater_than[0]');
            $this->form_validation->set_rules('nomor_sk_petugas', 'Nomor Surat Keputusan', 'trim|required'); // Wajib jika approved
        } else {
            $this->form_validation->set_rules('approved_quota', 'Kuota Disetujui', 'trim|numeric');
            $this->form_validation->set_rules('nomor_sk_petugas', 'Nomor Surat Keputusan', 'trim');
        }
        $this->form_validation->set_rules('admin_notes', 'Catatan Petugas', 'trim');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/proses_pengajuan_kuota_form', $data);
            $this->load->view('templates/footer');
        } else {
            $status_pengajuan = $this->input->post('status_pengajuan');
            $approved_quota = (int)$this->input->post('approved_quota');
            $admin_notes = $this->input->post('admin_notes');
            $nomor_sk_petugas = $this->input->post('nomor_sk_petugas');
            $nama_file_sk = null;

            $data_update_pengajuan = [
                'status' => $status_pengajuan,
                'admin_notes' => $admin_notes,
                'processed_date' => date('Y-m-d H:i:s'),
                'nomor_sk_petugas' => $nomor_sk_petugas
            ];

            // Proses Upload File SK jika status 'approved' atau 'rejected' (jika SK penolakan diupload)
            if (($status_pengajuan == 'approved' || $status_pengajuan == 'rejected') && isset($_FILES['file_sk_petugas']) && $_FILES['file_sk_petugas']['error'] != UPLOAD_ERR_NO_FILE) {
                $upload_dir_sk = 'uploads/sk_kuota/';
                $upload_path_sk = FCPATH . $upload_dir_sk;
                if (!is_dir($upload_path_sk)) { @mkdir($upload_path_sk, 0777, true); }
                if (!is_writable($upload_path_sk)) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload error: SK Kuota directory is not writable.</div>');
                    redirect('admin/proses_pengajuan_kuota/' . $id_pengajuan); return;
                }
                $config_sk['upload_path']   = $upload_path_sk;
                $config_sk['allowed_types'] = 'pdf|jpg|png|jpeg';
                $config_sk['max_size']      = '2048'; // 2MB
                $config_sk['encrypt_name']  = TRUE;
                $this->upload->initialize($config_sk, TRUE); // TRUE untuk reset config

                if ($this->upload->do_upload('file_sk_petugas')) {
                    // Hapus file lama jika ada
                    if (!empty($data['pengajuan']['file_sk_petugas']) && file_exists($upload_path_sk . $data['pengajuan']['file_sk_petugas'])) {
                        @unlink($upload_path_sk . $data['pengajuan']['file_sk_petugas']);
                    }
                    $nama_file_sk = $this->upload->data('file_name');
                    $data_update_pengajuan['file_sk_petugas'] = $nama_file_sk;
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">File SK Upload Error: ' . $this->upload->display_errors('', '') . '</div>');
                    redirect('admin/proses_pengajuan_kuota/' . $id_pengajuan); return;
                }
            } elseif ($status_pengajuan == 'approved' && empty($_FILES['file_sk_petugas']['name']) && empty($data['pengajuan']['file_sk_petugas']) ) {
                 // Jika status approved tapi tidak ada file SK baru atau lama, ini bisa jadi error validasi
                 $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">File Surat Keputusan wajib diupload jika status Disetujui.</div>');
                 redirect('admin/proses_pengajuan_kuota/' . $id_pengajuan); return;
            } elseif (!empty($data['pengajuan']['file_sk_petugas'])) {
                // Jika tidak ada file baru diupload, tapi sudah ada file lama, pertahankan
                $data_update_pengajuan['file_sk_petugas'] = $data['pengajuan']['file_sk_petugas'];
            }


            if ($status_pengajuan == 'approved') {
                $data_update_pengajuan['approved_quota'] = $approved_quota;
                // $perusahaan = $data['pengajuan']; // Ini sudah ada di $data['pengajuan']
                if ($data['pengajuan'] && isset($data['pengajuan']['initial_quota']) && isset($data['pengajuan']['remaining_quota'])) {
                    $new_initial_quota = (int)$data['pengajuan']['initial_quota'] + $approved_quota;
                    $new_remaining_quota = (int)$data['pengajuan']['remaining_quota'] + $approved_quota;

                    $this->db->where('id_pers', $data['pengajuan']['id_pers']);
                    $this->db->update('user_perusahaan', [
                        'initial_quota' => $new_initial_quota,
                        'remaining_quota' => $new_remaining_quota
                    ]);
                } else {
                     $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal update kuota perusahaan: data perusahaan tidak lengkap.</div>');
                     redirect('admin/daftar_pengajuan_kuota');
                     return;
                }
            } else {
                 $data_update_pengajuan['approved_quota'] = 0;
            }

            $this->db->where('id', $id_pengajuan);
            $this->db->update('user_pengajuan_kuota', $data_update_pengajuan);

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Pengajuan kuota telah berhasil diproses!</div>');
            redirect('admin/daftar_pengajuan_kuota');
        }
    }

    public function print_pengajuan_kuota($id_pengajuan)
    {
        // ... (kode method print_pengajuan_kuota yang sudah ada) ...
        $data['title'] = 'Detail Proses Pengajuan Kuota';
        $data['user_login'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->db->select('upk.*, upr.NamaPers, upr.npwp AS npwp_perusahaan, upr.alamat as alamat_perusahaan, upr.pic, upr.jabatanPic, u.email AS user_email, u.name AS user_name_pengaju, u.image AS logo_perusahaan_file');
        $this->db->from('user_pengajuan_kuota upk');
        $this->db->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $this->db->join('user u', 'upk.id_pers = u.id', 'left');
        $this->db->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $this->db->get()->row_array();

        if (!$data['pengajuan']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data pengajuan kuota tidak ditemukan.</div>');
            redirect('admin/daftar_pengajuan_kuota');
            return;
        }

        // Data untuk view cetak
        $data['user'] = $this->db->get_where('user', ['id' => $data['pengajuan']['id_pers']])->row_array(); // Data user pengaju
        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $data['pengajuan']['id_pers']])->row_array();

        $this->load->view('user/FormPengajuanKuota_print', $data); // Menggunakan view cetak yang sama dengan user
    }

    // Di dalam controllers/Admin.php

public function detailPengajuanKuotaAdmin($id_pengajuan)
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Proses Pengajuan Kuota';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->db->select('upk.*, upr.NamaPers, upr.npwp AS npwp_perusahaan, upr.alamat as alamat_perusahaan, upr.pic, upr.jabatanPic, u.email AS user_email_pemohon, u.name AS nama_pemohon');
        $this->db->from('user_pengajuan_kuota upk');
        $this->db->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $this->db->join('user u', 'upk.id_pers = u.id', 'left'); // Asumsi id_pers di user_pengajuan_kuota merujuk ke id user pemohon
        $this->db->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $this->db->get()->row_array();

        if (!$data['pengajuan']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data pengajuan kuota tidak ditemukan.</div>');
            redirect('admin/daftar_pengajuan_kuota'); // Atau ke halaman error yang sesuai
            return;
        }

        // Data perusahaan dan user pemohon bisa juga diambil terpisah jika diperlukan informasi lebih banyak
        // $data['user_pemohon_detail'] = $this->db->get_where('user', ['id' => $data['pengajuan']['id_pers']])->row_array();
        // $data['perusahaan_detail'] = $this->db->get_where('user_perusahaan', ['id_pers' => $data['pengajuan']['id_pers']])->row_array();


        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/detail_pengajuan_kuota_view', $data); // Load view baru untuk Admin
        $this->load->view('templates/footer');
    }

    public function download_sk_kuota_admin($id_pengajuan)
    {
        // ... (kode method download_sk_kuota_admin yang sudah ada) ...
        $this->load->helper('download'); // Pastikan helper download di-load
        $pengajuan = $this->db->get_where('user_pengajuan_kuota', ['id' => $id_pengajuan])->row_array();

        if ($pengajuan && !empty($pengajuan['file_sk_petugas'])) {
            $file_name = $pengajuan['file_sk_petugas'];
            $file_path = FCPATH . 'uploads/sk_kuota/' . $file_name;

            if (file_exists($file_path)) {
                force_download($file_path, NULL);
            } else {
                log_message('error', 'Admin: File SK Kuota tidak ditemukan di path: ' . $file_path . ' untuk id_pengajuan: ' . $id_pengajuan);
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">File Surat Keputusan tidak ditemukan di server.</div>');
                redirect('admin/daftar_pengajuan_kuota');
            }
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Surat Keputusan belum tersedia untuk pengajuan ini.</div>');
            redirect('admin/daftar_pengajuan_kuota');
        }
    }

    public function manajemen_user()
    {
        // ... (kode method manajemen_user yang sudah ada) ...
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Manajemen User (Petugas)';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        // Ambil daftar user dengan role petugas (misal role_id = 3 atau lainnya selain admin dan pengguna jasa)
        // Atau semua user jika admin bisa mengelola semua
        $this->db->where('role_id !=', 2); // Contoh: tidak menampilkan pengguna jasa
        $this->db->join('user_role ur', 'ur.id = user.role_id', 'left'); // Join untuk nama role
        $this->db->select('user.*, ur.role as role_name');
        $data['users'] = $this->db->get('user')->result_array();
        $data['roles'] = $this->db->get('user_role')->result_array();


        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/manajemen_user_view', $data); // Buat view ini
        $this->load->view('templates/footer');
    }

//     // Di Admin.php
// public function test_set_flash() {
//     $this->session->set_flashdata('message', '<div class="alert alert-info" role="alert">Ini adalah pesan flashdata UNTUK TES!</div>');
//     log_message('debug', 'TEST_SET_FLASH: Flashdata "message" telah di-set.');
//     redirect('admin/test_show_flash');
// }

// public function test_show_flash() {
//     $data['title'] = "Halaman Tes Flashdata";
//     $data['subtitle'] = "Menampilkan Flashdata Tes";
//     // Pastikan variabel $user dikirim ke view jika template membutuhkannya
//     $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

//     log_message('debug', 'TEST_SHOW_FLASH: Halaman dimuat. Flashdata "message" akan ditampilkan oleh topbar.');
//     // Flashdata akan ditampilkan oleh templates/topbar.php

//     // Buat file view sederhana di application/views/admin/simple_test_page.php
//     // Isi: <h1>Ini Halaman Tes Sederhana</h1>
//     $this->load->view('templates/header', $data);
//     $this->load->view('templates/sidebar', $data);
//     $this->load->view('templates/topbar', $data);
//     $this->load->view('admin/simple_test_page', $data); // Ganti dengan nama view tes Anda
//     $this->load->view('templates/footer');
// }

} // End class Admin