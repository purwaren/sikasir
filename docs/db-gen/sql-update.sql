
DROP TABLE `item_transaksi_penjualan`;

CREATE TABLE IF NOT EXISTS  `item_transaksi_penjualan` (
 `id_transaksi` VARCHAR( 11 ) NOT NULL DEFAULT  '',
 `id_barang` VARCHAR( 10 ) NOT NULL DEFAULT  '',
 `qty` INT( 11 ) NOT NULL ,
 `diskon` VARCHAR( 10 ) NOT NULL ,
 KEY  `id_transaksi` (  `id_transaksi` ),
 KEY  `id_barang` (  `id_barang` )
) ENGINE = INNODB DEFAULT CHARSET = latin1;


ALTER TABLE `item_transaksi_penjualan`
  ADD CONSTRAINT `item_transaksi_penjualan_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi_penjualan` (`id_transaksi`),
  ADD CONSTRAINT `item_transaksi_penjualan_ibfk_2` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`);

--
-- Dumping data for table `item_transaksi_penjualan`
--

INSERT INTO `item_transaksi_penjualan` (`id_transaksi`, `id_barang`, `qty`, `diskon`) VALUES
('1281354430', '01101000', 1, '0'),
('1281354430', '01200051', 1, '2.5'),
('1281354430', '01200087', 2, '5'),
('1281354730', '05710111', 1, '0'),
('1281354831', '11260222', 1, '0'),
('1281354942', '05144007', 2, '5'),
('1281354942', '07122009', 1, '7.5'),
('1281362810', '01230136', 2, '0'),
('1281362810', '07764053', 1, '0'),
('1281364064', '01230136', 2, '0'),
('1281364064', '11230839', 1, '0'),
('1281364169', '06440054', 1, '0'),
('1281364169', '07137044', 2, '0'),
('1281364169', '18554005', 1, '0'),
('1281364323', '46542000', 1, '0'),
('1281364323', '62335001', 1, '0'),
('1281364323', '72338066', 2, '0'),
('1281436332', '01250039', 1, '0');
