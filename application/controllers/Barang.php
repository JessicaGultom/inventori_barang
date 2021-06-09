<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Barang extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        cek_login();

        $this->load->model('Admin_model', 'admin');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $data['title'] = "Barang";
        $data['barang'] = $this->admin->getBarang();
        $this->template->load('templates/dashboard', 'barang/data', $data);
    }

    private function _validasi()
    {
        $this->form_validation->set_rules('nama_barang', 'Nama Barang', 'required|trim');
        $this->form_validation->set_rules('jenis_id', 'Jenis Barang', 'required');
        $this->form_validation->set_rules('satuan_id', 'Satuan Barang', 'required');
    }

public function add(){
    $this->_validasi();

        if ($this->form_validation->run() == false) {
            $data['title'] = "Barang";
            $data['jenis'] = $this->admin->get('jenis');
            $data['satuan'] = $this->admin->get('satuan');

            // Mengenerate ID Barang
            $kode_terakhir = $this->admin->getMax('barang', 'id_barang');
            $kode_tambah = substr($kode_terakhir, -6, 6);
            $kode_tambah++;
            $number = str_pad($kode_tambah, 6, '0', STR_PAD_LEFT);
            $data['id_barang'] = 'B' . $number;

            $this->template->load('templates/dashboard', 'barang/add', $data);
        } else {
            $id_barang=$this->input->post('id_barang');
            $nama_barang=$this->input->post('nama_barang');
            $stok=$this->input->post('stok');
            $satuan_id=$this->input->post('satuan_id');
            $jenis_id=$this->input->post('jenis_id');
    
            $this->load->library('ciqrcode'); //pemanggilan library QR CODE
    
            $config['cacheable']    = true; //boolean, the default is true
            $config['cachedir']     = './assets/'; //string, the default is application/cache/
            $config['errorlog']     = './assets/'; //string, the default is application/logs/
            $config['imagedir']     = './assets/images/'; //direktori penyimpanan qr code
            $config['quality']      = true; //boolean, the default is true
            $config['size']         = '1024'; //interger, the default is 1024
            $config['black']        =    array(224,255,255); // array, default is array(255,255,255)
            $config['white']        = array(70,130,180); // array, default is array(0,0,0)
            $this->ciqrcode->initialize($config);
    
            $image_name = $id_barang.'.png'; //buat name dari qr code sesuai dengan nim
    
            $params['data'] = "http://localhost/ci_barang/barang/detail/". $id_barang; //data yang akan di jadikan QR CODE
            $params['level'] = 'H'; //H=High
            $params['size'] = 10;
            $params['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
            $this->ciqrcode->generate($params); // fungsi untuk generate QR CODE

            $this->admin->simpan_barang($id_barang,$nama_barang,$stok,$satuan_id,$jenis_id,$image_name); //simpan ke database
            
            redirect('barang');
        } //redirect ke barang usai simpan data
    }

   

    public function edit($getId)
    {
        $id = encode_php_tags($getId);
        $this->_validasi();

        if ($this->form_validation->run() == false) {
            $data['title'] = "Barang";
            $data['jenis'] = $this->admin->get('jenis');
            $data['satuan'] = $this->admin->get('satuan');
            $data['barang'] = $this->admin->get('barang', ['id_barang' => $id]);
            $this->template->load('templates/dashboard', 'barang/edit', $data);
        } else {
            $input = $this->input->post(null, true);
            $update = $this->admin->update('barang', 'id_barang', $id, $input);

            if ($update) {
                set_pesan('data berhasil disimpan');
                redirect('barang');
            } else {
                set_pesan('gagal menyimpan data');
                redirect('barang/edit/' . $id);
            }
        }
    }

    public function delete($getId)
    {
        $id = encode_php_tags($getId);
        if ($this->admin->delete('barang', 'id_barang', $id)) {
            set_pesan('data berhasil dihapus.');
        } else {
            set_pesan('data gagal dihapus.', false);
        }
        redirect('barang');
    }

    public function detail($getId)
    {
        $id = encode_php_tags($getId);
        $data['title'] = "Barang";
        $data['barang'] = $this->admin->get('barang', ['id_barang' => $id]);
        $this->template->load('templates/dashboard', 'barang/detail',$data); 
        
    }

    public function getstok($getId)
    {
        $id = encode_php_tags($getId);
        $query = $this->admin->cekStok($id);
        output_json($query);
    }
}
