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
}
//End of item_transaksi.php
//Location: system/application/models