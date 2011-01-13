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
        //cek apakah karyawan terdaftar di toko
        $this->load->model('karyawan');
        $query = $this->karyawan->get_karyawan($id_karyawan);
        if($query->num_rows() > 0)
        {
            $karyawan = $query->row();
            //check apakah sudah absen, absen hanya boleh sekali
            $this->load->model('absensi');
            $data = array('id_karyawan'=>$id_karyawan,'tanggal'=>date("Y-m-d"));        
            $query = $this->absensi->get_presence_status($data);        
            if($query->num_rows() > 0) //sudah pernah absen
            {
                $this->data['err_msg'] = '';            
            }
            else //belum pernah absen
            {
                $this->absensi->set_presence($id_karyawan, time());            
                $query = $this->absensi->get_presence_status($data);
            }
            //status absensi
            $absensi = $query->row();           
            $data = array('NIK'=>$absensi->NIK,'nama'=>$karyawan->nama,'status'=>$absensi->status, 'datang'=>$absensi->datang);
            _e(json_encode($data));
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
        if($this->data['jabatan'] == 'supervisor') 
        {
            $this->data['result']='';
            if($this->input->post('submit_absensi'))
            {
                $tanggal = $this->input->post('date_absensi');            
                if(!empty($tanggal))
                {
                    $this->load->model('absensi');
                    $query = $this->absensi->get_presence($tanggal);
                    if($query->num_rows() > 0)
                    {
                        $tgl = explode('-',$tanggal);
                        $table = ' <h3 style="text-align:center">DATA ABSENSI KARYAWAN <br /> TANGGAL : '.$tgl[2].' '.$this->month_to_string($tgl[1]).' '.$tgl[0].'</h3>
                                    <table class="table-data" cellspacing="0" cellpadding="0" >
                                    <tr><!--<td class="head">No</td>--><td class="head">NIK</td><td class="head">Nama Karyawan</td><td class="head">Status</td><td class="head">Action</td></tr>';
                        $i=0;
                        foreach($query->result() as $row)
                        {
                            $table .= '<tr><!--<td></td>--><td>'.$row->NIK.'</td><td>'.$row->nama.'</td><td>'.$row->status.'</td>
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
        else
        {
            redirect('home/error');
        }
    }
    /**
    *Menampilkan detail absensi    
    */
    function view($nik="")
    {
        if(!empty($nik))
        {
            $tanggal = $this->uri->segment(4);
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
        if($this->data['jabatan'] == 'supervisor')
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
        else
        {
            redirect('home/error');
        }
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