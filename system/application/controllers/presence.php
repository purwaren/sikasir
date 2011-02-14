<?php
/**
*Presence Controller
*@Author: PuRwa
*Desc : This controller was design for presence module
*/
class Presence extends Controller {
    /*class field */
    protected $data;
    /*Controller constructor*/
	function Presence()
	{
		parent::Controller();            
		$this->data['page'] ='presence';
        
        if($this->session->userdata('logged_in'))
        {
            $this->load->model('karyawan');
            $query = $this->karyawan->get_karyawan($this->session->userdata('nik'));
            $data_karyawan = $query->row();
            $this->data['userinfo'] = $data_karyawan->nama;
            $this->data['jabatan'] = $this->session->userdata('jabatan');
            $this->data['now'] = strtoupper(date('M')).'<br />'.date('d');
            //only supervisor and absensi allowed
            if($this->data['jabatan'] != 'supervisor' && $this->data['jabatan'] != 'absensi')
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
    * Fungsi untuk absensi
    */
    function check($param='')
    {
        if($param == 'arrive')
            $this->load->view('presence-check-arrive',$this->data);
        else if($param == 'return')
            $this->load->view('presence-check-return',$this->data);
        else
            $this->load->view('presence-check-arrive',$this->data);
    }
    /**
    *simpan data absensi
    * opsi : 1-datang, 2-pulang
    */
    function save_presence() 
    {
        date_default_timezone_set("Asia/Jakarta");
        $id_karyawan = $this->input->post('id_karyawan');
        $opsi = $this->input->post('opsi');
        //cek apakah karyawan terdaftar di toko
        $this->load->model('karyawan');
        $query = $this->karyawan->get_karyawan($id_karyawan);
        if($query->num_rows() > 0)
        {
            $karyawan = $query->row();
            //check apakah sudah absen, absen hanya boleh sekali
            $this->load->model('absensi');
            $data = array('NIK'=>$id_karyawan,'tanggal'=>date("Y-m-d"));        
            $query = $this->absensi->get_presence_status($data);
            if($opsi == 1)
            {
                if($query->num_rows() > 0) //sudah pernah absen
                {
                    $this->data['err_msg'] = '';                    
                }
                else //belum pernah absen
                {
                    //set jam masuk karyawan
                    $this->absensi->set_presence($id_karyawan, time());           
                }
                $query = $this->absensi->get_presence_status($data);                   
                //status absensi
                $absensi = $query->row();
                if(empty($absensi->pulang))
                    $absensi->plg = 'Belum';
                $data = array('NIK'=>$absensi->NIK,'nama'=>$karyawan->nama,'status'=>$absensi->status, 'datang'=>$absensi->dtg,'pulang'=>$absensi->plg);
                _e(json_encode($data));
            }
            else if($opsi == 2)
            {
                //yang boleh absen pulang harus yang udah absen masuk
                if($query->num_rows() > 0)
                {                    
                    $absensi = $query->row();
                    if($absensi->status == 'masuk') 
                    {
                        //update jam pulang karyawan
                        $data = array('NIK'=>$absensi->NIK,'tanggal'=>$absensi->tanggal,'pulang'=>time());
                        if(empty($absensi->pulang))
                            $this->absensi->update_presence($data);
                        $query = $this->absensi->get_presence_status($data);                   
                        //status absensi
                        $absensi = $query->row();
                        if(empty($absensi->pulang))
                            $absensi->plg = 'Belum';
                        $data = array('NIK'=>$absensi->NIK,'nama'=>$karyawan->nama,'status'=>$absensi->status, 'datang'=>$absensi->dtg,'pulang'=>$absensi->plg);
                        _e(json_encode($data));                      
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
        }        
    }
    /**
    *simpan status absensi (sakit, izin , alpha)
    */
    function save_status()
    {
        if($this->data['jabatan'] == 'supervisor')
        {
            $nik = $this->input->post('id_karyawan');
            $status = $this->input->post('status');
            $this->load->model('absensi');
            $success = 0;
            for($i=0;$i<count($nik);$i++)
            {
                if($this->absensi->update_presence_status($nik[$i],$status[$i]))
                {
                    $success = 1; 
                }
                else
                {
                    $success = 0;
                    break;
                }
            }
            _e($success);
        }
        else 
        {
            _e(0);
        }
    }
    /**
    *Menampilkan data absen pada tanggal tertentu,
    */
    function manage()
    {        
        $this->data['result']='';
        if($this->input->post('submit_absensi'))
        {
            $tanggal = $this->input->post('date_absensi');
            $this->data['tgl_absensi'] = $tanggal;
            if(!empty($tanggal))
            {
                $this->load->model('absensi');
                $query = $this->absensi->get_presence($tanggal);
                if($query->num_rows() > 0)
                {
                    $tgl = explode('-',$tanggal);
                    $table = ' <h3 style="text-align:center">DATA ABSENSI KARYAWAN <br /> TANGGAL : '.$tgl[2].' '.$this->month_to_string($tgl[1]).' '.$tgl[0].'</h3>
                                <table class="table-data" cellspacing="0" cellpadding="0" >
                                <tr><!--<td class="head">No</td>--><td class="head">NIK</td><td class="head">Nama Karyawan</td><td class="head">Datang</td><td class="head">Pulang</td><td class="head">Keterangan</td><td class="head">Action</td></tr>';
                    $i=0;
                    foreach($query->result() as $row)
                    {
                        if(!empty($row->datang))
                            $dtg = $row->dtg;
                        else
                            $dtg = 'Belum';
                        if(!empty($row->pulang))
                            $plg = $row->plg;
                        else
                            $plg = 'Belum';
                        $table .= '<tr><!--<td></td>--><td>'.$row->NIK.'</td><td>'.$row->nama.'</td><td>'.$dtg.'</td><td>'.$plg.'</td><td>'.$row->status.'</td>
                                <td>
                                    <span class="button">&nbsp;<input type="button" class="button" value="Detail" onclick="viewDetailAbsensi(\''.$row->NIK.'\',\''.$row->tanggal.'\')"/></span>
                                    <span class="button">&nbsp;<input type="button" class="button" value="Edit" onclick="editAbsensi(\''.$row->NIK.'\',\''.$row->tanggal.'\')"/></span>
                                    <span class="button">&nbsp;<input type="button" class="button" value="Remove" onclick="removeAbsensi(\''.$row->NIK.'\',\''.$row->tanggal.'\',\''.$row->nama.'\')"/></span>
                                </td></tr>';
                    }
                    $this->data['result'] = $table.'</table>';
                }
                else
                {
                    $this->data['err_msg'] = 'Data tidak ditemukan';
                }
            }
            else
            {
                $this->data['err_msg'] = 'Tanggal tidak boleh dikosongkan';
            }
        }
        $this->load->view('presence-manage',$this->data);        
    }
    /**
    *Menampilkan detail absensi    
    */
    function view($nik="")
    {
        if(!empty($nik))
        {
            $tanggal = $this->uri->segment(4);
            $this->data['tgl_absensi'] = $tanggal;
            $this->load->model('absensi');
            $query = $this->absensi->get_presence_detail($nik,$tanggal);
            if($query->num_rows() > 0)
            {
                $this->data['detail'] = $query->row();
            }
            else
            {
                $this->data['err_msg'] = 'Maaf data tidak ditemukan';
            }            
        }
        $this->load->view('presence-detail',$this->data);
    }
    /**
    *fungsi ini digunakan untuk ngedit status absensi
    */
    function edit($nik="")
    {        
        $this->data['err_msg'] = '';
        $this->load->model('absensi');
        if(!empty($nik))
        {
            $tanggal = $this->uri->segment(4);                
            $query = $this->absensi->get_presence_detail($nik,$tanggal);
            if($query->num_rows() > 0)
            {
                $this->data['detail'] = $query->row();
            }
            else
            {
                $this->data['err_msg'] = 'Maaf data tidak ditemukan';
            }
        }
        if($this->input->post('submit_edit_absensi'))
        {
            $nik = $this->input->post('nik');
            $tgl = $this->input->post('tanggal');
            $status = $this->input->post('status');
            if($this->absensi->update_presence_status($nik,$status,$tgl))
            {
                $query = $this->absensi->get_presence_detail($nik,$tgl);
                if($query->num_rows() > 0)
                {
                    $this->data['detail'] = $query->row();
                    $this->data['msg'] = '<span style="color:green">Data telah disimpan</span>';
                }
            }
        }
        $this->load->view('presence-edit',$this->data);        
    }
    /**
    *fungsi untuk menghapus data absensi
    */
    function remove()
    {
        if($this->data['jabatan'] == 'supervisor')
        {
            $nik = $this->input->post('nik');
            $tgl = $this->input->post('tanggal');
            if(!empty($nik) && !empty($tgl))
            {
                $this->load->model('absensi');
                if($this->absensi->remove_presence($nik,$tgl))
                {
                    _e(1);
                }
                else
                {
                    _e(0);
                }
            }
        }
        else
        {
            _e(0);
        }
    }
    /**
    * Laporan rekap absensi
    */
    function report() 
    {
        if($this->data['jabatan'] == 'supervisor') 
        {
            $this->load->model('absensi');
            //klo pencet tombol display, tampilin rekapnya
            if($this->input->post('submit_presence_report') || $this->input->post('submit_print_report'))
            {
                $opsi = $this->input->post('opsi');
                $month = $this->input->post('month');
                $year = $this->input->post('year');
                //rekap kehadiran
                if($opsi == 1)
                {
                    //set background untuk header
                    $background = '';
                    if($this->input->post('submit_print_report')) 
                    {
                        $background = 'background-color: #cdcdcd;font-weight:bold;';
                    }
                    //rekap kehadiran
                    $this->load->model('karyawan');
                    $query = $this->karyawan->get_all_karyawan();
                    if($query->num_rows() > 0)
                    {
                        $head = '<div style="text-align:center;overflow:auto;width:100%"><h3>REKAPITULASI KEHADIRAN KARYAWAN</h3>
                                <table style="text-align:left">
                                    <tr><td style="width: 50px">CABANG</td><td>: '.config_item('shop_name').'</td></tr>
                                    <tr><td style="width: 50px">BULAN </td><td>: '.strtoupper(month_to_string($month)).' '.$year.'</td></tr>
                                </table><br />';
                        $head .= '<table class="table-data" cellspacing="0" cellpadding="0" style="border:1px solid;"><tr><td class="head" style="'.$background.'">NIK</td>';
                        for($i=0;$i<=max_day($month,$year);)
                        {
                            $head .= '<td class="head" style="'.$background.'">'.++$i.'</td>';
                        }
                        $head .= '<td class="head" style="'.$background.'">M</td><td class="head" style="'.$background.'">L</td><td class="head" style="'.$background.'">I</td><td class="head" style="'.$background.'">A</td></tr>';
                        $row_data = '';
                        $karyawan = $query->result();
                        $num = 0;
                        foreach($karyawan as $row)
                        {
                            $num++;
                            $query = $this->absensi->rekap_hadir_bulanan($row->NIK,$month,$year);
                            $row_absen = '<tr><td>'.$row->NIK.'</td>';
                            if($query->num_rows() > 0)
                            {                           
                                $data = $query->result();                            
                                $j=0;
                                $total_masuk = 0;
                                $total_libur = 0;
                                $total_izin = 0;
                                $total_alpha = 0;
                                for($i = 0;$i<=max_day($month,$year);$i++)
                                {
                                    if(isset($data[$j]) && $data[$j]->tgl == ($i+1))
                                    {
                                        if($data[$j]->status == 'masuk')
                                        {
                                            $status = 'M';
                                            $total_masuk++;
                                        }
                                        else if($data[$j]->status == 'izin')
                                        {
                                            $status = 'I';
                                            $total_izin++;
                                        }
                                        else if($data[$j]->status == 'alpha')
                                        {
                                            $status = 'A';
                                            $total_alpha++;
                                        }
                                        else if($data[$j]->status == 'libur/off')
                                        {
                                            $status = 'L';
                                            $total_libur++;
                                        }
                                        $row_absen .= '<td style="background-color:#dedede">'.$status.'</td>';
                                        $j++;                                    
                                    }
                                    else
                                    {
                                        $row_absen .= '<td>&nbsp;</td>';
                                    }
                                }
                                $row_absen .= '<td style="background-color:#dedede">'.$total_masuk.'</td><td style="background-color:#dedede">'.$total_libur.'</td>
                                                <td style="background-color:#dedede">'.$total_izin.'</td><td style="background-color:#dedede">'.$total_alpha.'</td></tr>';
                            }
                            else
                            {
                                for($i=0;$i<=max_day($month,$year);$i++)
                                {
                                    $row_absen .= '<td>&nbsp;</td>';
                                }
                                $row_absen .= '<td>0</td><td>0</td><td>0</td><td>0</td></tr>';
                            }
                            $row_data .= $row_absen;
                            if($num%60==0)
                            {
                                $list[] = $row_data;
                                $row_data = '';
                            }
                        }
                        if(!empty($row_data))
                        {
                            $list[] = $row_data;
                        }
                        $foot = '</table>
                                <p style="text-align:left;margin:0;">Keterangan :</p>
                                <ol style="text-align:left">
                                    <li>M = Masuk</li>
                                    <li>I = Izin</li>
                                    <li>A = Alpha</li>
                                    <li>L = Libur/Off</li>
                                </ol>                            
                                </div>';
                        $this->data['report'] = $head;
                        foreach($list as $row)
                        {
                            $this->data['report'] .= $row;
                        }
                        $this->data['report'] .= $foot;
                        
                        if($this->input->post('submit_print_report'))
                        {
                            $this->cetak_pdf($opsi,$head,$list,$foot);exit;
                        }
                    }
                }
                //rekap jam kerja
                else if($opsi == 2)
                {
                    //rekap jam kerja                    
                    $query = $this->absensi->rekap_absen_bulanan($month,$year);                    
                    if($query->num_rows() > 0)
                    {
                        $head = '<div style="text-align:center">
                                    <h3>REKAPITULASI JAM KERJA KARYAWAN</h3>
                                <table style="text-align:left">
                                    <tr><td style="width: 50px">CABANG</td><td>: '.config_item('shop_name').'</td></tr>
                                    <tr><td style="width: 50px">BULAN </td><td>: '.strtoupper(month_to_string($month)).' '.$year.'</td></tr>
                                </table><br />';
                        if($this->input->post('submit_print_report'))
                        {
                            $head .=  '<table class="table-data" cellspacing="0" cellpadding="0" style="width:485px;border:1px solid;" >
                                        <tr>
                                            <td class="head" style="width:30px;background-color:#dedede;">No</td>
                                            <td class="head" style="width:60px;background-color:#dedede;">NIK</td>
                                            <td class="head" style="width:150px;background-color:#dedede;">Nama Karyawan</td>
                                            <td class="head" style="width:80px;background-color:#dedede;">Total Jam Kerja <br />(Jam)</td>
                                            <td class="head" style="width:85px;background-color:#dedede;">Total Jam Lembur <br />(Jam)</td>
                                            <td class="head" style="width:80px;background-color:#dedede;">Total Hadir <br />(hari)</td></tr>';
                        }
                        else
                        {
                            $head .=  '<table class="table-data" cellspacing="0" cellpadding="0" style="width:485px;" >
                                        <tr>
                                            <td class="head" style="width:30px;">No</td>
                                            <td class="head" style="width:60px;">NIK</td>
                                            <td class="head" style="width:150px;">Nama Karyawan</td>
                                            <td class="head" style="width:80px;">Total Jam Kerja <br />(Jam)</td>
                                            <td class="head" style="width:85px;">Total Jam Lembur <br />(Jam)</td>
                                            <td class="head" style="width:80px;">Total Hadir <br />(hari)</td></tr>';
                        }
                        $i=0;
                        $row_data='';
                        foreach($query->result() as $row)
                        {
                            $total_jam = $row->total_jam + ceil($row->total_menit/60);
                            $total_lembur = $total_jam - config_item('work_cycle')*$row->total_masuk;
                            if($total_lembur <= 0)
                                $total_lembur = 0;
                            $row_data .= '<tr>
                                            <td style="width:30px">'.++$i.'</td>
                                            <td style="width:60px">'.$row->NIK.'</td>
                                            <td style="width:150px">'.$row->nama.'</td>
                                            <td style="width:80px">'.$total_jam.'</td>
                                            <td style="width:85px">'.$total_lembur.'</td>
                                            <td style="width:80px">'.$row->total_masuk.'</td>
                                        </tr>';
                            //60 data per lembar
                            if($i%60 == 0) 
                            {
                                $list[] = $row_data;
                                $row_data = '';
                            }
                        }
                        if(!empty($row_data))
                        {
                            $list[] = $row_data;
                        }
                        $foot = '</table></div>';                        
                    }
                    //tampilin sebagai preview print
                    $this->data['report'] = $head;
                    foreach($list as $row)
                    {
                        $this->data['report'] .= $row;
                    }
                    $this->data['report'] .= $foot;             
                    if($this->input->post('submit_print_report'))
                    {
                        $this->cetak_pdf($opsi,$head,$list,$foot);exit;
                    }
                    
                }
            }
            //ambil bulan dan tahun untuk rekap absensi
            $query = $this->absensi->get_month();
            if($query->result() > 0)
            {
                $bulan = '<select name="month" style="width:100px">';
                foreach($query->result() as $row)
                {
                    $bulan .= '<option value="'.$row->bulan.'">'.month_to_string($row->bulan).'</option>';
                }
                $bulan .= '</select>';
            }
            $query = $this->absensi->get_year();
            if($query->num_rows() > 0)
            {
                $year = '<select name="year" style="width:60px">';
                foreach($query->result() as $row)
                {
                    $year .= '<option value='.$row->tahun.'>'.$row->tahun.'</option>';
                }
                $year .= '</select>';
            }
            $this->data['month'] = $bulan;
            $this->data['year'] = $year;
            $this->load->view('presence-report',$this->data);
        }
        else
        {
            redirect('home/error');
        }
    }
    /*
    **Funngsi cetak pdf
    */
    function cetak_pdf($opsi,$head,$list_item,$footer)
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
        $pdf->SetMargins(PDF_MARGIN_LEFT, 20, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 10);
        
        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
        
        //set some language-dependent strings
        $pdf->setLanguageArray($l); 

        // ---------------------------------------------------------
        //print yang rekap jam kerja
        if($opsi==2)
        {     
            $pdf->setPageUnit('mm');
            $size = array(216,330);               
            $pdf->setPageFormat($size,'P');               
            // set font
            $pdf->SetFont('dejavusans', '', 9);                
            foreach($list_item as $rows)
            {
                // add a page
                $pdf->AddPage();                
                $html = $head.$rows.$footer;
                //echo $html;
                $pdf->writeHTML($html, true, 0, true, 0);            
            }
                
        }
        //print yang rekap kehadiran
        if($opsi == 1)
        {            
            $pdf->setPageUnit('mm');
            $size = array(216,330);               
            $pdf->setPageFormat($size,'L');               
            // set font
            $pdf->SetFont('dejavusans', '', 9);                
            foreach($list_item as $rows)
            {
                // add a page
                $pdf->AddPage();                
                $html = $head.$rows.$footer;
                //echo $html;
                $pdf->writeHTML($html, true, 0, true, 0);            
            }
        }
        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('tes.pdf', 'I');     
            
    }
    /**
    *konversi bulan dari angka ke string
    */
    function month_to_string($month)
    {
        $str = '';
        switch($month)
        {
            case 1 : $str = 'Januari';break;
            case 2 : $str = 'Februari';break;
            case 3 : $str = 'Maret';break;
            case 4 : $str = 'April';break;
            case 5 : $str = 'Mei';break;
            case 6 : $str = 'Juni';break;
            case 7 : $str = 'Juli';break;
            case 8 : $str = 'Agustus';break;
            case 9: $str = 'September';break;
            case 10 : $str = 'Oktober';break;
            case 11 : $str = 'November';break;
            case 12 : $str = 'Desember';break;
        }
        return $str;
    }
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */