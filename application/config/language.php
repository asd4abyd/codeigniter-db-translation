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

defined('BASEPATH') OR exit('No direct script access allowed');

// base_language is important to set one of languages as
// a base language incase that your database have no record
// for [text key], it will take it form base_language

$config['base_language'] = 1;


// define that languages you want to use in your website

$config['language_code']['1'] = 'en-us';
$config['language_code']['2'] = 'ar-sa';
$config['language_code']['3'] = 'es-es';
/*
  .
  .
  .
  .
  .
  .
  .
  .
 \/
 You can add what ever languages codes


 */