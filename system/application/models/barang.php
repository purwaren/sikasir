<?php
/**
* Model for table barang (CRUD)
*/
class Barang extends Model 
{
    /**
    *Model constructor
    */
    function Barang()
    {
        parent::Model();
    }
    /**
    *Retrieve semua data barang
    */
    function get_barang_all($id_barang='')
    {
    	if(empty($id_barang))
        	return $this->db->get('barang');
    	else 
    		return $this->db->get_where('barang',array('id_barang'=>$id_barang));
    }
    /**
    *Retrieve data barang
    */
    function get_barang($id_barang,$cond)
    {
        //retrieve barang untuk pos
        if($cond == 1)
        {
            $query = $this->db->get_where('barang',array('id_barang'=>$id_barang,'stok_barang >'=>'0'));
        }
        //retrieve barang untuk receipt
        else if($cond == 2)
        {
            $query = $this->db->get_where('barang',array('id_barang'=>$id_barang));
        }
        //retrieve barang untuk refund, mutasi keluar harus > 0
        else if($cond == 3)
        {
            $query = $this->db->get_where('barang',array('id_barang'=>$id_barang,'mutasi_keluar >' => '0'));
        }
        //retrieve barang untuk opname
        else if($cond == 4)
        {
            $query = 'select * from barang where (id_barang="'.$id_barang.'" and stok_barang > 0) or (id_barang="'.$id_barang.'" and stok_opname > 0)';
            $query = $this->db->query($query);
        }
        return $query;
    }
    /**
    *Ambil data kelompk barang yang ada di dalam database
    */
    function get_kel_barang()
    {
        $query = 'select kelompok_barang from barang group by kelompok_barang';
        return $this->db->query($query);
    }
    /**
    *Search barang
    */
    function search_barang($keywords)
    {
        $this->db->like('id_barang',$keywords,'after');
        //$this->db->or_like('nama',$keywords,'both');
        return $this->db->get('barang');
    }
    /**
    * search stok barang
    */
    function search_stok($keywords)
    {
        $this->db->select('*')->from('barang b')->join('barang_masuk bm','b.id_barang = bm.id_barang','left');
        $this->db->like('b.id_barang',$keywords,'after');
        $this->db->or_like('nama',$keywords,'both')->order_by('tanggal','desc');        
        return $this->db->get();
    }
    /**
    *Search barang untuk autocomplete
    */
    function search_autocomplete($keywords)
    {
        $this->db->like('id_barang',$keywords,'after');
        $this->db->where('stok_barang >','0');
        return $this->db->get('barang');
    }
    /**
    *Update data barang, stok_barang nya berkurang karena penjualan
    */
    function update_barang($cond,$data) 
    {
        $this->db->query('update barang set stok_barang = stok_barang - '.$data['jumlah_terjual'].', jumlah_terjual = jumlah_terjual + '.$data['jumlah_terjual'].', mutasi_keluar = mutasi_keluar + '.$data['jumlah_terjual'].'  where id_barang = "'.$cond['id_barang'].'"');
    }    
    /**
    *update data barang, semuanya, bukan stok aja, edit barang
    *berdasarkan kode bon pada saat mutasi masuk barang
    */
    function edit_barang($id_barang,$id_mutasi_masuk,$data)
    {
        $this->db->trans_begin();
        $query = 'update barang set nama="'.$data['nama'].'",harga="'.$data['harga'].'",kelompok_barang="'.$data['kelompok_barang'].'",diskon="'.$data['diskon'].'",
                  stok_barang = stok_barang + '.$data['beda'].',                  
                  total_barang = total_barang + '.$data['beda'].',
                  mutasi_masuk = mutasi_masuk + '.$data['beda'].'
                  where id_barang ="'.$data['id_barang'].'"';                  
        $this->db->query($query);
        $query = 'update barang_masuk set qty='.$data['qty'].' where id_mutasi_masuk="'.$id_mutasi_masuk.'" and id_barang="'.$id_barang.'"';
        $this->db->query($query);
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }
        else
        {
            $this->db->trans_commit();
            return TRUE;
        }
    }
    /**
    *Update data barang ketika datang barang dari gudang
    */
    function update_barang_gudang($data)
    {
        $query = 'update barang set nama="'.$data['nama'].'",harga="'.$data['harga'].'",kelompok_barang="'.$data['kelompok_barang'].'",diskon="'.$data['diskon'].'",
                  stok_barang = stok_barang + '.$data['stok_barang'].',                  
                  total_barang = total_barang + '.$data['total_barang'].',
                  mutasi_masuk = mutasi_masuk + '.$data['mutasi_masuk'].'
                  where id_barang ="'.$data['id_barang'].'"';
        return $this->db->query($query);
    }
    /**
    *Insert barang baru dari gudang
    */
    function insert_barang($data)
    {
        return $this->db->insert('barang',$data);
    }
    /**
    *insert ke table barang_masuk
    */
    function insert_barang_masuk($data)
    {
        return $this->db->insert('barang_masuk',$data);
    }
    /**
    *function refund barang, 
    * 1 - refund -> +1 stok-->barang tukar
    * 2 - jual -> +1 penjualan--->barang pengganti
    */
    function refund_barang($id_barang,$qty,$option)
    {
        if($option == 1)
        {
            $this->db->query('update barang set stok_barang = stok_barang+'.$qty.', jumlah_terjual = jumlah_terjual-'.$qty.', mutasi_keluar = mutasi_keluar-'.$qty.' where id_barang = "'.$id_barang.'"');        
        }
        if($option == 2)
        {
            $this->db->query('update barang set stok_barang = stok_barang-'.$qty.', jumlah_terjual = jumlah_terjual+'.$qty.', mutasi_keluar = mutasi_keluar+'.$qty.' where id_barang = "'.$id_barang.'"');
        }
        return TRUE;
    }
    /**
    *Ambil barang masuk berdasarkan tanggal, ini untuk fungsi manajemen
    */
    function get_barang_masuk($kode_bon)
    {
        $query = 'select bm.*,b.nama, b.kelompok_barang, b.harga from barang_masuk bm left join barang b on bm.id_barang = b.id_barang  where id_mutasi_masuk = "'.$kode_bon.'" order by bm.id';
        return $this->db->query($query);
    }
    /**
    * Ambil bon yang masuk pada tanggal terntentu
    */
    function get_bon_barang_masuk($tanggal)
    {
        $query = 'select id_mutasi_masuk, tanggal, count(id_barang) as jumlah_barang, sum(qty) as total from barang_masuk where tanggal="'.$tanggal.'" group by id_mutasi_masuk';
        return $this->db->query($query);
    }
    /**
    * check barang masuk untuk import
    */
    function check_barang_masuk_for_import($param)
    {
        $this->db->where($param);
        return $this->db->get('barang_masuk');
    }
    /**
    *Ambil detail barang masuk, sesuai id bon dan id barang
    */
    function get_detail_barang_masuk($id_mutasi_masuk, $id_barang)
    {
        $query = 'select bm.*,b.nama, b.kelompok_barang, b.harga, b.mutasi_masuk, b.stok_barang,b.diskon from barang_masuk bm left join barang b on bm.id_barang = b.id_barang  where id_mutasi_masuk="'.$id_mutasi_masuk.'" and bm.id_barang="'.$id_barang.'" ';
        return $this->db->query($query);
    }
    /**
    *Ambil stok barang berdasarkan kelompok barang
    */
    function get_stok_by_kb()
    {
        $query = 'select kelompok_barang,sum(stok_barang) as stok, sum(jumlah_terjual) as terjual, sum(total_barang) as total_stok, sum(mutasi_masuk) as masuk, sum(mutasi_keluar) as keluar from barang group by kelompok_barang';
        return $this->db->query($query);
    }
    /**
    *Check apakah ada duplikat di barang masuk, (kode bon - id barang)=> unik
    */
    function cek_barang_masuk($id_mutasi_masuk, $id_barang) {
        $query = 'select * from barang_masuk where id_mutasi_masuk="'.$id_mutasi_masuk.'" and id_barang="'.$id_barang.'"';
        return $this->db->query($query);
    }
    /**
    *Masukkan ke table retur barang
    */
    function retur_barang($data)
    {
        return $this->db->insert('retur_barang',$data);
    }
    /**
    *updata data barang setelah retur
    */
    function update_after_retur($data)
    {
        //$query = 'update barang set stok_barang = stok_barang - '.$data['qty'].', total_barang = total_barang - '.$data['qty'].', mutasi_masuk= mutasi_masuk - '.$data['qty'].' where id_barang="'.$data['id_barang'].'"';
        $query = 'update barang set stok_barang = stok_barang - '.$data['qty'].', mutasi_masuk= mutasi_masuk - '.$data['qty'].' where id_barang="'.$data['id_barang'].'"';
        return $this->db->query($query);
    }
    /**
    *Search data retur barang per tanggal, kelompokkan berdasarkan bon
    */
    function search_retur_barang($tanggal)
    {
        $query = 'select id_retur, count(id_barang) as jml_item, sum(qty) as total_item from retur_barang where tanggal="'.$tanggal.'" group by id_retur';
        return $this->db->query($query);
    }
    /**
    *Ambil data retur barang berdasarkan kode bon    
    */
    function get_barang_retur($kode_bon)
    {
        return $this->db->get_where('retur_barang',array('id_retur'=>$kode_bon));
    }
    /**
    *check apakah barang udah pernah diretur untuk bon tersebut
    */
    function check_retur($kode_bon, $id_barang)
    {
        return $this->db->get_where('retur_barang',array('id_retur'=>$kode_bon,'id_barang'=>$id_barang));
    }
    /**
    *Mengupdate rtur barang
    */
    function update_retur_barang($data)
    {
        $query = 'update retur_barang set qty = qty + '.$data['qty'].' where id_retur="'.$data['id_retur'].'" and id_barang="'.$data['id_barang'].'"';
        return $this->db->query($query);
    }
    /**
    *Update stok opname, saat pelaksanaan checking barang
    */
    function update_opname($data)
    {
        $query = 'update barang set stok_opname="'.$data['stok_opname'].'" where id_barang="'.$data['id_barang'].'"';
        return $this->db->query($query);
    }
    /**
    *Ambil data opname, dgn cattan stok != 0
    */
    function get_opname($kel_brg,$opsi)
    {
        //belum pernah opname, atw stok opname masih nol
        if($opsi == 1)
        {
            $query = 'select * from barang where kelompok_barang="'.$kel_brg.'" and stok_barang > 0 and stok_opname = 0';
        }
        //yang sudah pernh opname
        else if($opsi == 2)
        {
            $query = 'select * from barang where kelompok_barang="'.$kel_brg.'" and stok_barang > 0 and stok_opname > 0';
        }
        //ambil dua duanya
        else if($opsi == 3)
        {
            $query = 'select * from barang where kelompok_barang="'.$kel_brg.'" and stok_barang > 0';
        }
        //ambil semua barang
        else if($opsi == 4)
        {
            $query = 'select * from barang where stok_barang > 0 order by id_barang';
        }
        return $this->db->query($query);
    }
    /**
    *Ambil data barang dengan beda stok tidak nol, untuk laporan penggantian barang
    */
    function get_ganti_barang($opsi)
    {
        if($opsi == 1)
            $query = 'select * from (select barang.*, (barang.stok_barang - barang.stok_opname) as beda_stok from barang) as ganti where ganti.beda_stok !=0 and ganti.stok_barang > 0 order by ganti.id_barang asc';        
        else if($opsi == 3)
            $query = 'select * from (select barang.*, (barang.stok_barang - barang.stok_opname) as beda_stok from barang) as ganti where ganti.stok_opname !=0';
        return $this->db->query($query);
    }
    /**
    *Ambil data pada tabel penggantian barang untuk dibuat laporan
    * klo tanggal kosong -- ambil list laporan penggantian barang gabungin per tanggal
    * klo tanggal ada - ambil data laporan penggantian barang pada tanggal yang dimaksud
    */
    function get_penggantian_barang($tanggal='')
    {
        if(!empty($tanggal))
        {
            $query = 'select pb.*,b.nama,b.kelompok_barang,b.harga from penggantian_barang pb left join barang b on b.id_barang = pb.id_barang where tanggal="'.$tanggal.'"';
        }
        else
        {
            $query = 'select pb.tanggal,sum(qty) as total_qty, count(pb.id_barang) as total_item  from penggantian_barang pb group by tanggal';
        }        
        return $this->db->query($query);
    }
    /*
    *insert ganti barang
    */
    function insert_ganti_barang($data)
    {
        return $this->db->insert('penggantian_barang',$data);
    }
    /**
    *update barang setelah checking / opname
    * stok_barang = stok_opname
    * stok_awal = stok_opname
    * mutasi_masuk = 0
    * mutasi_keluar = 0
    * Stok_opname = 0
    * berlaku untuk semua barang, baik yang ada penggantian barangnya atau tidak.
    */
    function update_after_checking()
    {
        $query = 'update barang set stok_barang=stok_opname, stok_awal=stok_opname, mutasi_masuk=0, mutasi_keluar=0, stok_opname=0 ';
        return $this->db->query($query);
    }
}
//End of file barang.php
//Location; System/application/models