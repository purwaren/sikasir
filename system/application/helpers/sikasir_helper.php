<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*@Author : PuRwa
*Description: This helper is used to handle some process on sikasir.
*/

/*replace echo*/
if ( ! function_exists('_e'))
{
	function _e($string='')
    {
        if(strlen($string) > 0)
        {
            echo $string;
        }
    }
}
/*calling config item*/
if( ! function_exists('config_item'))
{
    function config_item($item)
    {
        $CI =& get_instance();
		return $CI->config->item($item);
    }
}
/*find max day on a month*/
if( ! function_exists('max_day'))
{
    function max_day($month,$year)
    {
        if($month == 2)
        {
            if($year%4 == 0)
            {
                return 29;
            }
            else
            {
                return 28;
            }
        }
        else
        {
            if($month == 1 && $month == 3 && $month == 5 && $month == 7 && $month == 8 && $month == 10 && $month == 12)
            {
                return 31;
            }
            else
            {
                return 30;
            }
        }
    }
}
/**
*Nampilin tanggal sekarang
*
*/
if(!function_exists('date_to_string')) 
{
    function date_to_string($date)
    {
        $date_arr = explode('-',$date);
        $date = $date_arr[2].' ';
        switch($date_arr[1])
        {
            case '01': $date .= 'Januari';break;
            case '02': $date .= 'Februari';break;
            case '03': $date .= 'Maret';break;
            case '04': $date .= 'April';break;
            case '05': $date .= 'Mei';break;
            case '06': $date .= 'Juni';break;
            case '07': $date .= 'Juli';break;
            case '08': $date .= 'Agustus';break;
            case '09': $date .= 'September';break;
            case '10': $date .= 'Oktober';break;
            case '11': $date .= 'November';break;
            case '12': $date .= 'Desember';break;
        }
        return $date.' '.$date_arr[0];
    }
}
/**
*konversi bulan dari angka ke string
*/
if(!function_exists('month_to_string')) 
{
    function month_to_string($month)
    {
        $str = '';
        switch($month)
        {
            case 1 : $str = 'Januari';break;
            case 2 : $str = 'Februari';break;
            case 3 : $str = 'Maret';break;
            case 4 : $str = 'April';break;
            case 5 : $str = 'Mei';break;
            case 6 : $str = 'Juni';break;
            case 7 : $str = 'Juli';break;
            case 8 : $str = 'Agustus';break;
            case 9: $str = 'September';break;
            case 10 : $str = 'Oktober';break;
            case 11 : $str = 'November';break;
            case 12 : $str = 'Desember';break;
        }
        return $str;
    }
}
if(!function_exists('get_header_receipt'))
{
    function get_header_receipt()
    {
        $head= str_pad(config_item('shop_name'),34,' ',STR_PAD_RIGHT).'#';
        $head.=str_pad(config_item('shop_address'),34,' ',STR_PAD_RIGHT).'#';
        $head.=str_pad('Telp. '.config_item('shop_phone'),34,' ',STR_PAD_RIGHT);

        return $head;
    }
}
//Helper sikasir
//Location: system/application/helper