<?php
/**
*Controller ITem: Input new items to system, manual or by import data from Sisgud
*/
class Item extends Controller {
    /*class field */
    protected $data;
    /*Controller constructor*/
	function Item()
	{
		parent::Controller();            
		$this->data['page'] ='item';
        
        if($this->session->userdata('logged_in'))
        {
            $this->load->model('karyawan');
            $query = $this->karyawan->get_karyawan($this->session->userdata('nik'));
            $data_karyawan = $query->row();
            $this->data['userinfo'] = $data_karyawan->nama;
            $this->data['jabatan'] = $this->session->userdata('jabatan');
            $this->data['now'] = strtoupper(date('M')).'<br />'.date('d');
            if($this->data['jabatan'] != 'supervisor')
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
        
        $this->load->view('home',$this->data);
	}
    /**
    * Function item autocomplete
    */
    function item_autocomplete($param="") 
    {
        $this->load->model('barang');
        $query = $this->barang->search_autocomplete($param);        
        foreach($query->result() as $row)
        {
            _e($row->id_barang.'|'.$row->nama.'|'.$row->kelompok_barang.'|'.$row->harga.'|'.$row->diskon.'|'.$row->stok_barang.chr(10));
        }        
    }
    /**
    *Input items manually
    */
    function add()
    {
        if($this->input->post('submit_mutasi_masuk'))
        {
                       
            $this->load->model('barang');
            //ambil data dari form mutasi
            $id_mutasi_masuk = $this->input->post('id_bon');
            $tgl_mutasi = $this->input->post('date_bon');
            if(!empty($id_mutasi_masuk) && !empty($tgl_mutasi))
            {
                $id_barang = $this->input->post('id_barang');
                $nama = $this->input->post('nama');
                $harga = $this->input->post('harga');
                $kel_barang = $this->input->post('kel_barang');
                $qty = $this->input->post('qty');
                $disc = $this->input->post('disc');                
                $this->data['err_msg']='';
                $gagal='';
                $success='';
                $duplikat='';
                for($i=0; $i<count($id_barang); $i++)
                {
                    if(!empty($id_barang[$i]))
                    {
                        $data = array(
                            'id_barang'=>$id_barang[$i],
                            'nama'=>$nama[$i],
                            'harga'=>$harga[$i],
                            'kelompok_barang'=>$kel_barang[$i],
                            'diskon'=> $disc[$i],
                            'total_barang'=>$qty[$i],
                            'stok_barang'=>$qty[$i],
                            'mutasi_masuk'=>$qty [$i]                                    
                        );                        
                        
                        if($this->validate_data($data))
                        {
                            //check data barang dengan id barang dan kode bon, sudah pernah diinput atw belum. menghindari duplikat
                            $check_bm = $this->barang->cek_barang_masuk($id_mutasi_masuk,$data['id_barang']);
                            if($check_bm->num_rows() == 0) {
                                //cek barang di database kloa ada tinggal update qty saja
                                $query = $this->barang->get_barang($data['id_barang'],2);                    
                                if(isset($query) && $query->num_rows()==1)
                                {
                                    if($this->barang->update_barang_gudang($data))
                                    {
                                        $success .= $data['id_barang'].', ';
                                    }
                                    else
                                    {
                                        $this->data['err_msg'] = 'Gagal update barang';
                                    }
                                }
                                else
                                {
                                    if($this->barang->insert_barang($data))
                                    {
                                        $success .= $data['id_barang'].', ';
                                    }
                                    else
                                    {
                                        $this->data['err_msg'] = 'Gagal insert barang';
                                    }
                                }                            
                                //insert data ke table barang masuk
                                $data = array(
                                    'id_mutasi_masuk'=>$id_mutasi_masuk,
                                    'id_barang'=>$data['id_barang'],
                                    'qty'=>$qty[$i],
                                    'tanggal'=>$tgl_mutasi
                                );                
                                $this->barang->insert_barang_masuk($data);
                            }
                            else {
                                $duplikat .= $data['id_barang'].', ';
                            }
                        }
                        else
                        {
                            $gagal .= $data['id_barang'].', ';
                        }                        
                    }                        
                }
                if(!empty($success)) 
                {                
                    $this->data['err_msg'] .= '<span style="color:green"> Kode Barang: '.$success.' telah dimutasikan masuk</span> <br />';
                }
                if(!empty($gagal))
                {
                    $this->data['err_msg'] .= '<span style="color:red"> Kode Barang: '.$gagal.' gagal dimutasikan masuk </span><br />';
                }
                if(!empty($duplikat)) {
                    $this->data['err_msg'] .= '<span style="color:red"> Kode Barang: '.$duplikat.' error duplikat data </span>';
                }
            }
            else
            {
                $this->data['err_msg'] = '<span style="color:red">Kode BON  dan Tanggal tidak boleh kosong </span>';
            }
        }
        $this->load->view('item-add',$this->data);
    }
    /**
    *Fungsi untuk retur barang ke gudang
    */
    function retur() 
    {
        if($this->input->post('submit_mutasi_keluar'))
        {
            $id_mutasi_keluar = $this->input->post('id_bon');
            $tanggal = $this->input->post('date_bon');
            $tujuan = $this->input->post('tujuan');
            $this->load->model('barang');
            $sukses = '';
            $gagal = '';
            if(!empty($id_mutasi_keluar) && !empty($tanggal) && !empty($tujuan))
            {
                $id_barang = $this->input->post('id_barang');
                $qty= $this->input->post('qty');
                for($i=0; $i<count($id_barang); $i++)
                {
                    $data = array(
                        'id_retur'=>$id_mutasi_keluar,
                        'id_barang'=>$id_barang[$i],
                        'tanggal'=>$tanggal,
                        'qty'=>$qty[$i],
                    	'tujuan'=>$tujuan
                    );
                    if($this->validate_data($data) && ($data['qty'] > 0))
                    {
                        //check apakah udh pernah diretur, untuk bon tersebut
                        $query = $this->barang->check_retur($data['id_retur'],$data['id_barang']);
                        if($query->num_rows() == 1)
                        {
                            //update data retur                           
                            if($this->barang->update_retur_barang($data))
                            {
                                //update stok di table barang
                                if($this->barang->update_after_retur($data))
                                {
                                    $sukses .= $data['id_barang'].',';
                                }
                                else
                                {
                                    $gagal .= $data['id_barang'].',';
                                }
                            }
                            else
                            {
                                $gagal .= $data['id_barang'].',';
                            }
                        }
                        else 
                        {
                            //masukkan ke tabel retur barang klo blom ada
                            if($this->barang->retur_barang($data))
                            {
                                //update stok di table barang
                                if($this->barang->update_after_retur($data))
                                {
                                    $sukses .= $data['id_barang'].',';
                                }
                                else
                                {
                                    $gagal .= $data['id_barang'].',';
                                }
                            }
                            else
                            {
                                $gagal .= $data['id_barang'].',';
                            }
                        }
                    }                    
                }
                $this->data['err_msg'] = '';
                if(!empty($sukses))
                {
                    $this->data['err_msg'] .= '<span style="color:green">Kode barang: '.$sukses.' berhasil dimutasikan keluar / retur</span><br />';
                }
                if(!empty($gagal)) 
                {
                    $this->data['err_msg'] .= '<span style="color:red">Kode barang: '.$gagal.' gagal dimutasikan keluar / retur</span><br />';
                }
            }
            else
            {
                $this->data['err_msg'] = '<span style="color:red">Kode bon, tanggal dan tujuan tidak boleh dikosongkan</spon>';
            }
        }
        //get list of shop
        $this->data['list_toko'] = $this->list_toko_tujuan_retur();
        $this->load->view('item-retur',$this->data);
    }
    /**
    *Fungsi untuk view barang
    */
    function view($param="")
    {
        if($this->input->post('submit_item_view'))
        {
            $this->load->model('barang');
            if($this->input->post('opsi') == 1)
            {
                $query = $this->barang->get_stok_by_kb();
                $this->data['row_data'] = '';
                $i=0;
                foreach($query->result() as $row)
                {
                    $this->data['row_data'] .= '<tr>
                                                    <td>'.++$i.'</td><td>'.$row->kelompok_barang.'</td><td>'.$row->stok.'</td>
                                                    <td>'.$row->masuk.'</td><td>'.$row->terjual.'</td><td>'.$row->total_stok.'</td>               
                                                </tr>';
                }
            }
        }
        $this->load->view('item-view',$this->data);
    }
    /**
    *Fungsi untuk edit barang
    */
    function edit()
    {
        $id_mutasi_masuk = $this->uri->segment(3);
        $id_barang = $this->uri->segment(4);
        $this->load->model('barang');
        if(!empty($id_barang))
        {
            $query =  $this->barang->get_detail_barang_masuk($id_mutasi_masuk, $id_barang);
            $barang = $query->row();
            $this->data['barang'] = $barang;            
        }
        if($this->input->post('submit_item_edit'))
        {
            if($this->validate_form_input())
            {
                $id_mutasi_masuk = $this->input->post('id_mutasi_masuk');
                $data = array(
                    'id_barang'=>$this->input->post('id_barang'),
                    'nama'=>$this->input->post('nama'),
                    'harga'=>$this->input->post('harga_jual'),
                    'kelompok_barang'=>$this->input->post('kel_barang'),                    
                    'diskon'=>$this->input->post('diskon')
                );
                $id_barang_old = $this->input->post('id_barang_old');
                $qty_old = $this->input->post('qty_old');
                $data['qty'] = $this->input->post('qty');
                $data['beda'] = $data['qty'] - $qty_old;
                
                if($this->barang->edit_barang($id_barang_old,$id_mutasi_masuk,$data))
                {
                    $this->data['err_msg'] = 'Data barang telah diupdate ';
                }
                else
                {
                    $this->data['err_msg'] = 'Gagal mengupdate data barang';
                }
            }
        }
        $this->load->view('item-edit',$this->data);
    }
    /**
     *Untuk manajemen data barang yang diinput, per tanggal penginputan
     */
    function manage($kode_bon="",$cetak="")
    {
        if($this->input->post('submit_item_manage'))
        {
            if($this->input->post('date_input'))
            {
                $this->data['tgl_input'] = $this->input->post('date_input');
                //ambil data barang masuk pada tanggal tersebut
                $this->load->model('barang');
                $query = $this->barang->get_bon_barang_masuk($this->input->post('date_input'));
                if($query->num_rows() >= 1)
                {
                    $i=0;
                    $row_data = '';
                    foreach($query->result() as $row)
                    {
                        $row_data .= '<tr>
                                        <td>'.++$i.'</td><td>'.$row->id_mutasi_masuk.'</td><td>'.date_to_string($row->tanggal).'</td>
                                        <td>'.$row->jumlah_barang.'</td><td>'.$row->total.'</td>
                                        <td>
                                            <span class="button">&nbsp;<a href="'.base_url().'item/manage/'.$row->id_mutasi_masuk.'"><input type="button" class="button" value="Detail"/></a></span>
                                            <span class="button">&nbsp;<a href="'.base_url().'item/manage/'.$row->id_mutasi_masuk.'/cetak"><input type="button" class="button" value="Cetak"/></a></span>
                                        </td>
                                    </tr>';
                    }
                    $this->data['row_data'] = $row_data;
                }
                else
                {
                    $this->data['err_msg'] = '<p style="color:red">Tidak ada data penginputan pada tanggal tersebut</p>';
                }
            }
            else
            {
                $this->data['row_data']='';
                $this->data['err_msg']='Tanggal penginputan tidak boleh dikosongkan';
            }
            $this->load->view('item-manage',$this->data);
        }
        else if(!empty($kode_bon))
        {

            //ambil data barang masuk dengan kode bon ini
            $this->load->model('barang');
            $query = $this->barang->get_barang_masuk($kode_bon);
            if($query->num_rows())
            {
                $i = 0;
                $row_data = '';
                $total = '';
                $total_jumlah = '';
                foreach($query->result() as $row)
                {
                    $jumlah = $row->qty * $row->harga;
                    $row_data .= '<tr><td>'.++$i.'</td><td>'.$row->id_barang.'</td><td>'.$row->nama.'</td>
                                <td>'.number_format($row->harga,0,',','.').'</td><td>'.$row->qty.'</td><td>'.number_format($jumlah,0,',','.').'</td></tr>';
                    $total += $row->qty;
                    $total_jumlah += $jumlah;
                }
                $row_data .= '<tr><td colspan="4" style="text-align:right">T O T A L</td><td>'.$total.'</td><td>'.number_format($total_jumlah,0,',','.').'</td></tr>';
                $this->data['row_data'] = $row_data;
                $this->data['kode_bon'] = $kode_bon;
                $this->data['tanggal_bon'] = $row->tanggal;
            }
            if($cetak=='cetak')
            {
                $head = '<div style="margin-top: 5px;">
            	         	<h3 style="text-align: center;">LAPORAN BARANG MASUK</h3>
            	         	<table style="width: 700px;">
            	         		<tr>
            	         			<td style="width: 80px;">Kode Bon</td><td style="width:260px;">: '.$kode_bon.'</td>
            	         		</tr>
            	         		<tr>
            	         			<td style="width: 80px;">Tanggal</td><td style="width:260px;">: '.date_to_string($row->tanggal).'</td>
            	         		</tr>
            	        	</table>
            	        </div><br />
            			<table style="width: 700px" border="1" cellpadding="4">
            				<tr>
            					<td style="width:30px; background-color:  #dedede;font-weight: bold;">No</td>
            					<td style="width:100px; background-color:  #dedede;font-weight: bold;">Kode Label</td>
            					<td style="width:170px; background-color:  #dedede;font-weight: bold;">Nama Barang</td>
            					<td style="width:100px; background-color:  #dedede;font-weight: bold;">Harga (Rp)</td>
            					<td style="width:50px; background-color:  #dedede;font-weight: bold;">Qty</td>
            					<td style="width:100px; background-color:  #dedede;font-weight: bold;">Jumlah</td>
            				</tr>';
                $list_item= array();
                $i=0;
                $row_data='';
                $total=0;$total_qty=0;
                foreach($query->result() as $row)
                {
                    $jumlah = $row->harga*$row->qty;
                    $row_data .= '<tr><td style="width:30px;">'.++$i.'</td>
            						<td style="width:100px;">'.$row->id_barang.'</td>
            						<td style="width:170px;">'.$row->nama.'</td>
            						<td style="width:100px;text-align:right">'.number_format($row->harga,0,',','.').'</td>
            						<td style="width:50px;text-align:center">'.$row->qty.'</td>
            						<td style="width:100px;text-align:right;">'.number_format($jumlah,0,',','.').'</td></tr>
            					';
                    $total += $jumlah;
                    $total_qty += $row->qty;
                    if($i%35 == 0)
                    {
                        $list_item[] = $row_data;
                        $row_data='';
                    }
                }
                $footer = '</table>';
                $row_data .= '<tr>
            					<td colspan="3" style="text-align:right;width:300px">TOTAL</td>
            					<td style="width:100px;">&nbsp;</td>
            					<td style="width:50px;text-align:center">'.$total_qty.'</td>
            					<td style="width:100px;text-align:right;">'.number_format($total,0,',','.').'</td>
            				</tr>';
                $list_item[]=$row_data;
                $this->cetak_input_pdf($head, $list_item, $footer);
            }
            else
                $this->load->view('item-detailbon',$this->data);
        }
        else
            $this->load->view('item-manage',$this->data);

    }

    function date_to_string($tgl)
    {
        $month = array('','Januari','Februari','Maret','April','Mei','Juni',
        'Juli','Agustus','September','Oktober','November','Desember');
        $tmp = explode('-',$tgl);

        return $tmp[2].' '.$month[intval($tmp[1])].' '.$tmp[0];
    }

    /**
     * Laporan stok dan umur barang
     */
    function report($option='')
    {
        //display report
        if(empty($option))
        {
            if($this->input->post('submit_report_display') || $this->input->post('submit_report_print'))
            {
                $this->load->model('barang');
                $type = $this->input->post('type');
                $param['type'] = $type;
                //laporan data barang
                if($type == 1)
                {
                    $param['from'] = $this->input->post('date_from');
                    $param['to'] = $this->input->post('date_to');

                    $this->session->set_userdata($param);
                    $query = $this->barang->stat_item_cat($param);
                    if($query->num_rows() >0)
                    {
                        $data = $query->result();
                        $head = '<br />
                                    <h3 style="text-align:center;font-size: 14px">LAPORAN DATA BARANG </h3>
                                     <h3 style="text-align:center;font-size: 14px"> PERIODE : '.$this->date_to_string($param['from']).' s.d. '.$this->date_to_string($param['to']).'</h3>
                                    <table style="width: 500px;border: 1px solid;text-align: center;margin: 0px auto;" cellspacing="0" cellpadding="1">
                                        <tr>
                                            <td style="width:50px;background-color:#dedede;font-weight: bold;border:1px solid;">No</td>
                                            <td style="width:110px;background-color:#dedede;font-weight: bold;border:1px solid;">Kelompok Barang</td>
                                            <td style="width:110px;background-color:#dedede;font-weight: bold;border:1px solid;">Stok Terakhir</td>
                                            <td style="width:110px;background-color:#dedede;font-weight: bold;border:1px solid;">Masuk Toko</td>
                                            <td style="width:120px;background-color:#dedede;font-weight: bold;border:1px solid;">Terjual</td>
                                        </tr>';
                        $footer = '</table>';
                        $i=0;$temp='';$total_stok=0;$body=array();
                        $total_masuk=0;$total_jual=0;

                        foreach($data as $row)
                        {

                            $temp .= '<tr>
                                <td style="width:50px; border: 1px solid">'.++$i.'</td>
                                <td style="width:110px; border: 1px solid">'.$row->kelompok_barang.'</td>
                                <td style="width:110px; border: 1px solid">'.number_format($row->stok).'</td>
                                <td style="width:110px; border: 1px solid">'.number_format($row->masuk).'</td>
                                <td style="width:120px; border: 1px solid">'.number_format($row->jual).'</td>
                            </tr>';
                            if($i%50 == 0)
                            {

                                $body[] = $temp;
                                $temp = '';
                            }
                            $total_masuk+= $row->masuk;
                            $total_stok += $row->stok;
                            $total_jual += $row->jual;
                        }
                        $body[] = $temp;
                        $row_total = '<tr>
                            <td colspan="2" style="width:160px; border: 1px solid">TOTAL</td>
                            <td style="width:110px; border: 1px solid">'.number_format($total_stok).'</td>
                            <td style="width:110px; border: 1px solid">'.number_format($total_masuk).'</td>
                            <td style="width:120px; border: 1px solid">'.number_format($total_jual).'</td>
                        </tr>';

                        if($this->input->post('submit_report_print'))
                        {
                            $this->cetak_pdf(6,$head, $body, $row_total, $footer);
                        }
                        $this->data['table'] = $head.$body[0].$footer;
                    }
                }
                //laporan umur barang
                else if($type == 2)
                {
                    $umur = $this->input->post('umur');
                    $param['umur'] = $umur;
                    $this->session->set_userdata($param);
                    if($umur == 1)
                    {
                        $to = '';
                        $from = date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y')-1));
                    }
                    else if($umur == 2)
                    {
                        $to = date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y')-1));
                        $from = date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y')-2));
                    }
                    else if($umur == 3)
                    {
                        $to = date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y')-2));
                        $from = date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y')-3));
                    }
                    else if($umur == 4)
                    {
                        $from = '';
                        $to = date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y')-3));
                    }
                    $query = $this->barang->stat_item_age(array('from'=>$from,'to'=>$to));
                    if($query->num_rows() > 0)
                    {
                        $data = $query->result();
                        $head = '<br /><h3 style="text-align:center;font-size: 14px">LAPORAN REKAP UMUR BARANG</h3><br />
                                    <table style="width: 500px;border: 1px solid;text-align: center;margin: 0px auto;" cellspacing="0" cellpadding="1">
                                        <tr>
                                            <td style="width:50px;background-color:#dedede;font-weight: bold;border:1px solid;">No</td>
                                            <td style="width:110px;background-color:#dedede;font-weight: bold;border:1px solid;">Kelompok Barang</td>
                                            <td style="width:110px;background-color:#dedede;font-weight: bold;border:1px solid;">Kode Barang</td>
                                            <td style="width:80px;background-color:#dedede;font-weight: bold;border:1px solid;">Qty</td>
                                            <td style="width:150px;background-color:#dedede;font-weight: bold;border:1px solid;">Umur</td>
                                        </tr>';
                        $body='';$i=0;$j=0;$temp='';
                        $total_qty=0;
                        foreach($data as $row)
                        {
                            $temp .= '<tr>
                                    <td style="width:50px; border: 1px solid">'.++$i.'</td>
                                    <td style="width:110px; border: 1px solid">'.$row->kelompok_barang.'</td>
                                    <td style="width:110px; border: 1px solid">'.$row->id_barang.'</td>
                                    <td style="width:80px; border: 1px solid">'.$row->stok_barang.'</td>
                                    <td style="width:150px; border: 1px solid">'.$this->parse_age($row->umur).'</td>
                            </tr>';
                            if($i%50==0)
                            {
                                $body[] = $temp;
                                $temp = '';
                            }
                            $total_qty += $row->qty;
                        }
                        $body[] = $temp;
                        $footer = '</table>';
                        $row_total = '<tr>
                                        <td colspan="3" style="width: 270px; border: 1px solid"></td>
                                        <td style="width:80px; border: 1px solid">'.$total_qty.'</td>
                                        <td style="width:150px; border: 1px solid"></td>
                                    </tr>';
                        $this->data['table'] = $head.$body[0].$footer;
                        if($this->input->post('submit_report_print'))
                        {
                            //echo 'masuk pdf';
                            //var_dump($body);
                            $this->cetak_pdf(6, $head, $body, $row_total, $footer);
                        }
                    }
                }
            }
            $this->load->view('item-report',$this->data);
        }
        else
        {
            //print menggunakan window.print
            if($option=='preview')
            {
                $this->load->model('barang');
                $type=$this->session->userdata('type');
                //laporan data barang
                if($type == 1)
                {
                    $param['from'] = $this->session->userdata('from');
                    $param['to'] = $this->session->userdata('to');

                    $query = $this->barang->stat_item_cat($param);
                    if($query->num_rows() >0)
                    {
                        $data = $query->result();
                        $head = '<div id="header">
                                        <img src="'.base_url().'css/images/logo_mode.png" />
                                        <h2>MODE FASHION GROUP</h2>
                                        <p>
                                            Kantor Pusat: <br />
                                            Jln. Laksana No. 68 ABC, Medan<br />
                                            Telepon: (061) 372 592
                                        </p>
                                    </div>
                                    <div id="content">
                                    <h3 style="text-align:center;font-size: 14px">LAPORAN DATA BARANG </h3>
                                     <h3 style="text-align:center;font-size: 14px"> PERIODE : '.$this->date_to_string($param['from']).' s.d. '.$this->date_to_string($param['to']).'</h3>
                                    <table class="table-data" cellspacing="0" cellpadding="0" border="1">
                                        <tr>
                                            <td class="head">No</td>
                                            <td class="head">Kelompok Barang</td>
                                            <td class="head">Stok Terakhir</td>
                                            <td class="head">Masuk Toko</td>
                                            <td class="head">Terjual</td>
                                        </tr>';
                        $footer = '</table></div>';
                        $i=0;$temp='';$total_stok=0;$body=array();
                        $total_masuk=0;$total_jual=0;

                        foreach($data as $row)
                        {

                            $temp .= '<tr>
                                <td>'.++$i.'</td>
                                <td>'.$row->kelompok_barang.'</td>
                                <td>'.number_format($row->stok).'</td>
                                <td>'.number_format($row->masuk).'</td>
                                <td>'.number_format($row->jual).'</td>
                            </tr>';
                            if($i%50 == 0)
                            {

                                $body[] = $temp;
                                $temp = '';
                            }
                            $total_masuk+= $row->masuk;
                            $total_stok += $row->stok;
                            $total_jual += $row->jual;
                        }
                        $body[] = $temp;
                        $row_total = '<tr>
                            <td colspan="2" >TOTAL</td>
                            <td >'.number_format($total_stok).'</td>
                            <td >'.number_format($total_masuk).'</td>
                            <td >'.number_format($total_jual).'</td>
                        </tr>';

                        $content='';
                        $i=0;
                        foreach($body as $row)
                        {
                            if($i == count($body)-1)
                                $footer = $row_total.$footer;
                            $content .= $head.$row.$footer.'<br /><br />';
                            $i++;
                        }
                        $this->data['content']=$content;
                    }
                }
                //laporan umur barang
                else if($type == 2)
                {
                    $umur = $this->session->userdata('umur');
                    $param['umur'] = $umur;
                    $this->session->set_userdata($param);
                    if($umur == 1)
                    {
                        $to = '';
                        $from = date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y')-1));
                    }
                    else if($umur == 2)
                    {
                        $to = date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y')-1));
                        $from = date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y')-2));
                    }
                    else if($umur == 3)
                    {
                        $to = date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y')-2));
                        $from = date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y')-3));
                    }
                    else if($umur == 4)
                    {
                        $from = '';
                        $to = date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y')-3));
                    }
                    $query = $this->barang->stat_item_age(array('from'=>$from,'to'=>$to));
                    if($query->num_rows() > 0)
                    {
                        $data = $query->result();
                        $head = '<div id="header">
                                        <img src="'.base_url().'css/images/logo_mode.png" />
                                        <h2>MODE FASHION GROUP</h2>
                                        <p>
                                            Kantor Pusat: <br />
                                            Jln. Laksana No. 68 ABC, Medan<br />
                                            Telepon: (061) 372 592
                                        </p>
                                    </div>
                                    <div id="content">
                                    <h3 style="text-align:center;font-size: 14px">LAPORAN REKAP UMUR BARANG</h3><br />
                                    <table class="table-data" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td class="head">No</td>
                                            <td class="head">Kelompok Barang</td>
                                            <td class="head">Kode Barang</td>
                                            <td class="head">Qty</td>
                                            <td class="head">Umur</td>
                                        </tr>';
                        $body='';$i=0;$j=0;$temp='';
                        $total_qty=0;
                        foreach($data as $row)
                        {
                            $temp .= '<tr>
                                    <td>'.++$i.'</td>
                                    <td>'.$row->kelompok_barang.'</td>
                                    <td>'.$row->id_barang.'</td>
                                    <td>'.$row->stok_barang.'</td>
                                    <td>'.$this->parse_age($row->umur).'</td>
                            </tr>';
                            if($i%50==0)
                            {
                                $body[] = $temp;
                                $temp = '';
                            }
                            $total_qty += $row->qty;
                        }
                        $body[] = $temp;
                        $footer = '</table></div>';
                        $row_total = '<tr>
                                        <td colspan="3"></td>
                                        <td>'.$total_qty.'</td>
                                        <td></td>
                                    </tr>';
                        $content='';
                        $i=0;
                        foreach($body as $row)
                        {
                            if($i == count($body)-1)
                                $footer = $row_total.$footer;
                            $content .= $head.$row.$footer.'<br /><br />';
                            $i++;
                        }
                        $this->data['content']=$content;
                    }
                }
                $this->load->view('print-template',$this->data);
            }
        }
    }

    /*
    **Funngsi cetak pdf
    */
    function cetak_pdf($opsi,$head,$row,$row_total,$foot)
    {
        require_once('lib/tcpdf/config/lang/eng.php');
        require_once('lib/tcpdf/tcpdf.php');

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('PuRwa ReN');
        $pdf->SetTitle('Laporan Sikasir');
        $pdf->SetSubject('Laporan');
        $pdf->SetKeywords('Penjualan, Barang, Harga');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 20, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(10);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        //$pdf->SetFont('dejavusans', '', 8);
        //$size = array(216,165);
        if($opsi == 1) //cetak laporan untuk penjualan harian
        {
            $pdf->SetFont('dejavusans', '', 8);
            $pdf->setPageFormat('A4','L');
        }
        else if($opsi == 2) //cetak laporan akumulasi
        {
            $pdf->SetFont('dejavusans', '', 8);
            $pdf->setPageFormat('A4','P');
        }
        else if($opsi == 3)
        {
            $pdf->setPageUnit('mm');
            $size = array(216,165);
            $pdf->SetFont('dejavusans', '', 8);
            $pdf->setPageFormat($size, 'P');
        }
        else if($opsi == 4)
        {
            //set font
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->setPageUnit('mm');
            $size = array(216,330);
            $pdf->setPageFormat($size,'L');

        }
        else if($opsi == 5)
        {
            $pdf->SetFont('dejavusans', '', 8);
            $pdf->setPageFormat('A4','P');
        }
        else if($opsi == 6) //cetak laporan rekap
        {
            $pdf->SetFont('dejavusans', '', 8);
            $pdf->setPageFormat('A4','P');
        }
        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //set some language-dependent strings
        //$pdf->setLanguageArray($l);

        // ---------------------------------------------------------

        $i = 0;
        $j = 0;
        if($opsi == 1)
        {
            foreach($row as $data)
            {
                // add a page
                $pdf->AddPage();
                if($i == (count($row) - 1))
                {
                    $foot = $row_total.$foot;
                }
                $pdf->writeHTML($head.$data.$foot, true, 0, true, 0);
                $i++;
            }
        }
        if($opsi == 2)
        {
            //acc per kode label
            foreach($row[0] as $data)
            {
                // add a page
                $pdf->AddPage();
                $foot1 = $foot;
                if($i == (count($row[0]) - 1))
                {
                    $foot1 = $row_total[0].$foot;
                }
                $pdf->writeHTML($head[0].$data.$foot1, true, 0, true, 0);
                $i++;
            }
            //acc per kel barang
            foreach($row[1] as $data)
            {
                $pdf->AddPage();
                $foot2 = $foot;
                if($j == (count($row[1]) - 1))
                {
                    $foot2 = $row_total[1].$foot;
                }
                $pdf->writeHTML($head[1].$data.$foot2, true, 0, true, 0);
                $j++;
            }
        }
        if($opsi == 3 || $opsi == 5)
        {

            foreach($row as $data)
            {
                if($i == (count($row) - 1))
                {
                    $foot = $row_total.$foot;
                }
                $pdf->AddPage();
                $pdf->writeHTML($head.$data.$foot, true, 0, true, 0);
                $i++;
            }
        }
        if($opsi == 4)
        {
            $i = 0;
            foreach($row as $data)
            {
                $pdf->AddPage();
                if($i == count($row)-1)
                {
                    $pdf->writeHTML($head.$data.$row_total.$foot, true, 0, true, 0);
                }
                else
                {
                    $pdf->writeHTML($head.$data.'</table>', true, 0, true, 0);
                }
                $i++;
            }
        }
        if($opsi == 6)
        {
            //acc per kode label
            foreach($row as $data)
            {
                // add a page
                $pdf->AddPage();
                $foot1 = $foot;
                if($i == (count($row) - 1))
                {
                    $foot1 = $row_total.$foot;
                }
                $pdf->writeHTML($head.$data.$foot1, true, 0, true, 0);
                $i++;
                //break;
            }
        }
        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('Laporan.pdf', 'I');

    }

    /*
    **Funngsi cetak pdf
    */
    function cetak_input_pdf($head,$list_item,$footer)
    {
        require_once('lib/tcpdf/config/lang/eng.php');
        require_once('lib/tcpdf/tcpdf.php');

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Nicola Asuni');
        $pdf->SetTitle('TCPDF Example 006');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set margins
        $pdf->SetMargins(9, 20, 9);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->setPageUnit('mm');
        $size = array(216,297);
        $pdf->setPageFormat($size,'P');
        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //set some language-dependent strings
        //$pdf->setLanguageArray($l);

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('dejavusans', '', 8);

        foreach($list_item as $rows)
        {
            // add a page
            $pdf->AddPage();

            $html = $head.$rows.$footer;
            //echo $html; exit;
            $pdf->writeHTML($html, true, 0, true, 0);
        }

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('tes.pdf', 'I');

    }



    /**
     * Parse age for display
     * @param $age in days
     */
    function parse_age($age)
    {
        $year = floor($age/365);
        $day = $age%365;
        $month = floor($day/30);
        $rest = $day%30;

        $umur = '';
        if($year > 0)
        {
            $umur .= $year.' tahun ';
        }
        if($month > 0)
        {
            $umur .= $month.' bulan ';
        }
        if($rest > 0)
        {
            $umur .= $rest.' hari ';
        }
        return $umur;
    }

    /**
    *Fungsi import data dari csv
    */
    function import()
    {
        //display import data
        if($this->input->post('submit_import'))
        {
            //upload datanya terlebih dahulu
            $config['upload_path'] = 'data/';
            $config['allowed_types'] = 'csv';
            $config['overwrite'] = TRUE;
            $config['file_name'] = 'temp';
            $this->load->library('upload', $config);
            //do upload            
            if($this->upload->do_upload('csv_file'))
            {            
                $this->load->library('csvreader');
                $file_name = 'data/temp.csv';
                $item = $this->csvreader->parse_file($file_name);
                if($item[0]['tujuan']==config_item('shop_code'))
                {
	                $this->data['row_data'] = '';
	                $i=0;
	                $total_qty = 0;
	                $total = 0;
	                foreach($item as $row)
	                {
	                    $jumlah = $row['item_hj']*$row['quantity'];
	                    $total += $jumlah;
	                    $total_qty += $row['quantity'];
	                    $this->data['row_data'] .= '<tr>
	                                                    <td>'.++$i.'</td>
	                                                    <td>'.$row['item_code'].'</td>
	                                                    <td>'.$row['item_name'].'</td>
	                                                    <td>'.$row['cat_code'].'</td>
	                                                    <td>'.$row['item_disc'].'</td>
	                                                    <td style="text-align:right;padding-right:10px;"><input type="hidden" id="item_hj_'.$i.'" value="'.$row['item_hj'].'">'.number_format($row['item_hj'],0,',','.').',-</td>
	                                                    <td>'.$row['quantity'].'</td>
	                                                    <td>'.number_format($jumlah,0,',','.').',-</td>
	                                                    <td><span class="button"><input type="button" class="button" value="O K" onclick="saveImport('.$i.')"/></span></td>
	                                                </tr>';
	                }
	                $this->data['row_data'] .= '<tr><td colspan="6" style="text-align:right">T O T A L</td><td>'.$total_qty.'</td><td>'.number_format($total,0,',','.').',-</td><td></td></tr>';
            
                }
                else
                {
                	$this->data['err_msg'] = '<span style="color:red">File yang diupload bukan diperuntukan untuk toko ini.</span>';
                }
            }
            else
            {
                $this->data['err_msg'] = '<span style="color:red">Gagal upload data! Pastikan file yang di upload adalah CSV</span>';
            }
            $this->load->view('item-import',$this->data);
        }        
        //saving import data
        else if($this->input->post('item_code'))
        {
            $this->load->model('barang');
            //check apakah barang sudah ada di dalam table barang masuk, untuk menghindari import 2x atau lebih
            $param = array('id_mutasi_masuk'=>$this->input->post('kode_bon'),'id_barang'=>$this->input->post('item_code'));
            $query = $this->barang->check_barang_masuk_for_import($param);
            if($query->num_rows() == 0) 
            {
                //check apakah barang sudah ada di table barang
                $query = $this->barang->get_barang($this->input->post('item_code'),2);
                //populate data barang
                $barang = array(
                        'id_barang'=>$this->input->post('item_code'),                        
                        'nama'=>$this->input->post('item_name'),                        
                        'harga'=>$this->input->post('item_hj'),                        
                        'kelompok_barang'=>$this->input->post('cat_code'),                        
                        'diskon'=>$this->input->post('item_disc'),                        
                        'total_barang'=>$this->input->post('quantity'),                        
                        'stok_barang'=>$this->input->post('quantity'),                        
                        'mutasi_masuk'=>$this->input->post('quantity'),                        
                        'stok_awal'=>0,                        
                        'stok_opname'=>0,                        
                        'mutasi_keluar'=>0,                        
                        'jumlah_terjual'=>0                        
                    ); 
                $barang_masuk = array(
                        'id_mutasi_masuk'=>$this->input->post('kode_bon'),
                        'id_barang'=>$this->input->post('item_code'),
                        'tanggal'=>$this->input->post('tgl_bon'),
                        'qty'=>$this->input->post('quantity'),
                    );
                //belum ada ditable barang, buat baru
                if($query->num_rows() == 0)
                {                                       
                    if($this->barang->insert_barang($barang))
                    {
                        if($this->barang->insert_barang_masuk($barang_masuk))
                        {
                            echo '1';
                        }
                        else
                        {
                            echo '0';
                        }
                    }
                    else
                    {
                        echo '0';
                    }
                }
                //yang udah ada ditable barang kita update, trus nulis ke barang masuk juga
                else
                {
                    if($this->barang->update_barang_gudang($barang))
                    {
                        if($this->barang->insert_barang_masuk($barang_masuk))
                        {
                            echo '1';
                        }
                        else
                        {
                            echo '0';
                        }
                    }
                    else
                    {
                        echo '0';
                    }
                }
            }
            else
            {
                echo '-1';//sudah ada di barang masuk
            }
        }
        //tampilin form import
        else
        {
            $this->load->view('item-import',$this->data);
        }
        
    }    
    /**
    * validasi form input item
    */
    function cari($param='')
    {
        $this->load->model('barang');
        if($this->input->post('submit_cari'))
        {
            $keywords = $this->input->post('keywords');
            $this->session->set_userdata('keywords',$keywords);
            $query = $this->barang->search_stok($keywords);            
        }
        else 
        {
            $query = $this->barang->search_stok($this->session->userdata('keywords'));            
        }
        if(isset($query) && $query->num_rows() > 0)
        {
            $this->data['total_item'] = $query->num_rows();           
            //setting up pagination
            $this->load->library('pagination');
            $config['base_url'] = base_url().'item/cari/';
            $config['total_rows'] = $this->data['total_item'];
            $config['per_page'] = 25;
            $this->pagination->initialize($config);
            $this->data['pagination'] = $this->pagination->create_links();
            //applying pagination on displaying result            
            if(isset($param) && intval($param) > 0)
            {
                $page_min = $param;
                $page_max = $page_min + $config['per_page'];
            }
            else
            {
                $page_min = 0;
                $page_max = $config['per_page'];
            }
            $this->data['total_qty'] = 0;
            $this->data['row_data'] = '';
            $i=0;
            foreach($query->result() as $row)
            {
                if($i>=$page_min && $i <$page_max)
                {
                    $this->data['row_data'] .= '<tr>
                                                    <td>'.++$i.'</td><td>'.date_to_string($row->tanggal).'</td><td>'.$row->id_barang.'</td><td>'.$row->nama.'</td>
                                                    <td>'.number_format($row->harga,0,',','.').'</td><td>'.$row->diskon.'</td><td>'.$row->total_barang.'</td>
                                                    <td>'.$row->qty.'</td><td>'.$row->mutasi_keluar.'</td><td>'.$row->stok_barang.'</td>
                                                </tr>';                    
                }
                else
                {
                    $i++;
                }
                $this->data['total_qty'] += $row->stok_barang;
            }
        }
        $this->load->view('item-cari',$this->data);
    }
    function validate_form_input()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('id_barang','kode barang','required|numeric');
        $this->form_validation->set_rules('nama','nama barang','required');
        $this->form_validation->set_rules('harga_jual','harga jual','required|numeric');
        $this->form_validation->set_rules('qty','quantity','required|numeric');
        $this->form_validation->set_rules('kel_barang','kelompok barang','required|alpha_numeric');
        $this->form_validation->set_rules('diskon','diskon','required|numeric');
        if($this->form_validation->run()== FALSE)
        {
            $this->data['err_msg'] = 'Terjadi kesalahan : '.validation_errors();            
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }
    /**
    * validasi form input item
    */
    function validate_data($data)
    {
        $status = TRUE;
        
        foreach($data as $row)
        {
            if($row=='')
            {
                $status = FALSE;
            }           
        }        
        return $status;
    }
    
    /**
     * Get list of toko
     */
    function list_toko_tujuan_retur() 
    {
    	$shop = config_item('refund_shop');
    	$options = '';
    	foreach ($shop as $key => $val)
    	{
    		$options .= '<option value="'.$key.'">'.$val.'</option>';
    	}
    	return '<select name="tujuan">
    				<option value="">Pilih Toko Tujuan</option>
    				'.$options.'
    			</select>';
    }
}
//End of file item.php
//Location: application/controller/item.php