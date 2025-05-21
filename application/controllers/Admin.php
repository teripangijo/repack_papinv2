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
        $this->load->library('session');
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

    // Di application/controllers/Admin.php

public function monitoring_kuota()
{
    $data['title'] = 'Returnable Package';
    $data['subtitle'] = 'Monitoring Kuota Perusahaan (per Jenis Barang)';
    $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

    // Query untuk mengambil data perusahaan dan agregat kuota barang mereka
    $this->db->select('
        up.id_pers,
        up.NamaPers,
        u.email as user_email,
        (SELECT GROUP_CONCAT(DISTINCT ukb.nomor_skep_asal SEPARATOR ", ")
         FROM user_kuota_barang ukb
         WHERE ukb.id_pers = up.id_pers AND ukb.status_kuota_barang = "active"
        ) as list_skep_aktif,
        (SELECT SUM(ukb.initial_quota_barang)
         FROM user_kuota_barang ukb
         WHERE ukb.id_pers = up.id_pers
        ) as total_initial_kuota_barang,
        (SELECT SUM(ukb.remaining_quota_barang)
         FROM user_kuota_barang ukb
         WHERE ukb.id_pers = up.id_pers
        ) as total_remaining_kuota_barang
    ');
    $this->db->from('user_perusahaan up');
    $this->db->join('user u', 'up.id_pers = u.id', 'left');
  
    $this->db->order_by('up.NamaPers', 'ASC');
    $data['monitoring_data'] = $this->db->get()->result_array();

    log_message('debug', 'ADMIN MONITORING KUOTA - Query: ' . $this->db->last_query());
    log_message('debug', 'ADMIN MONITORING KUOTA - Data: ' . print_r($data['monitoring_data'], true));

    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('templates/topbar', $data);
    $this->load->view('admin/monitoring_kuota_view', $data);
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
        // echo "ADMIN PROSES SURAT - Method prosesSurat() DIPANGGIL dengan ID: " . $id_permohonan . "<br>";
        // die("Eksekusi dihentikan di awal prosesSurat.");

        $admin_user = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['user'] = $admin_user; // Untuk header, sidebar, topbar
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Proses Finalisasi Permohonan Impor'; // Atur subtitle

        // Validasi ID Permohonan
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            redirect('admin/permohonanMasuk');
            return;
        }

        // Ambil data permohonan
        $this->db->select('up.*, upr.NamaPers, upr.npwp, upr.alamat, upr.NoSkep');
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->where('up.id', $id_permohonan);
        $data['permohonan'] = $this->db->get()->row_array();

        if (!$data['permohonan']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data permohonan dengan ID '.htmlspecialchars($id_permohonan).' tidak ditemukan.</div>');
            log_message('error', 'ADMIN PROSES SURAT - Data permohonan tidak ditemukan untuk ID: ' . $id_permohonan);
            redirect('admin/permohonanMasuk');
            return;
        }

        // Variabel $user_perusahaan untuk view, diambil dari join di atas
        // Namun, lebih eksplisit jika kita definisikan seperti ini atau pastikan semua field ada
        $data['user_perusahaan'] = [
            'NamaPers' => $data['permohonan']['NamaPers'],
            'alamat' => $data['permohonan']['alamat'],
            'NoSkep' => $data['permohonan']['NoSkep'] 
        ];
    
        $perusahaan_terkait = $this->db->get_where('user_perusahaan', ['id_pers' => $data['permohonan']['id_pers']])->row_array();
        if ($perusahaan_terkait) {
            $data['user_perusahaan'] = $perusahaan_terkait;
        } else {
            // Handle jika data perusahaan tidak ditemukan, meskipun seharusnya ada jika permohonan valid
            $data['user_perusahaan'] = ['NamaPers' => 'N/A', 'alamat' => 'N/A', 'NoSkep' => 'N/A']; // Default
            log_message('warning', 'ADMIN PROSES SURAT - Data user_perusahaan tidak ditemukan untuk id_pers: ' . $data['permohonan']['id_pers']);
        }


        // Ambil data LHP
        $data['lhp'] = $this->db->get_where('lhp', ['id_permohonan' => $id_permohonan])->row_array();

        // Validasi LHP dan status permohonan
        // Status '2' artinya LHP sudah direkam dan siap diproses final
        if (!$data['lhp'] || $data['permohonan']['status'] != '2' || empty($data['lhp']['NoLHP']) || empty($data['lhp']['TglLHP'])) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">LHP belum lengkap atau status permohonan (ID '.htmlspecialchars($id_permohonan).') tidak valid untuk finalisasi. Status saat ini: '.htmlspecialchars($data['permohonan']['status'] ?? 'Tidak Diketahui').'. Mohon periksa kembali data LHP.</div>');
            log_message('warning', 'ADMIN PROSES SURAT - Validasi LHP/Status gagal. ID: '.$id_permohonan.'. Status Permohonan: '.($data['permohonan']['status'] ?? 'N/A').'. Data LHP: '.print_r($data['lhp'], true));
            redirect('admin/detail_permohonan_admin/' . $id_permohonan); // Redirect ke detail atau permohonan masuk
            return;
        }
        
        log_message('debug', 'ADMIN PROSES SURAT - Data Permohonan: ' . print_r($data['permohonan'], true));
        log_message('debug', 'ADMIN PROSES SURAT - Data LHP: ' . print_r($data['lhp'], true));
        log_message('debug', 'ADMIN PROSES SURAT - Data User Perusahaan: ' . print_r($data['user_perusahaan'], true));


        // Validasi form
        $this->form_validation->set_rules('status_final', 'Status Final Permohonan', 'required|in_list[3,4]'); // 3 = Disetujui, 4 = Ditolak
        $this->form_validation->set_rules('nomorSetuju', 'Nomor Surat Persetujuan/Penolakan', 'trim|max_length[100]'); // Digunakan sebagai nomor surat keputusan
        $this->form_validation->set_rules('tgl_S', 'Tanggal Surat Persetujuan/Penolakan', 'trim'); // Digunakan sebagai tanggal surat keputusan
        $this->form_validation->set_rules('nomorND', 'Nomor Nota Dinas (Opsional)', 'trim|max_length[100]');
        $this->form_validation->set_rules('tgl_ND', 'Tanggal Nota Dinas (Opsional)', 'trim');
        $this->form_validation->set_rules('link', 'Link Surat Keputusan (Opsional)', 'trim|callback__valid_url_format_check');
        $this->form_validation->set_rules('linkND', 'Link Nota Dinas (Opsional)', 'trim|callback__valid_url_format_check');

        if ($this->form_validation->run() == false) {
            log_message('debug', 'ADMIN PROSES SURAT - Form validation failed or initial load. Loading view prosesSurat.');
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/prosesSurat', $data); // Load view di sini
            $this->load->view('templates/footer');
        } else {
            // Proses jika validasi form sukses (setelah POST)
            $status_final_permohonan = $this->input->post('status_final');
            
            // Jika disetujui, nomor dan tanggal LHP dari petugas bisa jadi dasar nomor surat persetujuan
            // Jika ditolak, nomor surat persetujuan diisi manual (atau bisa juga menggunakan nomor LHP jika kebijakannya begitu)
            $nomor_surat_keputusan = $this->input->post('nomorSetuju');
            $tanggal_surat_keputusan = $this->input->post('tgl_S');

            // Validasi nomor surat keputusan dan tanggal surat keputusan
            if (empty($nomor_surat_keputusan) && ($status_final_permohonan == '3' || $status_final_permohonan == '4')) {
                $this->form_validation->set_rules('nomorSetuju', 'Nomor Surat Keputusan', 'required');
            }
            if (empty($tanggal_surat_keputusan) && ($status_final_permohonan == '3' || $status_final_permohonan == '4')) {
                $this->form_validation->set_rules('tgl_S', 'Tanggal Surat Keputusan', 'required');
            }
            // Re-run validation
            if (!$this->form_validation->run()) {
                log_message('debug', 'ADMIN PROSES SURAT - Re-validation failed for nomorSetuju/tgl_S. Loading view prosesSurat.');
                $this->load->view('templates/header', $data);
                $this->load->view('templates/sidebar', $data);
                $this->load->view('templates/topbar', $data);
                $this->load->view('admin/prosesSurat', $data);
                $this->load->view('templates/footer');
                return;
            }


            $data_update_permohonan = [
                'nomorSetuju'   => $nomor_surat_keputusan,
                'tgl_S'         => !empty($tanggal_surat_keputusan) ? $tanggal_surat_keputusan : null,
                'nomorND'       => $this->input->post('nomorND'),
                'tgl_ND'        => $this->input->post('tgl_ND') ?: null,
                'link'          => $this->input->post('link'),
                'linkND'        => $this->input->post('linkND'),
                'time_selesai'  => date("Y-m-d H:i:s"),
                'status'        => $status_final_permohonan,
                // 'diproses_oleh_id_admin' => $admin_user['id'] // Catat siapa admin yang memproses
            ];
            $this->db->where('id', $id_permohonan);
            $this->db->update('user_permohonan', $data_update_permohonan);
            log_message('info', 'ADMIN PROSES SURAT - Permohonan ID ' . $id_permohonan . ' diupdate. Status: ' . $status_final_permohonan . '. Data: ' . print_r($data_update_permohonan, true));


            // --- LOGIKA PEMOTONGAN KUOTA BARANG ---
            // Jika status final adalah '3' (Disetujui) dan ada data LHP yang valid
            if ($status_final_permohonan == '3' && isset($data['lhp']['JumlahBenar']) && $data['lhp']['JumlahBenar'] > 0) {
                // ... (kode pemotongan kuota Anda) ...
                $id_pers_terkait = $data['permohonan']['id_pers'];
                $jumlah_barang_digunakan_lhp = (int)$data['lhp']['JumlahBenar'];
                $nama_barang_dimohonkan = $data['permohonan']['NamaBarang']; 
                $id_kuota_barang_yang_digunakan = $data['permohonan']['id_kuota_barang_digunakan'] ?? null;

                log_message('debug', 'ADMIN PROSES SURAT - Memulai Pemotongan Kuota Barang. ID Pers: ' . $id_pers_terkait . ', Jml LHP: ' . $jumlah_barang_digunakan_lhp . ', Barang: ' . $nama_barang_dimohonkan . ', ID Kuota Brg Digunakan: ' . $id_kuota_barang_yang_digunakan);

                if ($id_kuota_barang_yang_digunakan) {
                    $kuota_barang_db = $this->db->get_where('user_kuota_barang', ['id_kuota_barang' => $id_kuota_barang_yang_digunakan, 'id_pers' => $id_pers_terkait])->row_array();

                    if ($kuota_barang_db && $kuota_barang_db['nama_barang'] == $nama_barang_dimohonkan) {
                        $sisa_kuota_barang_sebelum = (float)$kuota_barang_db['remaining_quota_barang'];
                        $sisa_kuota_barang_baru = $sisa_kuota_barang_sebelum - $jumlah_barang_digunakan_lhp;
                        $status_kuota_brg_update = $kuota_barang_db['status_kuota_barang'];

                        if ($sisa_kuota_barang_baru < 0) {
                            log_message('warning', 'ADMIN PROSES SURAT - Pemotongan kuota barang (ID: '.$id_kuota_barang_yang_digunakan.') melebihi sisa. Sisa sebelumnya: '.$sisa_kuota_barang_sebelum.', Dipakai: '.$jumlah_barang_digunakan_lhp.'. Sisa direset ke 0.');
                            $sisa_kuota_barang_baru = 0;
                        }
                        if ($sisa_kuota_barang_baru <= 0) {
                            $status_kuota_brg_update = 'habis';
                        }

                        $this->db->where('id_kuota_barang', $id_kuota_barang_yang_digunakan);
                        $this->db->update('user_kuota_barang', [
                            'remaining_quota_barang' => $sisa_kuota_barang_baru,
                            'status_kuota_barang' => $status_kuota_brg_update
                        ]);
                        log_message('info', 'ADMIN PROSES SURAT - Kuota barang di user_kuota_barang diupdate. ID: ' . $id_kuota_barang_yang_digunakan . '. Sisa baru: ' . $sisa_kuota_barang_baru . '. Status baru: ' . $status_kuota_brg_update);

                        $this->_log_perubahan_kuota(
                            $id_pers_terkait, 'pengurangan', $jumlah_barang_digunakan_lhp,
                            $sisa_kuota_barang_sebelum, $sisa_kuota_barang_baru,
                            'Penggunaan Kuota. Aju: ' . ($data['permohonan']['nomorSurat'] ?? $id_permohonan) . '. Barang: ' . $nama_barang_dimohonkan,
                            $id_permohonan, 'permohonan_impor_selesai', $admin_user['id'],
                            $nama_barang_dimohonkan, $id_kuota_barang_yang_digunakan
                        );
                    } else {
                        log_message('error', 'ADMIN PROSES SURAT - Gagal potong kuota barang: Data user_kuota_barang (ID: '.$id_kuota_barang_yang_digunakan.') tidak ditemukan untuk perusahaan atau nama barang tidak cocok (' . $nama_barang_dimohonkan . ' vs ' . ($kuota_barang_db['nama_barang'] ?? 'Tidak ada') . ').');
                        $this->session->set_flashdata('message_error_quota', '<div class="alert alert-danger" role="alert">Peringatan: Permohonan disetujui tetapi pemotongan kuota otomatis gagal. Harap periksa log dan data kuota perusahaan secara manual.</div>');
                    }
                } else {
                    log_message('error', 'ADMIN PROSES SURAT - Gagal potong kuota barang: id_kuota_barang_digunakan tidak ada di data permohonan (ID: '.$id_permohonan.'). Pastikan User menyimpan ID kuota barang saat membuat permohonan.');
                    $this->session->set_flashdata('message_error_quota', '<div class="alert alert-danger" role="alert">Peringatan: Permohonan disetujui tetapi pemotongan kuota otomatis gagal karena ID kuota barang tidak ditemukan pada permohonan. Harap periksa log dan data kuota perusahaan secara manual.</div>');
                }
            }


            $pesan_status_akhir = ($status_final_permohonan == '3') ? 'Disetujui' : 'Ditolak';
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Status permohonan ID '.htmlspecialchars($id_permohonan).' telah berhasil diproses menjadi "'. $pesan_status_akhir .'"!</div>');
            redirect('admin/permohonanMasuk');
        }
    }

    public function _valid_url_format_check($str)
    {
        if (empty($str)) { // Jika tidak ada input, anggap valid
            return TRUE;
        }
        // Validasi format URL menggunakan filter_var
        if (filter_var($str, FILTER_VALIDATE_URL)) {
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
    $dicatat_oleh_user_id_param = null,
    $nama_barang_terkait_param = null,
    $id_kuota_barang_ref_param = null   
) {
    // Log perubahan kuota barang ke tabel user_kuota_barang_log

    $log_data = [
        'id_pers'                 => $id_pers_param,
        'nama_barang_terkait'     => $nama_barang_terkait_param,
        'id_kuota_barang_referensi'=> $id_kuota_barang_ref_param,
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
        $data['permohonan'] = $permohonan;

        if ($permohonan['status'] == '0' && $this->input->method() !== 'post') {
            $this->db->where('id', $id_permohonan);
            $this->db->update('user_permohonan', ['status' => '5']);
            $permohonan['status'] = '5'; // Update variabel lokal
            $data['permohonan']['status'] = '5'; // Update data yang dikirim ke view
            $this->session->set_flashdata('message_transient', '<div class="alert alert-info" role="alert">Status permohonan ID ' . htmlspecialchars($id_permohonan) . ' telah diubah menjadi "Diproses Admin". Lanjutkan dengan menunjuk petugas.</div>');
        }

        // Ambil daftar petugas dari tabel 'petugas'
        $data['list_petugas'] = $this->db->order_by('Nama', 'ASC')->get('petugas')->result_array();
        if (empty($data['list_petugas'])) {
            log_message('warning', 'Tidak ada data petugas ditemukan di tabel petugas.');
        }

        // Validasi form
        $this->form_validation->set_rules('petugas_id', 'Petugas Pemeriksa', 'required|numeric');
        $this->form_validation->set_rules('nomor_surat_tugas', 'Nomor Surat Tugas', 'required|trim');
        $this->form_validation->set_rules('tanggal_surat_tugas', 'Tanggal Surat Tugas', 'required');


        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/form_penunjukan_petugas', $data);
            $this->load->view('templates/footer');
        } else {
            // Proses data form
            $update_data = [
                'petugas' => $this->input->post('petugas_id'), // Ambil langsung dari 'petugas_id'
                'NoSuratTugas' => $this->input->post('nomor_surat_tugas'),
                'TglSuratTugas' => $this->input->post('tanggal_surat_tugas'),
                'status' => '1', // Status '1' = Penunjukan Pemeriksa
                'WaktuPenunjukanPetugas' => date('Y-m-d H:i:s')
            ];

            $nama_file_surat_tugas = $permohonan['FileSuratTugas'] ?? null;

            if (isset($_FILES['file_surat_tugas']) && $_FILES['file_surat_tugas']['error'] != UPLOAD_ERR_NO_FILE) {
                $upload_dir_st = './uploads/surat_tugas/';
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

                // Cek apakah file sudah ada sebelumnya
                $this->load->library('upload', $config_st, 'st_upload');
                $this->st_upload->initialize($config_st);

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
        log_message('debug', 'ADMIN PROSES PENGAJUAN KUOTA - Method dipanggil untuk id_pengajuan: ' . $id_pengajuan);
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Proses Pengajuan Kuota';
        $admin_user = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['user'] = $admin_user;

        // Ambil data pengajuan, termasuk nama_barang_kuota dan data perusahaan sebelum ada perubahan kuota
        $this->db->select('upk.*, upr.NamaPers, upr.initial_quota as initial_quota_umum_sebelum, upr.remaining_quota as remaining_quota_umum_sebelum, u.email as user_email');
        $this->db->from('user_pengajuan_kuota upk');
        $this->db->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $this->db->join('user u', 'upk.id_pers = u.id', 'left');
        $this->db->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $this->db->get()->row_array();
        log_message('debug', 'ADMIN PROSES PENGAJUAN KUOTA - Data pengajuan yang diambil: ' . print_r($data['pengajuan'], true));

        if (!$data['pengajuan'] || ($data['pengajuan']['status'] != 'pending' && $data['pengajuan']['status'] != 'diproses')) {
            $pesan_error_awal = 'Pengajuan kuota tidak ditemukan atau statusnya tidak memungkinkan untuk diproses (Status saat ini: ' . ($data['pengajuan']['status'] ?? 'Tidak Diketahui') . '). Hanya status "pending" atau "diproses" yang bisa dilanjutkan.';
            log_message('error', 'ADMIN PROSES PENGAJUAN KUOTA - Validasi awal gagal: ' . $pesan_error_awal);
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">' . $pesan_error_awal . '</div>');
            redirect('admin/daftar_pengajuan_kuota');
            return;
        }

        // Aturan validasi form
        $this->form_validation->set_rules('status_pengajuan', 'Status Pengajuan', 'required|in_list[approved,rejected,diproses]');
        if ($this->input->post('status_pengajuan') == 'approved') {
            $this->form_validation->set_rules('approved_quota', 'Kuota Disetujui', 'trim|required|numeric|greater_than[0]');
            $this->form_validation->set_rules('nomor_sk_petugas', 'Nomor Surat Keputusan', 'trim|required|max_length[100]');
            $this->form_validation->set_rules('tanggal_sk_petugas', 'Tanggal Surat Keputusan', 'trim|required');
        } else {
            $this->form_validation->set_rules('approved_quota', 'Kuota Disetujui', 'trim|numeric');
            $this->form_validation->set_rules('nomor_sk_petugas', 'Nomor Surat Keputusan', 'trim|max_length[100]');
            $this->form_validation->set_rules('tanggal_sk_petugas', 'Tanggal Surat Keputusan', 'trim');
        }
        $this->form_validation->set_rules('admin_notes', 'Catatan Admin', 'trim');
        // Tambahkan validasi untuk file_sk_petugas jika wajib saat approved dan belum ada file lama
        if ($this->input->post('status_pengajuan') == 'approved' && empty($data['pengajuan']['file_sk_petugas']) && empty($_FILES['file_sk_petugas']['name'])) {
            $this->form_validation->set_rules('file_sk_petugas', 'File SK Petugas', 'required');
        }


        if ($this->form_validation->run() == false) {
            log_message('debug', 'ADMIN PROSES PENGAJUAN KUOTA - Validasi Form Gagal. Errors: ' . validation_errors() . ' POST Data: ' . print_r($this->input->post(), true));
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/proses_pengajuan_kuota_form', $data);
            $this->load->view('templates/footer', $data);
        } else {
            log_message('debug', 'ADMIN PROSES PENGAJUAN KUOTA - Validasi Form Sukses. Memproses data...');
            $status_pengajuan = $this->input->post('status_pengajuan');
            $approved_quota_input = ($status_pengajuan == 'approved') ? (float)$this->input->post('approved_quota') : 0;
            $nomor_sk_petugas = $this->input->post('nomor_sk_petugas');
            $tanggal_sk_petugas = $this->input->post('tanggal_sk_petugas'); // Ambil tanggal SK
            $admin_notes = $this->input->post('admin_notes');

            $data_update_pengajuan = [
                'status' => $status_pengajuan,
                'admin_notes' => $admin_notes,
                'processed_date' => date('Y-m-d H:i:s'),
                'nomor_sk_petugas' => $nomor_sk_petugas,
                'tanggal_sk_petugas' => !empty($tanggal_sk_petugas) ? $tanggal_sk_petugas : null, // Simpan tanggal SK (jika ada kolomnya)
                'approved_quota' => $approved_quota_input
            ];

            // Proses Upload File SK
            $nama_file_sk = $data['pengajuan']['file_sk_petugas'] ?? null;
            if (($status_pengajuan == 'approved' || $status_pengajuan == 'rejected') && isset($_FILES['file_sk_petugas']) && $_FILES['file_sk_petugas']['error'] != UPLOAD_ERR_NO_FILE) {
                $upload_dir_sk = './uploads/sk_kuota/';
                if (!is_dir($upload_dir_sk)) { @mkdir($upload_dir_sk, 0777, true); }
                if (!is_writable($upload_dir_sk)) { /* ... error handling ... */ redirect('admin/proses_pengajuan_kuota/' . $id_pengajuan); return; }
                
                $config_sk['upload_path']   = $upload_dir_sk;
                $config_sk['allowed_types'] = 'pdf|jpg|png|jpeg';
                $config_sk['max_size']      = '2048';
                $config_sk['encrypt_name']  = TRUE;
                $this->load->library('upload', $config_sk, 'sk_upload_instance'); // Gunakan nama instance berbeda
                $this->sk_upload_instance->initialize($config_sk);

                if ($this->sk_upload_instance->do_upload('file_sk_petugas')) {
                    if (!empty($data['pengajuan']['file_sk_petugas']) && file_exists($upload_dir_sk . $data['pengajuan']['file_sk_petugas'])) {
                        @unlink($upload_dir_sk . $data['pengajuan']['file_sk_petugas']);
                    }
                    $nama_file_sk = $this->sk_upload_instance->data('file_name');
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">File SK Upload Error: ' . $this->sk_upload_instance->display_errors('', '') . '</div>');
                    redirect('admin/proses_pengajuan_kuota/' . $id_pengajuan); return;
                }
            }
            $data_update_pengajuan['file_sk_petugas'] = $nama_file_sk;

            // Update tabel user_pengajuan_kuota
            $this->db->where('id', $id_pengajuan);
            $this->db->update('user_pengajuan_kuota', $data_update_pengajuan);
            log_message('debug', 'ADMIN PROSES PENGAJUAN KUOTA - user_pengajuan_kuota diupdate. Affected: ' . $this->db->affected_rows());

            if ($status_pengajuan == 'approved' && $approved_quota_input > 0) {
                $id_pers_terkait = $data['pengajuan']['id_pers'];
                $nama_barang_diajukan = $data['pengajuan']['nama_barang_kuota']; // Harus ada di $data['pengajuan']

                if ($id_pers_terkait && !empty($nama_barang_diajukan)) {
                    $sisa_kuota_umum_sebelum_tambah = (float)($data['pengajuan']['remaining_quota_umum_sebelum'] ?? 0);

                    $data_kuota_barang = [
                        'id_pers' => $id_pers_terkait,
                        'id_pengajuan_kuota' => $id_pengajuan,
                        'nama_barang' => $nama_barang_diajukan,
                        'initial_quota_barang' => $approved_quota_input,
                        'remaining_quota_barang' => $approved_quota_input,
                        'nomor_skep_asal' => $nomor_sk_petugas,
                        'tanggal_skep_asal' => !empty($tanggal_sk_petugas) ? $tanggal_sk_petugas : null,
                        'status_kuota_barang' => 'active',
                        'dicatat_oleh_user_id' => $admin_user['id'],
                        'waktu_pencatatan' => date('Y-m-d H:i:s')
                    ];
                    $this->db->insert('user_kuota_barang', $data_kuota_barang);
                    $id_kuota_barang_baru = $this->db->insert_id();
                    log_message('info', 'ADMIN PROSES PENGAJUAN KUOTA - Data kuota barang baru disimpan. ID: ' . $id_kuota_barang_baru . ' untuk barang: ' . $nama_barang_diajukan);

                    if ($id_kuota_barang_baru) {
                        $this->_log_perubahan_kuota(
                            $id_pers_terkait, 'penambahan', $approved_quota_input,
                            0, // Kuota barang spesifik ini sebelumnya adalah 0 karena ini entri baru
                            $approved_quota_input, // Sisa sesudah = jumlah yang baru ditambahkan
                            'Persetujuan Pengajuan Kuota. Barang: ' . $nama_barang_diajukan . '. No. SK: ' . ($nomor_sk_petugas ?: '-'),
                            $id_pengajuan, 'pengajuan_kuota_disetujui', $admin_user['id'],
                            $nama_barang_diajukan, $id_kuota_barang_baru
                        );
                    }
                } else {
                    log_message('error', 'ADMIN PROSES PENGAJUAN KUOTA - Gagal menambah kuota barang: id_pers atau nama_barang_kuota kosong. ID Pers: ' . $id_pers_terkait . ', Nama Barang: ' . $nama_barang_diajukan);
                }
            }

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Pengajuan kuota telah berhasil diproses!</div>');
            redirect('admin/daftar_pengajuan_kuota');
        }
    }

    public function print_pengajuan_kuota($id_pengajuan)
    {
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

        $this->load->view('user/FormPengajuanKuota_print', $data);
    }

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


        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/detail_pengajuan_kuota_view', $data); // Load view baru untuk Admin
        $this->load->view('templates/footer');
    }

    public function download_sk_kuota_admin($id_pengajuan)
    {
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

        // Ambil semua user kecuali admin yang sedang login
        $this->db->select('u.*, ur.role as role_name');
        $this->db->from('user u');
        $this->db->join('user_role ur', 'u.role_id = ur.id', 'left');
        // $this->db->where('u.id !=', $data['user']['id']);
        $this->db->order_by('u.name', 'ASC');
        $data['users_list'] = $this->db->get()->result_array();

        // Ambil daftar role untuk dropdown di form tambah/edit (jika diperlukan)
        $data['roles'] = $this->db->get('user_role')->result_array();


        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/manajemen_user_view', $data);
        $this->load->view('templates/footer');
    }

    
public function tambah_user_petugas()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Tambah User Petugas Baru'; // Judul bisa dinamis jika untuk role lain juga
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

        // Default role yang akan ditambahkan adalah Petugas (ID 3)
        $target_role_id = 3; // Untuk Petugas
        // $target_role_id = $this->input->get('role_type'); // Contoh jika role dipilih dari link/parameter

        $data['target_role_info'] = $this->db->get_where('user_role', ['id' => $target_role_id])->row_array();

        if (!$data['target_role_info']) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Role target tidak valid.</div>');
            redirect('admin/manajemen_user');
            return;
        }
        $data['subtitle'] = 'Tambah User ' . htmlspecialchars($data['target_role_info']['role']);


        $this->form_validation->set_rules('name', 'Nama Lengkap', 'required|trim');
        // NIP akan disimpan di kolom 'email' dan harus unik
        $this->form_validation->set_rules('nip', 'NIP (Nomor Induk Pegawai)', 'required|trim|numeric|is_unique[user.email]', [
            'is_unique' => 'NIP ini sudah terdaftar sebagai login identifier.',
            'numeric'   => 'NIP harus berupa angka.'
        ]);
        $this->form_validation->set_rules('password', 'Password Awal', 'required|trim|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Konfirmasi Password', 'required|trim|matches[password]');
        
        if ($target_role_id == 3) { // Asumsi Role ID 3 adalah Petugas
            // $this->form_validation->set_rules('nip_detail_petugas', 'NIP (Detail Petugas)', 'trim|required|numeric'); // NIP di tabel petugas harus sama dengan NIP login
            $this->form_validation->set_rules('jabatan_petugas', 'Jabatan Petugas', 'trim|required');
        }

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/form_tambah_user_petugas', $data);
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
                    ];
                    $this->db->insert('petugas', $petugas_data_to_insert);
                }

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
        $this->load->view('admin/form_ganti_password_user', $data);
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
                'email' => htmlspecialchars($input_login_identifier, true), 
            ];

            if (!$is_editing_main_admin) {
                $update_data_user['role_id'] = (int)$this->input->post('role_id');
                $update_data_user['is_active'] = (int)$this->input->post('is_active');
            } else {
                $update_data_user['role_id'] = (int)$data['target_user_data']['role_id']; 
                $update_data_user['is_active'] = (int)$data['target_user_data']['is_active']; 
            }

            $this->db->where('id', $target_user_id);
            $this->db->update('user', $update_data_user);

            $new_role_id = (int)($this->input->post('role_id') ?? $data['target_user_data']['role_id']);

            // Jika role baru atau role saat ini adalah Petugas (ID 3)
            // dan pastikan tabel 'petugas' memiliki kolom 'id_user' sebagai foreign key ke 'user.id'
            if ($new_role_id == 3) { 
                if ($this->db->field_exists('id_user', 'petugas')) {
                    $petugas_detail = $this->db->get_where('petugas', ['id_user' => $target_user_id])->row_array();
                    $data_petugas_update = [
                        'Nama' => $update_data_user['name'],
                        'NIP' => $update_data_user['email'], // NIP diambil dari user.email (yang berisi NIP)
                        'Jabatan' => $this->input->post('jabatan_petugas_edit')
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
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Data user '.htmlspecialchars($update_data_user['name']).' berhasil diupdate.</div>');
            redirect('admin/manajemen_user');
        }
    }


// Di application/controllers/Admin.php

public function histori_kuota_perusahaan($id_pers = 0)
{
    log_message('debug', 'ADMIN HISTORI KUOTA - Method dipanggil dengan id_pers: ' . $id_pers);

    if ($id_pers == 0 || !is_numeric($id_pers)) {
        $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Perusahaan tidak valid.</div>');
        redirect('admin/monitoring_kuota');
        return;
    }

    $data['title'] = 'Returnable Package';
    $data['subtitle'] = 'Histori & Detail Kuota Perusahaan';
    $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();

    // Ambil data perusahaan umum
    $this->db->select('up.id_pers, up.NamaPers, up.npwp, u.email as email_kontak, u.name as nama_kontak_user');
    $this->db->from('user_perusahaan up');
    $this->db->join('user u', 'up.id_pers = u.id', 'left');
    $this->db->where('up.id_pers', $id_pers);
    $data['perusahaan'] = $this->db->get()->row_array();
    log_message('debug', 'ADMIN HISTORI KUOTA - Data Perusahaan: ' . print_r($data['perusahaan'], true));

    if (!$data['perusahaan']) {
        $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Data perusahaan tidak ditemukan untuk ID: ' . $id_pers . '</div>');
        redirect('admin/monitoring_kuota');
        return;
    }
    $data['id_pers_untuk_histori'] = $id_pers;

    // Ambil daftar kuota per jenis barang untuk perusahaan ini
    $this->db->select('ukb.*'); // Ambil semua kolom dari user_kuota_barang
    $this->db->from('user_kuota_barang ukb');
    $this->db->where('ukb.id_pers', $id_pers);
    $this->db->order_by('ukb.nama_barang ASC, ukb.waktu_pencatatan DESC');
    $data['daftar_kuota_barang_perusahaan'] = $this->db->get()->result_array();
    log_message('debug', 'ADMIN HISTORI KUOTA - Query Daftar Kuota Barang: ' . $this->db->last_query());
    log_message('debug', 'ADMIN HISTORI KUOTA - Data Daftar Kuota Barang: ' . print_r($data['daftar_kuota_barang_perusahaan'], true));


    // Ambil data log transaksi kuota untuk perusahaan ini (yang sudah ada nama barangnya)
    $this->db->select('lk.*, u_admin.name as nama_pencatat');
    $this->db->from('log_kuota_perusahaan lk');
    $this->db->join('user u_admin', 'lk.dicatat_oleh_user_id = u_admin.id', 'left');
    $this->db->where('lk.id_pers', $id_pers);
    $this->db->order_by('lk.tanggal_transaksi', 'DESC');
    $this->db->order_by('lk.id_log', 'DESC');
    $data['histori_kuota_transaksi'] = $this->db->get()->result_array(); // Ganti nama variabel agar tidak konflik
    log_message('debug', 'ADMIN HISTORI KUOTA - Query Log Transaksi: ' . $this->db->last_query());
    log_message('debug', 'ADMIN HISTORI KUOTA - Data Log Transaksi: ' . print_r($data['histori_kuota_transaksi'], true));


    $this->load->view('templates/header', $data);
    $this->load->view('templates/sidebar', $data);
    $this->load->view('templates/topbar', $data);
    $this->load->view('admin/histori_kuota_perusahaan_view', $data);
    $this->load->view('templates/footer');
}

public function detail_permohonan_admin($id_permohonan = 0)
    {
        // Aktifkan logging di awal method untuk memastikan method ini terpanggil
        log_message('debug', 'DETAIL PERMOHONAN ADMIN - Method dipanggil dengan id_permohonan: ' . $id_permohonan);

        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            log_message('error', 'DETAIL PERMOHONAN ADMIN - ID Permohonan tidak valid: ' . $id_permohonan);
            redirect('admin/permohonanMasuk');
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
            redirect('admin/permohonanMasuk');
            return;
        }

        // 2. Ambil data LHP (jika ada)
        $data['lhp_detail'] = $this->db->get_where('lhp', ['id_permohonan' => $id_permohonan])->row_array();
        log_message('debug', 'DETAIL PERMOHONAN ADMIN - Data LHP: ' . print_r($data['lhp_detail'], true));

        // 3. Ambil data barang yang diajukan

        // Load view
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/detail_permohonan_admin_view', $data);
        $this->load->view('templates/footer');
    }

} // End class Admin