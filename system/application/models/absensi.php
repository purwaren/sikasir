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
    *simpan data karyawan datang
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
    * update presence   untuk absensi pulang
    */
    function update_presence($data)
    {
        $this->db->where(array('NIK'=>$data['NIK'],'tanggal'=>$data['tanggal']));
        return $this->db->update('absensi',$data);
    }
    /**
    *Lihat status absensi karyawan pada hari terntentu, ditentukan dengan tanggal
    */
    function get_presence_status($data)
    {
        $this->db->select('time(from_unixtime(datang)) as dtg');
        $this->db->select('time(from_unixtime(pulang)) as plg');
        $this->db->select('absensi.*');
        $query = $this->db->get_where('absensi',array('NIK'=>$data['NIK'],'tanggal'=>$data['tanggal']));
        return $query;
    }
    /**
    *update status absensinya
    */
    function update_presence_status($nik,$status,$tanggal='')
    {
        if(empty($tanggal))
        {
            if($status == '1')
                $query = 'update absensi set status='.$status.' where NIK = "'.$nik.'"';
            else
                $query = 'update absensi set status='.$status.', datang="" where NIK = "'.$nik.'"';
        }
        else
        {
            if($status == '1')
                $query = 'update absensi set status='.$status.' where NIK = "'.$nik.'" and tanggal="'.$tanggal.'"';
            else
                $query = 'update absensi set status='.$status.', datang="", pulang="" where NIK = "'.$nik.'" and tanggal="'.$tanggal.'"';
        }
        return $this->db->query($query);
    }
    /**
    *ambil data kehadiran berdasarkan tanggal
    */
    function get_presence($tanggal)
    {
        $query = 'select k.*,a.*, time(from_unixtime(datang)) as dtg, time(from_unixtime(pulang)) as plg from karyawan k left join absensi a on k.NIK = a.NIK where tanggal="'.$tanggal.'"';
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