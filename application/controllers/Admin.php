<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_loggedin(); // Helper untuk otentikasi
        if ($this->session->userdata('role_id') != 1) { // Hanya Admin (role_id 1) yang boleh akses
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Access Denied! You are not authorized to access this page.</div>');
            redirect('auth/blocked');
            exit; 
        }
        $this->load->helper(array('form', 'url', 'repack_helper', 'download')); // Tambahkan helper download
        $this->load->library('form_validation');
        $this->load->library('upload');
        $this->load->library('session'); // Pastikan session di-load
        if (!isset($this->db)) {
            $this->load->database();
        }
    }

    public function index()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Admin Dashboard';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        
        $data['total_users'] = $this->db->where_in('role_id', [2,3,4])->count_all_results('user'); // Pengguna Jasa, Petugas, Monitoring
        $data['pending_permohonan'] = $this->db->where_in('status', ['0', '1', '2', '5'])->count_all_results('user_permohonan');
        $data['pending_kuota_requests'] = $this->db->where('status', 'pending')->count_all_results('user_pengajuan_kuota');

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/index', $data);
        $this->load->view('templates/footer');
    }

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


    public function role()
    {
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
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Role Access Management';
        $data['role'] = $this->db->get_where('user_role', ['id' => $role_id])->row_array();
        
        if(!$data['role']){
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Role tidak ditemukan.</div>');
            redirect('admin/role');
        }
        
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
        // Tidak perlu redirect di sini, biarkan AJAX yang handle respons jika perlu
    }

    public function permohonanMasuk()
    {
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Permohonan Impor';

        $this->db->select(
            'up.id, up.nomorSurat, up.TglSurat, up.time_stamp, up.status, ' .
            'upr.NamaPers, ' .
            'u_pemohon.name as nama_pengaju, ' .
            'u_real_petugas.name as nama_petugas_assigned' // Ambil nama petugas dari tabel user
        );
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $this->db->join('petugas p_assigned', 'up.petugas = p_assigned.id', 'left'); // up.petugas = petugas.id
        $this->db->join('user u_real_petugas', 'p_assigned.id_user = u_real_petugas.id', 'left'); // petugas.id_user = user.id
        
        $this->db->order_by("
            CASE up.status
                WHEN '0' THEN 1 -- Baru Masuk
                WHEN '5' THEN 2 -- Diproses Admin (sebelum tunjuk petugas)
                WHEN '1' THEN 3 -- Penunjukan Pemeriksa
                WHEN '2' THEN 4 -- LHP Direkam
                ELSE 5          -- Selesai (Disetujui/Ditolak) atau status lain
            END ASC, up.time_stamp DESC");
        $data['permohonan'] = $this->db->get()->result_array();
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/permohonan-masuk', $data); 
        $this->load->view('templates/footer');
    }

    public function prosesSurat($id_permohonan = 0)
    {
        // Validasi awal untuk ID permohonan
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid atau tidak disertakan.</div>');
            redirect('admin/permohonanMasuk');
            return;
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Penyelesaian Permohonan Impor';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        // Ambil data permohonan yang akan diproses beserta data perusahaan terkait
        $this->db->select('up.*, upr.NamaPers, upr.remaining_quota as sisa_kuota_perusahaan_saat_ini, upr.initial_quota as initial_kuota_perusahaan, upr.npwp, u_pemohon.email as email_pemohon');
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left'); // User yang mengajukan
        $this->db->where('up.id', $id_permohonan);
        $data['permohonan'] = $this->db->get()->row_array();

        if (!$data['permohonan']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan dengan ID ' . htmlspecialchars($id_permohonan) . ' tidak ditemukan!</div>');
            redirect('admin/permohonanMasuk');
            return;
        }

        // Pastikan LHP sudah direkam, status permohonan '2', dan data LHP inti (NoLHP, TglLHP) ada
        $data['lhp'] = $this->db->get_where('lhp', ['id_permohonan' => $id_permohonan])->row_array();
        if (!$data['lhp'] || $data['permohonan']['status'] != '2') {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">LHP untuk permohonan ID ' . htmlspecialchars($id_permohonan) . ' belum direkam atau status permohonan tidak sesuai untuk penyelesaian (Status saat ini: ' . htmlspecialchars($data['permohonan']['status']) . ').</div>');
            redirect('admin/permohonanMasuk');
            return;
        }
        if (empty($data['lhp']['NoLHP']) || empty($data['lhp']['TglLHP'])) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data Nomor LHP atau Tanggal LHP dari petugas tidak lengkap di database LHP untuk permohonan ID ' . htmlspecialchars($id_permohonan) . '. Tidak dapat melanjutkan.</div>');
            redirect('admin/permohonanMasuk'); // Atau ke detail permohonan jika ada halaman itu
            return;
        }
        log_message('debug', 'ADMIN PROSES SURAT - Data LHP yang diambil: ' . print_r($data['lhp'], true));


        // Aturan Validasi Form
        // Nomor LHP dan Tanggal LHP tidak lagi divalidasi di sini karena diambil dari data LHP yang sudah ada.
        $this->form_validation->set_rules('status_final', 'Status Final Permohonan', 'required|in_list[3,4]');
        $this->form_validation->set_rules('nomorND', 'Nomor Nota Dinas (Opsional)', 'trim');
        $this->form_validation->set_rules('tgl_ND', 'Tanggal Nota Dinas (Opsional)', 'trim');
        // Menggunakan callback untuk validasi URL yang mengizinkan kosong
        $this->form_validation->set_rules('link', 'Link Surat Keputusan (Opsional)', 'trim|callback__valid_url_format_check');
        $this->form_validation->set_rules('linkND', 'Link Nota Dinas (Opsional)', 'trim|callback__valid_url_format_check');


        if ($this->form_validation->run() == false) {
            log_message('debug', 'ADMIN PROSES SURAT - Validasi Gagal. Errors: ' . validation_errors() . ' POST Data: ' . print_r($this->input->post(), true));
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/proses_surat_lhp', $data);
            $this->load->view('templates/footer');
        } else {
            log_message('debug', 'ADMIN PROSES SURAT - Validasi Sukses. Memproses...');
            $status_final_permohonan = $this->input->post('status_final');

            // Ambil NoLHP dan TglLHP dari data LHP yang sudah di-load dari database
            $nomor_lhp_dari_petugas = $data['lhp']['NoLHP'];
            $tanggal_lhp_dari_petugas = $data['lhp']['TglLHP'];

            $data_update_permohonan = [
                'nomorSetuju'   => $nomor_lhp_dari_petugas,    // Diambil dari LHP
                'tgl_S'         => $tanggal_lhp_dari_petugas,   // Diambil dari LHP
                'nomorND'       => $this->input->post('nomorND'),
                'tgl_ND'        => $this->input->post('tgl_ND'),
                'link'          => $this->input->post('link'),
                'linkND'        => $this->input->post('linkND'),
                'time_selesai'  => date("Y-m-d H:i:s"),
                'status'        => $status_final_permohonan
            ];

            $this->db->where('id', $id_permohonan);
            $this->db->update('user_permohonan', $data_update_permohonan);
            log_message('debug', 'ADMIN PROSES SURAT - Data permohonan diupdate: ' . print_r($data_update_permohonan, true));

            // Logika Pemotongan Kuota jika permohonan DIS الموافق (status '3') dan LHP ada
            if ($status_final_permohonan == '3' && isset($data['lhp']['JumlahBenar'])) { // Pastikan JumlahBenar ada
                $id_pers_terkait = $data['permohonan']['id_pers'];
                $jumlah_barang_digunakan_lhp = (int)$data['lhp']['JumlahBenar'];

                if ($jumlah_barang_digunakan_lhp > 0) {
                    $perusahaan_db = $this->db->get_where('user_perusahaan', ['id_pers' => $id_pers_terkait])->row_array();
                    if ($perusahaan_db && isset($perusahaan_db['remaining_quota'])) {
                        $sisa_kuota_sebelum_pengurangan = (int)$perusahaan_db['remaining_quota'];
                        $sisa_kuota_baru = $sisa_kuota_sebelum_pengurangan - $jumlah_barang_digunakan_lhp;

                        if ($sisa_kuota_baru < 0) {
                            $sisa_kuota_baru = 0;
                            log_message('warning', 'Pemotongan kuota untuk id_pers: ' . $id_pers_terkait . ' melebihi sisa kuota. Digunakan: ' . $jumlah_barang_digunakan_lhp . ', Sisa sebelumnya: ' . $sisa_kuota_sebelum_pengurangan . '. Sisa direset ke 0.');
                        }

                        $this->db->where('id_pers', $id_pers_terkait);
                        $this->db->update('user_perusahaan', ['remaining_quota' => $sisa_kuota_baru]);

                        $this->_log_perubahan_kuota(
                            $id_pers_terkait,
                            'pengurangan',
                            $jumlah_barang_digunakan_lhp,
                            $sisa_kuota_sebelum_pengurangan,
                            $sisa_kuota_baru,
                            'Penggunaan Kuota untuk Permohonan Impor No. Aju: ' . ($data['permohonan']['nomorSurat'] ?? $id_permohonan),
                            $id_permohonan,
                            'permohonan_impor',
                            $data['user']['id'] // ID admin yang login sebagai pencatat
                        );
                        log_message('info', 'Quota updated (dikurangi) untuk id_pers: ' . $id_pers_terkait . '. Jumlah dari LHP: ' . $jumlah_barang_digunakan_lhp . '. Sisa kuota baru: ' . $sisa_kuota_baru);
                    } else {
                        log_message('error', 'ADMIN PROSES SURAT - Gagal mengambil data perusahaan atau remaining_quota untuk pemotongan. id_pers: ' . $id_pers_terkait);
                    }
                }
            }

            $pesan_status_akhir = ($status_final_permohonan == '3') ? 'Disetujui' : 'Ditolak';
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Status permohonan ID '.htmlspecialchars($id_permohonan).' telah berhasil diproses menjadi "'. $pesan_status_akhir .'"!</div>');
            redirect('admin/permohonanMasuk');
        }
    }

    public function _valid_url_format_check($str)
    {
        if (empty($str)) { // Izinkan kosong karena field opsional
            return TRUE;
        }
        // Menggunakan filter_var untuk validasi URL yang lebih robust
        if (filter_var($str, FILTER_VALIDATE_URL)) {
            // Tambahan: Anda bisa memeriksa apakah URL menggunakan http atau https jika diperlukan
            // if (preg_match("/^https?:\/\//i", $str)) {
            //    return TRUE;
            // } else {
            //    $this->form_validation->set_message('_valid_url_format_check', '{field} harus dimulai dengan http:// atau https://.');
            //    return FALSE;
            // }
            return TRUE;
        } else {
            $this->form_validation->set_message('_valid_url_format_check', '{field} harus berisi URL yang valid (contoh: http://example.com).');
            return FALSE;
        }
    }

    private function _log_perubahan_kuota(
        $id_pers_param,
        $jenis_transaksi_param,
        $jumlah_param,
        $kuota_sebelum_param,
        $kuota_sesudah_param,
        $keterangan_param,
        $id_referensi_param = null,
        $tipe_referensi_param = null,
        $dicatat_oleh_user_id_param = null
    ) {
        log_message('debug', 'LOG KUOTA METHOD - Dipanggil dengan: id_pers=' . $id_pers_param . ', jenis=' . $jenis_transaksi_param . ', jumlah=' . $jumlah_param . ', sebelum=' . $kuota_sebelum_param . ', sesudah=' . $kuota_sesudah_param . ', ref_id=' . $id_referensi_param . ', user_pencatat=' . $dicatat_oleh_user_id_param);

        if ($dicatat_oleh_user_id_param === null && $this->session->userdata('email')) {
            $admin_user = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
            if ($admin_user) {
                $dicatat_oleh_user_id_param = $admin_user['id'];
                log_message('debug', 'LOG KUOTA METHOD - User ID pencatat diambil dari session: ' . $dicatat_oleh_user_id_param);
            } else {
                log_message('warning', 'LOG KUOTA METHOD - Tidak dapat mengambil user ID pencatat dari session email.');
            }
        }

        $log_data = [
            'id_pers'                 => $id_pers_param,
            'jenis_transaksi'         => $jenis_transaksi_param,
            'jumlah_perubahan'        => $jumlah_param,
            'sisa_kuota_sebelum'      => $kuota_sebelum_param,
            'sisa_kuota_setelah'      => $kuota_sesudah_param,
            'keterangan'              => $keterangan_param,
            'id_referensi_transaksi'  => $id_referensi_param,
            'tipe_referensi'          => $tipe_referensi_param,
            'dicatat_oleh_user_id'    => $dicatat_oleh_user_id_param,
            'tanggal_transaksi'       => date('Y-m-d H:i:s')
        ];
        log_message('debug', 'LOG KUOTA METHOD - Data yang akan diinsert: ' . print_r($log_data, true));

        $inserted = $this->db->insert('log_kuota_perusahaan', $log_data);

        if ($inserted) {
            log_message('info', 'LOG KUOTA METHOD - Insert BERHASIL. id_pers: ' . $id_pers_param . '. Jenis: ' . $jenis_transaksi_param . '. Insert ID: ' . $this->db->insert_id());
        } else {
            $db_error = $this->db->error();
            log_message('error', 'LOG KUOTA METHOD - Insert GAGAL. DB Code: ' . $db_error['code'] . ' Message: ' . $db_error['message'] . '. Data yang dicoba: ' . print_r($log_data, true));
        }
    }

    public function penunjukanPetugas($id_permohonan)
    {
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Penunjukan Petugas Pemeriksa';

        // Ambil data permohonan yang akan diproses
        $this->db->select('up.*, upr.NamaPers'); // Ambil juga kolom 'petugas', 'NoSuratTugas', 'TglSuratTugas', 'FileSuratTugas' untuk pre-fill form jika diedit
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->where('up.id', $id_permohonan);
        $permohonan = $this->db->get()->row_array();

        if (!$permohonan) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan!</div>');
            redirect('admin/permohonanMasuk');
            return;
        }
        $data['permohonan'] = $permohonan; // Data permohonan ini akan digunakan untuk pre-fill form

        if ($permohonan['status'] == '0' && $this->input->method() !== 'post') {
            $this->db->where('id', $id_permohonan);
            $this->db->update('user_permohonan', ['status' => '5']);
            $permohonan['status'] = '5'; // Update variabel lokal juga
            $data['permohonan']['status'] = '5'; // Update data yang dikirim ke view
            $this->session->set_flashdata('message_transient', '<div class="alert alert-info" role="alert">Status permohonan ID ' . htmlspecialchars($id_permohonan) . ' telah diubah menjadi "Diproses Admin". Lanjutkan dengan menunjuk petugas.</div>');
        }

        // Ambil daftar petugas dari tabel 'petugas'
        // Pastikan tabel 'petugas' memiliki kolom 'id' (PK), 'Nama', 'NIP'
        $data['list_petugas'] = $this->db->order_by('Nama', 'ASC')->get('petugas')->result_array();
        if (empty($data['list_petugas'])) {
            log_message('warning', 'Tidak ada data petugas ditemukan di tabel petugas.');
            // Anda bisa menambahkan flashdata error di sini jika tidak ada petugas sama sekali
            // $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Data petugas tidak tersedia. Tidak dapat melakukan penunjukan.</div>');
        }

        // Validasi form
        $this->form_validation->set_rules('petugas_id', 'Petugas Pemeriksa', 'required|numeric');
        $this->form_validation->set_rules('nomor_surat_tugas', 'Nomor Surat Tugas', 'required|trim');
        $this->form_validation->set_rules('tanggal_surat_tugas', 'Tanggal Surat Tugas', 'required');
        // Validasi file upload bisa ditambahkan jika file wajib, misal dengan callback
        // if (empty($_FILES['file_surat_tugas']['name']) && empty($permohonan['FileSuratTugas'])) {
        //     $this->form_validation->set_rules('file_surat_tugas', 'File Surat Tugas', 'required');
        // }


        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/form_penunjukan_petugas', $data);
            $this->load->view('templates/footer');
        } else {
            // Proses data form
            $update_data = [
                // PERBAIKAN DI SINI:
                'petugas' => $this->input->post('petugas_id'), // Ambil langsung dari 'petugas_id'
                'NoSuratTugas' => $this->input->post('nomor_surat_tugas'),
                'TglSuratTugas' => $this->input->post('tanggal_surat_tugas'),
                'status' => '1', // Status '1' = Penunjukan Pemeriksa
                'WaktuPenunjukanPetugas' => date('Y-m-d H:i:s')
            ];

            $nama_file_surat_tugas = $permohonan['FileSuratTugas'] ?? null; // Pertahankan file lama jika tidak ada upload baru

            if (isset($_FILES['file_surat_tugas']) && $_FILES['file_surat_tugas']['error'] != UPLOAD_ERR_NO_FILE) {
                $upload_dir_st = './uploads/surat_tugas/'; // Gunakan path relatif dari index.php atau FCPATH
                if (!is_dir($upload_dir_st)) {
                    if (!@mkdir($upload_dir_st, 0777, true)) {
                        $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal membuat direktori upload Surat Tugas.</div>');
                        redirect('admin/penunjukanPetugas/' . $id_permohonan);
                        return;
                    }
                }

                if (!is_writable($upload_dir_st)) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload error: Direktori Surat Tugas tidak writable. Path: '.$upload_dir_st.'</div>');
                    redirect('admin/penunjukanPetugas/' . $id_permohonan);
                    return;
                }

                $config_st['upload_path']   = $upload_dir_st;
                $config_st['allowed_types'] = 'pdf|jpg|png|jpeg|doc|docx';
                $config_st['max_size']      = '2048'; // 2MB
                $config_st['encrypt_name']  = TRUE;

                // Penting untuk me-load library upload di sini jika belum di __construct atau hanya untuk instance ini
                $this->load->library('upload', $config_st, 'st_upload'); // Menggunakan alias 'st_upload'
                $this->st_upload->initialize($config_st); // Re-initialize dengan config spesifik

                if ($this->st_upload->do_upload('file_surat_tugas')) {
                    // Hapus file lama jika ada dan file baru berhasil diupload
                    if (!empty($permohonan['FileSuratTugas']) && file_exists($upload_dir_st . $permohonan['FileSuratTugas'])) {
                       @unlink($upload_dir_st . $permohonan['FileSuratTugas']);
                    }
                    $nama_file_surat_tugas = $this->st_upload->data('file_name');
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload File Surat Tugas Gagal: ' . $this->st_upload->display_errors('', '') . '</div>');
                    redirect('admin/penunjukanPetugas/' . $id_permohonan);
                    return;
                }
            }
            $update_data['FileSuratTugas'] = $nama_file_surat_tugas;


            // Update data permohonan
            $this->db->where('id', $id_permohonan);
            $this->db->update('user_permohonan', $update_data);

            // Logging untuk verifikasi
            $updated_permohonan = $this->db->get_where('user_permohonan', ['id' => $id_permohonan])->row_array();
            log_message('debug', 'PENUNJUKAN PETUGAS - Data Permohonan Setelah Update: ' . print_r($updated_permohonan, true));
            log_message('debug', 'PENUNJUKAN PETUGAS - Nilai petugas_id yang di-POST: ' . $this->input->post('petugas_id'));


            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Petugas pemeriksa berhasil ditunjuk untuk permohonan ID ' . htmlspecialchars($id_permohonan) . '. Status diubah menjadi "Penunjukan Pemeriksa".</div>');
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
        log_message('debug', 'PROSES PENGAJUAN KUOTA - Method dipanggil untuk id_pengajuan: ' . $id_pengajuan);

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Proses Pengajuan Kuota';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->db->select('upk.*, upr.NamaPers, upr.initial_quota as initial_quota_sebelum, upr.remaining_quota as remaining_quota_sebelum, u.email as user_email');
        $this->db->from('user_pengajuan_kuota upk');
        $this->db->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $this->db->join('user u', 'upk.id_pers = u.id', 'left');
        $this->db->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $this->db->get()->row_array();
        log_message('debug', 'PROSES PENGAJUAN KUOTA - Data pengajuan yang diambil: ' . print_r($data['pengajuan'], true));


        if (!$data['pengajuan'] || ($data['pengajuan']['status'] != 'pending' && $data['pengajuan']['status'] != 'diproses')) {
            $pesan_error_awal = 'Pengajuan kuota tidak ditemukan atau statusnya tidak memungkinkan untuk diproses (Status saat ini: ' . ($data['pengajuan']['status'] ?? 'Tidak Diketahui') . '). Hanya status "pending" atau "diproses" yang bisa dilanjutkan.';
            log_message('error', 'PROSES PENGAJUAN KUOTA - Validasi awal gagal: ' . $pesan_error_awal);
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">' . $pesan_error_awal . '</div>');
            redirect('admin/daftar_pengajuan_kuota');
            return;
        }

        $this->form_validation->set_rules('status_pengajuan', 'Status Pengajuan', 'required|in_list[approved,rejected,diproses]');
        if ($this->input->post('status_pengajuan') == 'approved') {
            $this->form_validation->set_rules('approved_quota', 'Kuota Disetujui', 'trim|required|numeric|greater_than[0]');
            $this->form_validation->set_rules('nomor_sk_petugas', 'Nomor Surat Keputusan', 'trim|required');
        } else {
            $this->form_validation->set_rules('approved_quota', 'Kuota Disetujui', 'trim|numeric');
            $this->form_validation->set_rules('nomor_sk_petugas', 'Nomor Surat Keputusan', 'trim');
        }
        $this->form_validation->set_rules('admin_notes', 'Catatan Petugas', 'trim');

        if ($this->form_validation->run() == false) {
            log_message('debug', 'PROSES PENGAJUAN KUOTA - Validasi Form Gagal. Errors: ' . validation_errors() . ' POST Data: ' . print_r($this->input->post(), true));
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/proses_pengajuan_kuota_form', $data);
            $this->load->view('templates/footer');
        } else {
            log_message('debug', 'PROSES PENGAJUAN KUOTA - Validasi Form Sukses. Memproses data...');
            $status_pengajuan = $this->input->post('status_pengajuan');
            $approved_quota = ($status_pengajuan == 'approved') ? (int)$this->input->post('approved_quota') : 0;
            $admin_notes = $this->input->post('admin_notes');
            $nomor_sk_petugas = $this->input->post('nomor_sk_petugas');

            log_message('debug', 'PROSES PENGAJUAN KUOTA - Input: status=' . $status_pengajuan . ', approved_quota=' . $approved_quota . ', nomor_sk=' . $nomor_sk_petugas);


            $data_update_pengajuan = [
                'status' => $status_pengajuan,
                'admin_notes' => $admin_notes,
                'processed_date' => date('Y-m-d H:i:s'),
                'nomor_sk_petugas' => $nomor_sk_petugas,
                'approved_quota' => $approved_quota
            ];

            // Proses Upload File SK
            $nama_file_sk = $data['pengajuan']['file_sk_petugas'] ?? null;
            if (($status_pengajuan == 'approved' || $status_pengajuan == 'rejected') && isset($_FILES['file_sk_petugas']) && $_FILES['file_sk_petugas']['error'] != UPLOAD_ERR_NO_FILE) {
                // ... (logika upload file SK seperti sebelumnya, tambahkan logging jika perlu) ...
                // Contoh: log_message('debug', 'PROSES PENGAJUAN KUOTA - Mencoba upload file SK...');
                $upload_dir_sk = './uploads/sk_kuota/';
                if (!is_dir($upload_dir_sk)) { @mkdir($upload_dir_sk, 0777, true); }

                if (!is_writable($upload_dir_sk)) {
                    log_message('error', 'PROSES PENGAJUAN KUOTA - Direktori SK Kuota tidak writable: ' . $upload_dir_sk);
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload error: Direktori SK Kuota tidak writable.</div>');
                    redirect('admin/proses_pengajuan_kuota/' . $id_pengajuan); return;
                }
                $config_sk['upload_path']   = $upload_dir_sk;
                $config_sk['allowed_types'] = 'pdf|jpg|png|jpeg';
                $config_sk['max_size']      = '2048';
                $config_sk['encrypt_name']  = TRUE;
                $this->load->library('upload', $config_sk, 'sk_upload');
                $this->sk_upload->initialize($config_sk);

                if ($this->sk_upload->do_upload('file_sk_petugas')) {
                    if (!empty($data['pengajuan']['file_sk_petugas']) && file_exists($upload_dir_sk . $data['pengajuan']['file_sk_petugas'])) {
                        @unlink($upload_dir_sk . $data['pengajuan']['file_sk_petugas']);
                    }
                    $nama_file_sk = $this->sk_upload->data('file_name');
                    log_message('info', 'PROSES PENGAJUAN KUOTA - File SK berhasil diupload: ' . $nama_file_sk);
                } else {
                    $upload_error_sk = $this->sk_upload->display_errors('', '');
                    log_message('error', 'PROSES PENGAJUAN KUOTA - File SK Upload Error: ' . $upload_error_sk);
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">File SK Upload Error: ' . $upload_error_sk . '</div>');
                    redirect('admin/proses_pengajuan_kuota/' . $id_pengajuan); return;
                }
            } elseif ($status_pengajuan == 'approved' && empty($_FILES['file_sk_petugas']['name']) && empty($nama_file_sk) ) {
                 log_message('error', 'PROSES PENGAJUAN KUOTA - File SK wajib diupload jika status Disetujui, tapi tidak ada file.');
                 $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">File Surat Keputusan wajib diupload jika status Disetujui.</div>');
                 redirect('admin/proses_pengajuan_kuota/' . $id_pengajuan); return;
            }
            $data_update_pengajuan['file_sk_petugas'] = $nama_file_sk;

            // Update tabel user_pengajuan_kuota
            $this->db->where('id', $id_pengajuan);
            $this->db->update('user_pengajuan_kuota', $data_update_pengajuan);
            log_message('debug', 'PROSES PENGAJUAN KUOTA - Data user_pengajuan_kuota diupdate: ' . print_r($data_update_pengajuan, true) . '. Affected rows: ' . $this->db->affected_rows());

            // Logika Penambahan Kuota dan Pencatatan Log
            if ($status_pengajuan == 'approved' && $approved_quota > 0) {
                log_message('debug', 'PROSES PENGAJUAN KUOTA - Memulai proses penambahan kuota dan logging.');
                $id_pers_terkait = $data['pengajuan']['id_pers'];
                // Ambil nilai kuota SEBELUM dari $data['pengajuan'] yang di-load di awal method
                $initial_quota_sblm = (int)($data['pengajuan']['initial_quota_sebelum'] ?? 0);
                $remaining_quota_sblm = (int)($data['pengajuan']['remaining_quota_sebelum'] ?? 0);

                log_message('debug', 'PROSES PENGAJUAN KUOTA - id_pers_terkait: ' . $id_pers_terkait . ', initial_sblm: ' . $initial_quota_sblm . ', remaining_sblm: ' . $remaining_quota_sblm);

                if ($id_pers_terkait) {
                    $new_initial_quota = $initial_quota_sblm + $approved_quota;
                    $new_remaining_quota = $remaining_quota_sblm + $approved_quota;

                    log_message('debug', 'PROSES PENGAJUAN KUOTA - Kalkulasi kuota baru: initial_baru=' . $new_initial_quota . ', remaining_baru=' . $new_remaining_quota);

                    $this->db->where('id_pers', $id_pers_terkait);
                    $this->db->update('user_perusahaan', [
                        'initial_quota' => $new_initial_quota,
                        'remaining_quota' => $new_remaining_quota
                    ]);
                    $affected_rows_user_perusahaan = $this->db->affected_rows();
                    log_message('info', 'PROSES PENGAJUAN KUOTA - Kuota perusahaan diupdate untuk id_pers: ' . $id_pers_terkait . '. Affected rows: ' . $affected_rows_user_perusahaan);

                    if ($affected_rows_user_perusahaan > 0) {
                        log_message('debug', 'PROSES PENGAJUAN KUOTA - SEBELUM PANGGIL _log_perubahan_kuota. Data untuk log: id_pers=' . $id_pers_terkait . ', approved_quota=' . $approved_quota . ', sisa_sebelum=' . $remaining_quota_sblm . ', sisa_sesudah=' . $new_remaining_quota);
                        $this->_log_perubahan_kuota(
                            $id_pers_terkait,
                            'penambahan',
                            $approved_quota,
                            $remaining_quota_sblm,
                            $new_remaining_quota,
                            'Persetujuan Pengajuan Kuota. No. SK: ' . ($nomor_sk_petugas ?: '-'),
                            $id_pengajuan,
                            'pengajuan_kuota',
                            $data['user']['id']
                        );
                        log_message('debug', 'PROSES PENGAJUAN KUOTA - SETELAH PANGGIL _log_perubahan_kuota.');
                    } else {
                        log_message('error', 'PROSES PENGAJUAN KUOTA - Update kuota user_perusahaan tidak mempengaruhi baris manapun untuk id_pers: ' . $id_pers_terkait);
                    }

                } else {
                     log_message('error', 'PROSES PENGAJUAN KUOTA - Gagal update kuota perusahaan: ID Perusahaan tidak ditemukan pada data pengajuan.');
                     $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal update kuota perusahaan: ID Perusahaan tidak ditemukan pada data pengajuan.</div>');
                     redirect('admin/daftar_pengajuan_kuota');
                     return;
                }
            } else {
                log_message('debug', 'PROSES PENGAJUAN KUOTA - Tidak ada penambahan kuota (status bukan approved atau approved_quota <= 0). Status: ' . $status_pengajuan . ', Kuota: ' . $approved_quota);
            }

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

    
public function tambah_user_petugas() // Atau bisa dinamai tambah_internal_user
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Tambah User Petugas Baru'; // Judul bisa dinamis jika untuk role lain juga
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        // Default role yang akan ditambahkan adalah Petugas (ID 3)
        // Anda bisa membuat ini lebih dinamis jika form ini digunakan untuk role lain seperti Monitoring
        $target_role_id = 3; // Untuk Petugas
        // $target_role_id = $this->input->get('role_type'); // Contoh jika role dipilih dari link/parameter

        $data['target_role_info'] = $this->db->get_where('user_role', ['id' => $target_role_id])->row_array();

        if (!$data['target_role_info']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Role target tidak valid.</div>');
            redirect('admin/manajemen_user');
            return;
        }
        // Sesuaikan subtitle berdasarkan role target jika perlu
        $data['subtitle'] = 'Tambah User ' . htmlspecialchars($data['target_role_info']['role']);


        $this->form_validation->set_rules('name', 'Nama Lengkap', 'required|trim');
        // NIP akan disimpan di kolom 'email' dan harus unik
        $this->form_validation->set_rules('nip', 'NIP (Nomor Induk Pegawai)', 'required|trim|numeric|is_unique[user.email]', [
            'is_unique' => 'NIP ini sudah terdaftar sebagai login identifier.',
            'numeric'   => 'NIP harus berupa angka.'
        ]);
        $this->form_validation->set_rules('password', 'Password Awal', 'required|trim|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Konfirmasi Password', 'required|trim|matches[password]');
        
        // Field tambahan khusus untuk tabel 'petugas' (jika role adalah Petugas)
        if ($target_role_id == 3) { // Asumsi Role ID 3 adalah Petugas
            // $this->form_validation->set_rules('nip_detail_petugas', 'NIP (Detail Petugas)', 'trim|required|numeric'); // NIP di tabel petugas harus sama dengan NIP login
            $this->form_validation->set_rules('jabatan_petugas', 'Jabatan Petugas', 'trim|required');
        }

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/form_tambah_user_petugas', $data); // View ini perlu disesuaikan
            $this->load->view('templates/footer');
        } else {
            $nip_input = $this->input->post('nip');

            $user_data_to_insert = [
                'name' => htmlspecialchars($this->input->post('name', true)),
                'email' => htmlspecialchars($nip_input, true), // NIP disimpan di kolom email
                'image' => 'default.jpg',
                'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                'role_id' => $target_role_id,
                'is_active' => 1, // Langsung aktif
                'force_change_password' => 1, // Wajib ganti password saat login pertama
                'date_created' => time()
            ];
            $this->db->insert('user', $user_data_to_insert);
            $new_user_id = $this->db->insert_id();

            if ($new_user_id) {
                // Jika role adalah Petugas, simpan juga ke tabel 'petugas'
                if ($target_role_id == 3) { // Asumsi Role ID 3 adalah Petugas
                    $petugas_data_to_insert = [
                        'id_user' => $new_user_id,
                        'Nama' => $user_data_to_insert['name'], // Ambil dari nama user
                        'NIP' => $nip_input, // NIP dari input form
                        'Jabatan' => htmlspecialchars($this->input->post('jabatan_petugas', true))
                        // Tambahkan kolom lain yang relevan untuk tabel petugas
                    ];
                    $this->db->insert('petugas', $petugas_data_to_insert);
                }
                // Tambahkan logika untuk role Monitoring jika ada tabel terpisah untuk detail Monitoring

                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">User ' . htmlspecialchars($data['target_role_info']['role']) . ' baru, ' . htmlspecialchars($user_data_to_insert['name']) . ', berhasil ditambahkan. User wajib mengganti password saat login pertama.</div>');
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal menambahkan user baru. Silakan coba lagi.</div>');
            }
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
        $data['user'] = $admin_logged_in; // Data admin yang login (untuk header, sidebar, dll)

        $data['target_user_data'] = $this->db->get_where('user', ['id' => $target_user_id])->row_array();

        if (!$data['target_user_data']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">User yang akan diedit tidak ditemukan (ID: '.htmlspecialchars($target_user_id).').</div>');
            redirect('admin/manajemen_user');
            return;
        }

        $is_editing_main_admin = ($data['target_user_data']['id'] == 1); // Asumsi Admin utama memiliki ID = 1
        
        // Tentukan apakah target adalah Petugas (3) atau Monitoring (4) berdasarkan role_id yang akan disubmit atau yang sudah ada
        $target_role_id_for_check = $this->input->post('role_id') ? (int)$this->input->post('role_id') : (int)$data['target_user_data']['role_id'];
        $is_target_petugas_or_monitoring = in_array($target_role_id_for_check, [3, 4]); // Asumsi Role Petugas = 3, Monitoring = 4

        $data['roles_list'] = $this->db->get('user_role')->result_array();

        // Validasi Form
        $this->form_validation->set_rules('name', 'Nama Lengkap', 'required|trim');

        $original_login_identifier = $data['target_user_data']['email']; // Kolom 'email' di DB bisa berisi email atau NIP
        $input_login_identifier = $this->input->post('login_identifier'); // Nama field input dari form

        // Membangun aturan validasi untuk login_identifier (Email/NIP)
        $login_identifier_rules = 'required|trim';
        $login_identifier_label = '';

        if ($is_target_petugas_or_monitoring) {
            $login_identifier_label = 'NIP';
            $login_identifier_rules .= '|numeric'; // NIP harus numerik
            if ($input_login_identifier !== null && $input_login_identifier != $original_login_identifier) {
                $login_identifier_rules .= '|is_unique[user.email]';
            }
        } else { // Untuk Admin dan Pengguna Jasa
            $login_identifier_label = 'Email';
            $login_identifier_rules .= '|valid_email';
            if ($input_login_identifier !== null && $input_login_identifier != $original_login_identifier) {
                $login_identifier_rules .= '|is_unique[user.email]';
            }
        }
        // Hanya set rules jika ada input (untuk menghindari error saat halaman pertama kali load)
        if ($this->input->post('login_identifier') !== null) {
            $this->form_validation->set_rules('login_identifier', $login_identifier_label, $login_identifier_rules, [
                'is_unique' => $login_identifier_label . ' ini sudah terdaftar.',
                'numeric'   => $login_identifier_label . ' harus berupa angka.',
                'valid_email' => $login_identifier_label . ' tidak valid.'
            ]);
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
                // Hanya update 'email' (login identifier) jika ada input dan berbeda, atau jika memang diinput
                'email' => htmlspecialchars($input_login_identifier, true), 
            ];

            if (!$is_editing_main_admin) {
                $update_data_user['role_id'] = (int)$this->input->post('role_id');
                $update_data_user['is_active'] = (int)$this->input->post('is_active');
            } else {
                // Jika admin utama, pastikan role dan status tidak berubah dari form ini
                $update_data_user['role_id'] = (int)$data['target_user_data']['role_id']; 
                $update_data_user['is_active'] = (int)$data['target_user_data']['is_active']; 
            }

            $this->db->where('id', $target_user_id);
            $this->db->update('user', $update_data_user);

            $new_role_id = (int)($this->input->post('role_id') ?? $data['target_user_data']['role_id']);

            // Jika role baru atau role saat ini adalah Petugas (ID 3)
            // dan pastikan tabel 'petugas' memiliki kolom 'id_user' sebagai foreign key ke 'user.id'
            if ($new_role_id == 3) { 
                // Cek apakah kolom 'id_user' ada di tabel 'petugas'
                if ($this->db->field_exists('id_user', 'petugas')) {
                    $petugas_detail = $this->db->get_where('petugas', ['id_user' => $target_user_id])->row_array();
                    $data_petugas_update = [
                        'Nama' => $update_data_user['name'],
                        'NIP' => $update_data_user['email'], // NIP diambil dari user.email (yang berisi NIP)
                        'Jabatan' => $this->input->post('jabatan_petugas_edit') // Pastikan field ini ada di form edit
                    ];

                    if ($petugas_detail) { 
                        $this->db->where('id_user', $target_user_id);
                        $this->db->update('petugas', $data_petugas_update);
                    } else { 
                        $data_petugas_update['id_user'] = $target_user_id;
                        $this->db->insert('petugas', $data_petugas_update);
                    }
                } else {
                    log_message('error', 'Kolom id_user tidak ditemukan di tabel petugas saat mencoba update/insert data petugas untuk user ID: ' . $target_user_id);
                    // Anda bisa set flashdata error di sini jika perlu
                    // $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Data user berhasil diupdate, tetapi detail petugas tidak dapat diproses karena struktur tabel petugas tidak sesuai (missing id_user).</div>');
                }
            }
            // Tambahkan logika jika role diubah DARI Petugas (misal, menghapus entri di tabel petugas)
            // atau jika role adalah Monitoring dan perlu update tabel terpisah (jika ada)

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Data user '.htmlspecialchars($update_data_user['name']).' berhasil diupdate.</div>');
            redirect('admin/manajemen_user');
        }
    }

    // Tambahkan method ini di controllers/Admin.php
