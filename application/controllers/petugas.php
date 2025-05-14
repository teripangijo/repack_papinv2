<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Petugas extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('upload'); // Untuk upload file LHP/Dokumentasi
        $this->load->helper(array('url', 'form'));
        if (!isset($this->db)) {
             $this->load->database();
        }
        $this->_check_auth_petugas(); // Fungsi helper untuk otentikasi & otorisasi Petugas
    }

    private function _check_auth_petugas()
    {
        if (!$this->session->userdata('email')) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Please login to continue.</div>');
            redirect('auth');
            exit;
        }
        if ($this->session->userdata('role_id') != 3) { // Role ID 3 untuk Petugas
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Access Denied! You are not authorized.</div>');
            // Redirect ke dashboard sesuai role atau ke blocked
            $role_id_session = $this->session->userdata('role_id');
            if ($role_id_session == 1) redirect('admin');
            elseif ($role_id_session == 2) redirect('user');
            elseif ($role_id_session == 4) redirect('monitoring'); // Jika ada controller monitoring
            else redirect('auth/blocked');
            exit;
        }
        // Cek apakah ada force_change_password (jika diimplementasikan)
        if ($this->session->userdata('force_change_password') == 1 && $this->router->fetch_method() != 'force_change_password_page') {
             // $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">You must change your password before continuing.</div>');
             // redirect('petugas/force_change_password_page'); // Buat halaman ini
             // exit;
        }
    }

    public function index() // Dashboard Petugas
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Dashboard Petugas';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $petugas_id = $data['user']['id']; // Asumsi ID Petugas sama dengan User ID

        // Contoh data untuk dashboard petugas: Jumlah permohonan yang perlu direkam LHP
        $this->db->where('petugas', $petugas_id);
        $this->db->where('status', '1'); // Status '1' = Penunjukan Pemeriksa (siap direkam LHP)
        $data['jumlah_tugas_lhp'] = $this->db->count_all_results('user_permohonan');

        // Jumlah LHP yang sudah direkam oleh petugas ini
        $this->db->where('id_petugas_pemeriksa', $petugas_id);
        $data['jumlah_lhp_selesai'] = $this->db->count_all_results('lhp');


        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data); // Sidebar perlu disesuaikan untuk role Petugas
        $this->load->view('templates/topbar', $data);
        $this->load->view('petugas/dashboard_petugas_view', $data); // Buat view ini
        $this->load->view('templates/footer');
    }

    public function daftar_pemeriksaan()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Pemeriksaan Ditugaskan';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $petugas_id = $data['user']['id'];

        $this->db->select('up.*, upr.NamaPers, u_pemohon.name as nama_pemohon');
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left'); // Untuk nama pemohon
        $this->db->where('up.petugas', $petugas_id); // Hanya permohonan yang ditugaskan ke petugas ini
        $this->db->where('up.status', '1'); // Status '1' = Penunjukan Pemeriksa (siap direkam LHP)
        $this->db->order_by('up.TglSuratTugas DESC, up.WaktuPenunjukanPetugas DESC');
        $data['daftar_tugas'] = $this->db->get()->result_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('petugas/daftar_pemeriksaan_view', $data); // Buat view ini
        $this->load->view('templates/footer');
    }

    public function rekam_lhp($id_permohonan = 0)
    {
        if ($id_permohonan == 0) {
            redirect('petugas/daftar_pemeriksaan');
            return;
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Perekaman Laporan Hasil Pemeriksaan (LHP)';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $petugas_id = $data['user']['id'];

        // Ambil data permohonan yang akan direkam LHP-nya
        // Pastikan permohonan ini memang ditugaskan ke petugas yang login dan statusnya sesuai
        $this->db->select('up.*, upr.NamaPers, upr.npwp, u_pemohon.name as nama_pemohon');
        $this->db->from('user_permohonan up');
        $this->db->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $this->db->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $this->db->where('up.id', $id_permohonan);
        $this->db->where('up.petugas', $petugas_id);
        $this->db->where('up.status', '1'); // Hanya bisa rekam LHP jika statusnya 'Penunjukan Pemeriksa'
        $permohonan = $this->db->get()->row_array();

        if (!$permohonan) {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan, tidak ditugaskan kepada Anda, atau sudah diproses LHP-nya.</div>');
            redirect('petugas/daftar_pemeriksaan');
            return;
        }
        $data['permohonan'] = $permohonan;

        // Cek apakah LHP sudah pernah direkam untuk permohonan ini
        $existing_lhp = $this->db->get_where('lhp', ['id_permohonan' => $id_permohonan])->row_array();
        if ($existing_lhp) {
            $this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">LHP untuk permohonan ini sudah pernah direkam. Anda bisa mengeditnya jika diperlukan (fitur edit belum dibuat).</div>');
            // redirect('petugas/edit_lhp/' . $existing_lhp['id']); // Jika ada fitur edit LHP
            redirect('petugas/daftar_pemeriksaan');
            return;
        }

        // Validasi form LHP
        $this->form_validation->set_rules('tanggal_lhp', 'Tanggal LHP', 'required');
        $this->form_validation->set_rules('catatan_pemeriksaan', 'Catatan Hasil Pemeriksaan', 'required|trim');
        $this->form_validation->set_rules('jumlah_barang_benar', 'Jumlah Barang Sesuai/Benar', 'required|numeric|greater_than_equal_to[0]');
        // Validasi untuk file dokumentasi foto (bisa dibuat wajib atau opsional)
        // Jika wajib dan tidak ada file, form_validation->run() akan false jika ada rule callback.
        if (empty($_FILES['file_dokumentasi_foto']['name'])) {
             // $this->form_validation->set_rules('file_dokumentasi_foto', 'Dokumentasi Foto', 'required'); // Ini akan error jika file kosong
        }


        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('petugas/form_rekam_lhp_view', $data); // Buat view ini
            $this->load->view('templates/footer');
        } else {
            $nama_file_dokumentasi = null;
            // Proses Upload File Dokumentasi Foto jika ada
            if (isset($_FILES['file_dokumentasi_foto']) && $_FILES['file_dokumentasi_foto']['error'] != UPLOAD_ERR_NO_FILE) {
                $upload_dir_doc = 'uploads/dokumentasi_lhp/';
                $upload_path_doc = FCPATH . $upload_dir_doc;
                if (!is_dir($upload_path_doc)) { @mkdir($upload_path_doc, 0777, true); }

                if (!is_writable($upload_path_doc)) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload error: Direktori Dokumentasi LHP tidak writable.</div>');
                    redirect('petugas/rekam_lhp/' . $id_permohonan);
                    return;
                }

                $config_doc['upload_path']   = $upload_path_doc;
                $config_doc['allowed_types'] = 'jpg|png|jpeg'; // Hanya gambar
                $config_doc['max_size']      = '2048'; // 2MB
                $config_doc['encrypt_name']  = TRUE;

                $this->upload->initialize($config_doc, TRUE); // Reset config untuk upload ini

                if ($this->upload->do_upload('file_dokumentasi_foto')) {
                    $nama_file_dokumentasi = $this->upload->data('file_name');
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Upload File Dokumentasi Foto Gagal: ' . $this->upload->display_errors('', '') . '</div>');
                    redirect('petugas/rekam_lhp/' . $id_permohonan);
                    return;
                }
            }
            // Jika file dokumentasi foto wajib:
            // elseif (empty($nama_file_dokumentasi)) {
            //    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">File Dokumentasi Foto wajib diupload.</div>');
            //    redirect('petugas/rekam_lhp/' . $id_permohonan);
            //    return;
            // }


            $data_lhp = [
                'id_permohonan' => $id_permohonan,
                'id_petugas_pemeriksa' => $petugas_id,
                'tanggal_lhp' => $this->input->post('tanggal_lhp'),
                'catatan_pemeriksaan' => $this->input->post('catatan_pemeriksaan'),
                'JumlahBenar' => (int)$this->input->post('jumlah_barang_benar'),
                'file_dokumentasi_foto' => $nama_file_dokumentasi,
                'waktu_rekam_lhp' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('lhp', $data_lhp);
            $id_lhp_baru = $this->db->insert_id();

            if ($id_lhp_baru) {
                // Update status permohonan menjadi 'LHP Direkam'
                $this->db->where('id', $id_permohonan);
                $this->db->update('user_permohonan', ['status' => '2']); // Status '2' = LHP Direkam

                // Logika Pemotongan Kuota (dipindahkan ke Admin::prosesSurat setelah LHP disetujui)
                // Jika pemotongan dilakukan langsung oleh petugas setelah rekam LHP:
                /*
                $jumlah_barang_disetujui_lhp = (int)$this->input->post('jumlah_barang_benar');
                if ($jumlah_barang_disetujui_lhp > 0) {
                    $perusahaan_pemohon = $this->db->get_where('user_perusahaan', ['id_pers' => $permohonan['id_pers']])->row_array();
                    if ($perusahaan_pemohon && isset($perusahaan_pemohon['remaining_quota'])) {
                        $sisa_kuota_lama = (int)$perusahaan_pemohon['remaining_quota'];
                        $sisa_kuota_baru = $sisa_kuota_lama - $jumlah_barang_disetujui_lhp;
                        if ($sisa_kuota_baru < 0) { $sisa_kuota_baru = 0; }
                        $this->db->where('id_pers', $permohonan['id_pers']);
                        $this->db->update('user_perusahaan', ['remaining_quota' => $sisa_kuota_baru]);
                        log_message('info', 'Quota updated by Petugas for id_pers: ' . $permohonan['id_pers'] . '. LHP Used: ' . $jumlah_barang_disetujui_lhp . '. Remaining: ' . $sisa_kuota_baru);
                    }
                }
                */

                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">LHP berhasil direkam untuk permohonan ID ' . $id_permohonan . '. Status permohonan diubah menjadi "LHP Direkam".</div>');
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Gagal menyimpan data LHP. Silakan coba lagi.</div>');
            }
            redirect('petugas/daftar_pemeriksaan');
        }
    }

    // Tambahkan method untuk force_change_password_page jika diperlukan
    // public function force_change_password_page() { ... }
}