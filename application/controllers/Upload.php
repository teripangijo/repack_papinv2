<?php

class Upload extends CI_Controller {

        public function __construct()
        {
                parent::__construct();
                $this->load->helper(array('form', 'url'));
        }

        public function index()
        {
                $this->load->view('admin/upload_form', array('error' => ' ' ));
        }

        public function do_upload()
        {
                $config['upload_path']          = './ttd/';
                $config['allowed_types']        = 'jpeg|png|pdf';
                $config['max_size']             = 1000;
                $config['max_width']            = 10240;
                $config['max_height']           = 7680;

                $this->load->library('upload', $config);

                if ( ! $this->upload->do_upload('userfile'))
                {
                        $error = array('error' => $this->upload->display_errors());

                        $this->load->view('admin/upload_form', $error);
                }
                else
                {       
                        $name = $this->upload->data('file_name');
                        var_dump($name);
                        // $data = array('upload_data' => $this->upload->data());

                        // $this->load->view('admin/upload_success', $data);
                }
        }
}
