<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Barangkeluar extends CI_Controller
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
        $data['title'] = "Barang keluar";
        $data['barangkeluar'] = $this->admin->getBarangkeluar();
        $this->template->load('templates/dashboard', 'barang_keluar/data', $data);
    }

    private function _validasi()
    {
        $this->form_validation->set_rules('tanggal_keluar', 'Tanggal Keluar', 'required|trim');
        $this->form_validation->set_rules('barang_id', 'Barang', 'required');

        $input = $this->input->post('barang_id', true);
        $stok = $this->admin->get('barang', ['id_barang' => $input])['stok'];
        $stok_valid = $stok + 1;

        $this->form_validation->set_rules(
            'jumlah_keluar',
            'Jumlah Keluar',
            "required|trim|numeric|greater_than[0]|less_than[{$stok_valid}]",
            [
                'less_than' => "Jumlah Keluar tidak boleh lebih dari {$stok}"
            ]
        );
    }

    public function add()
    {
        $this->_validasi();
        if ($this->form_validation->run() == false) {
            $data['title'] = "Barang Keluar";
            $data['barang'] = $this->admin->get('barang', null, ['stok >' => 0]);

            // Mendapatkan dan men-generate kode transaksi barang keluar
            $kode = 'T-BK-' . date('ymd');
            $kode_terakhir = $this->admin->getMax('barang_keluar', 'id_barang_keluar', $kode);
            $kode_tambah = substr($kode_terakhir, -5, 5);
            $kode_tambah++;
            $number = str_pad($kode_tambah, 5, '0', STR_PAD_LEFT);
            $data['id_barang_keluar'] = $kode . $number;

            $this->template->load('templates/dashboard', 'barang_keluar/add', $data);
        } else {
            $input = $this->input->post(null, true);
            $insert = $this->admin->insert('barang_keluar', $input);

            $this->load->library('ciqrcode'); //pemanggilan library QR CODE

            // $config['cacheable']    = true; //boolean, the default is true
            // $config['cachedir']     = './assets/'; //string, the default is application/cache/
            // $config['errorlog']     = './assets/'; //string, the default is application/logs/
            // $config['imagedir']     = './assets/images/'; //direktori penyimpanan qr code
            // $config['quality']      = true; //boolean, the default is true
            // $config['size']         = '1024'; //interger, the default is 1024
            // $config['black']        =    array(224,255,255); // array, default is array(255,255,255)
            // $config['white']        = array(70,130,180); // array, default is array(0,0,0)
            // $this->ciqrcode->initialize($config);

            // $image_name=$id_barang.'.png'; //buat name dari qr code sesuai dengan nim

            // $params['data'] = "http://localhost/ci_barang/barang_keluar/detail/". $id_barang; //data yang akan di jadikan QR CODE
            // $params['level'] = 'H'; //H=High
            // $params['size'] = 10;
            // $params['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
            // $this->ciqrcode->generate($params); // fungsi untuk generate QR CODE

            // $this->admin->simpan_barang($id_barang,$nama_barang,$stok,$satuan_id,$jenis_id,$image_name); //simpan ke database

            if ($insert) {
                set_pesan('data berhasil disimpan.');
                redirect('barangkeluar');
            } else {
                set_pesan('Opps ada kesalahan!');
                redirect('barangkeluar/add');
            }
        }
    }

    public function delete($getId)
    {
        $id = encode_php_tags($getId);
        if ($this->admin->delete('barang_keluar', 'id_barang_keluar', $id)) {
            set_pesan('data berhasil dihapus.');
        } else {
            set_pesan('data gagal dihapus.', false);
        }
        redirect('barangkeluar');
    }

    public function detail($getId)
    {
        $id = encode_php_tags($getId);
        $data['title'] = "Barang";
        $data['barang'] = $this->admin->get_detail_barang_keluar($id);
            // var_dump( $data['barang']);
            // die;
        
        $this->template->load('templates/dashboard', 'barang_keluar/detail',$data); 
        
    }

    public function setujui($id)
    {
        $data = array(
            'status' => 1
        );
        //update tabel pengadaan
        $where = array('id_barang_keluar' => $id);
        $this->Admin_model->update_record('barang_keluar', $data, $where);
        set_pesan('diterima');
        redirect('barangkeluar');
    }

    public function tolak($id)
    {

        $data = array(
            'status' => 2
        );
        //update tabel pengadaan
        $where = array('id_barang_keluar' => $id);
        $this->Admin_model->update_record('barang_keluar', $data, $where);
        redirect('barangkeluar');
        set_pesan('ditolak');
    }
}