public function histori_kuota_perusahaan($id_pers = 0)
{
    log_message('debug', 'HISTORI KUOTA - Method dipanggil dengan id_pers: ' . $id_pers); // Log awal

    if ($id_pers == 0 || !is_numeric($id_pers)) {
        $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Perusahaan tidak valid.</div>');
        log_message('error', 'HISTORI KUOTA - ID Perusahaan tidak valid: ' . $id_pers); // Log error
        redirect('admin/monitoring_kuota');
        return;
    }

    $data['title'] = 'Returnable Package';
    $data['subtitle'] = 'Histori Kuota Perusahaan';
    $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

    // Ambil data perusahaan
    $this->db->select('up.NamaPers, up.initial_quota, up.remaining_quota, u.email as email_kontak, u.name as nama_kontak');
    $this->db->from('user_perusahaan up');
    $this->db->join('user u', 'up.id_pers = u.id', 'left');
    $this->db->where('up.id_pers', $id_pers);
    $data['perusahaan'] = $this->db->get()->row_array();
    log_message('debug', 'HISTORI KUOTA - Query Data Perusahaan: ' . $this->db->last_query()); // Log query perusahaan
    log_message('debug', 'HISTORI KUOTA - Hasil Data Perusahaan: ' . print_r($data['perusahaan'], true)); // Log hasil data perusahaan

    if (!$data['perusahaan']) {
        $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data perusahaan tidak ditemukan untuk ID: ' . $id_pers . '</div>');
        log_message('error', 'HISTORI KUOTA - Data perusahaan tidak ditemukan untuk ID: ' . $id_pers); // Log error
        redirect('admin/monitoring_kuota');
        return;
    }
    $data['id_pers_untuk_histori'] = $id_pers;

    // Ambil data log kuota untuk perusahaan ini
    $this->db->select('lk.*, u_admin.name as nama_pencatat');
    $this->db->from('log_kuota_perusahaan lk'); // Pastikan nama tabel ini BENAR: 'log_kuota_perusahaan'
    $this->db->join('user u_admin', 'lk.dicatat_oleh_user_id = u_admin.id', 'left');
    $this->db->where('lk.id_pers', $id_pers);
    $this->db->order_by('lk.tanggal_transaksi', 'DESC');
    $this->db->order_by('lk.id_log', 'DESC');
    $data['histori_kuota'] = $this->db->get()->result_array();

    // Logging untuk query histori kuota
    log_message('debug', 'HISTORI KUOTA - Query Log Histori Kuota: ' . $this->db->last_query());
    log_message('debug', 'HISTORI KUOTA - Jumlah Log Histori Ditemukan: ' . count($data['histori_kuota']));
    if (count($data['histori_kuota']) > 0) {
        log_message('debug', 'HISTORI KUOTA - Data Log Histori (sampel data pertama): ' . print_r($data['histori_kuota'][0], true));
    } else {
        log_message('debug', 'HISTORI KUOTA - Tidak ada data log histori yang ditemukan.');
    }

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('templates/topbar', $data);
    $this->load->view('admin/histori_kuota_perusahaan_view', $data);
    $this->load->view('templates/footer');
}

