<?php
/**
*Tools Controller
*@Author: PuRwa
*Desc : This controller was design to do special task. It is used by system admin
*/
class Tools extends Controller {
    /*class field */
    protected $data;
    /*Controller constructor*/
	function Tools()
	{
		parent::Controller();            
		$this->data['page'] ='tools';
        
        if($this->session->userdata('logged_in'))
        {
            $this->load->model('karyawan');
            $query = $this->karyawan->get_karyawan($this->session->userdata('nik'));
            $data_karyawan = $query->row();
            $this->data['userinfo'] = $data_karyawan->nama;
            $this->data['jabatan'] = $this->session->userdata('jabatan');
            $this->data['now'] = strtoupper(date('M')).'<br />'.date('d'); 
            if($this->data['jabatan'] != 'admin')
            {
                redirect('home/error');
            }
        }
        else
        {
             redirect('home/login');
        }
        
	}
	/**
    *Default method to be called
    */
	function index()
	{
        if($this->session->userdata('logged_in') != TRUE)
        {
            redirect('home/login');
        }
        $this->load->view('home',$this->data);
	}
    /**
    * Fungsi check perjalanan data per item, per kelompok barang .
    */
    function check()
    {
        if($this->input->post('submit_search'))
        {
            //getting form data
            $opsi = $this->input->post('based_on');
            if(!empty($opsi))
            {                      
                //berdasarkan kelompok barang
                $this->load->model('transaksi');
                if($opsi == 1)
                {
                    $kb = $this->input->post('kb');                    
                    if(!empty($kb))
                    {
                        //query ambil data penjualan berdasarkan kelompok barang
                    }
                    else
                    {
                        $this->data['err_msg'] = 'Kelompok barang tidak boleh dikosongkan';
                    }
                }
                //berdasarkan kode barang
                if($opsi == 2)
                {
                    $ib = $this->input->post('ib');
                    if(!empty($ib))
                    {
                        //query ambil data penjualan berdasarkan kode barang
                        $query = $this->transaksi->sale_history_ib($ib);
                        if($query->num_rows() > 0)
                        {
                            //tampilkan hasil
                            $table = '<table class="table-data" cellspacing="0"><tr><td class="head">Tanggal</td><td class="head">'.$ib.'</td></tr>';
                            $total = 0;
                            foreach($query->result() as $row)
                            {
                                $table .= '<tr><td class="head">'.$row->tanggal.'</td><td>'.$row->qty_sale.'</td></tr>';
                                $total += $row->qty_sale;
                            }
                            $table .= '<tr><td class="head">T O T A L</td><td  style="background-color: #ddd">'.$total.'</td></tr>';
                            //ambil data barang
                            $this->load->model('barang');
                            $query = $this->barang->get_barang($ib,2);
                            $barang = $query->row();
                            //susun data barang
                            $table .= '<tr><td class="head">Mutasi Keluar</td><td style="background-color: #ddd">'.$barang->mutasi_keluar.'</td></tr>
                                        <tr><td class="head">Jumlah Terjual</td><td style="background-color: #ddd">'.$barang->jumlah_terjual.'</td></tr>
                                        <tr><td class="head">Mutasi Masuk</td><td style="background-color: #ddd">'.$barang->mutasi_masuk.'</td></tr>
                                        <tr><td class="head">Stok Barang</td><td style="background-color: #ddd">'.$barang->stok_barang.'</td></tr>
                                        <tr><td class="head">Total Barang Barang</td><td style="background-color: #ddd">'.$barang->total_barang.'</td></tr>
                                        ';
                            $table .= '</table>';
                            
                            $this->data['check_result'] = $table;
                        }
                        else
                        {
                            $this->data['err_msg'] = 'Data tidak ditemukan';
                        }
                    }
                    else
                    {
                        $this->data['err_msg'] = 'Kode barang tidak boleh dikosongkan';
                    }
                }
            }
            else
            {
                $this->data['err_msg'] = 'Opsi <b>berdasarkan</b> tidak boleh dikosongkan';
            }
        }
        $this->load->view('tools-check',$this->data);
    }
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */