<?php
/**
*Profile Controller
*@Author: PuRwa
*Desc : Profile controller
*/
class Profile extends Controller {
    /*class field */
    protected $data;
    /*Controller constructor*/
	function Profile()
	{
		parent::Controller();            
		$this->data['page'] ='profile';
        
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
    *
    */
    function index()
    {
        redirect('profile/view');
    }
    /**
    *Menampilkan profile pengguna yang sedang login
    */
    function view()
    {
        if($this->session->userdata('logged_in') != TRUE)
        {
            redirect('home/login');
        }
        else 
        {
            //retrieve data karyawan and pengguna
            $this->load->model('pengguna');
            $this->load->model('karyawan');
            $query = $this->pengguna->get_pengguna($this->session->userdata('nik'));
            $this->data['pengguna'] = $query->row();
            $query = $this->karyawan->get_karyawan($this->session->userdata('nik'));
            $this->data['karyawan'] = $query->row();
        }
        //tampilin ke browser
        $this->load->view('profile-view',$this->data);
    }
    /**
    * fungsi untuk ubah profile dan ubah password
    */
    function ubah()
    {
        //loading model
        $this->load->model('pengguna');
        $this->load->model('karyawan');
        //saving changes
        if($this->input->post('submit_ubah_profile'))
        {            
            //ubah karyawan
            if($this->validate_form_ubah())
            {
                $pengguna = array(
                    'NIK'=>$this->input->post('nik'),
                    'username'=>$this->input->post('username'),
                    'jabatan'=>$this->input->post('jabatan'),
                    'passwd'=> $this->input->post('passwd'),
                    'new_passwd'=> $this->input->post('new_passwd'),
                    'new_passwd_confirm'=> $this->input->post('new_passwd_confirm')
                );                
                //ubah pengguna / password
                $msg = '';
                if(!empty($pengguna['passwd']))
                {
                    $this->load->model('accounting');                
                    $query = $this->accounting->get_pengguna($pengguna['username'],$pengguna['passwd']);                    
                    if($query->num_rows() > 0)
                    {                        
                        if($pengguna['new_passwd'] == $pengguna['new_passwd_confirm'])
                        {                        
                            if($this->accounting->change_passwd($pengguna['username'],$pengguna['new_passwd']))
                            {
                                $msg = 'password';
                            }
                        }
                        else
                        {
                            $this->data['err_msg'] = '<span style="color:red">Password baru dan konfirmasi tidak cocok</span>';
                        }
                    }
                    else
                    {
                        $this->data['err_msg'] = '<span style="color:red">Password lama salah</span>';
                    }
                }
                //ubah data karyawan
                $karyawan = array(
                    'NIK'=>$this->input->post('nik'),
                    'nama'=>$this->input->post('nama'),
                    'alamat'=>$this->input->post('alamat'),
                    'telepon'=>$this->input->post('telepon')
                    //'divisi'=>$this->input->post('divisi'),
                );
                if($this->karyawan->update_karyawan($karyawan) && $this->pengguna->update_pengguna($pengguna))
                {
                    if(!empty($msg))
                    {
                        $this->data['err_msg'] = '<span style="color:green">Profile dan password yang diperbaharui telah disimpan</span>';
                    }
                    else
                    {
                        $this->data['err_msg'] = '<span style="color:green">Profile yang diperbaharui telah disimpan</span>';
                    }
                }
            }
            else
            {                
                $this->data['err_msg'] = '<span style="color:red">Terjadi kesalahan, pastikan informasi yang diminta sudah diisi dengan benar </span>';                
            }
        }
        //retrieve data karyawan and pengguna        
        $query = $this->pengguna->get_pengguna($this->session->userdata('nik'));
        $this->data['pengguna'] = $query->row();
        $query = $this->karyawan->get_karyawan($this->session->userdata('nik'));
        $this->data['karyawan'] = $query->row();
        //tampilin ke browser
        $this->load->view('profile-edit',$this->data);
    }
    /**
    *Validasi form ubah profile
    */
    function validate_form_ubah() 
    {
        $this->load->library('form_validation');
        //setting rule
        $this->form_validation->set_rules('username','username','required|alpha_numeric');       
        $this->form_validation->set_rules('nik','NIK','required|alpha_numeric');
        $this->form_validation->set_rules('nama','nama','required');
        $this->form_validation->set_rules('alamat','alamat','required');
        $this->form_validation->set_rules('jabatan','jabatan','required');
        $this->form_validation->set_rules('telepon','telepon','required|numeric');
        if($this->form_validation->run() == FALSE)
        {            
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }
}

//End of file profile.php
//Location: system/application/controller/