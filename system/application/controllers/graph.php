<?php
/**
*Graph Controller
*@Author: PuRwa
*Desc : This controller is used to generate chart and graph
*/
class Graph extends Controller {
    /*class field */
    protected $data;
    /*Controller constructor*/
	function Graph()
	{
		parent::Controller();            
		$this->data['page'] ='graph';
        
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
        if($this->session->userdata('logged_in') != TRUE)
        {
            redirect('home/login');
        }
        $this->load->view('home',$this->data);
	}
    /**
    *Generate graph for sales
    *1. daily sales along month    
    */
    function sales()
    {
        $this->load->model('transaksi');
        if($this->input->post('submit_graph_sales') || $this->input->post('submit_graph_sales_pdf'))
        {        
            //retrieve data sales
            $bulan = $this->input->post('bulan');            
            $tahun = $this->input->post('tahun');
            $query = $this->transaksi->get_omset($bulan,$tahun);
            $row_disc = array();
            $qty_sales = array();
            if($query->num_rows() > 0)
            {
                //ambil total qty sales untuk perbulan
                $qry = $this->transaksi->total_qty_sales($bulan,$tahun);
                if($qry->num_rows() > 0) 
                { 
                    $qty_sales = $qry->result();                   
                }
                //ambil total diskon untuk satu bulan
                $qry = $this->transaksi->total_disc_daily($bulan,$tahun);
                if($qry->num_rows() > 0) 
                { 
                    $row_disc = $qry->result();
                }
                $line= '0,0'.chr(10);
                $row_data = '';
                $total_omset=0;
                $total_qty=0;
                $total_disc=0;
                $total_infaq=0;
                $i=0;
                $min_omset='';
                $max_omset='';
                $idx_min='';
                $idx_max='';
                foreach($query->result() as $row)
                {             
                    $row_data .= '<tr>
                                    <td style="width:20px">'.++$i.'</td>
                                    <td style="width:50px">'.$row->tgl.'</td>
                                    <td style="width:50px">'.(isset($qty_sales[$i-1]) ? $qty_sales[$i-1]->total : 0).'</td>
                                    <td style="width:100px">'.number_format((isset($row_disc[$i-1]) ? $row_disc[$i-1]->total_diskon: 0),'0',',','.').',-</td>
                                    <td style="width:100px">'.number_format($row->total_infaq,'0',',','.').',-</td>
                                    <td style="width:100px">'.number_format($row->omset,'0',',','.').',-</td></tr>';
                    $line .= $row->tgl.','.($row->omset/1000). chr(10);
                    if($i==1)
                    {
                        $idx_min=$i;
                        $idx_max=$i;
                        $min_omset=$row->omset;
                        $max_omset=$row->omset;
                    }
                    if($i>1)
                    {
                        $temp = $row->omset;
                        if($temp >= $max_omset)
                        {
                            $max_omset = $temp;
                            $idx_max = $i;
                        }
                        if($temp <= $min_omset)
                        {
                            $min_omset = $temp;
                            $idx_min = $i;
                        }                        
                    }
                    $total_omset += $row->omset;
                    $total_qty += isset($qty_sales[$i-1]) ? $qty_sales[$i-1]->total : 0;
                    $total_disc += isset($row_disc[$i-1]) ? $row_disc[$i-1]->total_diskon: 0;
                    $total_infaq += $row->total_infaq;
                }                
                $row_total = '<tr><td colspan="2" style="width:70px;">T O T A L</td><td style="width:50px">'.$total_qty.'</td><td style="width:100px">'.number_format($total_disc,'0',',','.').',-</td><td style="width:100px">'.number_format($total_infaq,'0',',','.').',-</td><td style="width:100px">'.number_format($total_omset,'0',',','.').',-</td></tr>';
                $this->data['row_data']=$row_data.$row_total;
                $file = @fopen('lib/omset.csv','w');
                fwrite($file,$line);
                fclose($file);                
            
                $this->data['bulan'] = strtoupper($this->month_to_string($bulan)).' '.$tahun;
                //initialize config
                $config['file_name'] = 'css/chart/sales.png';
                $config['data'] = 'lib/omset.csv';
                $config['month'] = $this->month_to_string($bulan);
                $config['title'] = 'Grafik Omset';
                $config['y_axis_name'] = 'Jumlah Omset (Rp 1.000)';
                $config['x_axis_name'] = 'Tanggal';
                $config['type'] = 'bar';
                $config['idx_max'] = $idx_max;
                $config['idx_min'] = $idx_min;
                $config['max_omset'] = $max_omset;            
                $config['min_omset'] = $min_omset;            
                $this->generate_graph($config);
                //jika pilih cetak pdf maka ekspor ke pdf
                if($this->input->post('submit_graph_sales_pdf'))
                {
                    $head1 = '<h3 style="text-align:center">'.config_item('shop_name').'<br />GRAFIK OMSET '.$this->data['bulan'].'<br /> DALAM RIBUAN RUPIAH (Rp 1.000,-)</h3>';
                    $img = BASEPATH.'../css/chart/sales.png';                             
                    $head2 = '<h3 style="text-align:center">TABEL OMSET '.$this->data['bulan'].'</h3>';
                    $table = '<table class="table-data" cellspacing="0" cellpadding="0" style="width:300px; text-align:center;border:1px solid;">
                                <tr>
                                    <td class="head" style="width:20px">No</td>
                                    <td class="head" style="width:50px">Tanggal</td>
                                    <td class="head" style="width:50px">Qty Terjual</td>
                                    <td class="head" style="width:100px">Total Diskon(Rp)</td>
                                    <td class="head" style="width:100px">Total Infaq(Rp)</td>
                                    <td class="head" style="width:100px">Omset (Rp)</td>
                                </tr>';
                    $table .= $this->data['row_data'].'</table>';
                    $this->cetak_pdf(1,$head1,$head2,$table,$img);
                }
            }
            else
            {
                $this->data['err_msg'] = '<span style="color:red">Tidak ada penjualan pada bulan tersebut</span>';
            }
        }        
        //ambil bulan dan tahun
        $query = $this->transaksi->month_of_trans();
        $month = '<select name="bulan" style="width:148px;">';
        foreach($query->result() as $row)
        {
            $month .= '<option value="'.$row->bulan.'">'.$this->month_to_string($row->bulan).'</option>';
        }
        $month .= '</select>';
        $query = $this->transaksi->year_of_trans();
        $year = '<select name="tahun" style="width:50px;">';
        foreach($query->result() as $row)
        {
            $year .= '<option value="'.$row->tahun.'">'.$row->tahun.'</option>';
        }
        $year .= '</select>';
        $this->data['month'] = $month;
        $this->data['year'] = $year;        
        $this->load->view('graph-sales',$this->data);
    }
    /**
    *menampilkan grafik prestasi karyawan
    */
    function performance()
    {
        $this->load->model('transaksi');
        //ambil bulan dan tahun
        $query = $this->transaksi->month_of_trans();
        $month = '<select name="bulan" style="width:120px;">';
        foreach($query->result() as $row)
        {
            $month .= '<option value="'.$row->bulan.'">'.$this->month_to_string($row->bulan).'</option>';
        }
        $month .= '</select>';
        $query = $this->transaksi->year_of_trans();
        $year = '<select name="tahun" style="width:50px;">';
        foreach($query->result() as $row)
        {
            $year .= '<option value="'.$row->tahun.'">'.$row->tahun.'</option>';
        }
        $year .= '</select>';
        $this->data['month'] = $month;
        $this->data['year'] = $year;
        //ambil data karyawan
        $this->load->model('karyawan');
        $query = $this->karyawan->get_all_pramuniaga();        
        $this->data['karyawan'] = '';
        if($query->num_rows() > 0)
        {
            $this->data['pramu'] = $query->result();
            $karyawan = '<select name="nik" style="width:178px;"><option value="all">Semua Karyawan</option>';
            foreach($query->result() as $row)
            {
                $karyawan .= '<option value="'.$row->NIK.'">'.$row->nama.'</option>';
            }
            $karyawan .= '</select>';
            $this->data['karyawan'] = $karyawan;
        }
        if($this->input->post('submit_graph_performance') || $this->input->post('submit_graph_performance_pdf'))
        {
            $bulan = $this->input->post('bulan');
            $tahun = $this->input->post('tahun');
            $nik = $this->input->post('nik');            
            $this->data['bulan'] = strtoupper($this->month_to_string($bulan));
            $this->data['nik'] = $nik;
            $this->data['tahun'] = $tahun;
            $bgcolor='';
            $width = '';
            if($this->input->post('submit_graph_performance_pdf'))
            {
               $bgcolor='background-color: #dedede;';
               $width ='width:70px;';
            }
            //menampilkan grafik semua karyawan
            if($nik == 'all')
            {
                //ambil data nik semua pramuniaga                
                $pramu = $this->data['pramu'];
                $jumlah_pramu = count($pramu);
                $i = 0;
                $data = array();
                $series = array();
                $total = array();                
                foreach($pramu as $row)
                {
                    $query = $this->transaksi->get_omset_karyawan($row->NIK,$bulan,$tahun);                                        
                    if($query->num_rows() > 0)
                    {
                        //ambil nama pramuniaga untuk dijadiin series name                         
                        $series[$i] = $row->nama;
                        //proses data omset masukkin ke array 2 dimensi
                        //array[karyawan][omset]
                        $row_omset = $query->result();
                        $k = 0;
                        $total[$i] = 0;
                        $data[$i] = array_fill(0,$this->check_month($bulan,$tahun),0);
                        for($j=0; $j<count($row_omset);)
                        {
                            if($k == $row_omset[$j]->tgl)
                            {
                                $data[$i][$k] = ($row_omset[$j]->omset/1000);
                                $data_mentah[$i][$k] = $row_omset[$j]->omset;
                                $total[$i] += $row_omset[$j]->omset;
                                $j++;
                                $k++;
                            }
                            else
                            {
                                $data[$i][$k] = 0;
                                $data_mentah[$i][$k] = 0;
                                $k++;
                            }
                        }
                        $i++;
                    }                    
                }                                
                //setting data kedalam tabel untuk ditampilkan
                if(isset($data_mentah)) 
                {
                    //header table
                    $row_head = '<table class="table-data" cellspacing="0" cellpadding="0" style="border: 1px solid;"><tr><td class="head" style="text-align:center;'.$bgcolor.'">&nbsp;Tanggal&nbsp;</td>';
                    foreach($series as $row)
                    {
                        $row_head .='<td class="head" style="text-align:center;'.$width.$bgcolor.'">&nbsp;'.$row.'&nbsp;</td>';
                    }
                    $row_head .= '<td class="head" style="text-align:center;'.$width.$bgcolor.'">&nbsp;JUMLAH&nbsp;</td></tr>';
                    //row data untuk tabel
                    $row_data = '';                
                    for($i=1;$i<=31;$i++)
                    {
                        $line = '<tr><td style="text-align:center'.$bgcolor.'">'.$i.'</td>';
                        $total_day = 0;
                        for($j=0;$j<count($data_mentah);$j++)
                        {                        
                            if(isset($data_mentah[$j][$i]))
                            {
                                $line .= '<td style="text-align:right;'.$width.'">'.number_format($data_mentah[$j][$i],'0',',','.').',- &nbsp;</td>'; 
                                $total_day += $data_mentah[$j][$i];
                            }
                            else
                            {
                                $line .= '<td style="text-align:right'.$width.'">'.number_format(0,'0',',','.').',- &nbsp;</td>';
                            }
                        }
                        $line .= '<td style="text-align:right;'.$width.'">'.number_format($total_day,'0',',','.').',- &nbsp;</td></tr>';
                        $row_data .= $line;
                    }
                    //row total
                    $row_total = '<tr><td style="text-align:center">TOTAL</td>';
                    foreach($total as $row)
                    {
                        $row_total .= '<td style="text-align:right;'.$width.'">'.number_format($row,'0',',','.').',- &nbsp;</td>';
                    }
                    $row_total .='<td style="'.$width.'"></td></tr><tr><td style="text-align:center">RATA - RATA </td>';
                    for($i=0;$i<count($total);$i++)
                    {
                        $temp = array_filter($data_mentah[$i]);
                        $avg = $total[$i]/count($temp);
                        $row_total .= '<td style="text-align:right;'.$width.'">'.number_format($avg,'0',',','.').',- &nbsp;</td>';
                    }
                    $row_total .= '<td style="'.$width.'"></td></tr>';
                    $this->data['table'] = $row_head.$row_data.$row_total.'</table>';               
                
                    //initialize config
                    $config['series'] = $series;
                    $config['title'] = 'Grafik Perbandingan Omset Karyawan';
                    $config['file_name'] = 'css/chart/performance-all.png';
                    $config['y_axis_name'] = 'Jumlah Omset (Rp 1.000)';
                    $config['x_axis_name'] = 'Tanggal';
                    $config['type'] = 'cubic';
                    //$this->generate_multigraph($data,$config);
                    if($this->input->post('submit_graph_performance_pdf')) 
                    {
                        $head1 = '';$img = $jumlah_pramu;
                        $head2 = '<h3 style="text-align:center">TABEL OMSET KARYAWAN <br /> BULAN : '.$this->data['bulan'].' '.$this->data['tahun'].'<br /> (Rupiah)</h3>';
                        $table = $this->data['table'];
                        $this->cetak_pdf(3,$head1,$head2,$table,$img);
                    }
                }
                else
                {
                    $this->data['err_msg'] = 'Data tidak ditemukan';
                }
            }
            //menampilkan grafik per karyawan
            else
            {
                //ambil nama karyawan
                $query = $this->karyawan->get_karyawan($nik);
                $this->data['pramuniaga'] = $query->row();
                //ambil omset karyawan
                $query = $this->transaksi->get_sales_karyawan($nik,$bulan,$tahun);                
                if($query->num_rows() > 0)
                {
                    $omset = array_fill(0,$this->check_month($bulan,$tahun),0);
                    $item_sales = array_fill(0,$this->check_month($bulan,$tahun),0);
                    $customer = array_fill(0,$this->check_month($bulan,$tahun),0);
                    $avg_omset = array_fill(0,$this->check_month($bulan,$tahun),0);
                    $row_data = '';
                    $total_omset=0;
                    $total_item=0;
                    $total_customer=0;
                    $i=0;
                    $k=0;
                    $min_omset='';
                    $max_omset='';
                    $idx_min='';
                    $idx_max='';
                    $row = $query->result();
                    for($j=0;$j<count($row);)
                    {    
                        if($i == $row[$j]->tgl)
                        {
                            $omset[$i] = $row[$j]->omset/1000;
                            $item_sales[$i] = $row[$j]->total_item*100;
                            $customer[$i] = $row[$j]->total_customer*100;
                            $row_data .= '<tr><td>'.++$k.'</td><td>'.$row[$j]->tgl.'</td><td style="text-align:right;width:100px;">'.number_format($row[$j]->omset,'0',',','.').',-&nbsp;&nbsp;</td><td style="text-align:right;">'.$row[$j]->total_item.'&nbsp;&nbsp;</td><td style="text-align:right;">'.$row[$j]->total_customer.'&nbsp;&nbsp;</td></tr>';                        
                            if($i==1)
                            {
                                $idx_min=$i;
                                $idx_max=$i;
                                $min_omset=$row[$j]->omset;
                                $max_omset=$row[$j]->omset;
                            }
                            if($i>1)
                            {
                                $temp = $row[$j]->omset;
                                if($temp >= $max_omset)
                                {
                                    $max_omset = $temp;
                                    $idx_max = $i;
                                }
                                if($temp <= $min_omset)
                                {
                                    $min_omset = $temp;
                                    $idx_min = $i;
                                }                        
                            }
                            $total_omset += $row[$j]->omset;
                            $total_item += $row[$j]->total_item;
                            $total_customer += $row[$j]->total_customer;
                            $j++;
                            $i++;                            
                        }
                        else
                        {
                            $omset[$i] = 0;
                            $item_sales[$i] = 0;
                            $customer[$i] = 0;
                            $i++;
                        }                        
                    }                                       
                    $row_total = '<tr><td colspan="2">T O T A L</td><td style="text-align:right;width:100px;">'.number_format($total_omset,'0',',','.').',-&nbsp;&nbsp;</td><td style="text-align:right;">'.$total_item.'&nbsp;&nbsp;</td><td style="text-align:right;">'.$total_customer.'&nbsp;&nbsp;</td></tr>';
                    $row_total .= '<tr><td colspan="2">RATA RATA</td><td style="text-align:right;width:100px;">'.number_format($total_omset/$j,'0',',','.').',-&nbsp;&nbsp;</td><td style="text-align:right;">'.floor($total_item/$j).'&nbsp;&nbsp;</td><td style="text-align:right;">'.floor($total_customer/$j).'&nbsp;&nbsp;</td></tr>';
                    
                    //ambil omset rata-rata per hari dalam satu bulan
                    $query = $this->transaksi->get_avg_omset($bulan,$tahun);
                    $avg_omset_total = 0;
                    $total_item = 0;
                    $total_customer = 0;
                    if($query->num_rows() > 0)
                    {
                        $data_omset = $query->result();
                        $i = 0;
                        for($j=0;$j<count($data_omset);)
                        {                            
                            if($i == $data_omset[$j]->tgl)
                            {
                                $avg_omset[$i] = $data_omset[$j]->rata2_omset/1000;
                                $avg_omset_total += $data_omset[$j]->rata2_omset;
                                $total_item += $data_omset[$j]->rata2_qty;
                                $total_customer += $data_omset[$j]->rata2_customer;
                                $j++;                                
                            }
                            else
                            {
                                $avg_omset[$i] = 0;                                
                            }
                            $i++;
                        }
                    }
                   
                    $row_total .= '<tr><td colspan="2">RATA-RATA GLOBAL</td><td style="text-align:right;width:100px;">'.number_format($avg_omset_total/$i,'0',',','.').',-&nbsp;&nbsp;</td><td style="text-align:right;">'.floor($total_item/$i).'&nbsp;&nbsp;</td><td style="text-align:right;">'.floor($total_customer/$i).'&nbsp;&nbsp;</td></tr>';
                    $this->data['row_data']=$row_data.$row_total;
                    //ambil jumlah item yang berhasil dijual oleh karyawan
                    
                    //gabungkan data omset karyawan dan omset rata2 dalam 1 array
                    $data[0] = $omset; 
                    $data[1] = $avg_omset;
                    $data[2] = $item_sales;
                    $data[3] = $customer;
                    $series[0] = 'Omset Pramuniaga';
                    $series[1] = 'Omset Rata-rata';
                    $series[2] = 'Item Terjual';
                    $series[3] = 'Jumlah Customer';                    
                    //generating graph
                    //initialize config
                    $config['file_name'] = 'css/chart/performance.png';               
                    $config['title'] = 'Grafik Prestasi Karyawan';
                    $config['y_axis_name'] = 'Jumlah Omset (Rp 1.000), Item Terjual & Jumlah Customer (100x)';
                    $config['x_axis_name'] = 'Tanggal';
                    $config['series'] = $series;
                    $config['type'] = 'cubic';
                    $config['idx_max'] = $idx_max;
                    $config['idx_min'] = $idx_min;
                    $config['max_omset'] = $max_omset;            
                    $config['min_omset'] = $min_omset;                    
                    $this->generate_multigraph($data, $config);
                    if($this->input->post('submit_graph_performance_pdf'))
                    {
                        $head1='<h3 style="text-align:center">BULAN : '.$this->data['bulan'].' '.$this->data['tahun'].'<br /> 
                                NAMA KARYAWAN : '.strtoupper($this->data['pramuniaga']->nama).'<br /><br />
                                DALAM RIBUAN RUPIAH (Rp 1.000,-)</h3>';
                        $img = base_url().'css/chart/performance.png';
                        
                        $head2 = '<h3 style="text-align:center">TABEL OMSET '.$this->data['bulan'].' '.$this->data['tahun'].'</h3>';
                        $table = '<table class="table-data" cellspacing="0" cellpadding="0" style="width:300px; text-align:center; border: 1px solid;">
                                    <tr>
                                        <td class="head" style="background-color: #dedede;">No</td>
                                        <td class="head" style="background-color: #dedede;">Tanggal</td>
                                        <td class="head" style="width:100px;background-color: #dedede;">Omset (Rp)</td>
                                        <td class="head" style="background-color: #dedede;">Total Item</td>
                                        <td class="head" style="background-color: #dedede;">Total Customer</td>
                                    </tr>';
                        $table .= $this->data['row_data'].'</table>';
                        $this->cetak_pdf(2,$head1,$head2,$table,$img);
                    }
                }
                else
                {
                    $this->data['err_msg'] = 'Data tidak ditemukan';
                }
            }
        }
        $this->load->view('graph-performance',$this->data);
    }
    /**
    *Function for generating a single graph
    *@file_name : .csv file containing data to be generated as graph
    */
    function generate_graph($param="")
    {
        // Standard inclusions      
        include("lib/chart/pChart/pData.class");   
        include("lib/chart/pChart/pChart.class");   
         
        if(!empty($param))
        {
            // Dataset definition    
            $DataSet = new pData;   
            $DataSet->ImportFromCSV($param['data'],",",array(1),FALSE,0);   
            $DataSet->AddAllSeries();   
            $DataSet->SetAbsciseLabelSerie();   
            $DataSet->SetSerieName($param['month'],"Serie1");     
            $DataSet->SetYAxisName($param['y_axis_name']);
            $DataSet->SetXAxisName($param['x_axis_name']);
            //$DataSet->SetYAxisUnit(" rb");
          
            // Initialise the graph   
            $Test = new pChart(750,250);
            $Test->setFontProperties("lib/chart/Fonts/tahoma.ttf",8);   
            $Test->setGraphArea(70,30,680,200);   
            $Test->drawFilledRoundedRectangle(7,7,743,243,5,240,240,240);   
            $Test->drawRoundedRectangle(5,5,745,245,5,230,230,230);   
            $Test->drawGraphArea(255,255,255,TRUE);
            $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2);   
            $Test->drawGrid(4,TRUE,230,230,230,50);
             
            // Draw the 0 line   
            $Test->setFontProperties("lib/chart/Fonts/tahoma.ttf",6);   
            $Test->drawTreshold(0,143,55,72,TRUE,TRUE);   
            
            //Draw bar graph
            if($param['type'] == 'bar')
            {
                $Test->drawBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),TRUE,80);
            }
            // Draw the line graph
            else if($param['type'] == 'line')
            {
                $Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());   
                //$Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,0,0,0);         
            }
            else if($param['type'] == 'cubic')
            {
                $Test->drawCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription());
            }
            //Draw the label for max value and min value
            $Test->setFontProperties("lib/chart/Fonts/tahoma.ttf",8);

            $Test->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie1",$param['idx_max'],number_format($param['max_omset'],0,',','.'),239,233,195);
            $Test->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie1",$param['idx_min'],number_format($param['min_omset'],0,',','.'),239,233,195);
            
            // Finish the graph   
            $Test->setFontProperties("lib/chart/Fonts/tahoma.ttf",8);   
            $Test->drawLegend(75,35,$DataSet->GetDataDescription(),255,255,255);   
            $Test->setFontProperties("lib/chart/Fonts/tahoma.ttf",10);   
            $Test->drawTitle(60,22,$param['title'],50,50,50,585);   
            $Test->Render($param['file_name']);
        }
        else
        {
            $this->data['err_msg'] = 'Anda harus menentukan data yang akan dibuat grafik';
            return false;
        }
    }
    /**
    *Function for generating multi graph
    *series data stored in an array
    */
    function generate_multigraph($data, $param="")
    {
        // Standard inclusions      
        include("lib/chart/pChart/pData.class");   
        include("lib/chart/pChart/pChart.class");
        
        // Dataset definition 
        $DataSet = new pData;
        $i = 0;        
        foreach($data as $row)
        {
            $DataSet->AddPoint($row,'Serie'.++$i);
        }        
        //$DataSet->AddPoint(array(1,4,3,4,3,3,2,1,0,7,4,3,2,3,3,5,1,0,7),"Serie1");
        //$DataSet->AddPoint(array(1,4,2,6,2,3,0,1,5,1,2,4,5,2,1,0,6,4,2),"Serie2");
        $DataSet->AddAllSeries();
        $DataSet->SetAbsciseLabelSerie();
        $i = 0;
        foreach($param['series'] as $series)
        {
            $DataSet->SetSerieName($series,"Serie".++$i);
        }
        
        //$DataSet->SetSerieName("February","Serie2");
        
        $DataSet->SetYAxisName($param['y_axis_name']);
        $DataSet->SetXAxisName($param['x_axis_name']);
        
        // Initialise the graph
        $Test = new pChart(960,350);
        //$Test->setFixedScale(-2,8);
        $Test->setFontProperties("lib/chart/Fonts/tahoma.ttf",8);
        $Test->setGraphArea(60,30,940,300);
        $Test->drawFilledRoundedRectangle(7,7,953,343,5,240,240,240);
        $Test->drawRoundedRectangle(5,5,955,345,5,230,230,230);
        $Test->drawGraphArea(255,255,255,TRUE);
        $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2);
        $Test->drawGrid(4,TRUE,230,230,230,50);
        
        // Draw the 0 line
        $Test->setFontProperties("lib/chart/Fonts/tahoma.ttf",6);
        $Test->drawTreshold(0,143,55,72,TRUE,TRUE);
        
        // Draw the cubic curve graph
        if($param['type'] == 'cubic')
        {            
            $Test->drawCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription());
        }        
        if(isset($param['max_omset']) && isset($param['min_omset']))
        {
            //$Test->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie1",$param['idx_max'],number_format($param['max_omset'],0,',','.'),239,233,195);
            //$Test->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie1",$param['idx_min'],number_format($param['min_omset'],0,',','.'),239,233,195);
            ;
        }
        // Finish the graph
        $Test->setFontProperties("lib/chart/Fonts/tahoma.ttf",8);
        $Test->drawLegend(755,31,$DataSet->GetDataDescription(),255,255,255);
        $Test->setFontProperties("lib/chart/Fonts/tahoma.ttf",10);
        $Test->drawTitle(350,22,$param['title'],50,50,50,585);        
        $Test->Render($param['file_name']);
    }
    /*
    **Funngsi cetak pdf
    */
    function cetak_pdf($opsi,$head1,$head2,$data,$img)
    {
        require_once('lib/tcpdf/config/lang/eng.php');
        require_once('lib/tcpdf/tcpdf.php');

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Sikasir');
        $pdf->SetTitle('Grafik');
        $pdf->SetSubject('Cetak Grafik');
        $pdf->SetKeywords('grafik');

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
        if($opsi == 1)
        {
            $pdf->setPageUnit('mm');
            $size = array(216,330);               
            $pdf->setPageFormat($size,'P');               
            // set font
            $pdf->SetFont('dejavusans', '', 9);      
            // add a page
            $pdf->AddPage();
            $pdf->writeHTML('<br />', true, 0, true, 0);
            $pdf->writeHTML($head1, true, 0, true, 0);
            $pdf->Image($img,13,45,190,60);
            $pdf->writeHTML('<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />', true, 0, true, 0);      
            $pdf->writeHTML($head2,true,0,true,0);     
            $pdf->writeHTMLCell(0,0,35,115,$data);
        }
        if($opsi == 2)
        {
            $pdf->setPageUnit('mm');
            $size = array(216,330);               
            $pdf->setPageFormat($size,'P');               
            // set font
            $pdf->SetFont('dejavusans', '', 9);      
            // add a page
            $pdf->AddPage();
            $pdf->writeHTML('<br />', true, 0, true, 0);
            $pdf->writeHTML($head1, true, 0, true, 0);
            $pdf->Image($img,13,50,190,65);
            $pdf->writeHTML('<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />', true, 0, true, 0);      
            $pdf->writeHTML($head2,true,0,true,0);     
            $pdf->writeHTMLCell(0,0,50,130,$data);
        }
        if($opsi == 3)
        {
            $pdf->setPageUnit('mm');
            if($img <= 10)
            {
                $size = array(216,330);               
            }
            else
            {
                $w = 216;
                $h = 330 + (($img - 10)*20);
                $size = array($w,$h);
            }
            $pdf->setPageFormat($size,'L');               
            // set font
            $pdf->SetFont('dejavusans', '', 9);      
            // add a page
            $pdf->AddPage();               
            $pdf->writeHTMLCell(0,0,0,25,$head2); 
            $pdf->writeHTMLCell(0,0,15,45,$data);
        }
        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('tes.pdf', 'I');     
            
    }
    /**
    *hitung jumlah hari dalam satu bulan
    */
    function check_month($bulan,$tahun)
    {
        if($bulan == 2 && $tahun%4==0)
        {
            $num = 30;
        }
        else if($bulan == 2 && $tahun%4 != 0)
        {
            $num = 29;
        }
        else if($bulan == 1 ||$bulan == 3 ||$bulan == 5 ||$bulan == 7 ||$bulan == 8 ||$bulan == 10 ||$bulan == 12)
        {
            $num = 32;
        }
        else
        {
            $num = 31;
        }
        return $num;
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