public function detail_permohonan_admin($id_permohonan = 0) // PASTIKAN METHOD INI ADA
    {
        // Aktifkan logging di awal method untuk memastikan method ini terpanggil
        log_message('debug', 'DETAIL PERMOHONAN ADMIN - Method dipanggil dengan id_permohonan: ' . $id_permohonan);

        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            log_message('error', 'DETAIL PERMOHONAN ADMIN - ID Permohonan tidak valid: ' . $id_permohonan);
            redirect('admin/permohonanMasuk'); // atau ke halaman yang sesuai
            return;
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Permohonan Impor ID: ' . htmlspecialchars($id_permohonan);
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        // 1. Ambil data permohonan utama
        $this->db->select('up.*, upr.NamaPers, upr.npwp, u_pemohon.name as nama_pengaju_permohonan, u_pemohon.email as email_pengaju_permohonan, u_petugas.name as nama_petugas_pemeriksa');
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        // Asumsi: user_permohonan.petugas adalah ID dari tabel 'petugas', dan 'petugas.id_user' adalah FK ke 'user.id'
        $this->db->join('petugas p', 'up.petugas = p.id', 'left'); // Sesuaikan jika 'up.petugas' merujuk langsung ke user.id
        $this->db->join('user u_petugas', 'p.id_user = u_petugas.id', 'left'); // Jika 'up.petugas' adalah ID user, join ini tidak perlu melalui tabel 'petugas'
        $this->db->where('up.id', $id_permohonan);
        $data['permohonan_detail'] = $this->db->get()->row_array();

        log_message('debug', 'DETAIL PERMOHONAN ADMIN - Query Permohonan: ' . $this->db->last_query());
        log_message('debug', 'DETAIL PERMOHONAN ADMIN - Data Permohonan: ' . print_r($data['permohonan_detail'], true));


        if (!$data['permohonan_detail']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data permohonan dengan ID ' . htmlspecialchars($id_permohonan) . ' tidak ditemukan.</div>');
            log_message('error', 'DETAIL PERMOHONAN ADMIN - Data permohonan tidak ditemukan untuk ID: ' . $id_permohonan);
            redirect('admin/permohonanMasuk'); // atau ke halaman yang sesuai
            return;
        }

        // 2. Ambil data LHP (jika ada)
        $data['lhp_detail'] = $this->db->get_where('lhp', ['id_permohonan' => $id_permohonan])->row_array();
        log_message('debug', 'DETAIL PERMOHONAN ADMIN - Data LHP: ' . print_r($data['lhp_detail'], true));

        // 3. Anda bisa tambahkan pengambilan data lain jika perlu (misal file lampiran, detail surat tugas, dll)

        // Load view
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        // BUAT VIEW BARU INI: application/views/admin/detail_permohonan_admin_view.php
        $this->load->view('admin/detail_permohonan_admin_view', $data);
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