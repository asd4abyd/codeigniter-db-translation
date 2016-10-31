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
( ! defined('BASEPATH'))? exit('No direct script access allowed'):null;

/**
 * Class LangSwitch
 *
 * @category Translation
 * @package  Codeigniter DB Translation
 * @author   Abdelqader <asd_abyd@yahoo.com>
 * @license  Policy <http://abyd.net/>
 * @link     http://abyd.net/
 */

class Test extends CI_Controller
{
    // this important to set one of languages as a base language
    // incase that your database have no record for [text key]
    // it will take it form base language

    public function index()
    {
        echo ci_lang('label.hello_word');
    }

    public function get_key($key)
    {
        echo ci_lang($key);
    }

    public function get_key_with_specific_language($key, $langID=-1)
    {
        echo ci_lang('label.hello_word', $langID);
    }

    public function setlanguage($text='HeLlO WoRd')
    {
        var_dump(ci_set_lang('label.hello_word', $text));
    }


    public function set_key_with_specific_language($key, $text, $langID=-1)
    {
        echo ci_lang($key, $text, $langID);
    }

}