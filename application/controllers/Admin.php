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
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Role Management';
        $data['role'] = $this->db->get('user_role')->result_array(); // Mengambil semua role dari tabel

        $this->form_validation->set_rules('role', 'Role', 'required|trim|is_unique[user_role.role]');
        if($this->form_validation->run() == false){
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/role', $data); // View untuk menampilkan daftar role dan form tambah
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

        // Ambil semua menu utama (controllers)
        // $this->db->where('id !=', 1); // Contoh: Jangan izinkan akses ke menu Admin diubah dari sini
        $data['menu'] = $this->db->get('user_menu')->result_array(); // Ini mengambil menu utama

        // Jika Anda ingin mengatur akses per SUBMENU, query-nya akan berbeda:
        // $this->db->select('user_sub_menu.*, user_menu.menu as parent_menu_name');
        // $this->db->from('user_sub_menu');
        // $this->db->join('user_menu', 'user_sub_menu.menu_id = user_menu.id');
        // $this->db->order_by('user_menu.menu ASC, user_sub_menu.title ASC');
        // $data['all_sub_menus'] = $this->db->get()->result_array();
        // Dan view 'admin/role-access.php' perlu diubah untuk menampilkan submenu.

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/role-access', $data); // View untuk menampilkan daftar menu dan checkbox akses
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
        // $this->db->order_by('up.time_stamp', 'DESC');
        $this->db->order_by("
            CASE up.status
                WHEN '0' THEN 1  -- Prioritas 1 untuk status 'Baru Masuk'
                WHEN '1' THEN 2  -- Prioritas 2 untuk status berikutnya yang dianggap 'pending'
                WHEN '2' THEN 3  -- Prioritas 3 untuk status berikutnya yang dianggap 'pending'
                ELSE 4           -- Prioritas lebih rendah untuk status lainnya (misal: Selesai, Ditolak)
            END ASC,             -- Urutkan berdasarkan prioritas status ini secara menaik (ASC)
            up.time_stamp DESC   -- Untuk status dengan prioritas yang sama, urutkan berdasarkan waktu terbaru
        ");
        $data['permohonan'] = $this->db->get()->result_array();

        $data['list_petugas'] = $this->db->get('petugas')->result_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/permohonan-masuk', $data);
        $this->load->view('templates/footer');
    }

    // public function prosesSurat($id_permohonan)
    // {
    //     // ... (kode method prosesSurat yang sudah ada) ...
    //     $data['title'] = 'Returnable Package';
    //     $data['subtitle'] = 'Penyelesaian Permohonan Impor'; // Disesuaikan
    //     $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

    //     $this->db->select('up.*, upr.NamaPers, upr.remaining_quota, upr.npwp, u.email as email_pemohon');
    //     $this->db->from('user_permohonan up');
    //     $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
    //     $this->db->join('user u', 'upr.id_pers = u.id', 'left');
    //     $this->db->where('up.id', $id_permohonan);
    //     $data['permohonan'] = $this->db->get()->row_array();

    //     if (!$data['permohonan']) {
    //         $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan!</div>');
    //         redirect('admin/permohonanMasuk');
    //     }

    //     $data['lhp'] = $this->db->get_where('lhp', ['id_permohonan' => $id_permohonan])->row_array();

    //     $this->form_validation->set_rules('nomorSetuju', 'Nomor Surat Keputusan', 'trim|required');
    //     $this->form_validation->set_rules('tgl_S', 'Tanggal Surat Keputusan', 'trim|required');
    //     $this->form_validation->set_rules('status_final', 'Status Final Permohonan', 'required|in_list[3,4]'); // 3=Selesai (Disetujui), 4=Selesai (Ditolak)

    //     if ($this->form_validation->run() == false) {
    //         $this->load->view('templates/header', $data);
    //         $this->load->view('templates/sidebar', $data);
    //         $this->load->view('templates/topbar', $data);
    //         $this->load->view('admin/proses_surat_lhp', $data);
    //         $this->load->view('templates/footer');
    //     } else {
    //         $status_final_permohonan = $this->input->post('status_final');

    //         $data_update_permohonan = [
    //             'nomorSetuju' => $this->input->post('nomorSetuju'),
    //             'tgl_S' => $this->input->post('tgl_S'),
    //             'nomorND' => $this->input->post('nomorND'),
    //             'tgl_ND' => $this->input->post('tgl_ND'),
    //             'link' => $this->input->post('link'),
    //             'linkND' => $this->input->post('linkND'),
    //             'time_selesai' => date("Y-m-d H:i:s"),
    //             'status' => $status_final_permohonan
    //         ];

    //         $this->db->where('id', $id_permohonan);
    //         $this->db->update('user_permohonan', $data_update_permohonan);

    //         // Logika Pemotongan Kuota
    //         if ($status_final_permohonan == '3' && $data['lhp'] && isset($data['lhp']['JumlahBenar'])) {
    //             $id_pers_terkait = $data['permohonan']['id_pers'];
    //             $jumlah_barang_disetujui = (int)$data['lhp']['JumlahBenar'];

    //             if ($jumlah_barang_disetujui > 0) {
    //                 $perusahaan = $this->db->get_where('user_perusahaan', ['id_pers' => $id_pers_terkait])->row_array();
    //                 if ($perusahaan && isset($perusahaan['remaining_quota'])) {
    //                     $sisa_kuota_lama = (int)$perusahaan['remaining_quota'];
    //                     $sisa_kuota_baru = $sisa_kuota_lama - $jumlah_barang_disetujui;
    //                     if ($sisa_kuota_baru < 0) { $sisa_kuota_baru = 0; }
    //                     $this->db->where('id_pers', $id_pers_terkait);
    //                     $this->db->update('user_perusahaan', ['remaining_quota' => $sisa_kuota_baru]);
    //                     log_message('info', 'Quota updated for id_pers: ' . $id_pers_terkait . '. Used: ' . $jumlah_barang_disetujui . '. Remaining: ' . $sisa_kuota_baru);
    //                 }
    //             }
    //         }
    //         $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Status permohonan telah berhasil diproses!</div>');
    //         redirect('admin/permohonanMasuk');
    //     }
    // }

    public function penunjukanPetugas($id_permohonan)
    {
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Penunjukan Petugas Pemeriksa';

        // Ambil data permohonan yang akan diproses
        $this->db->select('up.*, upr.NamaPers');
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->where('up.id', $id_permohonan);
        $permohonan = $this->db->get()->row_array();

        if (!$permohonan) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan!</div>');
            redirect('admin/permohonanMasuk');
            return;
        }
        $data['permohonan'] = $permohonan;

        // (Langkah 1 Alur) Ketika admin klik "Proses" pertama kali, ubah status menjadi "Diproses Admin"
        // Ini hanya dilakukan jika status masih "Baru Masuk" ('0') dan ini bukan POST request (artinya baru masuk ke form)
        if ($permohonan['status'] == '0' && $this->input->method() !== 'post') {
            $this->db->where('id', $id_permohonan);
            $this->db->update('user_permohonan', ['status' => '5']); // Status '5' = Diproses Admin
            // Refresh data permohonan setelah update status
            $permohonan['status'] = '5';
            $data['permohonan']['status'] = '5';
            // Set flashdata bahwa status telah diubah ke "Diproses Admin"
            $this->session->set_flashdata('message_transient', '<div class="alert alert-info" role="alert">Status permohonan ID ' . $id_permohonan . ' telah diubah menjadi "Diproses Admin". Lanjutkan dengan menunjuk petugas.</div>');
        }


        // Ambil daftar petugas dari tabel 'petugas'
        $data['list_petugas'] = $this->db->order_by('Nama', 'ASC')->get('petugas')->result_array();
        if (empty($data['list_petugas'])) {
            // Sebaiknya ada penanganan jika tidak ada petugas tersedia
            log_message('warning', 'Tidak ada data petugas ditemukan di tabel petugas.');
        }


        // Validasi form
        $this->form_validation->set_rules('petugas_id', 'Petugas Pemeriksa', 'required|numeric');
        $this->form_validation->set_rules('nomor_surat_tugas', 'Nomor Surat Tugas', 'required|trim');
        $this->form_validation->set_rules('tanggal_surat_tugas', 'Tanggal Surat Tugas', 'required');
        // Validasi file upload (opsional, bisa dibuat wajib jika file_surat_tugas harus ada)
        // Jika file wajib, tambahkan validasi callback atau cek $_FILES secara manual.
        // Untuk contoh ini, kita asumsikan file bisa dikosongkan, tapi jika ada akan diupload.


        if ($this->form_validation->run() == false) {
            // Tampilkan form penunjukan petugas
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/form_penunjukan_petugas', $data); // Buat view baru ini
            $this->load->view('templates/footer');
        } else {
            // Proses data form
            $update_data = [
                'petugas' => $this->input->post('petugas_id'),
                'NoSuratTugas' => $this->input->post('nomor_surat_tugas'), // Sesuaikan nama kolom di DB
                'TglSuratTugas' => $this->input->post('tanggal_surat_tugas'), // Sesuaikan nama kolom di DB
                'status' => '1', // Status '1' = Penunjukan Pemeriksa
                'WaktuPenunjukanPetugas' => date('Y-m-d H:i:s') // Sesuaikan nama kolom di DB
            ];

            // Proses upload file Surat Tugas jika ada
            if (isset($_FILES['file_surat_tugas']) && $_FILES['file_surat_tugas']['error'] != UPLOAD_ERR_NO_FILE) {
                $upload_dir_st = 'uploads/surat_tugas/';
                $upload_path_st = FCPATH . $upload_dir_st;

                if (!is_dir($upload_path_st)) {
                    @mkdir($upload_path_st, 0777, true);
                }

                if (!is_writable($upload_path_st)) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload error: Direktori Surat Tugas tidak writable.</div>');
                    redirect('admin/penunjukanPetugas/' . $id_permohonan);
                    return;
                }

                $config_st['upload_path']   = $upload_path_st;
                $config_st['allowed_types'] = 'pdf|jpg|png|jpeg|doc|docx'; // Sesuaikan tipe file
                $config_st['max_size']      = '2048'; // 2MB
                $config_st['encrypt_name']  = TRUE;

                $this->load->library('upload', $config_st, 'st_upload'); // Gunakan alias 'st_upload' untuk instance upload ini
                $this->st_upload->initialize($config_st); // Re-initialize dengan config spesifik

                if ($this->st_upload->do_upload('file_surat_tugas')) {
                    // Hapus file lama jika ada (jika fitur edit penunjukan ada dan file bisa diganti)
                    // if (!empty($permohonan['FileSuratTugas']) && file_exists($upload_path_st . $permohonan['FileSuratTugas'])) {
                    //    @unlink($upload_path_st . $permohonan['FileSuratTugas']);
                    // }
                    $update_data['FileSuratTugas'] = $this->st_upload->data('file_name'); // Sesuaikan nama kolom di DB
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload File Surat Tugas Gagal: ' . $this->st_upload->display_errors('', '') . '</div>');
                    redirect('admin/penunjukanPetugas/' . $id_permohonan);
                    return;
                }
            } elseif (empty($permohonan['FileSuratTugas']) && (isset($_FILES['file_surat_tugas']) && $_FILES['file_surat_tugas']['error'] == UPLOAD_ERR_NO_FILE) ) {
                // Jika file WAJIB diupload dan tidak ada file lama maupun baru.
                // $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">File Surat Tugas wajib diupload.</div>');
                // redirect('admin/penunjukanPetugas/' . $id_permohonan);
                // return;
            }


            // Update data permohonan
            $this->db->where('id', $id_permohonan);
            $this->db->update('user_permohonan', $update_data);

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Petugas pemeriksa berhasil ditunjuk untuk permohonan ID ' . $id_permohonan . '. Status diubah menjadi "Penunjukan Pemeriksa".</div>');
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
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Manajemen User';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        // Ambil semua user kecuali admin yang sedang login (misalnya)
        // Atau tampilkan semua user jika admin bisa mengelola admin lain (hati-hati)
        $this->db->select('u.*, ur.role as role_name');
        $this->db->from('user u');
        $this->db->join('user_role ur', 'u.role_id = ur.id', 'left');
        // $this->db->where('u.id !=', $data['user']['id']); // Opsional: jangan tampilkan admin sendiri
        $this->db->order_by('u.name', 'ASC');
        $data['users_list'] = $this->db->get()->result_array(); // Ganti nama variabel agar tidak konflik dengan $data['user']

        // Ambil daftar role untuk dropdown di form tambah/edit (jika diperlukan)
        $data['roles'] = $this->db->get('user_role')->result_array();


        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/manajemen_user_view', $data); // View untuk menampilkan daftar user
        $this->load->view('templates/footer');
    }

    
