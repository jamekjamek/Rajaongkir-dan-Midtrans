<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Home_model');
        $this->keyrajaongkir = '32f693bc2c100e523f6a5b5220b010b4';
        $this->kabupatenrajaongkir = 'https://api.rajaongkir.com/starter/city?key=' . $this->keyrajaongkir;
    }


    public function index()
    {
        $this->form_validation->set_rules('kotaasalrajaongkir', 'Kabupaten/Kota Asal', 'trim|required');
        $this->form_validation->set_rules('kotatujuanrajaongkir', 'Kabupaten/Kota Tujuan', 'trim|required');
        $this->form_validation->set_rules('beratkirim', 'berat Pengiriman', 'trim|required|numeric');


        if ($this->form_validation->run() == FALSE) {
            $this->load->view('index');
        } else {
            $berat = $this->input->post('beratkirim');
            $beratkirim = $berat * 1000;
            if ($beratkirim > 30000) {
                $this->session->set_flashdata('pesan', 'Berat pengiriman maksimal 30 KG');
                redirect('home');
            } else {
                $datarajaongkir = json_decode(file_get_contents($this->kabupatenrajaongkir));
                $kotaasal = $this->input->post('kotaasalrajaongkir');
                $pisahKabnamakabupatendarikotaasal = str_replace('KAB. ', '', $kotaasal);
                $pisahKotapisahKabupatenhasilpisahkabupatenasal = str_replace('KOTA ', '', $pisahKabnamakabupatendarikotaasal);
                $namakabupatenbaruasal = ucwords(strtolower($pisahKotapisahKabupatenhasilpisahkabupatenasal));

                $kotatujuan = $this->input->post('kotatujuanrajaongkir');
                $pisahKabnamakabupatendarikotatujuan = str_replace('KAB. ', '', $kotatujuan);
                $pisahKotapisahKabupatenhasilpisahkabupatentujuan = str_replace('KOTA ', '', $pisahKabnamakabupatendarikotatujuan);
                $namakabupatenbarutujuan = ucwords(strtolower($pisahKotapisahKabupatenhasilpisahkabupatentujuan));

                $semuakabupatenrajaongkir = $datarajaongkir->rajaongkir->results;

                foreach ($semuakabupatenrajaongkir as $row) {
                    if ($namakabupatenbaruasal == $row->city_name) {
                        $origin = $row->city_id;
                    }
                    if ($namakabupatenbarutujuan == $row->city_name) {
                        $destination = $row->city_id;
                    }
                }
                if ($origin == null || $destination == null) {
                    $this->session->set_flashdata('pesan', 'Data kabupaten yang dipilih tidak ada di rajaongkir');
                    redirect('home');
                }
                $kurir = ['jne', 'pos', 'tiki'];
                $datakurir = [];
                foreach ($kurir as $value) {
                    $itemcourier = $this->_cost($origin, $destination, $beratkirim, $value);
                    array_push($datakurir, $itemcourier);
                }
                // echo "<pre>";
                // print_r($datakurir);
                // echo "</pre>";
                $data['hasil'] = $datakurir;
                $this->load->view('hasil', $data);
            }
        }
    }

    private function _cost($origin, $destination, $beratkirim, $value)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.rajaongkir.com/starter/cost",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "origin=$origin&destination=$destination&weight=$beratkirim&courier=$value",
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded",
                "key: " . $this->keyrajaongkir
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $data = json_decode($response);
            return $data;
        }
    }


    public function getDataKabupaten()
    {
        $kabupaten = $this->input->get('term');
        if ($kabupaten) {
            $getDataKabupaten = $this->Home_model->getDataKabupaten($kabupaten);
            foreach ($getDataKabupaten as $row) {
                $results[] = array(
                    'label' => $row['provinsi'] . ', ' . $row['kabupaten'] . ', Kecamatan ' . $row['kecamatan'],
                    'kabupaten' => $row['kabupaten']
                );
                $this->output->set_content_type('application/json')->set_output(json_encode($results));
            }
        }
    }

    public function getDataKabupatenRajaOngkir()
    {
        $kabupaten = json_decode(file_get_contents($this->kabupatenrajaongkir));
        $semuadatakabupaten = $kabupaten->rajaongkir->results;
        foreach ($semuadatakabupaten as $row) {
            echo "<pre>";
            print_r($row->city_name);
            echo "</pre>";
        }
        die;
    }

    public function getDataKabupatenDB()
    {
        $semuakabupaten = $this->db->get('m_kabupaten')->result_array();
        foreach ($semuakabupaten as $kabupaten) {
            $namakabupaten = $kabupaten['name'];
            $pisahKabnamakabupaten = str_replace('KAB. ', '', $namakabupaten);
            $pisahKotapisahKabupaten = str_replace('KOTA ', '', $pisahKabnamakabupaten);
            $namakabupatenbaru = ucwords(strtolower($pisahKotapisahKabupaten));
            echo "<pre>";
            print_r($namakabupatenbaru);
            echo "</pre>";
        }
        die;
    }
}
