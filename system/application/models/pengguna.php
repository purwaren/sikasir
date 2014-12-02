<?php
/**
*Model for Pengguna (CRUD)
*/
class Pengguna extends Model
{
    /**
    *Model constructor
    */
    function Pengguna()
    {
        parent::Model();        
    }
    /**
    *fungsi tambah pengguna
    */
    function add_pengguna($data)
    {
        return $this->db->insert('pengguna',$data);
    }
    /**
    *fungsi untuk update pengguna
    */
    function update_pengguna($data)
    {
        $query = 'update pengguna set username="'.$data['username'].'", jabatan='.$data['jabatan'].' where NIK="'.$data['NIK'].'"';
        return $this->db->query($query);
    }
    /**
    *fungsi untuk update password
    */
    function update_password($data)
    {
        $query = 'update pengguna set passwd="'.$data['passwd'].'" where NIK="'.$data['NIK'].'"';
        return $this->db->query($query);
    }
    /**
    *update status (blok /unblok)
    */
    function update_status($data)
    {
        $query = 'update pengguna set status="'.$data['status'].'" where NIK="'.$data['nik'].'"';
        return $this->db->query($query);
    }
    /**
    *menghapus pengguna
    */
    function update_flag_hapus($data)
    {
        $query = 'update pengguna set flag_hapus=1 where NIK="'.$data['nik'].'"';
        return $this->db->query($query);
    }
    /**
    * get data pengguna
    */
    function get_pengguna($nik)
    {
        $query = 'select * from pengguna p left join karyawan k on p.NIK = k.NIK where p.NIK= "'.$nik.'"';
        return $this->db->query($query);
    }
    /**
    *ambil semua data pengguna
    */
    function get_all_pengguna()
    {
        $query = 'select * from pengguna p left join karyawan k on p.NIK = k.NIK where p.flag_hapus=0 order by p.NIK ';
        return $this->db->query($query);
    }
}
//End of file pengguna.php