public function tambah_user_petugas()
{
    $data['title'] = 'Returnable Package';
    $data['subtitle'] = 'Tambah User Petugas Baru';
    $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

    // Role untuk Petugas adalah ID 3 (sesuaikan jika berbeda)
    $role_petugas_id = 3;
    $data['role_petugas'] = $this->db->get_where('user_role', ['id' => $role_petugas_id])->row_array();
    if (!$data['role_petugas']) {
        $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Role "Petugas" tidak ditemukan. Harap konfigurasikan role terlebih dahulu.</div>');
        redirect('admin/manajemen_user');
        return;
    }


    $this->form_validation->set_rules('name', 'Nama Lengkap Petugas', 'required|trim');
    $this->form_validation->set_rules('email', 'Email Petugas', 'required|trim|valid_email|is_unique[user.email]', [
        'is_unique' => 'Email ini sudah terdaftar.'
    ]);
    $this->form_validation->set_rules('password', 'Password Awal', 'required|trim|min_length[6]');
    $this->form_validation->set_rules('nip_petugas', 'NIP Petugas', 'trim'); // Opsional, atau buat wajib jika perlu
    $this->form_validation->set_rules('jabatan_petugas', 'Jabatan Petugas', 'trim'); // Opsional

    if ($this->form_validation->run() == false) {
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/form_tambah_user_petugas', $data); // Buat view ini
        $this->load->view('templates/footer');
    } else {
        // Simpan ke tabel user
        $user_data = [
            'name' => htmlspecialchars($this->input->post('name', true)),
            'email' => htmlspecialchars($this->input->post('email', true)),
            'image' => 'default.jpg', // Default image
            'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
            'role_id' => $role_petugas_id, // Role Petugas
            'is_active' => 1, // Langsung aktif
            'force_change_password' => 1, // Petugas baru wajib ganti password saat login pertama
            'date_created' => time()
        ];
        $this->db->insert('user', $user_data);
        $new_user_id = $this->db->insert_id();

        // Simpan ke tabel petugas (jika tabel petugas terpisah dan menyimpan info NIP, Jabatan)
        // Asumsi Anda memiliki tabel `petugas` dengan kolom `id_user`, `NIP`, `Jabatan`, `Nama`
        // Jika nama sudah di tabel user, mungkin tidak perlu kolom nama lagi di tabel petugas.
        if ($new_user_id && ($this->input->post('nip_petugas') || $this->input->post('jabatan_petugas'))) {
            $petugas_data = [
                'id_user' => $new_user_id, // FK ke tabel user.id
                'Nama' => $this->input->post('name'), // Bisa ambil dari input nama user
                'NIP' => $this->input->post('nip_petugas'),
                'Jabatan' => $this->input->post('jabatan_petugas')
                // Tambahkan kolom lain yang relevan untuk tabel petugas
            ];
            // Cek apakah NIP unik jika diperlukan
            // $is_nip_unique = $this->db->get_where('petugas', ['NIP' => $petugas_data['NIP']])->num_rows();
            // if ($is_nip_unique > 0) { ... handle error NIP sudah ada ... }
            $this->db->insert('petugas', $petugas_data);
        }

        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">User petugas baru berhasil ditambahkan. Petugas wajib mengganti password saat login pertama.</div>');
        redirect('admin/manajemen_user');
    }
}


