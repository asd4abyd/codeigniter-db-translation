<?php
/**
 * translation Model
 *
 * PHP Version 5.5
 *
 * @category Translation
 * @package  Codeigniter DB Translation
 * @author   Abdelqader Osama <asd_abyd@yahoo.com>
 * @license  Policy <http://abyd.net>
 * @link     http://abyd.net
 *
 */

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}


if (! function_exists('ci_lang')) {
    /**
     * get String By Language
     *
     * @param string $line
     * @param int $langID
     * @param bool $editable
     *
     * @return string
     */

    function ci_lang($line, $langID = -1, $editable = true)
    {
        $CI =& get_instance();

        if($langID==-1){
            $CI->load->config('language');
            $langID = $CI->config->item('base_language');
        }


        $txt = str_replace('&#0;', '', $CI->translation->lang($line, $langID));

        return $txt;
    }

    /**
     * @param $key
     * @param $val
     * @param int $langID
     * @return bool
     */

    function ci_set_lang($key, $val, $langID=-1)
    {
        $CI =& get_instance();

        if($langID==-1){
            $CI->load->config('language');
            $langID = $CI->config->item('base_language');
        }

        return $CI->translation->setLang($key, $val, $langID);
    }
}
