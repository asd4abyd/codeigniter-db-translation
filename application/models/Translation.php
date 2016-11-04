<?php
/**
 * translation Model
 *
 * PHP Version 5.5
 *
 * @category Translation
 * @package  Codeigniter DB Translation
 * @author   Abdelqader Osama <asd_abyd@yahoo.com>
 */
(!defined('BASEPATH')) ? exit('No direct script access allowed') : null;

/**
 * Class translation
 *
 * @category Translation
 * @package  Codeigniter DB Translation
 * @author   Abdelqader Osama <asd_abyd@yahoo.com>
 * @license  Policy <http://abyd.net>
 * @link     http://abyd.net
 *
 */
class translation extends CI_Model
{
    /**
     *  the general table which cached
     *  it just the only table cached in file
     *
     * @var string $_generalTable
     */
    private $_generalTable = 'general';
    private $_prefixTableName = 'lg_';

    /**
     * to cache general table in local file
     *
     * @var bool $_cacheEnabled
     */
    private $_cacheEnabled = true;

    /**
     * default path of cache file is /application/cache/langFile
     * it should have a full permission for create file
     *
     * @var string $_cachePathFile
     */
    private $_cachePathFile = '';


    /**
     * it is using for map a spacial keys to a different table
     * except
     *
     * @var  array $_tables  default [] (empty array)
     *
     */
    private $_tables = [
        'label.'         =>'label',
        'button.'        =>'college',
        'error_request.' =>'request',
    ];

    private $_generalValues=null;

    public function __construct() {
        parent::__construct();

        if($this->_cachePathFile=='') {
            $this->_cachePathFile = APPPATH . 'cache/langFile';
        }

        $this->load->config('language');
    }

    /**
     * get text by KEY
     *
     * @param  string $key    key
     * @param  int    $langID language id
     * @return array|string
     */
    public function lang($key, $langID)
    {

        // creating keys active in Development mode
        if(defined('ENVIRONMENT') && ENVIRONMENT=='development') {  // Developer Mode
            if(!$this->db->table_exists($this->getTableName($key))){
                $this->createTable($this->getTableName($key));
            }

            $this->db->select('lang_id');
            $rs2 = $this->db->get_where($this->getTableName($key), ['key' => $key]);

            if (!$rs2->num_rows()) {
                $value=explode('.', $key);
                if(isset($value[1])){
                    $val =  ucfirst(strtolower(str_replace('_', ' ', $value[1])));
                }else{
                    $val = ucfirst(strtolower(str_replace('_', ' ', $value[0])));
                }
                $this->setLang($key, $val, $langID);
            }
        }

        if(is_null($this->_generalValues && $this->_cacheEnabled)) {

            if(file_exists($this->_cachePathFile)){
                $strFile = file_get_contents($this->_cachePathFile);
                $this->_generalValues = json_decode($strFile,true);
            }

            if(count($this->_generalValues)==0 || is_null($this->_generalValues))  {
                $this->db->select('md5(concat(`key`, "/", `lang_id`)) as `key`', false);
                $this->db->select('text_value');

                $rs = $this->db->get($this->_generalTable);

                $this->_generalValues = array_column($rs->result_array(), 'text_value', 'key');

                file_put_contents($this->_cachePathFile, json_encode($this->_generalValues));
            }
        }
        $newKey = md5($key.'/'.$langID);

        if($this->getTableName($key)==$this->_generalTable && $this->_cacheEnabled){


            if(isset($this->_generalValues[$newKey])) {
                return $this->_generalValues[$newKey];
            }

            foreach($this->config->item('base_language') as $languageID){
                if(isset($this->_generalValues[md5("{$key}/{$languageID}")])){
                    return $this->_generalValues[md5("{$key}/{$languageID}")];
                }
            }

            $value=explode('.', $key);
            if(isset($value[1])) {
                return ucfirst(strtolower(str_replace('_', ' ', $value[1])));
            }

            return ucfirst(strtolower(str_replace('_', ' ', $value[0])));

        }

        return $this->_getTextFromDB($key, $langID);
    }