public function ganti_password_user($target_user_id = 0)
{
    if ($target_user_id == 0) {
        redirect('admin/manajemen_user');
        return;
    }

    $data['title'] = 'Returnable Package';
    $data['subtitle'] = 'Ganti Password User';
    $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array(); // Admin yang login

    $data['target_user'] = $this->db->get_where('user', ['id' => $target_user_id])->row_array();

    if (!$data['target_user'] || $data['target_user']['role_id'] == 1) { // Tidak bisa ganti password admin lain atau jika user tidak ada
        $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">User tidak ditemukan atau Anda tidak dapat mengganti password user ini.</div>');
        redirect('admin/manajemen_user');
        return;
    }

    $this->form_validation->set_rules('new_password', 'Password Baru', 'required|trim|min_length[6]');
    $this->form_validation->set_rules('confirm_password', 'Konfirmasi Password Baru', 'required|trim|matches[new_password]');

    if ($this->form_validation->run() == false) {
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/form_ganti_password_user', $data); // Buat view ini
        $this->load->view('templates/footer');
    } else {
        $new_password_hash = password_hash($this->input->post('new_password'), PASSWORD_DEFAULT);
        $update_data = [
            'password' => $new_password_hash,
            'force_change_password' => 1 // Wajibkan user ganti password setelah ini
        ];

        $this->db->where('id', $target_user_id);
        $this->db->update('user', $update_data);

        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Password untuk user ' . htmlspecialchars($data['target_user']['name']) . ' berhasil diubah. User tersebut wajib mengganti passwordnya saat login berikutnya.</div>');
        redirect('admin/manajemen_user');
    }
}

