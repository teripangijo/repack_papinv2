<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_loggedin();
        $this->load->helper('repack_helper');
        $this->load->helper('form');
        $this->load->helper('url');
        $this->load->library('upload');
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
        $this->load->view('admin/index', $data);
        $this->load->view('templates/footer');
    }

    public function role()
    {
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Role';
        $data['role'] = $this->db->get('user_role')->result_array();
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/role', $data);
        $this->load->view('templates/footer');
    }

    public function roleAccess($role_id)
    {
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Role Access';
        $data['role'] = $this->db->get_where('user_role',['id' => $role_id])->row_array();
        $this->db->where('id !=', 1);
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

        if ($result->num_rows() < 1){
            $this->db->insert('user_access_menu', $data);
        }else{
            $this->db->delete('user_access_menu', $data);
        }

        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Access Changed!</div>');
    }

    public function permohonanMasuk()
    {
        $data['menu'] = $this->db->get_where('user_menu')->result_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['permohonan'] = $this->db->get_where('user_permohonan',)->result_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Permohonan Masuk';

        $this->form_validation->set_rules('menu', 'Menu', 'required');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/permohonan-masuk', $data);
            $this->load->view('templates/footer');
        } else {
            $this->db->insert('user_menu', ['menu' => $this->input->post('menu')]);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Menu has been added!</div>');
            redirect('menu');
        }
    }

    public function cetakLHP($id)
    {
        $data['permohonan'] = $this->db->get_where('user_permohonan', ['id' => $id])->row_array();
        $data['lhp'] = $this->db->get_where('lhp', ['id_permohonan' => $id])->row_array();
        $data['petugas'] = $this->db->get_where('petugas', ['id' => $data['permohonan']['petugas']])->row_array();
        // var_dump($data);
        $this->load->library('pdf');
        $this->pdf->setPaper('A4', 'potrait');
        $this->pdf->filename = "print.pdf";
        $this->pdf->load_view('user/LHP', $data);
    }

    public function konsepST($id)
    {
        $data['permohonan'] = $this->db->get_where('user_permohonan', ['id' => $id])->row_array();
        $data['petugas'] = $this->db->get_where('petugas', ['id' => $data['permohonan']['petugas']])->row_array();
        // var_dump($data);
        $this->load->view('user/KonsepST',$data);
    }

    public function konsepND($id)
    {
        $data['permohonan'] = $this->db->get_where('user_permohonan', ['id' => $id])->row_array();
        $data['petugas'] = $this->db->get_where('petugas', ['id' => $data['permohonan']['petugas']])->row_array();
        $data['lhp'] = $this->db->get_where('lhp', ['id_permohonan' => $data['permohonan']['id']])->row_array();
        // var_dump($data);
        $this->load->view('user/ND', $data);
    }

    public function konsepSurat($id)
    {
        $data['permohonan'] = $this->db->get_where('user_permohonan', ['id' => $id])->row_array();
        $data['petugas'] = $this->db->get_where('petugas', ['id' => $data['permohonan']['petugas']])->row_array();
        $data['lhp'] = $this->db->get_where('lhp', ['id_permohonan' => $data['permohonan']['id']])->row_array();
        // var_dump($data);
        $this->load->view('user/KonsepSurat', $data);
    }

    public function proses($id)
    {
        $data['menu'] = $this->db->get_where('user_menu')->result_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['permohonan'] = $this->db->get_where('user_permohonan', ['id' => $id])->row_array();
        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' =>$data['permohonan']['id_pers']])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Permohonan Masuk';

        $this->form_validation->set_rules('petugas', 'Petugas', 'required');

        if ($this->form_validation->run() == false) {
            // var_dump($data['user']);
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/proses', $data);
            $this->load->view('templates/footer');
        } else {
            $this->db->where('id', $id);
            $this->db->update('user_permohonan', [
                'petugas' => $this->input->post('petugas'),
                'status'  => '1'
            ]);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Petugas telah ditambahkan</div>');
            redirect('admin/permohonanMasuk');
        }
    }

    public function gantipetugas($id)
    {
        $data['menu'] = $this->db->get_where('user_menu')->result_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['permohonan'] = $this->db->get_where('user_permohonan', ['id' => $id])->row_array();
        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $data['permohonan']['id_pers']])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Permohonan Masuk';

        $this->form_validation->set_rules('petugas', 'Petugas', 'required');

        if ($this->form_validation->run() == false) {
            // var_dump($data['user']);
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/proses', $data);
            $this->load->view('templates/footer');
        } else {
            $this->db->where('id', $id);
            $this->db->update('user_permohonan', [
                'petugas' => $this->input->post('petugas'),
                'status'  => '1'
            ]);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Petugas telah ditambahkan</div>');
            redirect('admin/permohonanMasuk');
        }
    }

    public function prosesLHP($id)
    {
        $data['menu'] = $this->db->get_where('user_menu')->result_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['permohonan'] = $this->db->get_where('user_permohonan', ['id' => $id])->row_array();
        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $data['permohonan']['id_pers']])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Rekam LHP';

        $this->form_validation->set_rules('TglPeriksa', 'Tanggal Periksa', 'required');
        $this->form_validation->set_rules('JumlahBenar', 'Jumlah Benar', 'required');
        $this->form_validation->set_rules('Kondisi', 'Kondisi', 'required');
        $this->form_validation->set_rules('Kesimpulan', 'Kesimpulan', 'required');
        $this->form_validation->set_rules('hasil', 'Hasil Keputusan', 'required');
        $this->form_validation->set_rules('tgl_st', 'Tanggal ST', 'required');
        $this->form_validation->set_rules('wkmulai', 'Waktu Mulai', 'required');
        $this->form_validation->set_rules('wkselesai', 'Waktu Selesai', 'required');
        $this->form_validation->set_rules('pemilik', 'Pemilik Barang', 'required');
        $this->form_validation->set_rules('linkST', 'Link ST', 'required');

        if ($this->form_validation->run() == false) {
            // var_dump($data['user']);
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/prosesLHP', $data);
            $this->load->view('templates/footer');
        } else {
            $time = time();
            $timenow = date("Y-m-d h:i:sa", $time);
            $this->db->insert('lhp', [
                'TglPeriksa' => $this->input->post('TglPeriksa'),
                'wkmulai' => $this->input->post('wkmulai'),
                'wkselesai' => $this->input->post('wkselesai'),
                'JumlahBenar' => $this->input->post('JumlahBenar'),
                'Kondisi' => $this->input->post('Kondisi'),
                'Kesimpulan' => $this->input->post('Kesimpulan'),
                'nomorST' => $this->input->post('nomorST'),
                'tgl_st' => $this->input->post('tgl_st'),
                'hasil' => $this->input->post('hasil'),
                'pemilik' => $this->input->post('pemilik'),
                'id_permohonan'  => $id,
                'time_stamp'  => $timenow,
            ]);
            $this->db->where('id', $id);
            $this->db->update('user_permohonan', [
                'status'  => '2',
                'linkST' => $this->input->post('linkST')
            ]);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">LHP telah direkam</div>');
            redirect('admin/permohonanMasuk');
        }
    }

    public function editLHP($id)
    {
        $data['menu'] = $this->db->get_where('user_menu')->result_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['permohonan'] = $this->db->get_where('user_permohonan', ['id' => $id])->row_array();
        $data['lhp'] = $this->db->get_where('lhp', ['id_permohonan' => $data['permohonan']['id']])->row_array();
        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $data['permohonan']['id_pers']])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Rekam LHP';

        $this->form_validation->set_rules('TglPeriksa', 'Tanggal Periksa', 'required');
        $this->form_validation->set_rules('JumlahBenar', 'Jumlah Benar', 'required');
        $this->form_validation->set_rules('Kondisi', 'Kondisi', 'required');
        $this->form_validation->set_rules('Kesimpulan', 'Kesimpulan', 'required');
        $this->form_validation->set_rules('hasil', 'Hasil Keputusan', 'required');
        $this->form_validation->set_rules('tgl_st', 'Tanggal ST', 'required');
        $this->form_validation->set_rules('wkmulai', 'Waktu Mulai', 'required');
        $this->form_validation->set_rules('wkselesai', 'Waktu Selesai', 'required');
        $this->form_validation->set_rules('pemilik', 'Pemilik Barang', 'required');

        if ($this->form_validation->run() == false) {
            // var_dump($data['user']);
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/editLHP', $data);
            $this->load->view('templates/footer');
        } else {
            $time = time();
            $timenow = date("Y-m-d h:i:sa", $time);
            $this->db->where('id_permohonan', $data['permohonan']['id']);
            $this->db->update('lhp', [
                'TglPeriksa' => $this->input->post('TglPeriksa'),
                'wkmulai' => $this->input->post('wkmulai'),
                'wkselesai' => $this->input->post('wkselesai'),
                'JumlahBenar' => $this->input->post('JumlahBenar'),
                'Kondisi' => $this->input->post('Kondisi'),
                'Kesimpulan' => $this->input->post('Kesimpulan'),
                'nomorST' => $this->input->post('nomorST'),
                'tgl_st' => $this->input->post('tgl_st'),
                'hasil' => $this->input->post('hasil'),
                'pemilik' => $this->input->post('pemilik'),
                'id_permohonan'  => $id,
                'time_stamp'  => $timenow
            ]);
            $this->db->where('id', $id);
            $this->db->update('user_permohonan', [
                'status'  => '2'
            ]);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">LHP telah direkam</div>');
            redirect('admin/permohonanMasuk');
        }
    }

    public function prosesSurat($id)
    {
        $data['menu'] = $this->db->get_where('user_menu')->result_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['permohonan'] = $this->db->get_where('user_permohonan', ['id' => $id])->row_array();
        $data['lhp'] = $this->db->get_where('lhp', ['id_permohonan' => $data['permohonan']['id']])->row_array();
        $data['user_perusahaan'] = $this->db->get_where('user_perusahaan', ['id_pers' => $data['permohonan']['id_pers']])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Penyelesaian Permohonan';

        $this->form_validation->set_rules('nomorSetuju', 'Nomor Surat', 'required');
        $this->form_validation->set_rules('tgl_S', 'Tanggal Surat', 'required');
        $this->form_validation->set_rules('nomorND', 'Nomor ND', 'required');
        $this->form_validation->set_rules('tgl_ND', 'Tanggal ND', 'required');
        $this->form_validation->set_rules('link', 'link Surat', 'required');
        $this->form_validation->set_rules('linkND', 'link ND', 'required');

        if ($this->form_validation->run() == false) {
            // var_dump($data['user']);
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('admin/prosesSurat', $data);
            $this->load->view('templates/footer');
        } else {
            // $ini = array(
            //     'nomorSetuju' => $this->input->post('nomorSetuju'),
            //     'tgl_S' => $this->input->post('tgl_S'),
            //     'nomorND' => $this->input->post('nomorND'),
            //     'tgl_ND' => $this->input->post('tgl_ND'),
            //     'link' => $this->input->post('link'),
            //     'time_selesai'  => $timenow,
            //     'status'  => '3'
            // );
            // var_dump($ini);

            $time = time();
            $timenow = date("Y-m-d h:i:sa", $time);
            $this->db->where('id', $id);
            $this->db->update('user_permohonan', [
                'nomorSetuju' => $this->input->post('nomorSetuju'),
                'tgl_S' => $this->input->post('tgl_S'),
                'nomorND' => $this->input->post('nomorND'),
                'tgl_ND' => $this->input->post('tgl_ND'),
                'link' => $this->input->post('link'),
                'linkND' => $this->input->post('linkND'),
                'time_selesai'  => $timenow,
                'status'  => '3'
            ]);


            $qawal = $data['user_perusahaan']['quota'];
            $jlhp = $data['lhp']['JumlahBenar'];
            $qakhir = (int)$qawal - (int)$jlhp;
            $quota = (int)$qakhir;

            $this->db->where('id_pers', $data['permohonan']['id_pers']);
            $this->db->update('user_perusahaan', [
                'quota'  => $quota
            ]);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Surat Persetujuan telah direkam</div>');
            redirect('admin/permohonanMasuk');
        }
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
        // var_dump($data);
        // $this->load->library('pdf');
        // $this->pdf->setPaper('A4', 'potrait');
        // $this->pdf->filename = "print.pdf";
        // $this->pdf->load_view('user/FormPermohonan', $data);

        $this->load->view('user/FormPermohonan', $data);
    }

    public function upload()
    {
        $data['menu'] = $this->db->get_where('user_menu')->result_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['perusahaan'] = $this->db->get_where('user_perusahaan',)->result_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Data Perusahaan Terdaftar';

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/upload', $data);
        $this->load->view('templates/footer');
    }

    public function uploadproses($id)
    {
        $data['menu'] = $this->db->get_where('user_menu')->result_array();
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['perusahaan'] = $this->db->get_where('user_perusahaan', ['id' => $id])->row_array();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Data Perusahaan Terdaftar';

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('admin/uploadproses', $data);
        $this->load->view('templates/footer');
    }

    public function prosesuploadnpwp($id)
    {
        if ($id == 1) {
            $nama ='sspb';
        } elseif ($id == 4) {
            $nama = 'shlb';
        } elseif ($id == 5) {
            $nama = 'pbj';
        }
        $data['perusahaan'] = $this->db->get_where('user_perusahaan', ['id' => $id])->row_array();
        // var_dump($data);
        $config['upload_path'] = './uploads/npwp/'. $nama .'/';
        $config['allowed_types'] = 'gif|jpeg|png|pdf';
        $config['max_size']     = '2048';

        $this->upload->initialize($config);
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('npwp')) {
            $error = array('error' => $this->upload->display_errors());

            $this->load->view('admin/upload_form', $error);
        } else {
            // $name = $this->upload->data('file_name');
            // var_dump($name);
            $this->db->where('id_pers', $data['perusahaan']['id_pers']);
            $this->db->update('user_perusahaan', [
                'npwp_files'  => $this->upload->data('file_name')
            ]);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">NPWP berhasil di Upload</div>');
            redirect('admin/uploadproses/'.$id);
        }
        
    }

    public function prosesuploadskep($id)
    {

        $data['perusahaan'] = $this->db->get_where('user_perusahaan', ['id' => $id])->row_array();
        // var_dump($data);
        $config['upload_path'] = './uploads/skep/'. $id .'/';
        $config['allowed_types'] = 'gif|jpeg|png|pdf';
        $config['max_size']     = '2048';

        $this->upload->initialize($config);
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('skep')) {
            $error = array('error' => $this->upload->display_errors());

            $this->load->view('admin/upload_form', $error);
        } else {
            // $name = $this->upload->data('file_name');
            // var_dump($name);
            $this->db->where('id_pers', $data['perusahaan']['id_pers']);
            $this->db->update('user_perusahaan', [
                'skep_files'  => $this->upload->data('file_name')
            ]);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">SKEP berhasil di Upload</div>');
            redirect('admin/uploadproses/
            ' . $id);
        }
    }

    public function prosesuploadttd($id)
    {

        $data['perusahaan'] = $this->db->get_where('user_perusahaan', ['id' => $id])->row_array();
        // var_dump($data);
        $config['upload_path'] = './uploads/ttd/' . $id . '/';
        $config['allowed_types'] = 'gif|jpeg|png|pdf';
        $config['max_size']     = '2048';

        $this->upload->initialize($config);
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('ttd')) {
            $error = array('error' => $this->upload->display_errors());

            $this->load->view('admin/upload_form', $error);
        } else {
            // $name = $this->upload->data('file_name');
            // var_dump($name);
            $this->db->where('id_pers', $data['perusahaan']['id_pers']);
            $this->db->update('user_perusahaan', [
                'ttd'  => $this->upload->data('file_name')
            ]);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">SKEP berhasil di Upload</div>');
            redirect('admin/uploadproses/
            ' . $id);
        }
    }
}
