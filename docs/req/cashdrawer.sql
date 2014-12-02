-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Waktu pembuatan: 13. Desember 2011 jam 00:47
-- Versi Server: 5.5.8
-- Versi PHP: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `kasir`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cashdrawer`
--

CREATE TABLE IF NOT EXISTS `cashdrawer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kassa` int(11) NOT NULL,
  `shift` int(1) NOT NULL,
  `ra` varchar(11) NOT NULL,
  `cash` varchar(11) NOT NULL,
  `tanggal` date NOT NULL,
  `kasir1` int(11) NOT NULL,
  `kasir2` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data untuk tabel `cashdrawer`
--

