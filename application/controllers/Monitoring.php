<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monitoring extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->helper('repack_helper'); 
        if (!isset($this->db)) {
             $this->load->database();
        }
        
        // Hanya method 'logout' yang dikecualikan dari _check_auth_monitoring
        // Karena 'force_change_password_page' sudah tidak ada/tidak digunakan
        $excluded_methods_for_full_auth_check = ['logout']; 
        $current_method = $this->router->fetch_method();

        if (!in_array($current_method, $excluded_methods_for_full_auth_check)) {
            $this->_check_auth_monitoring();
        } 
        // Pengecekan sesi email dasar jika method adalah salah satu yang dikecualikan (hanya logout sekarang)
        elseif (!$this->session->userdata('email') && $current_method != 'logout') {
             $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Sesi tidak valid atau telah berakhir. Silakan login kembali.</div>');
             redirect('auth');
             exit;
        }
        log_message('debug', 'Monitoring Class Initialized. Method: ' . $current_method);
    }

    private function _check_auth_monitoring()
    {
        log_message('debug', 'Monitoring: _check_auth_monitoring() called. Email session: ' . ($this->session->userdata('email') ?? 'NULL'));
        if (!$this->session->userdata('email')) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Mohon login untuk melanjutkan.</div>');
            redirect('auth');
            exit;
        }
        
        $role_id_session = $this->session->userdata('role_id');
        // $force_change_password = $this->session->userdata('force_change_password'); // Tidak relevan lagi untuk monitoring
        // $current_method = $this->router->fetch_method(); // Tidak perlu lagi di sini jika tidak ada force_change_password

        log_message('debug', 'Monitoring: _check_auth_monitoring() - Role ID: ' . ($role_id_session ?? 'NULL'));

        if ($role_id_session != 4) { 
            log_message('warning', 'Monitoring: _check_auth_monitoring() - Akses ditolak, role ID tidak sesuai: ' . $role_id_session);
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Akses Ditolak! Anda tidak diotorisasi untuk mengakses halaman ini.</div>');
            if ($role_id_session == 1) redirect('admin');
            elseif ($role_id_session == 2) redirect('user');
            elseif ($role_id_session == 3) redirect('petugas');
            else redirect('auth/blocked');
            exit;
        }

        log_message('debug', 'Monitoring: _check_auth_monitoring() passed.');
    }

    public function index() 
    {
        log_message('debug', 'Monitoring: index() called.');
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Dashboard Monitoring';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        // Statistik untuk Dashboard Monitoring
        $data['total_pengajuan_kuota_all'] = $this->db->count_all_results('user_pengajuan_kuota');
        $data['total_permohonan_impor_all'] = $this->db->count_all_results('user_permohonan');
        
        $data['pengajuan_kuota_pending'] = $this->db->where('status', 'pending')->count_all_results('user_pengajuan_kuota');
        $data['pengajuan_kuota_approved'] = $this->db->where('status', 'approved')->count_all_results('user_pengajuan_kuota');
        $data['pengajuan_kuota_rejected'] = $this->db->where('status', 'rejected')->count_all_results('user_pengajuan_kuota');

        $data['permohonan_impor_baru_atau_diproses_admin'] = $this->db->where_in('status', ['0','5'])->count_all_results('user_permohonan');
        $data['permohonan_impor_penunjukan_petugas'] = $this->db->where('status', '1')->count_all_results('user_permohonan');
        $data['permohonan_impor_lhp_direkam'] = $this->db->where('status', '2')->count_all_results('user_permohonan');
        $data['permohonan_impor_selesai_disetujui'] = $this->db->where('status', '3')->count_all_results('user_permohonan');
        $data['permohonan_impor_selesai_ditolak'] = $this->db->where('status', '4')->count_all_results('user_permohonan');


        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data); 
        $this->load->view('templates/topbar', $data);
        $this->load->view('monitoring/dashboard_monitoring_view', $data); 
        $this->load->view('templates/footer');
    }

    public function pengajuan_kuota()
    {
        log_message('debug', 'Monitoring: pengajuan_kuota() (daftar) called.');
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Pantauan Data Pengajuan Kuota';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->db->select('upk.*, upr.NamaPers, u_pemohon.name as nama_pengaju_kuota, u_pemohon.email as email_pengaju_kuota'); // Tambahkan email pengaju
        $this->db->from('user_pengajuan_kuota upk');
        $this->db->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $this->db->join('user u_pemohon', 'upk.id_pers = u_pemohon.id', 'left'); 
        $this->db->order_by('upk.submission_date', 'DESC');
        $data['daftar_pengajuan_kuota'] = $this->db->get()->result_array();
        log_message('debug', 'Monitoring: pengajuan_kuota() - Query: ' . $this->db->last_query());

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('monitoring/daftar_pengajuan_kuota_view', $data); 
        $this->load->view('templates/footer');
    }

    public function permohonan_impor()
    {
        log_message('debug', 'Monitoring: permohonan_impor() (daftar) called.');
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Pantauan Data Permohonan Impor';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->db->select('up.*, upr.NamaPers, u_pemohon.name as nama_pemohon_impor, u_pemohon.email as email_pemohon_impor, u_petugas.name as nama_petugas_pemeriksa'); // Tambahkan email pemohon & nama petugas dari tabel user
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left'); 
        $this->db->join('petugas p_petugas', 'up.petugas = p_petugas.id', 'left'); 
        $this->db->join('user u_petugas', 'p_petugas.id_user = u_petugas.id', 'left'); // Join ke tabel user untuk nama petugas
        $this->db->order_by('up.time_stamp', 'DESC');
        $data['daftar_permohonan_impor'] = $this->db->get()->result_array();
        log_message('debug', 'Monitoring: permohonan_impor() - Query: ' . $this->db->last_query());


        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        // Pastikan nama view ini benar: monitoring/daftar_permohonan_impor_view.php
        $this->load->view('monitoring/daftar_permohonan_impor_view', $data); 
        $this->load->view('templates/footer');
    }
    
    public function detail_pengajuan_kuota($id_pengajuan = 0)
    {
        log_message('debug', 'Monitoring: detail_pengajuan_kuota() called with ID: ' . $id_pengajuan);
        if ($id_pengajuan == 0 || !is_numeric($id_pengajuan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Pengajuan Kuota tidak valid.</div>');
            redirect('monitoring/pengajuan_kuota');
            return;
        }
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Pantauan Pengajuan Kuota ID: ' . htmlspecialchars($id_pengajuan);
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        $this->db->select('upk.*, upr.NamaPers, upr.npwp as npwp_perusahaan, upr.alamat as alamat_perusahaan, u_pemohon.name as nama_pengaju_kuota, u_pemohon.email as email_pengaju_kuota');
        $this->db->from('user_pengajuan_kuota upk');
        $this->db->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $this->db->join('user u_pemohon', 'upk.id_pers = u_pemohon.id', 'left');
        // Asumsi ada kolom 'diproses_oleh_admin_id' di 'user_pengajuan_kuota' yang merujuk ke 'user.id' admin
        // $this->db->join('user admin_pemroses', 'upk.diproses_oleh_admin_id = admin_pemroses.id', 'left'); 
        $this->db->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $this->db->get()->row_array();
        log_message('debug', 'Monitoring: detail_pengajuan_kuota() - Query: ' . $this->db->last_query());

        if (!$data['pengajuan']) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Data pengajuan kuota tidak ditemukan.</div>');
            redirect('monitoring/pengajuan_kuota');
            return;
        }

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        // Pastikan view ini ada: monitoring/detail_pengajuan_kuota_view.php
        $this->load->view('monitoring/detail_pengajuan_kuota_view', $data); 
        $this->load->view('templates/footer');
    }

    public function detail_permohonan_impor($id_permohonan = 0)
    {
        log_message('debug', 'Monitoring: detail_permohonan_impor() called with ID: ' . $id_permohonan);
         if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            redirect('monitoring/permohonan_impor');
            return;
        }
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Pantauan Permohonan Impor ID: ' . htmlspecialchars($id_permohonan);
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        // Query ini mirip dengan Admin::detail_permohonan_admin, pastikan semua field yang dibutuhkan view ada
        $this->db->select(
            'up.*, '.
            'upr.NamaPers, upr.npwp AS npwp_perusahaan, upr.alamat AS alamat_perusahaan, upr.NoSkep AS NoSkep_perusahaan_asal, ' . // NoSkep dari user_perusahaan
            'u_pemohon.name as nama_pengaju_permohonan, u_pemohon.email as email_pengaju_permohonan, '.
            'p_petugas.NIP as nip_petugas_pemeriksa, u_petugas.name as nama_petugas_pemeriksa, '
        );
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $this->db->join('petugas p_petugas', 'up.petugas = p_petugas.id', 'left');
        $this->db->join('user u_petugas', 'p_petugas.id_user = u_petugas.id', 'left');
        // $this->db->join('user admin_pemroses', 'up.diproses_oleh_id_admin = admin_pemroses.id', 'left');
        $this->db->where('up.id', $id_permohonan);
        $data['permohonan_detail'] = $this->db->get()->row_array(); // Diubah ke permohonan_detail agar konsisten dengan view user/admin
        log_message('debug', 'Monitoring: detail_permohonan_impor() - Query: ' . $this->db->last_query());

        if (!$data['permohonan_detail']) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">Data permohonan impor tidak ditemukan.</div>');
            redirect('monitoring/permohonan_impor');
            return;
        }

        // Ambil data LHP jika ada
        $data['lhp_detail'] = $this->db->get_where('lhp', ['id_permohonan' => $id_permohonan])->row_array();
        
        $data['is_monitoring_view'] = TRUE; // Flag untuk view
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/detail_permohonan_admin_view', $data); // View admin yang dipakai bersama
        $this->load->view('templates/footer');
    }

    // --- METHOD BARU UNTUK PANTAUAN KUOTA PERUSAHAAN ---
    public function pantau_kuota_perusahaan()
    {
        log_message('debug', 'Monitoring: pantau_kuota_perusahaan() called.');
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Pantauan Kuota Perusahaan';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        // Query untuk mengambil data perusahaan dan agregat kuota barang mereka
        $this->db->select('
            up.id_pers,
            up.NamaPers,
            u.email as user_email_kontak,
            (SELECT GROUP_CONCAT(DISTINCT ukb.nomor_skep_asal ORDER BY ukb.nomor_skep_asal SEPARATOR ", ")
             FROM user_kuota_barang ukb
             WHERE ukb.id_pers = up.id_pers AND ukb.status_kuota_barang = "active"
            ) as list_skep_aktif_barang,
            (SELECT SUM(ukb.initial_quota_barang)
             FROM user_kuota_barang ukb
             WHERE ukb.id_pers = up.id_pers 
            ) as total_initial_kuota_all_items,
            (SELECT SUM(ukb.remaining_quota_barang)
             FROM user_kuota_barang ukb
             WHERE ukb.id_pers = up.id_pers
            ) as total_remaining_kuota_all_items
        ');
        $this->db->from('user_perusahaan up');
        $this->db->join('user u', 'up.id_pers = u.id', 'left'); // User yang mendaftarkan perusahaan
        $this->db->order_by('up.NamaPers', 'ASC');
        $data['perusahaan_kuota_list'] = $this->db->get()->result_array();

        log_message('debug', 'Monitoring: pantau_kuota_perusahaan() - Query: ' . $this->db->last_query());
        log_message('debug', 'Monitoring: pantau_kuota_perusahaan() - Data Count: ' . count($data['perusahaan_kuota_list']));

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('monitoring/pantau_kuota_perusahaan_view', $data); // View baru
        $this->load->view('templates/footer');
    }

    // --- METHOD BARU UNTUK DETAIL KUOTA PERUSAHAAN ---
    public function detail_kuota_perusahaan($id_pers = 0)
    {
        log_message('debug', 'Monitoring: detail_kuota_perusahaan() called for id_pers: ' . $id_pers);
        if ($id_pers == 0 || !is_numeric($id_pers)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Perusahaan tidak valid.</div>');
            redirect('monitoring/pantau_kuota_perusahaan');
            return;
        }

        $data['title'] = 'Returnable Package';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        // Ambil data perusahaan umum
        $this->db->select('up.id_pers, up.NamaPers, up.npwp, up.alamat, u.email as user_email_kontak, u.name as nama_kontak_user');
        $this->db->from('user_perusahaan up');
        $this->db->join('user u', 'up.id_pers = u.id', 'left');
        $this->db->where('up.id_pers', $id_pers);
        $data['perusahaan_info'] = $this->db->get()->row_array();

        if (!$data['perusahaan_info']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data perusahaan tidak ditemukan untuk ID: ' . htmlspecialchars($id_pers) . '</div>');
            redirect('monitoring/pantau_kuota_perusahaan');
            return;
        }
        $data['subtitle'] = 'Detail Rincian & Histori Kuota: ' . htmlspecialchars($data['perusahaan_info']['NamaPers']);

        // Ambil daftar kuota per jenis barang untuk perusahaan ini
        $this->db->select('ukb.id_kuota_barang, ukb.nama_barang, ukb.initial_quota_barang, ukb.remaining_quota_barang, ukb.nomor_skep_asal, ukb.tanggal_skep_asal, ukb.status_kuota_barang, ukb.waktu_pencatatan, admin_pencatat.name as nama_admin_pencatat_kuota');
        $this->db->from('user_kuota_barang ukb');
        $this->db->join('user admin_pencatat', 'ukb.dicatat_oleh_user_id = admin_pencatat.id', 'left');
        $this->db->where('ukb.id_pers', $id_pers);
        $this->db->order_by('ukb.nama_barang ASC, ukb.tanggal_skep_asal DESC, ukb.waktu_pencatatan DESC');
        $data['detail_kuota_items'] = $this->db->get()->result_array();
        log_message('debug', 'Monitoring: detail_kuota_perusahaan() - Query Detail Kuota Items: ' . $this->db->last_query());

        // --- AMBIL HISTORI TRANSAKSI KUOTA ---
        $this->db->select('lk.*, u_pencatat.name as nama_pencatat_log');
        $this->db->from('log_kuota_perusahaan lk');
        $this->db->join('user u_pencatat', 'lk.dicatat_oleh_user_id = u_pencatat.id', 'left');
        $this->db->where('lk.id_pers', $id_pers);
        $this->db->order_by('lk.tanggal_transaksi', 'DESC');
        $this->db->order_by('lk.id_log', 'DESC'); // Urutan sekunder jika timestamp sama
        $data['histori_transaksi_kuota'] = $this->db->get()->result_array();
        log_message('debug', 'Monitoring: detail_kuota_perusahaan() - Query Histori Transaksi Kuota: ' . $this->db->last_query());
        log_message('debug', 'Monitoring: detail_kuota_perusahaan() - Data Histori Transaksi Kuota Count: ' . count($data['histori_transaksi_kuota']));
        // --- AKHIR AMBIL HISTORI TRANSAKSI KUOTA ---

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('monitoring/detail_kuota_perusahaan_view', $data);
        $this->load->view('templates/footer');
    }

    // force_change_password_page dan edit_profil bisa jadi berbeda untuk Monitoring,
    // atau bisa diarahkan ke method yang sama dengan User/Petugas jika logikanya identik
    // dan hanya dibedakan oleh role_id di _check_auth untuk akses.
    // Untuk saat ini, saya asumsikan method ini belum ada atau belum disesuaikan untuk Monitoring.
    // Jika diperlukan, Anda bisa menambahkannya serupa dengan controller lain.

}