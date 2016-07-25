<?php

/**
 * sql缓存
 * User: zhanglirong
 * Date: 15-12-20
 * Time: 18:15
 */
abstract class Q_Db_Adapter_Pdo_Abstract
{

    /**
     * 数据库对象
     * @var PDO
     */
    protected $_connection;

    /**
     * 常量强制列名为指定的大小写
     * @var int
     */
    protected $_caseFolding = 0;

    /**
     * 数据库驱动参数
     * @var array
     */
    protected $driverConfig = array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
        PDO::ATTR_PERSISTENT => false,
        PDO::ATTR_TIMEOUT => 30
    );

    /**
     * 字符集
     *
     * @var string
     */
    protected $charset = 'utf8';

    /**
     * fetch 参数
     * @var array
     */
    protected $fetchOptions = array(
        'column_number' => 0,
        'fetch_style' => null,
        'fetch_argument' => null,
        'ctor_args' => array(),
        'class_name' => 'stdClass'
    );

    /**
     * @var Q_Db_Adapter_Pdo_ElementParser
     */
    protected $elementParser;

    /**
     * 数据库名
     * @var String
     */
    protected $dbname;

    /**
     * 服务器地址列表
     * @var array
     */
    protected $serverConfig;

    /**
     * DB 结果缓存
     * @var bool
     */
    protected $cacheStatus = true;

    /**
     * DB 缓存时间
     * @var int
     */
    protected $cacheExpire = 60;

    /**
     * 缓存KEY
     * @var null
     */
    protected $cacheKey = null;

    /**
     * 缓存TAG
     * @var null
     */
    protected $cacheTag = null;

    /**
     * 缓存对象
     * @var Q_Cache_Memcached
     */
    protected $cacheObj;

    /**
     * @var null
     */
    protected $prepareDriverOptions = null;

    /**
     * 对应SqlMap根节点nameSpace 也就是数据库名
     *
     * 主要是为了多库下同样的表结构 使用指定的sqlmap
     *
     * @var String
     */
    protected $nameSpace;

    /**
     * key前缀使用在刷新范围内的Cache
     *
     * 如：
     * $prefix = 'list_';
     * 范围：1,10 or a,z
     * 结果就是删除 'list_1'、'list_2'、'list_3' ... 'list_10'
     * @var String
     */
    protected $cacheKeyPrefix = null;

    /**
     * 缓存节点
     * @var string
     */
    protected $cacheNodeName = 'servers';

    /**
     * 缓存池ID
     * @var string
     */
    protected $cachePersistentID = '';

    /**
     * 实例缓存
     * @return Q_Cache_Memcached
     */
    public function dbCache()
    {
        if ($this->cacheObj) {
            return $this->cacheObj;
        }
        $sqlCacheClass = $this->dbname ? $this->dbname : Q_Core_Consts::DB_SQL_CACHE_CLASS;
        $options = array(
            'persistent_id' => $this->cachePersistentID
        );

//        $this->cacheObj = Q_Cache::factory('Redis', $options, $this->cacheNodeName)->setPrefix(Q_Core_Consts::DB_SQL_CACHE_DOMAIN . '_' . $sqlCacheClass);
        $this->cacheObj = Q_Cache::factory('Memcached', $options, $this->cacheNodeName)->setPrefix(Q_Core_Consts::DB_SQL_CACHE_DOMAIN . '_' . $sqlCacheClass);
        return $this->cacheObj;
    }

    /**
     * 获取Db Server 服务器 Map
     *
     */
    public function getServerInfo()
    {
        return $this->serverConfig;
    }

    /**
     * 获取key
     * @return null
     */
    public function getKey()
    {
        return $this->cacheKey;
    }

    /**
     * 缓存
     * @param bool $status
     * @param String $key
     * @param integer $expire
     * @return  Q_Db_Adapter_Pdo_SqlMap
     */
    public function cache($status = true)
    {
        if (is_bool($status) === false) {
            throw new Q_Db_Exception(' Db Cache status is error ');
        }
        $this->cacheStatus = $status;
        return $this;
    }

    /**
     * 设置缓存时间
     *
     * @param integer $expire 秒
     * @return  Q_Db_Adapter_Pdo_SqlMap
     */
    public function expire($expire)
    {
        $expire = intval($expire);
        if ($expire > 0) {
            $this->cacheExpire = $expire;
        }
        return $this;
    }

    /**
     * 设置cache key
     *
     * @param String $key
     * @return  Q_Db_Adapter_Pdo_SqlMap
     */
    public function key($key = null, $prefix = null)
    {
        if (!empty($key)) {
            $this->cacheKey = $key;
        }
        $this->cacheKeyPrefix = trim($prefix);
        return $this;
    }

    /**
     * 组合key
     * @param string $sqlId
     * @param array $parameterMap
     * @param array $replaceMap
     * @param $act
     */
    protected function _makeKey($sqlId, $parameterMap, $replaceMap)
    {
        $this->cacheKey = md5($this->getSql($sqlId, $parameterMap, $replaceMap));
    }


    /**
     * @param $sqlId
     * @param $parameterMap
     * @param $replaceMap
     * @param $act
     * @return array|Mixed
     */
    protected function _prepare($sqlId, $parameterMap, $replaceMap, $act)
    {
        if ($this->cacheStatus === true) {
            $this->dbCache();
            if (empty($this->cacheKey)) {
                $this->_makeKey($sqlId, $parameterMap, $replaceMap);
            }
            /** 判定是否获取Tag **/
            if (!empty($this->cacheTag)) {
                $cacheVal = $this->cacheObj->tag($this->cacheTag)->get($this->cacheKey);
            } else {
                $cacheVal = $this->cacheObj->get($this->cacheKey);
            }
            if (!empty($cacheVal)) {
                $this->_resetParameter();
                return $cacheVal;
            }
        }
        //$this->checkElement($sqlId); //新Sqlmap 2016.03.23
        //$this->elementParser->setReplaceMap($replaceMap);
        $sql = $this->getSql($sqlId, $parameterMap, $replaceMap);//$this->elementParser->sql($this->nameSpace . '.' . $sqlId);//新Sqlmap 不需要获取sql
        // db start
        //默认读操作了 = true;
        $statement = $this->_connect()->prepare($sql);
        $_statement = new Q_Db_Adapter_Pdo_Statement($statement);
        $_statement->bindParams($parameterMap);
        $statement->execute();
        switch ($act) {
            case 'fetchRow':
                $result = $_statement->fetchRow();
                break;
            case 'fetchOne':
                $result = $_statement->fetchOne($this->fetchOptions['column_number']);
                break;
            case 'fetchAll':
                $result = $_statement->fetchAll($this->fetchOptions['fetch_style'], $this->fetchOptions['fetch_argument'], $this->fetchOptions['ctor_args']);
                break;
            case 'fetchCol':
                $result = $_statement->fetchCol();
                break;
            case 'fetchPairs':
                $result = $_statement->fetchPairs();
                break;
            case 'fetchAllObject':
                $result = $_statement->fetchAllObject();
                break;
            case 'fetchObject':
                $result = $_statement->fetchObject($this->fetchOptions['class_name'], $this->fetchOptions['ctor_args']);
                break;
            case 'fetchAssoc':
                $result = $_statement->fetchAssoc();
                break;
        }
        if ($this->cacheStatus === true) {
            if (!empty($this->cacheTag)) {
            	//print_R($this->cacheObj->tag($this->cacheTag)->set('a', '2'));die;
                $this->cacheObj->tag($this->cacheTag)->set($this->cacheKey, $result, $this->cacheExpire);
            } else {
                $this->cacheObj->set($this->cacheKey, $result, $this->cacheExpire);
            }
        }
        $this->_resetParameter();
        $statement->closeCursor();
        return $result;
    }

    /**
     * @param $sqlId
     * @param array $parameterMap
     * @param null $replaceMap
     * @return Q_Db_Adapter_Pdo_Finale
     */
    protected function _execute($sqlId, $parameterMap = array(), $replaceMap = null)
    {
        //$this->checkElement($sqlId); //新mapsql 不需要检测sqlmap文件
    	$anran = new Q_Db_Adapter_Pdo_ElementParser();
        $anran->setReplaceMap($replaceMap);
        $sql = $anran->sql($sqlId); //$this->elementParser->sql($this->nameSpace . '.' . $sqlId);
        // db start
        ////写操作；
        $pdo = $this->_connect(false);
        $statement = $pdo->prepare($sql);
        $_statement = new Q_Db_Adapter_Pdo_Statement($statement);
        $_statement->bindParams($parameterMap);
        $statement->execute();
        $this->delCache();
        return new Q_Db_Adapter_Pdo_Finale($pdo, $statement);
    }

    protected function _dsn($readtag = true)
    {
        $options = array(
            'mode' => $readtag === true ? Q_Server_Core::SERVER_MODE_READ : Q_Server_Core::SERVER_MODE_WRITE,
            'select' => Q_Server_Core::SERVER_SELECT_MODE_RAND
        );
        $servers = Q_Server::factory('db', 'mysql', strtolower($this->dbname), $options);
        if (empty($servers)) {
            throw new Q_Db_Exception('server config is null ,find: ' . $this->dbname . ' No such config file ');
        }
        $driver_options = array(
            'dbname' => isset($servers['dbname']) ? $servers['dbname'] : $this->dbname,
            'driver_options' => $this->driverConfig
        );
        $this->driverConfig = array_merge($servers, $driver_options);
        // use all remaining parts in the DSN
        $dsn = array(
            'host' => 'host=' . $this->driverConfig['host'],
            'dbname' => 'dbname=' . $this->driverConfig['dbname'],
            'port' => 'port=' . $this->driverConfig['port'],
            'charset' => 'charset=' . $this->charset
        );
        return $this->_pdoType . ':' . implode(';', $dsn);
    }

    /**
     * 创建PDO数据库对象
     * @throws Q_Db_Exception
     * @return PDO
     */
    protected function _connect($actMode = true)
    {
        $rwtag = $actMode === true ? 'r' : 'w';
        $_connectionKey = $this->dbname . '_' . $rwtag;
        if (isset($this->_connection[$_connectionKey])) {
            return $this->_connection[$_connectionKey];
        }
        $dsn = $this->_dsn($actMode);
        // 检查pdo模块
        if (!extension_loaded('pdo')) {
            throw new Q_Db_Exception('The PDO extension is required for this adapter but the extension is not loaded');
        }
        // 检查PDO中的驱动
        if (!in_array($this->_pdoType, PDO::getAvailableDrivers())) {
            throw new Q_Db_Exception('The ' . $this->_pdoType . ' driver is not currently installed');
        }
        // 添加一个请求一个持久连接，而非创建一个新连接
        if (isset($this->driverConfig['persistent']) && ($this->driverConfig['persistent'] == true)) {
            $this->driverConfig['driver_options'][PDO::ATTR_PERSISTENT] = true;
        }
        try {
            $_connection = new PDO(
                $dsn,
                $this->driverConfig['username'],
                $this->driverConfig['password'],
                $this->driverConfig['driver_options']
            );
            // set the PDO connection to perform case-folding on array keys, or not
            $_connection->setAttribute(PDO::ATTR_CASE, $this->_caseFolding);
            // always use exceptions.
            $_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 判断环境设置是否开启预处理
            if (version_compare(phpversion(), '5.3.6', '<') == true) {
                $_connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            }
        } catch (PDOException $e) {
            throw new Q_Db_Exception($e->getMessage(), $e->getCode(), $e);
        }
        $this->_connection[$_connectionKey] = $_connection;
        return $_connection;
    }

    /**
     * Force the connection to close.
     *
     * @return void
     */
    public function closeConnection()
    {
        $this->_connection = array();
    }

    /**
     * 检查节点是否存在
     *
     * @param String $sqlId
     * @return bool
     */
    protected function checkElement($sqlId)
    {
        $element = $this->elementParser->element($this->nameSpace . '.' . $sqlId);
        if ($element == NULL) {
            throw new Q_Db_Exception($sqlId . ' element not find');
        }
        return true;
    }

    /**
     * 获取当前执行sql
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return String
     */
    public function getSql($sqlId, $parameterMap = array(), $replaceMap = array())
    {
//        $this->checkElement($sqlId); //使用新sqlmap  不需要
        //$this->elementParser->setReplaceMap($replaceMap);
    	$anran = new Q_Db_Adapter_Pdo_ElementParser();
    	$anran->setReplaceMap($replaceMap);
        $sql = $anran->sql($sqlId); //$this->elementParser->sql($this->nameSpace . '.' . $sqlId);  //使用新sqlmap  不需要
        if (strstr($sql, ':')) {
            $matches_s = array();
            foreach ($parameterMap as $key => $val) {
                if (is_string($val)) {
                    $val = "'{$val}'";
                }
                $matches_s[':' . $key] = $val;
            }
            $asSql = strtr($sql, $matches_s);
        } else {
            $asSql = $sql;
            foreach ($parameterMap as $val) {
                $strPos = strpos($asSql, '?');
                if($strPos === false){
                	break;
                }
                if (is_string($val)) {
                    $val = "'{$val}'";
                }
                $asSql = substr_replace($asSql, $val, $strPos, 1);
            }
        }
        return $asSql;
    }

    /**
     * 删除Cache 只限于自己设定的key
     *
     **/
    public function delCache()
    {
        #删除Tag 里面 的 key
        if (!empty($this->cacheKey) && !empty($this->cacheTag)) {
            $this->delCaches('tag');
        } elseif (!empty($this->cacheTag)) {
            $this->dbCache();
            $this->cacheObj->deleteTag($this->cacheTag);
        } elseif (!empty($this->cacheKey)) {
            $this->delCaches();
        }
        #重置一些默认值
        $this->_resetParameter();
    }


    /**
     * 删除多个Cache key 和 范围Cache key
     *
     * 例子:1,10 或 a,z
     *
     * @param String $act
     */
    protected function delCaches($act = '')
    {
        $this->dbCache();
        #sql_map_db_cache_key 类型是 数字或字母  Case: 1,10 or a-z
        if (is_string($this->cacheKey) && substr_count($this->cacheKey, ',') == 1) {
            $keyArray = explode(',', $this->cacheKey);
            #验证key是否合法
            $keyRange = range($keyArray[0], $keyArray[1]);
            foreach ($keyRange as $val) {
                $rangeKey = '';
                if (empty($this->cacheKeyPrefix)) {
                    $rangeKey = $val;
                } else {
                    $rangeKey = $this->cacheKeyPrefix . $val;
                }
                if ($act == 'tag') {
                    $this->cacheObj->tag($this->cacheTag)->delete($rangeKey);
                } else {
                    $this->cacheObj->delete($rangeKey);
                }
            }

            #如果是数组的情况下
        } elseif (is_array($this->cacheKey)) {
            foreach ($this->cacheKey as $val) {
                if ($act == 'tag') {
                    $this->cacheObj->tag($this->cacheTag)->delete($val);
                } else {
                    $this->cacheObj->delete($val);
                }
            }
            #是字符的情况下
        } elseif (is_string($this->cacheKey)) {
            if ($act == 'tag') {
                $this->cacheObj->tag($this->cacheTag)->delete($this->cacheKey);
            } else {
                $this->cacheObj->delete($this->cacheKey);
            }
        }
    }

    /**
     * 恢复默认设置
     */
    protected function _resetParameter()
    {
        unset($this->cacheKey);
        unset($this->cacheTag);
        unset($this->cacheKeyPrefix);
        unset($this->actMode);
        $this->cacheStatus = true;
        $this->fetchOptions = array(
            'column_number' => 0,
            'fetch_style' => null,
            'fetch_argument' => null,
            'ctor_args' => array()
        );
    }

    /**
     * 设置缓存池id
     * @param $persistentID
     * @return $this
     */
    public function setCachePersistentID($persistentID)
    {
        $this->cachePersistentID = $persistentID;
        return $this;
    }

    /**
     * 设置编码
     * @param $charset
     * @return $this
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * 实例缓存
     * @return Q_Cache_Memcached
     */
    public function cacheObj()
    {
        return $this->dbCache();
    }
}