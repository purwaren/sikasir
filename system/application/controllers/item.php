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
            $this->load->model('barang');
            $sukses = '';
            $gagal = '';
            if(!empty($id_mutasi_keluar) && !empty($tanggal))
            {
                $id_barang = $this->input->post('id_barang');
                $qty= $this->input->post('qty');
                for($i=0; $i<count($id_barang); $i++)
                {
                    $data = array(
                        'id_retur'=>$id_mutasi_keluar,
                        'id_barang'=>$id_barang[$i],
                        'tanggal'=>$tanggal,
                        'qty'=>$qty[$i]
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
                    $this->data['err_msg'] .= '<span style="color:green">Kode barang: '.$sukses.' berhasil dimutasikan keluar</span><br />';
                }
                if(!empty($gagal)) 
                {
                    $this->data['err_msg'] .= '<span style="color:red">Kode barang: '.$gagal.' gagal dimutasikan keluar</span><br />';
                }
            }
            else
            {
                $this->data['err_msg'] = '<span style="color:red">Kode bon dan tanggal tidak boleh dikosongkan</spon>';
            }
        }
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
    function manage()
    {
        if($this->input->post('submit_item_manage'))
        {
            if($this->input->post('date_input'))
            {
                //ambil data barang masuk pada tanggal tersebut
                $this->load->model('barang');
                $query = $this->barang->get_barang_masuk($this->input->post('date_input'));                
                if($query->num_rows() >= 1)
                {
                    $i=0;
                    $row_data = '';
                    foreach($query->result() as $row)
                    {
                        $row_data .= '<tr>
                                        <td>'.++$i.'</td><td>'.$row->id_mutasi_masuk.'</td><td>'.$row->id_barang.'</td><td>'.$row->nama.'</td><td>'.$row->kelompok_barang.'</td><td style="text-align:right;padding-right:15px;">Rp '.number_format($row->harga,2,',','.').'</td><td>'.$row->qty.'</td>
                                        <td>
                                            <span class="button">&nbsp;<input type="button" class="button" value="Edit" onclick="editBarang('.$i.',\''.$row->id_barang.'\',\''.$row->id_mutasi_masuk.'\')"/></span> 
                                            <!--<span class="button">&nbsp;<input type="button" class="button" value="Detail" onclick="viewDetail('.$i.',\''.$row->id_barang.'\',\''.$row->id_mutasi_masuk.'\')"/></span>                                                               
                                            <span class="button">&nbsp;<input type="button" class="button" value="Remove" onclick="deleteBarang('.$i.',\''.$row->id_barang.'\',\''.$row->id_mutasi_masuk.'\')"/></span>-->                   
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
        }
        $this->load->view('item-manage',$this->data);
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
                                                    <td>'.++$i.'</td><td>'.$row->id_barang.'</td><td>'.$row->nama.'</td>
                                                    <td>'.number_format($row->harga,0,',','.').'</td><td>'.$row->diskon.'</td><td>'.$row->total_barang.'</td>
                                                    <td>'.$row->mutasi_masuk.'</td><td>'.$row->mutasi_keluar.'</td><td>'.$row->stok_barang.'</td>
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
}
//End of file item.php
//Location: application/controller/item.php