// Di controllers/Admin.php

public function edit_user($target_user_id = 0)
    {
        if ($target_user_id == 0 || !is_numeric($target_user_id)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">User ID tidak valid.</div>');
            redirect('admin/manajemen_user');
            return;
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Data User';
        $admin_logged_in = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['user'] = $admin_logged_in; 

        $data['target_user_data'] = $this->db->get_where('user', ['id' => $target_user_id])->row_array();

        if (!$data['target_user_data']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">User yang akan diedit tidak ditemukan (ID: '.htmlspecialchars($target_user_id).').</div>');
            redirect('admin/manajemen_user');
            return;
        }

        $is_editing_main_admin = ($data['target_user_data']['id'] == 1); 
        // Asumsi Role Petugas = 3, Monitoring = 4. Pengguna Jasa = 2.
        $is_target_petugas_or_monitoring = in_array($data['target_user_data']['role_id'], [3, 4]);

        $data['roles_list'] = $this->db->get('user_role')->result_array();

        // Validasi Form
        $this->form_validation->set_rules('name', 'Nama Lengkap', 'required|trim');

        $original_login_identifier = $data['target_user_data']['email']; // Kolom 'email' sekarang bisa berisi email atau NIP
        $input_login_identifier = $this->input->post('login_identifier'); // Nama field baru di form

        if ($is_target_petugas_or_monitoring) {
            // Untuk Petugas/Monitoring, validasi sebagai NIP (misal: numerik, panjang tertentu, unik)
            // Kolom di DB tetap 'email', tapi isinya NIP
            $this->form_validation->set_rules('login_identifier', 'NIP', 'required|trim|numeric'); // Tambahkan is_unique jika NIP harus unik di tabel user
            if ($input_login_identifier != $original_login_identifier) {
                 $this->form_validation->add_rule('is_unique[user.email]'); // Tetap cek ke kolom 'email'
            }
        } else {
            // Untuk role lain (Admin, Pengguna Jasa), validasi sebagai Email
            $this->form_validation->set_rules('login_identifier', 'Email', 'required|trim|valid_email');
            if ($input_login_identifier != $original_login_identifier) {
                 $this->form_validation->add_rule('is_unique[user.email]');
            }
        }


        if (!$is_editing_main_admin) {
            $this->form_validation->set_rules('role_id', 'Role', 'required|numeric');
            $this->form_validation->set_rules('is_active', 'Status Aktif', 'required|in_list[0,1]');
        }
        
        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/form_edit_user', $data); 
            $this->load->view('templates/footer');
        } else {
            $update_data_user = [
                'name' => htmlspecialchars($this->input->post('name', true)),
                'email' => htmlspecialchars($input_login_identifier, true), // Simpan NIP atau Email ke kolom 'email'
            ];

            if (!$is_editing_main_admin) {
                $update_data_user['role_id'] = $this->input->post('role_id');
                $update_data_user['is_active'] = $this->input->post('is_active');
            } else {
                $update_data_user['role_id'] = $data['target_user_data']['role_id']; 
                $update_data_user['is_active'] = $data['target_user_data']['is_active']; 
            }

            $this->db->where('id', $target_user_id);
            $this->db->update('user', $update_data_user);

            $new_role_id = (int)($this->input->post('role_id') ?? $data['target_user_data']['role_id']);

            // Jika target adalah Petugas (role_id 3) atau diubah menjadi Petugas
            if ($new_role_id == 3) {
                $petugas_detail = $this->db->get_where('petugas', ['id_user' => $target_user_id])->row_array();
                $data_petugas_update = [
                    'Nama' => $update_data_user['name'],
                    // NIP untuk Petugas sekarang diambil dari 'login_identifier' (yang disimpan di user.email)
                    'NIP' => ($is_target_petugas_or_monitoring || $new_role_id == 3) ? $update_data_user['email'] : $this->input->post('nip_petugas_edit'), 
                    'Jabatan' => $this->input->post('jabatan_petugas_edit') 
                ];

                if ($petugas_detail) { 
                    $this->db->where('id_user', $target_user_id);
                    $this->db->update('petugas', $data_petugas_update);
                } else { 
                    $data_petugas_update['id_user'] = $target_user_id;
                    $this->db->insert('petugas', $data_petugas_update);
                }
            }
            // Anda bisa menambahkan logika jika role diubah DARI Petugas (misal, menghapus entri di tabel petugas)
            // atau jika role adalah Monitoring dan perlu update tabel terpisah (jika ada)

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Data user '.htmlspecialchars($update_data_user['name']).' berhasil diupdate.</div>');
            redirect('admin/manajemen_user');
        }
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