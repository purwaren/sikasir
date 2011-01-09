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
            if($query->num_rows() > 0)
            {
                $line= '0,0'.chr(10);
                $row_data = '';
                $total_omset=0;
                $i=0;
                $min_omset='';
                $max_omset='';
                $idx_min='';
                $idx_max='';
                foreach($query->result() as $row)
                {                                        
                    $row_data .= '<tr><td>'.++$i.'</td><td>'.$row->tgl.'</td><td>'.number_format($row->omset,'0',',','.').',-</td></tr>';
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
                }                
                $row_total = '<tr><td colspan="2">T O T A L</td><td>'.number_format($total_omset,'0',',','.').',-</td></tr>';
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
            $karyawan = '<select name="nik" style="width:178px;">';
            foreach($query->result() as $row)
            {
                $karyawan .= '<option value="'.$row->NIK.'">'.$row->nama.'</option>';
            }
            $karyawan .= '</select>';
            $this->data['karyawan'] = $karyawan;
        }
        if($this->input->post('submit_graph_performance'))
        {
            $bulan = $this->input->post('bulan');
            $tahun = $this->input->post('tahun');
            $nik = $this->input->post('nik');            
            $this->data['bulan'] = strtoupper($this->month_to_string($bulan));
            $this->data['nik'] = $nik;
            $this->data['tahun'] = $tahun;
            //menampilkan grafik semua karyawan
            if($nik == 'all')
            {
                //ambil data nik semua pramuniaga                
                $pramu = $this->data['pramu'];
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
                //header table
                $row_head = '<table class="table-data" cellspacing="0" cellpadding="0"><tr><td class="head">Tanggal</td>';
                foreach($series as $row)
                {
                    $row_head .='<td class="head">'.$row.'</td>';
                }
                $row_head .= '<td class="head">TOTAL</td></tr>';
                //row data untuk tabel
                $row_data = '';                
                for($i=1;$i<=31;$i++)
                {
                    $line = '<tr><td>'.$i.'</td>';
                    $total_day = 0;
                    for($j=0;$j<count($data_mentah);$j++)
                    {                        
                        if(isset($data_mentah[$j][$i]))
                        {
                            $line .= '<td>'.number_format($data_mentah[$j][$i],'0',',','.').',-</td>'; 
                            $total_day += $data_mentah[$j][$i];
                        }
                        else
                        {
                            $line .= '<td>'.number_format(0,'0',',','.').',-</td>';
                        }
                    }
                    $line .= '<td>'.number_format($total_day,'0',',','.').',-</td></tr>';
                    $row_data .= $line;
                }
                //row total
                $row_total = '<tr><td>TOTAL</td>';
                foreach($total as $row)
                {
                    $row_total .= '<td>'.number_format($row,'0',',','.').',-</td>';
                }
                $row_total .='<td></td></tr><tr><td>RATA - RATA </td>';
                for($i=0;$i<count($total);$i++)
                {
                    $temp = array_filter($data_mentah[$i]);
                    $avg = $total[$i]/count($temp);
                    $row_total .= '<td>'.number_format($avg,'0',',','.').',-</td>';
                }
                $row_total .= '<td></td></tr>';
                $this->data['table'] = $row_head.$row_data.$row_total.'</table>';                
                //initialize config
                $config['series'] = $series;
                $config['title'] = 'Grafik Perbandingan Omset Karyawan';
                $config['file_name'] = 'css/chart/performance-all.png';
                $config['y_axis_name'] = 'Jumlah Omset (Rp 1.000)';
                $config['x_axis_name'] = 'Tanggal';
                $config['type'] = 'cubic';
                $this->generate_multigraph($data,$config);
                
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
                            $row_data .= '<tr><td>'.++$k.'</td><td>'.$row[$j]->tgl.'</td><td>'.number_format($row[$j]->omset,'0',',','.').',-</td><td>'.$row[$j]->total_item.'</td><td>'.$row[$j]->total_customer.'</td></tr>';                        
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
                    $row_total = '<tr><td colspan="2">T O T A L</td><td>'.number_format($total_omset,'0',',','.').',-</td><td>'.$total_item.'</td><td>'.$total_customer.'</td></tr>';
                    $row_total .= '<tr><td colspan="2">RATA RATA</td><td>'.number_format($total_omset/$j,'0',',','.').',-</td><td>'.floor($total_item/$j).'</td><td>'.floor($total_customer/$j).'</td></tr>';
                    
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
                   
                    $row_total .= '<tr><td colspan="2">RATA-RATA GLOBAL</td><td>'.number_format($avg_omset_total/$i,'0',',','.').',-</td><td>'.floor($total_item/$i).'</td><td>'.floor($total_customer/$i).'</td></tr>';
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