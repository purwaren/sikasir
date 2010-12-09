<?php
/**
*Controller Checking
*Desc: This controller for checking
*/
class Checking extends Controller {
    /*class field */
    protected $data;
    /*Controller constructor*/
	function Checking()
	{
		parent::Controller();            
		$this->data['page'] ='checking';
        
        if($this->session->userdata('logged_in'))
        {
            $this->load->model('karyawan');
            $query = $this->karyawan->get_karyawan($this->session->userdata('nik'));
            $data_karyawan = $query->row();
            $this->data['userinfo'] = $data_karyawan->nama;
            $this->data['jabatan'] = $this->session->userdata('jabatan');
            $this->data['now'] = strtoupper(date('M')).'<br />'.date('d'); 
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
        $this->load->view('report',$this->data);
	}
    /**
    *input opname, mulai checking barang
    */
    function add()
    {
        if($this->input->post('submit_search_opname'))
        {
            $id_barang = $this->input->post('id_barang');
            if(!empty($id_barang))
            {
                $this->load->model('barang');            
                $query = $this->barang->get_barang($id_barang, 2);
                if($query->num_rows() > 0)
                {
                    $brg = $query->row();
                    $this->data['search_result'] = '<tr>
                                                        <td><input type="text" name="id_barang" value="'.$brg->id_barang.'" readonly="readonly"/></td>                                                        
                                                        <td><input type="text" value="'.$brg->nama.'" readonly="readonly"/></td>                                                        
                                                        <td><input type="text" name="stok_barang" id="stok_barang" value="'.$brg->stok_barang.'" readonly="readonly"/></td>                                                        
                                                        <td><input type="text" name="stok_opname" id="stok_opname" value="'.$brg->stok_opname.'" onkeyup="countBedaStok()"/></td>                                                        
                                                        <td><input type="text" name="beda_stok" id="beda_stok" value="'.($brg->stok_barang - $brg->stok_opname).'"/></td>                                                        
                                                    </tr>';
                }
            }
            else
            {
                $this->data['err_msg'] = 'Kode barang tidak boleh dikosongkan';
            }
        }
        if($this->input->post('submit_save_opname'))
        {
            
            $id_barang = $this->input->post('id_barang');
            $stok_opname = $this->input->post('stok_opname');
            if($stok_opname > 0)
            {
                //update data stok_opname, disimpan
                $this->load->model('barang');
                $data = array(
                    'id_barang'=>$id_barang,
                    'stok_opname'=>$stok_opname
                );
                //print_r($data);exit;
                if($this->barang->update_opname($data))
                {
                    $this->data['err_msg'] = '<span style="color:green">Stok opname telah disimpan</span>';
                }
                else
                {
                    $this->data['err_msg'] = 'Gagal menyimpan stok opname';
                }
            }
            
        }
        $this->load->view('checking-add',$this->data);
    }
    /**
    *fungsi untuk lihat opname
    */    
    function manage($param="")
    {
        if(!empty($param))
        {
            $kel_barang = $param;
            $this->load->model('barang');
            $query = $this->barang->get_opname($kel_barang);
            if($query->num_rows > 0)
            {
                $head ='<div><h3 style="text-align:center;margin:0;">LAPORAN STOK OPNAME</h3>
                                    <table style="text-align:left">
                                        <tr><td >CABANG</td><td>: '.config_item('shop_name').'</td></tr>
                                        <tr><td >KELOMPOK BARANG </td><td>: '.$kel_barang.'</td></tr>                                    
                                    </table>                                  
                                    <table cellspacing="0" cellpadding="2" border="1" width="100%">
                                        <tr>
                                            <td>No</td>                                        
                                            <td>Kode Label</td>
                                            <td>Nama Barang</td>
                                            <td>Mutasi <br /> Masuk</td>                                        
                                            <td>Mutasi <br /> Keluar</td>                                        
                                            <td>Stok <br /> Awal</td>
                                            <td>Stok <br /> Barang</td>
                                            <td>Stok <br /> Opname</td>                                        
                                            <td>Beda <br /> Stok</td>                                        
                                            <td>Harga Jual <br /> (Rp)</td>                                        
                                        </tr>';
                $row_data = '';
                //urusan paging                
                $i=0;
                $total_beda = 0;
                $total_opname= 0;
                $total_stok = 0;
                foreach($query->result() as $row)
                {
                    $row_data .= '<tr>
                                    <td>'.++$i.'</td><td>'.$row->id_barang.'</td><td >'.$row->nama.'</td>
                                    <td>'.$row->mutasi_masuk.'</td><td>'.$row->mutasi_keluar.'</td>
                                    <td>'.$row->stok_awal.'</td><td>'.$row->stok_barang.'</td>
                                    <td>'.$row->stok_opname.'</td><td>'.($row->stok_barang-$row->stok_opname).'</td>
                                    <td>'.number_format($row->harga,0,',','.').'</td>
                                </tr>';
                    $total_beda += ($row->stok_barang - $row->stok_opname);
                    $total_opname += $row->stok_opname;
                    $total_stok += $row->stok_barang;                    
                }                
                $row_total = '<tr><td colspan="6"> T O T A L</td>
                                <td >'.$total_stok.'</td><td >'.$total_opname.'</td>
                                <td >'.$total_beda.'</td><td >&nbsp;</td></tr>';
                $this->data['content'] = $head.$row_data.$row_total;       
            }
            $this->load->view('print-template',$this->data);
        }
        else 
        {
            if($this->input->post('submit_view_opname'))
            {
                $kel_barang = $this->input->post('kel_barang');
                $this->data['kel_barang'] = $kel_barang;
                if(!empty($kel_barang))
                {
                    $this->load->model('barang');
                    $query = $this->barang->get_opname($kel_barang);                    
                    if($query->num_rows() > 0)
                    {
                        $row_data = '';
                        //urusan paging                
                        $i=0;
                        $total_beda = 0;
                        $total_opname= 0;
                        $total_stok = 0;
                        foreach($query->result() as $row)
                        {
                            $row_data .= '<tr>
                                            <td>'.++$i.'</td><td>'.$row->id_barang.'</td><td>'.$row->nama.'</td>
                                            <td>'.$row->mutasi_masuk.'</td><td>'.$row->mutasi_keluar.'</td>
                                            <td>'.$row->stok_awal.'</td><td>'.$row->stok_barang.'</td>
                                            <td>'.$row->stok_opname.'</td><td>'.($row->stok_barang-$row->stok_opname).'</td>
                                            <td>'.number_format($row->harga,0,',','.').'</td>
                                        </tr>';
                            $total_beda += ($row->stok_barang - $row->stok_opname);
                            $total_opname += $row->stok_opname;
                            $total_stok += $row->stok_barang;
                        }
                        $row_total = '<tr><td colspan="6"><input type="hidden" name="kel_barang" value="'.$kel_barang.'"/> T O T A L</td><td>'.$total_stok.'</td><td>'.$total_opname.'</td><td>'.$total_beda.'</td><td></td></tr>';
                        $this->data['search_result'] = $row_data.$row_total;
                    }
                    else
                    {
                        $this->data['err_msg'] = 'Data tidak ditemukan';
                    }
                }
                else
                {
                    $this->data['err_msg'] =  'Kelompok barang tidak boleh dikosongkan';
                }
            }
            if($this->input->post('submit_cetak_opname'))
            {            
                $kel_barang = $this->input->post('kel_barang');            
                if(!empty($kel_barang))
                {
                    $this->load->model('barang');
                    $query = $this->barang->get_opname($kel_barang);
                    if($query->num_rows() > 0)
                    {
                        $head ='<div id="report-sales"><h3 style="text-align:center;font-size: 14px">LAPORAN STOK OPNAME <br /> MODE FASHION GROUP </h3>
                                    <table style="text-align:left">
                                        <tr><td style="width: 100px">CABANG</td><td>: '.config_item('shop_name').'</td></tr>
                                        <tr><td style="width: 100px">KELOMPOK BARANG </td><td>: '.$kel_barang.'</td></tr>                                    
                                    </table>
                                   <br />
                                    <table style="width: 940px;border: 1px solid;text-align: center;margin: 0px auto;" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td style="width:30px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">No</td>                                        
                                            <td style="width:65px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Kode Label</td>
                                            <td style="width:110px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Nama Barang</td>
                                            <td style="width:40px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Mutasi <br /> Masuk</td>                                        
                                            <td style="width:40px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Mutasi <br /> Keluar</td>                                        
                                            <td style="width:40px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Stok <br /> Awal</td>
                                            <td style="width:40px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Stok <br /> Barang</td>
                                            <td style="width:40px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Stok <br /> Opname</td>                                        
                                            <td style="width:40px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Beda <br /> Stok</td>                                        
                                            <td style="width:65px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Harga Jual <br /> (Rp)</td>                                        
                                        </tr>';
                        $row_data = '';
                        //urusan paging                
                        $i=0;
                        $total_beda = 0;
                        $total_opname= 0;
                        $total_stok = 0;
                        foreach($query->result() as $row)
                        {
                            $row_data .= '<tr>
                                            <td style="width:30px;">'.++$i.'</td><td style="width:65px;">'.$row->id_barang.'</td><td style="width:110px;">'.$row->nama.'</td>
                                            <td style="width:40px;">'.$row->mutasi_masuk.'</td><td style="width:40px;">'.$row->mutasi_keluar.'</td>
                                            <td style="width:40px;">'.$row->stok_awal.'</td><td style="width:40px;">'.$row->stok_barang.'</td>
                                            <td style="width:40px;">'.$row->stok_opname.'</td><td style="width:40px;">'.($row->stok_barang-$row->stok_opname).'</td>
                                            <td style="width:65px;">'.number_format($row->harga,0,',','.').'</td>
                                        </tr>';
                            $total_beda += ($row->stok_barang - $row->stok_opname);
                            $total_opname += $row->stok_opname;
                            $total_stok += $row->stok_barang;
                            if($i%65 == 0)
                            {
                                $list[] = $row_data;
                                $row_data='';
                            }
                        }
                        $list[] = $row_data;
                        $row_total = '<tr><td colspan="6" style="width:325px;text-align:center;"> T O T A L</td>
                                        <td style="width:40px;">'.$total_stok.'</td><td style="width:40px;">'.$total_opname.'</td>
                                        <td style="width:40px;">'.$total_beda.'</td><td style="width:65px;">&nbsp;</td></tr>';
                        $this->cetak_pdf(1,$head,$list,$row_total,'</table>');
                    }
                }
            }
            $this->load->view('checking-manage',$this->data);
        }
    }
    /**
    *Fungsi untuk konfirmasi ganti barang, jika sudah  ok, akan dilaporkan sebagai laporan penggantian barang
    */
    function confirm($param='')
    {
        //ambil data barang yang memiliki beda stok
        $this->load->model('barang');
        $query = $this->barang->get_ganti_barang(1);
        if($query->num_rows() > 0)
        {
            $head = '<table class="table-data" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="head">No</td><td class="head">Kode Barang</td><td class="head">Nama Barang</td>
                        <td class="head">Stok Barang</td> <td class="head">Stok Opname</td><td class="head">Beda Stok</td>
                        <td class="head">Harga Ganti</td><td class="head">Jumlah Ganti</td>
                    </tr>';
            $row_data = '';
            $i=0;
            $total_beda=0;
            $total_ganti=0;
            $total_stok = 0;
            $total_opname=0;
            foreach($query->result() as $row)
            {
                $beda = $row->stok_barang-$row->stok_opname;
                $harga_ganti = $row->harga *(1-10/100);
                $ganti = $beda * $harga_ganti;
                $row_data .= '<tr>
                                <td>'.++$i.'</td><td>'.$row->id_barang.'</td><td>'.$row->nama.'</td>
                                <td>'.$row->stok_barang.'</td><td>'.$row->stok_opname.'</td><td>'.$beda.'</td>
                                <td>'.number_format($harga_ganti,0,',','.').'</td>
                                <td>'.number_format($ganti,0,',','.').'</td>
                            </tr>';
                $total_stok += $row->stok_barang;
                $total_opname += $row->stok_opname;
                $total_beda += $beda;
                $total_ganti += $ganti;
                if($i%65 == 0)
                {
                    $list[] = $row_data;
                    $row_data = '';
                }
            }
            $list[] = $row_data;
            $row_total = '<tr><td colspan="3">T O T A L</td><td>'.$total_stok.'</td><td>'.$total_opname.'</td><td>'.$total_beda.'</td><td>&nbsp;</td><td>'.number_format($total_ganti,0,',','.').'</td></tr>';
            $foot = '</table>';            
            
            //setting pagination :
            $this->load->library('pagination');
            $config['base_url'] = base_url().'checking/confirm/';
            $config['total_rows'] = count($list);
            $config['per_page'] = '1';
            $this->pagination->initialize($config);
            
            $pagination = $this->pagination->create_links();
            if(empty($param)) 
            {
                $this->data['search_result'] = $head.$list[0].$row_total.$foot.$pagination;
            }
            else
            {
                $this->data['search_result'] = $head.$list[$param].$row_total.$foot.$pagination;
            }
        }
        else
        {
            $this->data['err_msg'] = 'Tidak ada penggantian barang';
        }
        $this->load->view('checking-confirm',$this->data);
        
    }
    function confirm_checking()
    {
        $username = $this->input->post('username');
        $passwd = $this->input->post('passwd');
        $this->load->model('accounting');
        $check = $this->accounting->get_pengguna($username,$passwd);
        if($check->num_rows() ==  1)
        {
            $person = $check->row();
            if($person->jabatan == 'supervisor')
            {
                $this->load->model('barang');
                //sebelum update after checking, catat dulu data penggantian barang, yaitu data dengan beda stok != 0, taro di table ganti barang
                $query = $this->barang->get_ganti_barang(2);
                if($query->num_rows() > 0)
                {
                    foreach($query->result() as $row)
                    {
                        $harga_ganti = $row->harga * (1 - 10/100);
                        $data = array(
                            'id_barang'=>$row->id_barang,
                            'tanggal'=>date('Y-m-d'),
                            'harga_ganti'=>$harga_ganti,
                            'qty'=>$row->beda_stok
                        );
                        $this->barang->insert_ganti_barang($data);
                    }
                    if($this->barang->update_after_checking())
                    {
                        _e(1);
                    }                
                }
                else
                {
                    _e(-1);
                }
            }
            else
            {
                _e(0);
            }
        }
        else
        {
            _e(0);
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
        $pdf->SetTitle('Laporan Penjualan Barang');
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
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        //$pdf->SetFont('dejavusans', '', 8);        
        if($opsi == 1) //cetak laporan untuk penjualan harian
        {
            $pdf->setPageUnit('mm');
            $size = array(216,330);
            $pdf->SetFont('dejavusans', '', 8);
            $pdf->setPageFormat($size,'P');
        }
        
        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
        
        //set some language-dependent strings
        $pdf->setLanguageArray($l); 

        // ---------------------------------------------------------
        
        $i = 0;
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
        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('Laporan.pdf', 'I');     
            
    }
}

//End of file checking.php
//location system/application/controller