<?php
/**
*PoinfOfSales Controller
*@Author: PuRwa
*Desc : This controller was used for point of sales application
*/
class PointOfSales extends Controller {
    
    /*Controller constructor*/
    function PointOfSales()
    {
        parent::Controller();
        
        $this->data['page'] ='pos';
        //check if user logged in
        if($this->session->userdata('logged_in'))
        {
            $this->load->model('karyawan');
            $query = $this->karyawan->get_karyawan($this->session->userdata('nik'));
            $data_karyawan = $query->row();
            $this->data['userinfo'] = $data_karyawan->nama;
            $this->data['jabatan'] = $this->session->userdata('jabatan');
            $this->data['now'] = strtoupper(date('M')).'<br />'.date('d');
            if($this->data['jabatan'] != 'kasir')
            {
                redirect('home/error');
            }
        }
        else
        {
            redirect('home/login');
        }
    }
    /*default method to be called by controller*/
    function index()
    {
        if($this->input->post('submit_launch'))
        {
            $no_shift = $this->input->post('no_shift');
            $no_kassa = $this->input->post('no_kassa');            
            if($this->validate_launch_pos())
            {
                $data = array(
                        'no_shift'=>$no_shift,
                        'no_kassa'=>$no_kassa
                    );
                $this->session->set_userdata($data);
                redirect('pointofsales/launch','refresh');
            }            
        }
        $this->load->view('pos-index',$this->data);
    }
    /*form validation for launch pos*/
    function validate_launch_pos()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('no_shift','no shift','required|is_natural_no_zero');
        $this->form_validation->set_rules('no_kassa','no kassa','required|is_natural_no_zero');
        if ($this->form_validation->run() == FALSE)
		{
			$this->data['notification'] = 'Error occured :'.validation_errors();
            return FALSE;
		}
		else
		{
			return TRUE;
		}        
    }
    function get_kassa()
    {
        $kassa = $this->session->userdata('no_kassa');
        _e($kassa);
    }
    /*redirect page to home controller*/
    function home()
    {
        redirect('home','refresh');
    }
    /*launching pos system*/
    function launch()
    {
        if($this->data['jabatan']== 'kasir')
        {
            $this->data['current_date'] = $this->current_date();
            $this->data['no_tunggu'] = time();
            $this->load->view('point.of.sales.php',$this->data);
        }
        else
        {
            echo 'Anda bukan kasir, tidak boleh mengakses applikasi POS';
        }
    }
    /**
    *screen configuration
    */
    function screen_config()
    {
        _e(config_item('screen'));
    }
    /**
    *Function for saving transaction data to database
    */
    function transaction()
    {
        //data berasal dari  pos system
        $id_transaksi = $this->input->post('id_trans');
        $id_barang = $this->input->post('id_barang');
        $qty = $this->input->post('qty');
        $disc = $this->input->post('disc');
        $id_pramuniaga = $this->input->post('id_pramu');
        $jumlah = $this->input->post('jumlah');
        $total = $this->input->post('total');
        $disc_all = $this->input->post('disc_all');
        $now = date('Y-m-d');
        //insert data ke tabel transaksi penjualan        
        $data = array(
                'id_transaksi'=>$id_transaksi,
                'tanggal'=>$now,
                'total'=>$total,
                'diskon'=>$disc_all,
                'id_kasir'=>$this->session->userdata('nik'),
                'id_pramuniaga'=>$id_pramuniaga
                );
        $this->load->model('transaksi');
        if($this->transaksi->add_transaksi($data))
        {
            //insert item transaksi ke table item_transaksi penjualan dan update table barang
            $this->load->model('item_transaksi');
            $this->load->model('barang');
            $i=0;
            foreach($jumlah as $row)
            {
                if($row >= 0)
                {
                    $data = array(
                        'id_transaksi'=>$id_transaksi,
                        'id_barang'=>$id_barang[$i],                        
                        'qty'=>$qty[$i],
                        'diskon'=>$disc[$i]
                        );
                    //insert ke item transaksi
                    $this->item_transaksi->add_item_transaksi($data);
                    //update table barang
                    $cond = array('id_barang'=>$id_barang[$i]);
                    $data = array(
                            'stok_barang'=>$qty[$i],
                            'jumlah_terjual'=>$qty[$i]
                        );
                    $this->barang->update_barang($cond,$data);
                }
                $i++;
            }
            
            _e(1);
        }
        else
        {
            _e(0);
        }
    }
    /**
    *Transaction with Credit Card
    */
    function transaction_credit()
    {
        //data berasal dari  pos system
        $id_transaksi = $this->input->post('id_trans');
        $id_barang = $this->input->post('id_barang');
        $qty = $this->input->post('qty');
        $disc = $this->input->post('disc');
        $id_pramuniaga = $this->input->post('id_pramu');
        $jumlah = $this->input->post('jumlah');
        $total = $this->input->post('total');
        $cc_num = $this->input->post('cc_num');
        $disc_all = $this->input->post('disc_all');
        $now = date('Y-m-d');
        //insert data ke tabel transaksi penjualan        
        $data = array(
                'id_transaksi'=>$id_transaksi,
                'tanggal'=>$now,
                'total'=>$total,
                'diskon'=>$disc_all,
                'no_cc'=>$cc_num,
                'id_kasir'=>$this->session->userdata('nik'),
                'id_pramuniaga'=>$id_pramuniaga
                );
        $this->load->model('transaksi');
        if($this->transaksi->add_transaksi($data))
        {
            //insert item transaksi ke table item_transaksi penjualan dan update table barang
            $this->load->model('item_transaksi');
            $this->load->model('barang');
            $i=0;
            foreach($jumlah as $row)
            {
                if($row >= 0)
                {
                    $data = array(
                        'id_transaksi'=>$id_transaksi,
                        'id_barang'=>$id_barang[$i],                        
                        'qty'=>$qty[$i],
                        'diskon'=>$disc[$i]
                        );
                    //insert ke item transaksi
                    $this->item_transaksi->add_item_transaksi($data);
                    //update table barang
                    $cond = array('id_barang'=>$id_barang[$i]);
                    $data = array(
                            'stok_barang'=>$qty[$i],
                            'jumlah_terjual'=>$qty[$i]
                        );
                    $this->barang->update_barang($cond,$data);
                }
                $i++;
            }
            
            _e(1);
        }
        else
        {
            _e(0);
        }
    }
    /**
    *Function for viewing temp. total sales
    */
    function temp_sales()
    {
        $this->load->model('transaksi');
        $now = date('Y-m-d');
        $print = $this->input->post('print');
        //ambil temp sales dalam sehari
        $query = $this->transaksi->total_sales_a_day($now);
        if($query->num_rows())
        {
            $sales = $query->row();            
        }
        //klo print tampilin lengkap+ penjualan per kode barang
        if(isset($print) && $print==1) 
        {
            $query = $this->transaksi->total_qty_sales_by_cat($now);
            if($query->num_rows() > 0)
            {
                $detail = '';
                $total_qty = 0;
                $i=0;
                foreach($query->result() as $row)
                {
                    $i++;
                    if($i%3 == 0)
                    {
                        $detail .= $row->kelompok_barang.' : '.$row->total_jual.'#';
                    }
                    else
                    {
                        $detail .= $row->kelompok_barang.' : '.$row->total_jual.',  ';
                    }
                    $total_qty += $row->total_jual;
                }
            }
            //rapihin yang mau dicetak
            //open template file
            $file = fopen('lib/temp-sales.txt','r');
            $report = '';
            while(!feof($file))                    
            {
                $report .= fgets($file);
            }
            fclose($file);
            //masuk2in datanya
            $report = str_replace('<tanggal>',date_to_string($now),$report);
            $report = str_replace('<omset>',number_format($sales->temp_sales,0,',','.').',-',$report);
            $report = str_replace('<total>',$total_qty,$report);
            $report = str_replace('<detail>',$detail,$report);
            //tulis ke file txt             
            $filename = 'lib/receipt-'.$this->session->userdata('nik').'-'.$this->session->userdata('no_shift').'.txt';
            $file = fopen($filename,'w');
            fwrite($file,$report);
            fclose($file);
            //output to ajax
            _e($report);
        }
        else
        {
           _e($sales->temp_sales); 
        }
    }
    /**
    *refund barang
    */
    function trans_refund()
    {
        //retrieve data dari client, request ajax
        $id_tukar = $this->input->post('id_tukar');
        $qty_tukar = $this->input->post('qty_tukar');
        $id_pengganti = $this->input->post('id_pengganti');
        $qty_pengganti = $this->input->post('qty_pengganti');
        $disc_pengganti = $this->input->post('disc_pengganti');
        $id_pramu = $this->input->post('id_pramu');
        $id_transaksi = time();
        $total = $this->input->post('total');
        $total = floor($total/100) * 100;
        //tambah data ke tabel item_transaksi dulu        
        $data = array(
            'id_transaksi'=>$id_transaksi,            
            'tanggal'=>date('Y-m-d'),
            'total'=>$total,
            'diskon'=> 0,
            'id_kasir'=>$this->session->userdata('nik'),
            'id_pramuniaga'=>$id_pramu          
        );
        $this->load->model('transaksi');
        if($this->transaksi->add_transaksi($data))
        {
            //tambah data ke item_transaksi_penjualan
            $this->load->model('item_transaksi');
            $this->load->model('barang');
            //sambung data tukar dan data
            for($i=0;$i<count($id_pengganti);$i++)
            {
                $data = array(
                    'id_transaksi'=>$id_transaksi,
                    'id_barang'=>$id_pengganti[$i],
                    'qty'=> $qty_pengganti[$i],
                    'diskon'=>$disc_pengganti[$i]
                );                 
                
                if($this->item_transaksi->add_item_transaksi($data))
                {                    
                    //update stok barang pengganti (stok berkurang)
                    $this->barang->refund_barang($id_pengganti[$i],$qty_pengganti[$i],2);                   
                }               
            }
            //update stok barang yang ditukar (stok berkurang)
            for($i=0;$i<count($id_tukar);$i++)
            {
                $data = array(
                    'id_transaksi'=>$id_transaksi,
                    'id_barang'=>$id_tukar[$i],
                    'qty'=> (-1 * $qty_tukar[$i]),
                    'diskon'=> 0
                );
                if($this->item_transaksi->add_item_transaksi($data))
                {
                    $this->barang->refund_barang($id_tukar[$i],$qty_tukar[$i],1);
                }
            }
            _e(1);
        }
        else
        {
            _e(0);
        }        
    }
    /**
    *Print receipt
    */
    function print_receipt()
    {
        //baca input parameter
        $cash = $this->input->post('cash');
        //resi transaksi normal
        if($this->input->post('option')==1)
        {
            //siapkan data resi yang akan diprint
            $this->load->model('transaksi');
            $query = $this->transaksi->last_transaksi();
            if($query->num_rows() > 0)
            {
                //ambil item transaksi yang ada di tabel item_transaksi_penjualan
                $transaksi = $query->row();
                $this->load->model('karyawan');
                $pramu="";
                if(!empty($transaksi->id_pramuniaga))
                {
                    $arr = explode(',',$transaksi->id_pramuniaga);
                    if(count($arr) == 1)
                    {
                        $query = $this->karyawan->get_karyawan($arr[0]);
                        $pramu = $query->row();
                        $pramu = ucwords($pramu->nama);
                    }
                    else
                    {                        
                        foreach($arr as $row)
                        {
                            $query = $this->karyawan->get_karyawan($row);
                            $temp = $query->row();
                            $pramu .= ucwords($temp->nama).',';
                        }
                    }
                }
                $this->load->model('item_transaksi');
                $query = $this->item_transaksi->get_item_transaksi($transaksi->id_transaksi);                
                $detail = "";
                $sub_detail = "";
                if($query->num_rows() > 0)
                {
                    $this->load->model('barang');         
                    $subtotal = 0;     
                    $all = 0;                    
                    foreach($query->result() as $row)
                    {
                        $brg_query = $this->barang->get_barang($row->id_barang,2);
                        $barang = $brg_query->row();
                        $sub = $row->qty * $barang->harga;
                        //$subtotal += $sub;
                        $all += $row->qty;
                        $detail.=$row->id_barang.' '.$barang->nama.'#'.chr(10);
                        
                        if($row->diskon > 0) 
                        {
                            $sub = $sub *(1-($row->diskon/100));
                            $detail.='  '.$row->qty.' @'.number_format($barang->harga,0,',','.').' disc '.$row->diskon.'% #'.chr(10);
                            $sub_detail = '= '.number_format($sub,0,',','.').'#'.chr(10);
                            $detail .= $this->spacer(35-strlen($sub_detail)).$sub_detail;
                        }                        
                        else
                        {
                            $sub_detail = $row->qty.' @'.number_format($barang->harga,0,',','.').' = '.number_format($sub,0,',','.').'#'.chr(10);
                            $detail .= $this->spacer(35-strlen($sub_detail)).$sub_detail;
                        }
                        $subtotal += $sub; 
                    }
                    //open template file
                    $file = fopen('lib/template-resi.txt','r');
                    $resi = '';
                    while(!feof($file))                    
                    {
                        $resi .= fgets($file);
                    }
                    fclose($file);
                    //do resi stuff ..hehe apalah namanya itu..nyusun resinye...
                    $resi = str_replace('<jam>',$transaksi->jam,$resi); //tulis no resi
                    $resi = str_replace('<tanggal>',$transaksi->tanggal,$resi); //tulis tanggal resi
                    $resi = str_replace('<detail>',$detail,$resi);//tulis detail transaksi                    
                    $resi = str_replace('<all>',$all.' items',$resi);//all item                     
                    $resi = str_replace('<subtotal>',$this->spacer(22-strlen(number_format($subtotal,0,',','.'))).number_format($subtotal,0,',','.'),$resi);                    
                    $resi = str_replace('<total>',$this->spacer(23-strlen(number_format($transaksi->total,0,',','.'))).number_format($transaksi->total,0,',','.'),$resi); 
                    $disc = '';
                    if($transaksi->diskon > 0) 
                    {
                        $discno = ($transaksi->diskon/100) * $subtotal;
                        $disc = 'Diskon   = '.$transaksi->diskon.'% x '.number_format($subtotal,0,',','.').'#'.chr(10);
                        $disc .= '         = '.$this->spacer(22-strlen(number_format($discno,0,',','.'))).number_format($discno,0,',','.').'#';
                    } 
                    $resi = str_replace('<disc>',$disc,$resi);
                    $resi = str_replace('<kasir>',$this->data['userinfo'],$resi);                    
                    $resi = str_replace('<pramu>',$pramu,$resi);
                    if(empty($transaksi->no_cc)) 
                    {
                        $resi = str_replace('<cash>',$this->spacer(23 - strlen(number_format($cash,0,',','.'))).number_format($cash,0,',','.'),$resi);  
                    }
                    else 
                    {
                        $tunai = '[CdtCrd]'.number_format($cash,0,',','.');
                        $resi = str_replace('<cash>',$this->spacer(23 - strlen($tunai)).'[CdtCrd]'.number_format($cash,0,',','.'),$resi);
                    }
                    $cashback = $cash - $transaksi->total;                    
                    $resi = str_replace('<cashback>',$this->spacer(23 - strlen(number_format($cashback,0,',','.'))).number_format($cashback,0,',','.'),$resi);                               
                    //open file to write
                    $filename = 'lib/receipt-'.$this->session->userdata('nik').'-'.$this->session->userdata('no_shift').'.txt';
                    $file = fopen($filename,'w');
                    fwrite($file,$resi);
                    fclose($file);
                    //output receipt for printing 
                    _e($resi);
                }
            }
        }
        //resi transaksi terakhir
        if($this->input->post('option')==2)
        {
            //read receipt from file
            $filename = 'lib/receipt-'.$this->session->userdata('nik').'-'.$this->session->userdata('no_shift').'.txt';
            $file = fopen($filename,'r');
            $resi = fread($file,filesize($filename));
            //output resi for printing
            _e($resi);
        }
        //resi untuk refund
        if($this->input->post('option')==3)
        {
            //baca input parameter
            $cash = $this->input->post('cash');
            $id_tukar = $this->input->post('brg_tukar');
            $qty_tukar = $this->input->post('qty_tukar');
            //siapkan data resi yang akan diprint
            $this->load->model('transaksi');
            $query = $this->transaksi->last_transaksi();
            if($query->num_rows() > 0)
            {
                //ambil data barang pengganti yang disimpan sebagai transaksi penjualan
                $transaksi = $query->row();              
                $this->load->model('item_transaksi');
                $query = $this->item_transaksi->get_item_transaksi($transaksi->id_transaksi);                
                $tukar = "";
                $pengganti ="";
                $total_tukar = 0;
                $total_pengganti = 0;
                if($query->num_rows() > 0)
                {
                    $this->load->model('barang');        
                    
                    //susun barang pengganti untuk ditaro resi
                    $total_item = 0;
                    foreach($query->result() as $row) 
                    {
                        $brg_query = $this->barang->get_barang($row->id_barang,2);
                        $barang = $brg_query->row();
                        $pengganti.= $row->id_barang.' '.$barang->nama.'#'.chr(10);
                        if($row->diskon > 0)
                        {
                            $harga_pengganti = $row->qty * $barang->harga * (1 - $row->diskon/100);
                            $tmp = '  '.$row->qty.' @'.number_format($barang->harga,0,',','.').' disc '.$row->diskon.'% = '.number_format($harga_pengganti,0,',','.').'#'.chr(10);
                            $pengganti.= $this->spacer(36-strlen($tmp)).$tmp; 
                        }
                        else
                        {
                            $harga_pengganti = $row->qty * $barang->harga;
                            $tmp = '  '.$row->qty.' @'.number_format($barang->harga,0,',','.').' = '.number_format($harga_pengganti,0,',','.').'#'.chr(10);
                            $pengganti.= $this->spacer(36-strlen($tmp)).$tmp;
                        }
                        $total_item += $row->qty;
                        $total_pengganti += $harga_pengganti;
                    }
                    //susun barang tukar untuk ditaro resi
                    for($i=0;$i<count($id_tukar);$i++)
                    {
                        $brg_query = $this->barang->get_barang($id_tukar[$i],2);
                        $barang = $brg_query->row();
                        $harga_tukar = $qty_tukar[$i] * $barang->harga;                        
                        $tukar .=$barang->id_barang.' '.$barang->nama.'#'.chr(10);
                        $tmp = '  '.$qty_tukar[$i].' @'.number_format($barang->harga,0,',','.').' = '.number_format($harga_tukar,0,',','.').'#'.chr(10);
                        $tukar.= $this->spacer(36-strlen($tmp)).$tmp;
                        $total_tukar += $harga_tukar;
                    }
                    $total = $transaksi->total;
                    //ambil data nama pramuniaga
                    $this->load->model('karyawan');
                    $pramu="";
                    if(!empty($transaksi->id_pramuniaga))
                    {
                        $arr = explode(',',$transaksi->id_pramuniaga);
                        if(count($arr) == 1)
                        {
                            $query = $this->karyawan->get_karyawan($arr[0]);
                            $pramu = $query->row();
                            $pramu = ucwords($pramu->nama);
                        }
                        else
                        {                        
                            foreach($arr as $row)
                            {
                                $query = $this->karyawan->get_karyawan($row);
                                $temp = $query->row();
                                $pramu .= ucwords($temp->nama).',';
                            }
                        }
                    }
                    //open template file
                    $file = fopen('lib/refund-resi.txt','r');
                    $resi = '';
                    while(!feof($file))                    
                    {
                        $resi .= fgets($file);
                    }
                    fclose($file);
                    //do resi stuff ..hehe apalah namanya itu..nyusun resinye...
                    $resi = str_replace('<jam>',$transaksi->jam,$resi); //tulis no resi
                    $resi = str_replace('<tanggal>',$transaksi->tanggal,$resi); //tulis tanggal resi
                    $resi = str_replace('<tukar>',$tukar,$resi);//tulis detail barang ditukar
                    $resi = str_replace('<pengganti>',$pengganti,$resi);//tulis detail barang ditukar               
                    $resi = str_replace('<all>',$total_item.' items',$resi);         
                    $tmp = number_format($total,0,',','.');
                    $resi = str_replace('<kasir>',$this->data['userinfo'],$resi);                                        
                    $resi = str_replace('<pramu>',$pramu,$resi);                                        
                    $tmp = number_format($cash,0,',','.');
                    $cashback = $cash - $total;                    
                    $tmp = number_format($cashback,0,',','.');
                    //open file to write
                    $filename = 'lib/receipt-'.$this->session->userdata('nik').'-'.$this->session->userdata('no_shift').'.txt';
                    $file = fopen($filename,'w');
                    fwrite($file,$resi);
                    fclose($file);
                    //output receipt for printing 
                    _e($resi);
                }
            }
        }      
    }
    function spacer($width) 
    {
        $spacer = "";
        for($i=0;$i<$width; $i++)
        {
            $spacer .= " ";
        }
        return $spacer;
    }
    /**
    *Autocomplete pramuniaga
    */
    function pramu_autocomplete($param="")
    {
        //break param
        $arr = explode(',',$param);
        $name="";
        if(count($arr) > 1)
        {
            $name = $arr[count($arr)-1];           
            unset($arr[count($arr)-1]);
            $param = implode(',',$arr);
        }           
        else
        {
            $name=$param;
            $param="";
        }        
        $this->load->model('karyawan');
        $query = $this->karyawan->get_pramuniaga($name,$param);
        foreach($query->result_array() as $row)
        {
            $data[]=$row;
        }
        if(isset($data))
        {
            _e(json_encode($data));
        }
    }
    /*get current date*/
    function current_date()
    {
        date_default_timezone_set("Asia/Jakarta");
        $date = date('Y-m-d');
        $arr = explode('-',$date);
        switch($arr[1])
        {
            case '01' : $month="Januari";break;
            case '02' : $month="Februari";break;
            case '03' : $month="Maret";break;
            case '04' : $month="April";break;
            case '05' : $month="Mei";break;
            case '06' : $month="Juni";break;
            case '07' : $month="Juli";break;
            case '08' : $month="Agustus";break;
            case '09' : $month="September";break;
            case '10' : $month="Oktober";break;
            case '11' : $month="November";break;
            case '12' : $month="Desember";break;
        }
        return $arr[2].' '.$month.' '.$arr[0];
    }
    /*retrieve item from database*/
    function getItem()
    {
        $item_code = $this->input->post('id_barang');
        $this->load->model('barang');
        $query = $this->barang->get_barang($item_code,1);
        if(isset($query))
        {
            $barang = $query->row_array();
            _e(json_encode($barang));
        }
        else
        {
            _e(0);
        }        
    }
    /**
    *get item untuk keperluan refund
    */
    function get_item()
    {
        $item_code = $this->input->post('id_barang');
        $opsi = $this->input->post('opsi');    
        $this->load->model('barang');
        if($opsi == 1)
        {
            $query = $this->barang->get_barang($item_code,3);
        }
        else if($opsi == 2)
        {
            $query = $this->barang->get_barang($item_code,2);
        }
        if(isset($query))
        {
            $barang = $query->row_array();
            _e(json_encode($barang));
        }
        else
        {
            _e(0);
        }  
    }
    /**
    *search item based on id_barang and nama barang
    */
    function search_item()
    {
        $keywords = $this->input->post('keywords');
        if(!empty($keywords))
        {
            $this->load->model('barang');
            $query = $this->barang->search_barang($keywords);
            if(isset($query))
            {
                if($query->num_rows() > 1)
                {
                    foreach($query->result() as $row)
                    {
                        $barang[] = $row;
                    }
                    _e(json_encode($barang));
                }
                else
                {
                    $barang = $query->row();
                    _e(json_encode($barang));
                }
            }
            else
            {
                _e(0);
            }
        }
    }
}