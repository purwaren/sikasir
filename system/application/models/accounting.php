<?php
/**
* Model for accounting user (register, login, logout etc)
*/
class Accounting extends Model 
{
    /**
    *Model constructor
    */
    function Accounting()
    {
        parent::Model();
    }
    /**
    *Retrieve data pengguna
    */
    function get_pengguna($username,$passwd)
    {
        $query = 'select * from pengguna where md5(username)=md5("'.$username.'") and passwd = md5("'.$passwd.'") and flag_hapus=0';                
        return $this->db->query($query);
    }
    /**
    *
    */
    function change_passwd($username,$passwd)
    {
        $query = 'update pengguna set passwd = md5("'.$passwd.'") where md5(username)=md5("'.$username.'")';
        return $this->db->query($query);
    }
}
//End of accounting.php
//Location: system/application/models