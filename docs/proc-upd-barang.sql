CREATE DEFINER=`root`@`localhost` PROCEDURE `update_barang`(IN `id_brg` VARCHAR(16), IN `qty_terjual` INT(10))
BEGIN
DECLARE opname INT;
SELECT stok_opname INTO opname FROM barang WHERE id_barang=id_brg;
IF opname = 0 THEN
  UPDATE barang SET stok_barang = stok_barang-qty_terjual, jumlah_terjual=jumlah_terjual+qty_terjual,mutasi_keluar=mutasi_keluar+qty_terjual WHERE id_barang = id_brg;
ELSE
  UPDATE barang SET stok_barang = stok_barang-qty_terjual, jumlah_terjual=jumlah_terjual+qty_terjual,mutasi_keluar=mutasi_keluar+qty_terjual, stok_opname = stok_opname-qty_terjual WHERE id_barang = id_brg; 
END IF;
END;