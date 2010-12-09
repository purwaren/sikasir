<?php
/**
*Home Controller
*@Author: PuRwa
*Desc : This controller was the default controller that will be called, used for accounting users
*/
class Home extends Controller {
    /*class field */
    protected $data;
    /*Controller constructor*/
	function Home()
	{
		parent::Controller();            
		$this->data['page'] ='home';
        
        if($this->session->userdata('logged_in'))
        {
            $this->load->model('karyawan');
            $query = $this->karyawan->get_karyawan($this->session->userdata('nik'));
            $data_karyawan = $query->row();
            $this->data['userinfo'] = $data_karyawan->nama;
            $this->data['jabatan'] = $this->session->userdata('jabatan');
            $this->data['now'] = strtoupper(date('M')).'<br />'.date('d'); 
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
    * Method login
    * Used to do login things
    */
    function login()
    {
        $this->load->model('accounting');
        if($this->input->post('submit_login'))
        {
            $username = $this->input->post('username');
            $passwd = $this->input->post('passwd');
            if(!empty($username) && $passwd)
            {
                //validate username and password with database
                $data_check = $this->accounting->get_pengguna($username, $passwd);
                if($data_check->num_rows() == 1)
                {
                    $account = $data_check->row();
                    if($account->status == 1)
                    {
                        //create session and write session data
                        $user = $data_check->row();
                        $data = array(
                                'nik'=>$user->NIK,
                                'jabatan'=>$user->jabatan,
                                'logged_in'=>TRUE
                                );
                        $this->session->set_userdata($data);
                        //redirect to home if success
                        redirect('home','refresh');
                    }
                    else
                    {
                        $this->data['login_error'] = 'Username anda diblokir oleh sistem, silahkan menghubungi administrator';
                    }
                }
                else
                {                    
                    $this->data['login_error'] = 'Terjadi kesalahan username atau password';
                }       
            }
            else
            {
                $this->data['login_error'] = 'Username dan password tidak boleh dikosongkan';
            }
        }
        $this->load->view('login',$this->data);
    }
    /**
    * Method logout
    * Used to do logout things
    */
    function logout()
    {
        $this->session->sess_destroy();
        redirect('home/login','refresh');
    }
    /**
    *Display error message for access denied
    *
    */
    function error()
    {
        $this->load->view('error',$this->data);
    }
    /**
    *Error 404, page not found
    */
    function error_404()
    {
        $this->load->view('error_404',$this->data);
    }
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */