/*ubah field id transaksi jadi 16 panjangnya*/
ALTER TABLE  `transaksi_penjualan` CHANGE  `id_transaksi`  
`id_transaksi` VARCHAR( 16 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL

ALTER TABLE  `item_transaksi_penjualan` CHANGE  `id_transaksi`  
`id_transaksi` VARCHAR( 16 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL 
