<?php
/**
* Controller User
* Design for user management
*/
class User extends Controller {
    /*class field */
    protected $data;
    /*Controller constructor*/
	function User()
	{
		parent::Controller();            
		$this->data['page'] ='user';
        
        if($this->session->userdata('logged_in'))
        {
            $this->load->model('karyawan');
            $query = $this->karyawan->get_karyawan($this->session->userdata('nik'));
            $data_karyawan = $query->row();
            $this->data['userinfo'] = $data_karyawan->nama;
            $this->data['jabatan'] = $this->session->userdata('jabatan');
            $this->data['now'] = strtoupper(date('M')).'<br />'.date('d');    
            if($this->data['jabatan'] != 'admin')
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
    *menampilkan detail pengguna
    */
    function view($nik='')
    {
        if(!empty($nik))
        {
            $this->load->model('pengguna');
            $query = $this->pengguna->get_pengguna($nik);
            if($query->num_rows() > 0)
            {
                $this->data['pengguna'] = $query->row();
            }
            else
            {
                $this->data['err_msg'] = 'Data tidak ditemukan';
            }
            $this->load->view('user-view',$this->data);
        }
        else
        {
            redirect('home/error_404');
        }
    }
    /**
    *Tambah user baru
    */
    function add() 
    {
        if($this->input->post('submit_user_add'))
        {
            if($this->validate_form_add())
            {
                //ambil data-data form
                $username = $this->input->post('username');
                $passwd = $this->input->post('passwd');
                $nik = $this->input->post('nik');
                $nama = $this->input->post('nama');
                $jabatan = $this->input->post('jabatan');
                $alamat = $this->input->post('alamat');
                $telp = $this->input->post('telp');
                //load model pengguna, model karyawan. Simpan ke table pengguna dulu baru karyawan
                $this->load->model('karyawan');
                $this->load->model('pengguna');
                $data_pengguna = array(
                            'NIK'=>$nik,
                            'username'=>$username,
                            'passwd'=>md5($passwd),
                            'jabatan'=>$jabatan,
                            'status'=> 1
                        );
                $data_karyawan = array(
                    'NIK'=>$nik,
                    'nama'=>$nama,
                    'alamat'=>$alamat,
                    'telepon'=>$telp,
                    'divisi'=>$jabatan
                );
                //check dulu, apakah NIK yang dipilih sudah terdaftar
                $query = $this->pengguna->get_pengguna($nik);
                if($query->num_rows() == 0)
                {
                    if($this->pengguna->add_pengguna($data_pengguna))
                    {
                        if($this->karyawan->add_karyawan($data_karyawan))
                        {
                            $this->data['err_msg'] = '<span style="color:green">Pengguna baru telah ditambahkan</span>';
                        }
                    }
                    else
                    {
                        $this->data['err_msg'] = '<span style="color:red">Gagal menambah pengguna</span>';
                    }
                }
                else
                {
                    $this->data['err_msg'] = '<span style="color:red">Karyawan dengan NIK : <b>'.$nik.'</b> sudah terdaftar.</span>' ;
                }
            }
        }
        $this->load->view('user-add',$this->data);
    }
    /**
    *Validasi form tambah user
    */
    function validate_form_add($param='') 
    {
        $this->load->library('form_validation');
        //setting rule
        $this->form_validation->set_rules('username','username','required|alpha_numeric');
        if(empty($param))
        {
            $this->form_validation->set_rules('passwd','password','required|min_length[6]');
            $this->form_validation->set_rules('confirm_passwd','konfirmasi','required|matches[passwd]');
        }
        $this->form_validation->set_rules('nik','NIK','required|alpha_numeric');
        $this->form_validation->set_rules('nama','nama','required');
        $this->form_validation->set_rules('alamat','alamat','required');
        $this->form_validation->set_rules('jabatan','jabatan','required');
        $this->form_validation->set_rules('telp','telepon','required|numeric');
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
    *validate_form_changepasswd
    */
    function validate_form_changepasswd()
    {
        $this->load->library('form_validation');
        //setting rule
        $this->form_validation->set_rules('username','username','required|alpha_numeric');        
        $this->form_validation->set_rules('new_passwd','password baru','required|min_length[6]');
        $this->form_validation->set_rules('new_passwd_confirm','konfirmasi password baru','required|matches[new_passwd]');
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
    *User management
    */
    function manage()
    {
        $this->load->model('pengguna');
        $query = $this->pengguna->get_all_pengguna();        
        if($query->num_rows() > 0)
        {
            $row_data = '';
            foreach($query->result() as $row)
            {
                if($row->status == 1)
                {
                    $status = 'Active';
                    $status_btn = '<span class="button">&nbsp;<input type="button" class="button" value="Block" onclick="blockPengguna(\''.$row->NIK.'\',\''.$row->nama.'\')"/></span>';
                }
                else
                {
                    $status = 'Blocked';
                    $status_btn = '<span class="button">&nbsp;<input type="button" class="button" value="Unblock" onclick="unblockPengguna(\''.$row->NIK.'\',\''.$row->nama.'\')"/></span>';
                }
                $row_data .= '<tr>
                                <td>'.$row->NIK.'</td>
                                <td>'.$row->nama.'</td>
                                <td>'.$row->jabatan.'</td>
                                <td>'.$status.'</td>
                                <td>
                                    <span class="button">&nbsp;<input type="button" class="button" value="Detail" onclick="detailPengguna(\''.$row->NIK.'\')"/></span>
                                    <span class="button">&nbsp;<input type="button" class="button" value="Edit" onclick="editPengguna(\''.$row->NIK.'\')"/></span>
                                    <span class="button">&nbsp;<input type="button" class="button" value="Password" onclick="changePassword(\''.$row->NIK.'\')"/></span>
                                    '.$status_btn.'
                                    <span class="button">&nbsp;<input type="button" class="button" value="Remove" onclick="removePengguna(\''.$row->NIK.'\',\''.$row->nama.'\')"/></span>
                                </td>
                            </tr>';
            }
            $this->data['row_data'] = $row_data;
        }
        $this->load->view('user-manage',$this->data);
    }
    /**
    *fungsi untuk edit pengguna
    */
    function edit($nik)
    {
        if(!empty($nik))
        {
            $this->load->model('pengguna');
            $this->load->model('karyawan');
            //check apakah sudah submit data
            if($this->input->post('submit_user_edit'))
            {
                if($this->validate_form_add(1))
                {
                    //password pengguna
                    $passwd = $this->input->post('passwd');
                    $confirm_passwd = $this->input->post('confirm_passwd');
                    //data pengguna                    
                    $data_p = array(
                                'username'=>$this->input->post('username'),                                    
                                'NIK'=>$this->input->post('nik'),
                                'jabatan'=>$this->input->post('jabatan')
                            );
                    //update pengguna
                    if($this->pengguna->update_pengguna($data_p))
                    {                        
                        //jika pengguna ingin mengubah password
                        if(isset($passwd) && $passwd == $confirm_passwd)
                        {
                            $data_p['passwd'] = md5($passwd);
                            $this->pengguna->update_password($data_p);
                        }                   
                        //data karyawan
                        $data_k = array(
                                    'NIK'=>$this->input->post('nik'),
                                    'nama'=>$this->input->post('nama'),
                                    'alamat'=>$this->input->post('alamat'),
                                    'telepon'=>$this->input->post('telp')
                                );
                        //lakukan update data
                        if($this->karyawan->update_karyawan($data_k))
                        {
                            $this->data['err_msg']='<span style="color:green">Perubahan data telah disimpan</span>';
                            
                        }
                        else
                        {
                            $this->data['err_msg']='<span style="color:red">Error!! Gagal mengubah data</span>';
                        }
                    }
                    else
                    {
                        $this->data['err_msg']='<span style="color:red">Error! Gagal mengubah data</span>'.$this->db->last_query();
                    }
                }
            }
            $query = $this->pengguna->get_pengguna($nik);
            if($query->num_rows() > 0)
            {
                $this->data['pengguna'] = $query->row();                
            }
            else
            {
                $this->data['err_msg'] = 'Data tidak ditemukan';
            }
            $this->load->view('user-edit',$this->data);            
        }
        else
        {
            redirect('home/error_404');
        }        
    }
    /**
    *fungsi untuk ubah password pengguna
    */
    function editpasswd($nik='')
    {
        if(!empty($nik))
        {
            $this->load->model('pengguna');
            //edit user password
            if($this->input->post('submit_user_editpasswd'))
            {
                if($this->validate_form_changepasswd())
                {
                    $data = array(
                            'username'=>$this->input->post('username'),                            
                            'new_password'=>$this->input->post('new_passwd')
                            );
                    $this->load->model('accounting');
                    //lakukan penggantian passwd
                    if($this->accounting->change_passwd($data['username'],$data['new_password']))
                    {
                        $this->data['err_msg'] = '<span style="color:green">Penggantian password berhasil</span>';
                    }                    
                }
            }      
            //tampilkan data user
            $query = $this->pengguna->get_pengguna($nik);
            if($query->num_rows() > 0)
            {
                $this->data['pengguna'] = $query->row();
            }
            else
            {
                $this->data['err_msg'] = '<span style="color:red">Data tidak ditemukan</span>';
            }
            
            $this->load->view('user-edit-password',$this->data);
        }
        else
        {
            redirect('home/error_404');
        }
    }
    /**
    *fungsi untuk blok dan unblok pengguna    
    */
    function block()
    {
        $data = array(
                    'nik'=>$this->input->post('nik'),
                    'status'=>$this->input->post('status')
                );
        $this->load->model('pengguna');
        if($this->pengguna->update_status($data))
        {
            _e(1);
        }
        else
        {
            _e(0);
        }
    }
    /**
    *fungsi untuk menghapus pengguna
    */
    function remove()
    {
        $data = array('nik'=>$this->input->post('nik'));
        $this->load->model('pengguna');
        if($this->pengguna->update_flag_hapus($data))
        {
            _e(1);
        }
        else
        {
            _e(0);
        }
    }    
}
//End of file user.php