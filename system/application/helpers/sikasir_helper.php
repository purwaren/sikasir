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
//Helper sikasir
//Location: system/application/helper