<?php
/**
*Model for karyawan (CRUD)
*/
class Karyawan extends Model
{
    /**
    *Model constructor
    */
    function Karyawan()
    {
        parent::Model();        
    }
    /**
    *tambah karyawan
    */
    function add_karyawan($data) 
    {
        return $this->db->insert('karyawan',$data);
    }
    /**
    *fungsi untuk update data karyawan
    */
    function update_karyawan($data)
    {
        $query = 'update karyawan set nama="'.$data['nama'].'", alamat="'.$data['alamat'].'", telepon="'.$data['telepon'].'" where NIK="'.$data['NIK'].'"';
        return $this->db->query($query);
    }
    /**
    *Retrieve data karywan
    */
    function get_karyawan($nik)
    {        
        return $this->db->get_where('karyawan',array('NIK'=>$nik));
    }
    /**
    *Retrieve all karyawan data
    */
    function get_all_karyawan()
    {
        return $this->db->get_where('karyawan');
    }
    /**
    *Ambil data semua pramuniaga, untuk bikin grafik
    */
    function get_all_pramuniaga()
    {
        $query = 'select * from karyawan k left join pengguna p on p.NIK = k.NIK where p.jabatan="pramuniaga"';
        return $this->db->query($query);
    }
    /**
    *Retrieve data pramuniaga
    */
    function get_pramuniaga($name,$nik)
    {
        if(!empty($nik))
        {
            $query = $this->db->query('select * from karyawan where nama like "'.$name.'%" and NIK in(select NIK from pengguna where jabatan="pramuniaga" and NIK not in('.$nik.')) order by nama asc');
        }
        else
        {
            $query = $this->db->query('select * from karyawan where nama like "'.$name.'%" and NIK in(select NIK from pengguna where jabatan="pramuniaga" and flag_hapus=0) order by nama asc');
        }
        return $query;
    }
    /**
    *Ambil data supervisor
    */
    function get_supervisor()
    {
        $query = 'select karyawan.* from karyawan, (select NIK from pengguna where jabatan="supervisor") as sup where karyawan.NIK = sup.NIK';
        return $this->db->query($query);
    }
}
//End of karyawan.php
//Location: system/application/models