<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Configuration for shop
*/
$config['shop_code'] = "01";
$config['shop_name'] = "MODE FASHION PERBAUNGAN";
$config['shop_address'] = "Jl. Serdang No. 141";
$config['shop_phone'] = "(061) 799 0009";

/**
*Screen configuration
* 14 inch => 1024px x 640px=>default
* 17 inch => 1360px x 768px
*/
$config['screen'] = '14';

/**
* jam kerja normal dalam satu hari, sisanya dianggap lembur
*/
$config['work_cycle'] = 8;

/**
 * Printer port
 */
$config['port'] =  20000;

/**
 * Display port
 */
$config['display'] = 2345;
$config['use_display'] = false;
$config['base_url'] = '/sikasir/';
/**
 * Application version
 */
$config['version'] = '1.1 rev 101';
$config['name']='Sikasir';

/**
 * Refund khusus, kelompok barang yang boleh direfund
 */
$config['refund'] = array('016','017','018','019','052');
$config['refund_period'] = '2014-12-31';
$config['refund_disc'] = '20';

/**
 * Daftar toko tujuan retur
 */
$config['refund_shop'] = array(
		'99'=>'Gudang Pusat',
		'01'=>'Mode Dept.Store Kisaran',
		'02'=>'Mode Dept.Store Tembung',
		'11'=>'Mode Nibung',
// 		'13'=>'',
		'14'=>'Mode Stabat',
		'15'=>'Mode Binjai',
		'16'=>'Mode Rampah',
		'17'=>'Mode Perbaungan',
		'18'=>'Modiest Perbaungan',
		'19'=>'Mode Titipapan',
		'20'=>'Mode Tanjung Morawa',
		'21'=>'Mode Kisaran',
		'22'=>'Modiest Stabat',
		'23'=>'Mode Tembung',
// 		'24'=>'',
		'25'=>'Mode Marelan',
// 		'26'=>'',
		'27'=>'Modiest Halat',
// 		'28'
);

/* End of file doctypes.php */
/* Location: application/config/doctypes.php */