<?php
/**
* Model for presence
*/
class Absensi extends Model 
{
    /**
    *Model constructor
    */
    function Absensi()
    {
        parent::Model();
    }
    /**
    *Retrieve data pengguna
    */
    function set_presence($id_karyawan, $datang)
    {
        $data = array(
                    'NIK'=>$id_karyawan,
                    'tanggal'=>date("Y-m-d"),
                    'datang'=>time(),
                    'pulang'=>0                    
                );
        return $this->db->insert('absensi',$data);
    }
    /**
    *Lihat status absensi karyawan pada hari terntentu, ditentukan dengan tanggal
    */
    function get_presence_status($data)
    {
        $query = $this->db->get_where('absensi',array('NIK'=>$data['id_karyawan'],'tanggal'=>$data['tanggal']));
        return $query;
    }
    /**
    *update status absensinya
    */
    function update_presence_status($nik,$status,$tanggal='')
    {
        if(empty($tanggal))
        {
            $query = 'update absensi set status='.$status.' where NIK = "'.$nik.'"';
        }
        else
        {
            $query = 'update absensi set status='.$status.' where NIK = "'.$nik.'" and tanggal="'.$tanggal.'"';
        }
        return $this->db->query($query);
    }
    /**
    *ambil data kehadiran berdasarkan tanggal
    */
    function get_presence($tanggal)
    {
        $query = 'select * from karyawan k left join absensi a on k.NIK = a.NIK where tanggal="'.$tanggal.'"';
        return $this->db->query($query);
    }
    /**
    *ambil data kehadiran + data karyawan
    */
    function get_presence_detail($nik,$tanggal)
    {
        $query = 'select * from absensi a left join karyawan k on a.NIK = k.NIK where tanggal="'.$tanggal.'" and a.NIK="'.$nik.'"'; 
        return $this->db->query($query);
    }
    /**
    *hapus data absensi
    */
    function remove_presence($nik,$tanggal)
    {
        $query = 'delete from absensi where nik="'.$nik.'" and tanggal="'.$tanggal.'"';
        return $this->db->query($query);
    }
}
//End of accounting.php
//Location: system/application/models