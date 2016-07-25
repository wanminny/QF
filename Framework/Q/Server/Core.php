<?php

class Q_Server_Core extends Q_Server_Abstract
{

    /**
     * 读模式
     *
     * @var Integer
     *
     */
    const SERVER_MODE_READ = 1;

    /**
     * 写模式
     *
     * @var Integer
     *
     */
    const SERVER_MODE_WRITE = 2;

    /**
     * 代理模式
     *
     * @var Integer
     *
     */
    const SERVER_MODE_PROXY = 3;

    /**
     * 服务器均衡选择模式
     *
     * @var Integer
     *
     */
    const SERVER_SELECT_MODE_BALANCE = 1;

    /**
     * 服务器随机选择模式
     *
     * @var Integer
     *
     */
    const SERVER_SELECT_MODE_RAND = 2;

    /**
     * 服务器Hash选择模式
     *
     * @var Integer
     *
     */
    const SERVER_SELECT_MODE_HASH = 3;

    /**
     * 服务器选择静态模式
     *
     * @var Integer
     *
     */
    const SERVER_SELECT_MODE_STATIC = 4;

    /**
     * 服务器选择静态模式取出第一条
     *
     * @var Integer
     *
     */
    const SERVER_SELECT_MODE_STATIC_ROW = 5;

    /**
     * 服务器选择静态模式（综合数据模式）
     *
     * @var Integer
     *
     */
    const SERVER_SELECT_MODE_STATIC_MIXED = 6;

    /**
     *
     * @var Q_Core_Config_Ini
     *
     */
    private $section_data;

    /**
     * Enter description here
     */
    public function __construct()
    {
        defined('Q_APPLICATION_ENV') || define('Q_APPLICATION_ENV', Q_Core_Consts::Q_APPLICATION_ENV_RELEASE);
        defined('QIN_CONFIG') || define('QIN_CONFIG', Q_Core_Consts::Q_CONFIG);
        $this->config_path = QIN_CONFIG;
    }

    /**
     * 获取一个单元
     *
     * @see Q_Server_Interface::readerSection()
     * @return Q_Server_Core
     *
     */
    public function readerSection($config_name, $section)
    {
        $config_file_str = $this->config_path . DIRECTORY_SEPARATOR . $config_name . '.' . Q_APPLICATION_ENV . '.config.ini';
        $config_file = realpath($config_file_str);
        if ($config_file == false || !file_exists($config_file)) {
            throw new Q_Server_Exception('Config File ' . $config_file_str . ' Not Find.');
        }
        $cacheFileKey = $config_name . ':' . $section . ':' . $config_file;
        $lastModified = filemtime($config_file);

        if (Q_Cache_Backend::isEnabled()) {
            $loadData = Q_Cache_Backend::load($cacheFileKey);
            if (isset($loadData['config']) && !empty($loadData['config']) && $lastModified == $loadData['lastModified']) {
                $this->section_data = $loadData['config'];
            } else {
                $configData = new Q_Core_Config_Ini($config_file, $section);
                $this->section_data = $configData->getSectionData();
                $ini_val = array(
                    'config' => $this->section_data,
                    'lastModified' => $lastModified
                );
                Q_Cache_Backend::save($cacheFileKey, $ini_val);
            }
        } else {
            $configData = new Q_Core_Config_Ini($config_file, $section);
            $this->section_data = $configData->getSectionData();
        }
        return $this;
    }

    /**
     * 获取
     *
     * @return ArrayObject
     *
     */
    public function getSectionData()
    {
        return $this->section_data;
    }

    /**
     * 设置hash key
     *
     * @param String $key
     * @return Q_Server_Core
     *
     */
    public function hashKey($key)
    {
        assert(is_string($key));
        $this->hash_key = $key;
        return $this;
    }

    /**
     * 获取servers 列表
     *
     * @param String $node
     * @param Integer $mode
     * @throws Q_Server_Exception
     * @return Array
     *
     */
    public function loadServer($node, $mode = Q_Server_Core::SERVER_MODE_READ, $select = Q_Server_Core::SERVER_SELECT_MODE_RAND, $check = false)
    {
        $this->check_server = $check;
        if (!isset($this->section_data[$node])) {
            throw new Q_Cache_Exception(__CLASS__ . ', No ' . $node . ' Config. ');
        }
        switch ($mode) {
            case self::SERVER_MODE_READ :
                $ip_list = $this->section_data[$node]['readers'];
                break;
            case self::SERVER_MODE_WRITE :
                $ip_list = $this->section_data[$node]['writers'];
                break;
            case self::SERVER_MODE_PROXY :
                $ip_list = $this->section_data[$node]['proxys'];
                break;
            default :
                throw new Q_Server_Exception(' No Server Mode ' . $mode);
        }
        $node_arr = $this->section_data[$node];
        $node_options = array();
        foreach ($node_arr as $k => $v) {
            $node_options[$k] = $v;
        }
        $ip_array = $this->parseServerString($ip_list);
        switch ($select) {
            case self::SERVER_SELECT_MODE_BALANCE :
                throw new Q_Server_Exception(' Balance Unrealized :). ');
                break;
            case self::SERVER_SELECT_MODE_RAND :
                $server_options = array_merge($node_options, $this->randServer($ip_array));
                break;
            case self::SERVER_SELECT_MODE_HASH :
                $server_options = array_merge($node_options, $this->hashServer($ip_array));
                break;
            case self::SERVER_SELECT_MODE_STATIC :
                $server_options = array_merge($node_options, $ip_array);
                break;
            case self::SERVER_SELECT_MODE_STATIC_ROW :
                $server = array_merge($node_options, $ip_array);
                $server_options = count($server) > 0 ? $server[0] : array();
                break;
            case self::SERVER_SELECT_MODE_STATIC_MIXED:
                $server_options = $node_options;
                if (!empty($ip_array))
                    $server_options = array_merge($node_options, array('hosts' => $ip_array));
                break;
            default :
                throw new Q_Server_Exception('No Mode Select.');
        }
        $this->_unset($server_options);
        return $server_options;
    }

    private function _unset(&$options)
    {
        $this->section_data = array();
        unset($options['writers']);
        unset($options['readers']);
        unset($options['proxys']);
        unset($this->hash_key);
    }
}