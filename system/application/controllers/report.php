<?php
/**
*Controller Report
*Desc: This controller is used to generate report
*/
class Report extends Controller {
    /*class field */
    protected $data;
    /*Controller constructor*/
	function Report()
	{
		parent::Controller();            
		$this->data['page'] ='report';
        
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
        $this->load->view('report',$this->data);
	}
    /**
    *Sales report / laporan penjualan
    */
    function sales()
    {
        //Laporan penjualan harian
        $this->load->model('transaksi');
        if($this->input->post('submit_report_sales') || $this->input->post('submit_report_sales_pdf'))
        {
            $tipe = $this->input->post('report-type');
            $tanggal = $this->input->post('date-report');
            $bulan = $this->input->post('bulan');
            //loading library            
            $this->load->model('barang');
            $this->load->model('karyawan');
            if(!empty($tipe) && (!empty($tanggal) || isset($bulan)))
            {
                //laporan penjualan harian 
                if($tipe ==  1)
                {                    
                     //ambil data transaksi hari yang diminta, 
                    $query = $this->transaksi->trans_a_day($tanggal); 
                    $query_per_bon = $this->transaksi->trans_based_bon($tanggal);
                    if(isset($query_per_bon) && $query_per_bon->num_rows() > 0)                    {
                                              
                        $head ='<div id="report-sales"><h3 style="text-align:center;font-size: 14px">LAPORAN PENJUALAN HARIAN</h3>
                                <table style="text-align:left">
                                    <tr><td style="width: 50px">CABANG</td><td>: '.config_item('shop_name').'</td></tr>
                                    <tr><td style="width: 50px">TANGGAL </td><td>: '.$this->convert_date($tanggal).'</td></tr>
                                </table><br />
                                <table style="width: 940px;border: 1px solid;text-align: center;margin: 0px auto;" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="width:30px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">No</td>
                                        <td style="width:50px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">JAM</td>
                                        <td style="width:90px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Nama Kasir</td>
                                        <td style="width:75px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Kode Label</td>
                                        <td style="width:120px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Nama Barang</td>
                                        <td style="width:50px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Kelompok <br />Barang</td>
                                        <td style="width:40px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Disc</td>
                                        <td style="width:40px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Disc All</td>
                                        <td style="width:75px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Harga Jual (Rp)</td>
                                        <td style="width:30px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Qty</td>
                                        <td style="width:75px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Jumlah (Rp)</td>
                                        <td style="width:75px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Omset (Rp)</td>
                                    </tr>';
                        $i=0;
                        $j=0;
                        $n=0;
                        $temp ="";
                        $total_jumlah=0;
                        $total_tunai=0;
                        $total_qty=0;
                        $row = $query->result();
                        foreach($query_per_bon->result() as $bon)
                        {
                            //handling kasus klo satu bon kepisah di dua halaman -- baris terakhir pada halaman sekarang
                            $ada = 30-($i%30);
                            $butuh = $bon->jml_item;
                            $kurang='';
                            if($butuh > $ada)
                            {
                                $kurang = $butuh - $ada;
                                $n=$i+1;
                                //echo $n.'-'.$ada.'-'.$butuh.'-'.$kurang.'<br />';
                            }                            
                            for($k=$bon->jml_item;$k>0;$k--)
                            {                                
                                $query_brg = $this->barang->get_barang($row[$i]->id_barang,2);
                                if($query_brg->num_rows() > 0)
                                {
                                    $query_kry = $this->karyawan->get_karyawan($row[$i]->id_kasir);
                                    $barang = $query_brg->row();
                                    $kasir = $query_kry->row();                                
                                    $jumlah = $row[$i]->qty * $barang->harga;
                                    $jumlah = $jumlah * (1 - ($row[$i]->diskon_item/100));
                                    $jumlah = $jumlah * (1 - ($row[$i]->diskon/100));
                                    $total_jumlah += $jumlah;
                                    $total_qty += $row[$i]->qty;
                                    $temp .= '<tr>
                                                    <td style="width:30px;border: 1px solid;">'.($i+1).'</td>';
                                    if(!empty($kurang))
                                    {
                                        if(($i+1)>30 && ($i+1)%30==1)
                                        {
                                           $temp .='<td rowspan="'.$kurang.'" style="width:50px;border: 1px solid;">'.$row[$i]->jam_transaksi.'</td>
                                                    <td rowspan="'.$kurang.'" style="width:90px;border: 1px solid;">'.ucwords($kasir->nama).'</td>'; 
                                        }                                    
                                        if(($i+1)==$n)
                                        {
                                            $temp .='<td rowspan="'.$ada.'" style="width:50px;border: 1px solid;">'.$row[$i]->jam_transaksi.'</td>
                                                    <td rowspan="'.$ada.'" style="width:90px;border: 1px solid;">'.ucwords($kasir->nama).'</td>';
                                        }
                                    }
                                    else
                                    {
                                        if($k==$bon->jml_item)
                                        {
                                            $temp .='<td rowspan="'.$bon->jml_item.'" style="width:50px;border: 1px solid;">'.$row[$i]->jam_transaksi.'</td>
                                                    <td rowspan="'.$bon->jml_item.'" style="width:90px;border: 1px solid;">'.ucwords($kasir->nama).'</td>';
                                        }
                                    }
                                    $temp .= '  <td style="width:75px;border: 1px solid;text-align: left;padding-left:5px;">&nbsp;&nbsp;'.$row[$i]->id_barang.'</td>
                                                <td style="width:120px;border: 1px solid;">'.$barang->nama.'</td>
                                                <td style="width:50px;border: 1px solid;">'.$barang->kelompok_barang.'</td>
                                                <td style="width:40px;border: 1px solid;">'.$row[$i]->diskon_item.'</td>
                                                <td style="width:40px;border: 1px solid;">'.$row[$i]->diskon.'</td>
                                                <td style="width:75px;border: 1px solid;text-align:right;padding-right:10px;">'.number_format($barang->harga,2,',','.').'&nbsp;&nbsp;</td>
                                                <td style="width:30px;border: 1px solid;">'.$row[$i]->qty.'</td>
                                                <td style="width:75px;border: 1px solid;text-align:right;padding-right:10px;">'.number_format($jumlah,2,',','.').'&nbsp;&nbsp;</td>';
                                                       
                                    //menyisipkan total sebenarnya, dibuat colspan
                                    if(!empty($kurang))
                                    {
                                        if(($i+1)>30 && ($i+1)%30==1)
                                        {
                                            $temp .= '<td rowspan="'.$kurang.'" style="width:75px;border: 1px solid;text-align:right;padding-right:10px;">&nbsp;&nbsp;</td>';
                                        }
                                        if(($i+1)==$n)
                                        {
                                            $temp .= '<td rowspan="'.$ada.'" style="width:75px;border: 1px solid;text-align:right;padding-right:10px;">'.number_format($bon->total,2,',','.').'&nbsp;&nbsp;</td>';
                                        }
                                    }
                                    else 
                                    {
                                        if($k==$bon->jml_item)
                                        {
                                            $temp .= '<td rowspan="'.$bon->jml_item.'" style="width:75px;border: 1px solid;text-align:right;padding-right:10px;">'.number_format($bon->total,2,',','.').'&nbsp;&nbsp;</td>';
                                        }
                                    }
                                    $temp .='</tr>';
                                    $i++;                                
                                    if($i%30 == 0)
                                    {
                                        $list[$j] = $temp;
                                        $j++;
                                        $temp = "";
                                    }
                                }
                                else
                                {
                                    print_r($row[$i]);
                                    echo $this->db->last_query();exit;
                                }
                            }
                            $total_tunai += $bon->total;                            
                        }
                        $list[$j] = $temp;
                        $row_total = '<tr><td colspan="9" style="width:570px;border: 1px solid;text-align:right">T O T A L &nbsp;&nbsp;</td>
                                        <td style="width:30px;border: 1px solid;">'.$total_qty.'</td>
                                        <td style="width:75px;border: 1px solid;text-align:right;padding-right:10px;">'.number_format($total_jumlah,2,',','.').'&nbsp;&nbsp;</td>
                                        <td style="width:75px;border: 1px solid;text-align:right;padding-right:10px;">'.number_format($total_tunai,2,',','.').'&nbsp;&nbsp;</td>
                                    </tr>';
                        //ambil data supervisor sama data kasir
                        $query = $this->transaksi->get_kasir($tanggal);
                        foreach($query->result() as $row)
                        {
                            $qry = $this->karyawan->get_karyawan($row->id_kasir);
                            $data_kasir[] = $qry->row();                            
                        }
                        $query = $this->karyawan->get_supervisor();
                        $supervisor = $query->row();
                        //nyusun data untuk ditampilkan
                        $line1 = '<td style="text-align:center">S U P E R V I S O R</td>';
                        $line2 = '<td style="text-align:center"><br />('.strtoupper($supervisor->nama).')</td>';
                        $i=0;
                        foreach($data_kasir as $row)
                        {
                            $line1 .= '<td style="text-align:center">K A S I R '.++$i.'</td>';
                            $line2 .= '<td style="text-align:center"><br />('.strtoupper($row->nama).')</td>';
                        }
                        $foot ='</table>
                                    <br /><table>
                                    <tr>'.$line1.'</tr>
                                    <tr>'.$line2.'</tr>
                                </table></div>';
                        //tampilin semuanya aje... memanjang kebawah gak papa
                        $this->data['report_sales']=$head;
                        foreach($list as $row)
                        {
                            $this->data['report_sales'] .= $row;
                        }
                        $this->data['report_sales'] .= $row_total.'</table></div>';
                        if(isset($list) && $this->input->post('submit_report_sales_pdf'))
                        {                            
                            $this->cetak_pdf(1,$head,$list,$row_total,$foot);
                        }                        
                    }
                    else
                    {
                        $this->data['report_sales'] = '<p style="color:red">Tidak ada transaksi untuk tanggal tersebut</p>';   
                    }                     
                }
                //laporan akumulasi penjualan harian
                else if($tipe==2)
                {
                    $query = $this->transaksi->acc_sales_a_day($tanggal,1);
                    if($query->num_rows() > 0)
                    {
                        $head[0] ='<div id="report-sales"><h3 style="text-align:center;font-size: 14px">LAPORAN AKUMULASI PENJUALAN HARIAN</h3>
                                <table style="text-align:left">
                                    <tr><td style="width: 50px">CABANG</td><td>: '.config_item('shop_name').'</td></tr>
                                    <tr><td style="width: 50px">TANGGAL </td><td>: '.$this->convert_date($tanggal).'</td></tr>
                                    <tr><td style="width: 50px">TIPE</td><td>: AKUMULASI PER KODE LABEL</td></tr>
                                </table>
                               <br />
                                <table style="width: 940px;border: 1px solid;text-align: center;margin: 0px auto;" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="width:30px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">No</td>                                        
                                        <td style="width:75px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Kode Label</td>
                                        <td style="width:120px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Nama Barang</td>
                                        <td style="width:50px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Kelompok <br />Barang</td>                                        
                                        <td style="width:75px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Harga Jual (Rp)</td>
                                        <td style="width:75px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Stok Barang</td>                                        
                                        <td style="width:75px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Jumlah Terjual</td>                                        
                                    </tr>';
                        //ambil row data untuk disusun ke dalam table
                        $i=0;
                        $j=0;
                        $temp ="";
                        $total_harian = 0;
                        foreach($query->result() as $row)
                        {
                            $temp .= '<tr>
                                        <td style="width:30px;border: 1px solid;">'.++$i.'</td>                                        
                                        <td style="width:75px;border: 1px solid;text-align: left;padding-left:5px;">&nbsp;&nbsp;'.$row->id_barang.'</td>
                                        <td style="width:120px;border: 1px solid;">'.$row->nama.'</td>
                                        <td style="width:50px;border: 1px solid;">'.$row->kelompok_barang.'</td>                                        
                                        <td style="width:75px;border: 1px solid;text-align:right;padding-right:10px;">'.number_format($row->harga,2,',','.').'&nbsp;&nbsp;</td>
                                        <td style="width:75px;border: 1px solid;">'.$row->stok_barang.'</td>                                        
                                        <td style="width:75px;border: 1px solid;">'.$row->jml_terjual.'</td>                                        
                                    </tr>';
                            $total_harian += $row->jml_terjual;
                            if($i%50 == 0)
                            {
                                $list[]= $temp;
                                $temp ='';
                                $j++;
                            }
                        }
                        $list[]= $temp;
                        $row_total[0] = '<tr><td  colspan="6" style="text-align:right;width:425px;border: 1px solid;">T O T A L &nbsp;</td><td style="width:75px;border: 1px solid;">'.$total_harian.'</td></tr>';
                        //ambil data supervisor sama data kasir
                        $query = $this->transaksi->get_kasir($tanggal);
                        foreach($query->result() as $row)
                        {
                            $qry = $this->karyawan->get_karyawan($row->id_kasir);
                            $data_kasir[] = $qry->row();                            
                        }
                        $query = $this->karyawan->get_supervisor();
                        $supervisor = $query->row();
                        //nyusun data untuk ditampilkan
                        $line1 = '<td style="text-align:center">S U P E R V I S O R</td>';
                        $line2 = '<td style="text-align:center"><br />('.strtoupper($supervisor->nama).')</td>';
                        $i=0;
                        foreach($data_kasir as $row)
                        {
                            $line1 .= '<td style="text-align:center">K A S I R '.++$i.'</td>';
                            $line2 .= '<td style="text-align:center"><br />('.strtoupper($row->nama).')</td>';
                        }
                        $foot ='</table>
                                <br />
                                <table>
                                    <tr>'.$line1.'</tr>
                                    <tr>'.$line2.'</tr>
                                </table></div>';
                    }
                    else
                    {
                        $this->data['err_msg'] = 'Tidak ada transaksi pada tanggal tersebut';
                    }
                    
                    //akumulasi per kelompok barang
                    $query = $this->transaksi->acc_sales_a_day($tanggal,2);
                    
                    if($query->num_rows() > 0)
                    {
                        $head[1] ='<div id="report-sales"><h3 style="text-align:center;font-size: 14px">LAPORAN AKUMULASI PENJUALAN HARIAN</h3>
                                <table style="text-align:left">
                                    <tr><td style="width: 50px">CABANG</td><td>: '.config_item('shop_name').'</td></tr>
                                    <tr><td style="width: 50px">TANGGAL </td><td>: '.$this->convert_date($tanggal).'</td></tr>
                                    <tr><td style="width: 50px">TIPE</td><td>: AKUMULASI PER KELOMPOK BARANG</td></tr>
                                </table>
                               <br />
                               &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <table style="width: 250px;border: 1px solid;text-align: center; margin: 0px auto" cellspacing="0" cellpadding="0" >
                                    <tr>
                                        <td style="width:30px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">No</td>                                        
                                        <td style="width:120px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Kelompok Barang</td>
                                        <td style="width:100px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Jumlah Terjual</td>                                                                                
                                    </tr>';
                        $i=0;
                        $j=0;
                        $temp ='';
                        $total_terjual = 0;
                        foreach($query->result() as $row)
                        {
                            $temp .= '<tr>
                                        <td style="width:30px;border: 1px solid;">'.++$i.'</td>                                        
                                        <td style="width:120px;border: 1px solid;text-align: center;padding-left:5px;">&nbsp;&nbsp;'.$row->kelompok_barang.'</td>
                                        <td style="width:100px;border: 1px solid;">'.$row->acc_terjual.'</td>                                                                             
                                    </tr>';
                            $total_terjual += $row->acc_terjual;
                            if($i%50 == 0)
                            {
                                $acc_kb[$j]= $temp;
                                $j++;
                            }
                        }
                        $acc_kb[$j]= $temp;
                        $row_total[1] = '<tr><td colspan="2" style="text-align:right;width:150px;border: 1px solid;">T O T A L &nbsp;</td><td style="width:100px;border: 1px solid;">'.$total_terjual.'</td></tr>';
                    }
                    if(isset($list))
                    {
                        $this->data['report_sales']=$head[0];
                        foreach($list as $row)
                        {
                            $this->data['report_sales'] .= $row;
                        }
                        $this->data['report_sales'] .= $row_total[0].'</table></div>'.$head[1];
                        foreach($acc_kb as $row)
                        {
                            $this->data['report_sales'] .= $row;
                        }
                        $this->data['report_sales'] .= $row_total[1].'</table></div>';
                    }
                    if(!empty($list) && $this->input->post('submit_report_sales_pdf'))
                    {                            
                        $itemreport[0] = $list;
                        //echo count($itemreport[1]);exit;
                        $itemreport[1] = $acc_kb;
                        //$row_total="";
                        $this->cetak_pdf(2,$head,$itemreport,$row_total,$foot);
                    }
                    
                }
                //laporan akumulasi penjualan bulanan
                else if($tipe == 3)
                {
                    //ambil parameter bulan dan tahun
                    $bulan = $this->input->post('bulan');
                    $tahun = $this->input->post('tahun');
                    //ambil data akumulasi bulanan
                    $query = $this->barang->get_kel_barang();
                    $head ='<div id="report-sales"><h3 style="text-align:center;font-size: 14px">LAPORAN AKUMULASI PENJUALAN BULANAN </h3>
                                <table style="text-align:left">
                                    <tr><td style="width: 50px">CABANG</td><td>: '.config_item('shop_name').'</td></tr>
                                    <tr><td style="width: 50px">BULAN </td><td>: '.$this->month_to_string($bulan).' '.$tahun.'</td></tr>
                                </table><br />
                                <table style="width: 940px;border: 1px solid;text-align: center;margin: 0px auto;" cellspacing="0" cellpadding="1">
                                    <tr>
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;"></td>
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">01</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">02</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">03</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">04</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">05</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">06</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">07</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">08</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">09</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">10</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">11</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">12</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">13</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">14</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">15</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">16</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">17</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">18</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">19</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">20</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">21</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">22</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">23</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">24</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">25</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">26</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">27</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">28</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">29</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">30</td>                                    
                                        <td style="width:25px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">31</td>                                    
                                        <td style="width:50px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Total</td>
                                    </tr>';                    
                    $list = '';
                    $row_data = '';
                    $k=0;
                    $total_all = 0;
                    foreach($query->result() as $row)
                    {
                        $qry_sales = $this->transaksi->acc_sales_a_month($row->kelompok_barang,$bulan,$tahun);                        
                        $sales_aday = $qry_sales->result();                        
                        $j=0;
                        $temp = '<tr><td style="width:25px; border: 1px solid;">'.$row->kelompok_barang.'</td>';
                        $total = 0;
                        for($i=1; $i<=31; $i++)
                        {                   
                            if(isset($sales_aday[$j]) && $i==$sales_aday[$j]->tgl)
                            {
                                $temp .= '<td style="width:25px;border: 1px solid; background-color: #aaaaaa">'.$sales_aday[$j]->jumlah.'</td>';
                                $total += $sales_aday[$j]->jumlah;
                                $j++;
                            }
                            else 
                            {
                                $temp .= '<td style="width:25px;border: 1px solid;"> 0 </td>';
                            }
                        }
                        $total_all += $total;
                        $temp .= '<td style="width:50px; border: 1px solid;">'.$total.'</td></tr>';
                        $row_data .= $temp;
                        $k++;
                        if($k%25==0)
                        {
                            $list[] = $row_data;
                            $row_data = '';
                        }                                                
                    }
                    $list[] = $row_data;
                    //tampilin totalnya                    
                    $query = $this->transaksi->total_qty_sales($bulan,$tahun);
                    if($query->num_rows() > 0)
                    {
                        $row_total = '<tr><td style="width:25px; background-color:#dedede; font-weight:bold; text-transform: uppercase; border:1px solid;">TOT</td>';
                        $total = $query->result();
                        $j=0;
                        for($i=1;$i<=31;$i++)
                        {
                            if(isset($total[$j]) && $total[$j]->tgl == $i)
                            {
                                $row_total .= '<td  style="width:25px;border: 1px solid; background-color: #aaaaaa">'.$total[$j]->total.'</td>';
                                $j++;
                            }
                            else
                            {
                                $row_total .= '<td style="width:25px;border: 1px solid;"> 0 </td>';
                            }
                        }
                        $row_total .= '<td  style="width:50px;border: 1px solid; background-color: #dedede">'.$total_all.'</td></tr>';
                    }                    
                    //ambil data supervisor sama data kasir
                    $query = $this->transaksi->get_kasir($tanggal);
                    foreach($query->result() as $row)
                    {
                        $qry = $this->karyawan->get_karyawan($row->id_kasir);
                        $data_kasir[] = $qry->row();                            
                    }
                    $query = $this->karyawan->get_supervisor();
                    $supervisor = $query->row();
                    //nyusun data untuk ditampilkan
                    $line1 = '<td style="text-align:center">S U P E R V I S O R</td>';
                    $line2 = '<td style="text-align:center">('.strtoupper($supervisor->nama).')</td>';
                    $i=0;
                    /*foreach($data_kasir as $row)
                    {
                        $line1 .= '<td style="text-align:center">K A S I R '.++$i.'</td>';
                        $line2 .= '<td style="text-align:center"><br />('.strtoupper($row->nama).')</td>';
                    }*/
                    $foot = '</table>
                            <br />
                            <table>
                                <tr>'.$line1.'</tr>
                                <tr>'.$line2.'</tr>
                            </table></div>';
                    //tampilin sebagai preview
                    $this->data['report_sales']=$head;
                    foreach($list as $row)
                    {
                        $this->data['report_sales'] .= $row;
                    }
                    $this->data['report_sales'] .= $row_total.$foot;
                    //cetak ke pdf
                    if(isset($list) && $this->input->post('submit_report_sales_pdf'))
                    {                            
                        $this->cetak_pdf(4,$head,$list,$row_total,$foot);
                    }                   
                }
            }
            else
            {
                $this->data['err_msg'] = 'Pilih jenis laporan dan tanggal terlebih dahulu';  
                $this->data['report_sales'] = '';
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
        $this->load->view('report-sales',$this->data);        
    }
    /**
    *Laporan Stok Barang
    */
    function stok($param="")
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
                                                    <td>'.++$i.'</td><td>'.$row->kelompok_barang.'</td><td>'.$row->total_stok.'</td><td>'.$row->terjual.'</td>
                                                    <td>'.$row->masuk.'</td><td>'.$row->keluar.'</td><td>'.$row->stok.'</td>               
                                                </tr>';
                }
                $this->session->unset_userdata('opsi');
            }
            else if($this->input->post('opsi') == 2)
            {
                //tulis opsi ke session untuk pagination
                $this->session->set_userdata('opsi','2');
                $query = $this->barang->get_barang_all();
                $this->data['total_item'] = $query->num_rows();                
                //create paginations
                //setting up pagination
                $this->load->library('pagination');
                $config['base_url'] = base_url().'report/stok/';
                $config['total_rows'] = $this->data['total_item'];
                $config['per_page'] = 50;
                $this->pagination->initialize($config);
                $this->data['pages'] = $this->pagination->create_links();
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
                                                        <td>'.$row->stok_barang.'</td><td>'.$row->mutasi_masuk.'</td><td>'.$row->mutasi_keluar.'</td>
                                                    </tr>';                        
                    }
                    else
                    {
                        $i++;
                    }
                    $this->data['total_qty'] += $row->stok_barang;
                }
            }
        }
        else 
        {
            if($this->session->userdata('opsi'))
            {
                $this->load->model('barang');
                $query = $this->barang->get_barang_all();
                //echo $query->num_rows();exit;
            }
            if(isset($query) && $query->num_rows() > 0)
            {
                $this->data['total_item'] = $query->num_rows();           
                //setting up pagination
                $this->load->library('pagination');
                $config['base_url'] = base_url().'report/stok/';
                $config['total_rows'] = $this->data['total_item'];
                $config['per_page'] = 50;
                $this->pagination->initialize($config);
                $this->data['pages'] = $this->pagination->create_links();
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
        }
        $this->load->view('item-view',$this->data);
    }
    /**
    *Laporan retur barang
    */
    function retur($kode_bon="")
    {
        //tampilkan list retur per tanggal
        if($this->input->post('submit_search_retur'))
        {
            $tanggal = $this->input->post('date-report');
            if(!empty($tanggal))
            {
                //ambil data retur barang pada tanggal tersebut
                $this->load->model('barang');
                $query = $this->barang->search_retur_barang($tanggal);
                if($query->num_rows() > 0)
                {
                    $row_tr = '';
                    $i = 0;
                    foreach($query->result() as $row)
                    {
                        $row_tr .= '<tr>
                                    <td>'.++$i.'</td>
                                    <td>'.$row->id_retur.'</td>
                                    <td>'.$row->jml_item.' jenis</td>
                                    <td>'.$row->total_item.' item </td>
                                    <td><span class="button"><input type="button" value="Print" class="button" onclick="cetakBon('.$row->id_retur.')"/></span></td>
                                </tr>';
                    }
                    $this->data['search_result'] = $row_tr;
                }
                else
                {
                    $this->data['err_msg'] = 'Tidak ada retur pada tanggal tersebut';
                }
            }
            else
            {
                $this->data['err_msg'] = 'Tanggal tidak boleh dikosongkan';
            }
        }
        //cetak pdf laporan retur
        if(!empty($kode_bon))
        {
            //ambil data retur berdasarkan kode bon
            $this->load->model('barang');
            $query = $this->barang->get_barang_retur($kode_bon);
            if($query->num_rows() > 0)
            {
                $retur = $query->row();
                //susun layout untuk atasnya
                $head = '<div style="margin-top: 5px;">
                            <h3 style="text-align: center;">BON RETUR BARANG</h3>
                            <table style="width: 700px;">
                            <tr><td style="width: 80px;">Kode Bon</td><td style="width:270px;">: '.$retur->id_retur.'</td>
                            <td style="width:60px;">Tanggal Retur</td><td style="width:100px;">: '.$this->convert_date($retur->tanggal).'</td></tr>
                            <tr><td style="width: 80px; ">Asal Toko</td><td style="width: 270px;">: '.config_item('shop_name').'</td>
                            <td style="width:60px;">Tujuan</td><td style="width:100px;">: GUDANG PUSAT</td></tr>                                
                            </table>
                        </div><br />';
                        
                $head .= '&nbsp;<table style="width: 600px;border: 1px solid;" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="width: 40px;text-align: center;border: 1px solid;background-color:  #dedede;font-weight: bold;">No Urut</td>
                            <td style="width: 60px;text-align: center;border: 1px solid;background-color:  #dedede;font-weight: bold;">Kode Barang</td>
                            <td style="width: 70px;text-align: cente;rborder: 1px solid;background-color:  #dedede;font-weight: bold;">Kelompok Barang</td>
                            <td style="width: 110px;text-align: center;border: 1px solid;background-color:  #dedede;font-weight: bold;">Nama Barang</td>
                            <td style="width: 40px;text-align: center;border: 1px solid;background-color:  #dedede;font-weight: bold;">Qty Brg</td>
                            <td style="width: 75px;text-align: center;border: 1px solid;background-color:  #dedede;font-weight: bold;">Harga Jual (Rp.)</td>
                            <td style="width: 50px;text-align: center;border: 1px solid;background-color:  #dedede;font-weight: bold;">Disc %</td>
                            <td style="width: 80px;text-align: center;border: 1px solid;background-color:  #dedede;font-weight: bold;">Jumlah (Rp.) </td>
                        </tr>';
                $i = 0;
                $temp ='';
                $total = 0;
                $total_qty = 0;
                foreach($query->result() as $row)
                {
                    $brg_query = $this->barang->get_barang($row->id_barang,2);
                    $barang = $brg_query->row();
                    $jumlah = $row->qty * $barang->harga * (1 - $barang->diskon/100);
                    $total += $jumlah;
                    $total_qty += $row->qty;
                    $temp .= '<tr>
                            <td style="width: 40px;height:;text-align: center;">'.++$i.'</td>
                            <td style="width: 60px;padding-left:5px;">'.$barang->id_barang.'</td>
                            <td style="width: 70px;text-align:center;">'.$barang->kelompok_barang.'</td>
                            <td style="width: 110px;padding-left: 10px;">'.$barang->nama.'</td>                                
                            <td style="width: 40px;text-align:center;padding-right:10px;">'.$row->qty.'</td>
                            <td style="width: 75px;text-align: right;padding-right:10px;">'.number_format($barang->harga,'0',',','.').',-</td>
                            <td style="width: 50px;text-align: center;">'.$barang->diskon.'</td>
                            <td style="width: 80px;text-align: right;padding-right:10px;">'.number_format($jumlah,'0',',','.').',-</td>
					    </tr>';
                    if($i%15 == 0)
                    {
                        $row_data[]=$temp; 
                    }
                }
                $row_data[] = $temp;
                $row_total = '<tr><td colspan="4" style="width:280px;text-align: right;">T O T A L &nbsp;</td>
                                <td style="width: 40px; text-align: center;">'.$total_qty.'</td>
                                <td style="width: 75px"></td>
                                <td style="width: 50px"></td>
                                <td style="width: 80px;text-align: right; padding-right: 10px;">'.number_format($total,'0',',','.').',-</td>
                                </tr>';
                $footer = '</table><br />
                        <table style="text-align:center; border: 1px solid; margin: 0px;" cellspacing="0" cellpadding="0">
                            <tr><td border: 1px solid;>Diminta</td><td border: 1px solid;>Disiapkan</td><td border: 1px solid;>Disetujui</td><td border: 1px solid;>Diterima</td></tr>                            
                            <tr><td border: 1px solid;>&nbsp;<br /></td><td border: 1px solid;></td><td border: 1px solid;></td><td border: 1px solid;width: 100px;></td></tr>                            
                        </table>';
                //cetak ke pdf
                $this->cetak_pdf(3,$head,$row_data,$row_total,$footer);
            }
        }
        $this->load->view('report-retur',$this->data);
    }
    /**
    * Laporan penggantian barang setelah checking
    */
    function checking($tanggal='')
    {
        $this->load->model('barang');
        if(!empty($tanggal))
        {
            $query = $this->barang->get_penggantian_barang($tanggal);
            if($query->num_rows() > 0)
            {
                //langsung cetak ke pdf saja
                $head ='<div id="report-sales"><h3 style="text-align:center;font-size: 14px">LAPORAN PENGGANTIAN BARANG</h3>
                                <table style="text-align:left">
                                    <tr><td style="width: 100px">CABANG</td><td>: '.config_item('shop_name').'</td></tr>
                                    <tr><td style="width: 100px">TANGGAL CHECKING</td><td>: '.$this->convert_date($tanggal).'</td></tr>
                                   
                                </table>
                               <br />
                                <table style="width: 940px;border: 1px solid;text-align: center;margin: 0px auto;" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="width:30px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">No</td>                                        
                                        <td style="width:75px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Kode Label</td>
                                        <td style="width:120px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Nama Barang</td>
                                        <td style="width:50px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Kelompok <br />Barang</td>                                        
                                        <td style="width:75px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Harga Ganti<br /> (Rp)</td>
                                        <td style="width:75px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Qty</td>                                        
                                        <td style="width:75px;background-color:  #dedede;font-weight: bold;text-transform: uppercase;border:1px solid;">Jumlah Ganti<br /> (Rp)</td>                                        
                                    </tr>';
                $row_data = '';                
                $total_qty=0;
                $total_ganti=0;
                $i = 0;
                foreach($query->result() as $row)
                {
                    $jumlah_ganti = $row->qty * $row->harga_ganti;
                    $row_data .= '<tr>
                                    <td style="width:30px">'.++$i.'</td>
                                    <td style="width:75px">'.$row->id_barang.'</td>
                                    <td style="width:120px">'.$row->nama.'</td>
                                    <td style="width:50px">'.$row->kelompok_barang.'</td>
                                    <td style="width:75px">'.number_format($row->harga_ganti,0,',','.').'</td>
                                    <td style="width:75px">'.$row->qty.'</td>
                                    <td style="width:75px">'.number_format($jumlah_ganti,0,',','.').'</td>
                                </tr>';
                    $total_qty += $row->qty;
                    $total_ganti += $jumlah_ganti;
                    if($i%50 == 0)
                    {
                        $list[] = $row_data;
                        $row_data = '';
                    }
                }
                $list[] = $row_data;
                $row_total = '<tr><td colspan="5" style="width:350px">T O T A L</td><td style="width:75px">'.$total_qty.'</td><td style="width:75px">'.number_format($total_ganti,0,',','.').'</td></tr>';
                
                //ambil data supervisor sama data kasir
                /*$this->load->model('transaksi');
                $query = $this->transaksi->get_kasir($tanggal);
                foreach($query->result() as $row)
                {
                    $qry = $this->karyawan->get_karyawan($row->id_kasir);
                    $data_kasir[] = $qry->row();                            
                }*/
                $query = $this->karyawan->get_supervisor();
                $supervisor = $query->row();
                //nyusun data untuk ditampilkan
                $line1 = '<td style="text-align:center">S U P E R V I S O R</td>';
                $line2 = '<td style="text-align:center"><br />('.strtoupper($supervisor->nama).')</td>';
                $i=0;
                /*foreach($data_kasir as $row)
                {
                    $line1 .= '<td style="text-align:center">K A S I R '.++$i.'</td>';
                    $line2 .= '<td style="text-align:center"><br />('.strtoupper($row->nama).')</td>';
                }*/
                $foot ='</table>
                        <br />
                        <table>
                            <tr>'.$line1.'</tr>
                            <tr>'.$line2.'</tr>
                        </table></div>';
                $this->cetak_pdf(5,$head,$list,$row_total,$foot);
            }            
        }
        //tampilkan list laporan penggantian barang
        $query = $this->barang->get_penggantian_barang();
        if($query->num_rows() > 0)
        {
            $row_data = '';
            $i = 0;
            foreach($query->result() as $row)
            {
                $row_data .= '<tr>
                                <td>'.++$i.'</td>
                                <td>'.$this->convert_date($row->tanggal).'</td>
                                <td>'.$row->total_item.' jenis </td>
                                <td>'.$row->total_qty.' item</td>
                                <td><span class="button"><input type="button" class="button" value="Cetak" onclick="cetakGantiBarang(\''.$row->tanggal.'\')"/></span></td>
                            </tr>';
            }
            $this->data['row_data'] = $row_data;
        }
        $this->load->view('report-checking',$this->data);
    }
    /**
    *Cari laporan penjualan
    */
    function search()
    {
        if($this->input->post('submit_search'))
        {
            if($this->validate_search())
            {
                $this->load->model('transaksi');
                //ambil data dari form
                $tanggal = $this->input->post('date-report');
                $based_on = $this->input->post('based_on');
                $query = "";
                if($based_on == 1)
                {
                    $kb_low = $this->input->post('kb_low');
                    $kb_high = $this->input->post('kb_high');
                    if(!empty($kb_low)) 
                    {
                        $data = array(
                            'tanggal'=>$tanggal,
                            'kb_low'=>$kb_low,
                            'kb_high'=>$kb_high
                        );
                        $query = $this->transaksi->search_sales($based_on,$data);
                    }
                }
                else if($based_on == 2)
                {
                    $ib_low = $this->input->post('ib_low');
                    $ib_high = $this->input->post('ib_high');
                    $data = array(
                            'tanggal'=>$tanggal,
                            'ib_low'=>$ib_low,
                            'ib_high'=>$ib_high
                        );
                    $query = $this->transaksi->search_sales($based_on,$data);
                }
                if($query->num_rows() > 0)
                {
                    $row_tr = "";
                    $i=0;
                    foreach($query->result() as $row)
                    {
                        $row_tr .= '<tr>
                                    <td>'.++$i.'</td>
                                    <td>'.$row->kelompok_barang.'</td>
                                    <td>'.$row->id_barang.'</td>
                                    <td>'.$row->nama.'</td>
                                    <td>'.number_format($row->harga,0,',','.').'</td>
                                    <td>'.$row->jumlah_terjual.'</td>                                    
                            </tr>';
                    }
                    $this->data['result'] = $row_tr;
                }
                else 
                {
                    $this->data['result'] = '<span style="color:red">Tidak ada data dengan keterangan yang dimaksud</span>';
                }
            }
        }
        $this->load->view('report-search',$this->data);
    }
    /**
    *validasi form searching penjualan
    */
    function validate_search()
    {
        $this->load->library('form_validation');
        
        $this->form_validation->set_rules('date-report','tanggal','required');
        $this->form_validation->set_rules('based_on', 'pilihan berdasarkan', 'required');        
        
        if($this->form_validation->run() == FALSE)
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
        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
        
        //set some language-dependent strings
        $pdf->setLanguageArray($l); 

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
        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('Laporan.pdf', 'I');     
            
    }
    /**
    *convert date to string
    */
    function convert_date($date)
    {
        $date_arr = explode('-',$date);        
        switch($date_arr[1])
        {
            case '01': $month = 'Januari';break;
            case '02': $month = 'Februari';break;
            case '03': $month = 'Maret';break;
            case '04': $month = 'April';break;
            case '05': $month = 'Mei';break;
            case '06': $month = 'Juni';break;
            case '07': $month = 'Juli';break;
            case '08': $month = 'Agustus';break;
            case '09': $month = 'September';break;
            case '10': $month = 'Oktober';break;
            case '11': $month = 'November';break;
            case '12': $month = 'Desember';break;
        }
        return $date_arr[2].' '.$month.' '.$date_arr[0];
    }
}
//End of file Report.php
//Location: application/controller/report.php