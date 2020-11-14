<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Midtrans extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Midtrans_model');
    }


    public function index()
    {
        $data   = [
            'semuaproduk'   => $this->Midtrans_model->getallproduct(),
            'datakeranjang' => $this->Midtrans_model->getallkeranjang()
        ];
        $this->load->view('midtrans/index', $data);
    }

    public function simpan()
    {
        $produk     = $this->input->post('produk');
        $jumlah     = $this->input->post('jumlah');

        $datainsert = [
            'produk_id' => $produk,
            'jumlah'    => $jumlah,
            'status'    => 0,
        ];

        $insert     = $this->Midtrans_model->insert($datainsert);
        if ($insert > 0) {
            $this->session->set_flashdata('pesan', 'Data berhasil di simpan');
        } else {
            $this->session->set_flashdata('pesan', 'Server sedang sibuk, silahkan coba lagi');
        }
        redirect('midtrans');
    }
}

/* End of file Controllername.php */