    /**
     * Get Text From DB
     *
     * @param string $key    key
     * @param int    $langID language id
     *
     * @return array|string
     */
    private function _getTextFromDB($key, $langID)
    {

        $key = strtolower($key);

        $table = $this->getTableName($key);

        $debugStatus = $this->db->db_debug;
        $this->db->db_debug = false;

        $this->db->select(['lang_id', 'text_value']);
        $this->db->where(['key'=>$key]);
        $rs = $this->db->get($table);

        $this->db->db_debug = $debugStatus;

        if ($this->db->_error_number()==1146) {
            $value=explode('.', $key);
            $value=ucfirst(strtolower(str_replace('_', ' ', $value[1])));

            // creating keys active in Development mode
            if (defined('ENVIRONMENT') && ENVIRONMENT=='development') { // Developer Mode
                $this->setLang($key, $value);
                return $value;
            }

            return '';
        }

        $result=[];


        foreach ($rs->result_array() as $val) {
                $result[$val['lang_id']] = $val['text_value'];
        }

        if (isset($result[$langID])) {
            return $result[$langID];
        } elseif (isset($result[$this->config->item('base_language')])) {
            return $result[$this->config->item('base_language')];
        } elseif (count($result)>0) {
            ksort($result);

            foreach ($result as $val) {
                return html_entity_decode($val);
            }
        } else {
            $value=explode('.', $key);
            $value=ucfirst(strtolower(str_replace('_', ' ', $value[1])));

            if (defined('ENVIRONMENT') and ENVIRONMENT=='development') {
                $this->setLang($key, $value);
                return $value;
            }

            return '';
        }
    }

    /**
     * Create Table
     *
     * @param string $tableName table name
     *
     * @return bool
     */
    private function createTable($tableName)
    {

        return (bool) $this->db->query("
            create table if not exists $tableName (
        `key` VARCHAR(100) NOT NULL,
        `lang_id` INT NOT NULL,
        `text_value` TEXT NOT NULL,
        PRIMARY KEY (`key`, `lang_id`), INDEX `key` (`key`),
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT 0
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
    }

    /**
     * set Language
     * @param string $key key
     * @param string $value value
     * @param int $langID lang ID
     * @return bool|string
     */
    public function setLang($key, $value, $langID)
    {
        $value = mb_convert_encoding(trim($value), 'utf-8');
        $value = htmlentities($value, ENT_NOQUOTES, 'UTF-8');

        $key = strtolower($key);

        $table = $this->getTableName($key);

        $debugStatus = $this->db->db_debug;
        $this->db->db_debug = false;


        $result = $this->db->query("
            insert into {$table} (`key`, lang_id, text_value)  VALUES (?, ?, ?)
            on duplicate key update `text_value`=values(`text_value`),  updated_at=now()", [$key, $langID, $value]);

        $this->db->db_debug = $debugStatus;

        if ($this->db->_error_number() == 1146) {
            $this->createTable($table);

            $this->db->set([
                'key' => $key,
                'lang_id' => $langID,
                'text_value' => $value
            ]);
            $result = (bool)$this->db->insert($table);
        }


        if ($result) {
            if ($this->getTableName($key) == $this->_generalTable && $this->_cacheEnabled) {
                @unlink($this->_cachePathFile);
            }

            return (bool)$result;
        }

        return false;
    }

    /**
     * get Table Name
     * @param string $key key
     * @return string
     */
    private function getTableName($key)
    {
        $table = $this->_generalTable;

        foreach ($this->_tables as $tableKeys=>$val) {
            if (!(strpos($key, $tableKeys)===false) && strpos($key, $tableKeys)==0) {
                $table=$val;
                break;
            }
        }

        if (strpos($table, $this->_prefixTableName)===false || strpos($table, $this->_prefixTableName)!=0) {
            $table = $this->_prefixTableName.$table;
        }

        return $table;
    }

    /**
     * get All General Keys
     * @param array $criteria criteria
     * @param null $limit limit
     * @param null $offset offset
     * @return array
     */
    public function getAllGeneralKeys($criteria=[],$limit=null, $offset=null)
    {
        $this->db->select(['key','text_value']);
        $this->db->distinct();
        $this->db->where($criteria);
        $rs = $this->db->get($this->_generalTable, $limit, $offset);

        return $rs->result_array();
    }

    /**
     * is Key Exist
     * @param string $key key
     * @return bool|string
     */
    public function isKeyExist($key)
    {
        if ($key=='') {
            return '';
        }

        $key = strtolower($key);

        $table = $this->getTableName($key);

        $this->db->select(['key']);
        $this->db->where('key', $key);
        $rs = $this->db->get($table, 1);
        if ($rs->num_rows()==0) {
            return false;
        }

        return true;
    }

    /**
     * delete Key
     * @param string $key key
     * @return bool|string
     */
    public function deleteKey($key)
    {
        if ($key=='') {
            return '';
        }

        $key = strtolower($key);

        $table = $this->getTableName($key);

        $this->db->where('key', $key);
        $rs = $this->db->delete($table);
        if (!$rs) {
            return false;
        }

        return true;
    }
}
