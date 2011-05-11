<?php
/**
* Model for item transaksi penjualan
*/
class Item_transaksi extends Model 
{
    /**
    *Model constructor
    */
    function Transaksi()
    {
        parent::Model();
    }
    /**
    *Menyimpan data item transaksi
    */
    function add_item_transaksi($data)
    {
        return $this->db->insert('item_transaksi_penjualan',$data);        
    }
    /**
    *Ambil item transaksi per id transaksi
    */
    function get_item_transaksi($id_transaksi)
    {
        $query = 'select id_transaksi,id_barang, sum(qty) as qty,diskon from item_transaksi_penjualan where id_transaksi="'.$id_transaksi.'" group by id_barang, diskon';
        return $this->db->query($query);
    }
    
    /**
    * Ambil item transaksi per id transaksi tanpa diakumulasi
    */
    function get_all_item($id_transaksi) 
    {
        return $this->db->get_where('item_transaksi_penjualan',array('id_transaksi'=>$id_transaksi));
    }
    /**
    * Hapus id transaksi tertentu
    */
    function remove($id_transaksi)
    {
        return $this->db->where('id_transaksi',$id_transaksi)->delete('item_transaksi_penjualan');
    }
}
//End of item_transaksi.php
//Location: system/application/models