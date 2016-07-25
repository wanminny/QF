<?php

class Q_Db
{

    /**
     *
     * Enter description here ...
     * @var Q_Db_Mysql_SqlMap_MapQuery
     */
    private static $mysql;

    /**
     * mysql对比串
     * @var String
     */
    private static $mysqlDiffKey;


	/**
	 * 
	 * @var Q_Db_Adapter_Pdo_FastSqlMap
	 */    
    private static $fastPdo;

    /**
     * @param $router
     * @param null $configPath
     * @param bool $isCacheObj
     * @return Q_Db_Adapter_Pdo_Mysql
     */
    public static function mysql($router, $configPath = null, $isCacheObj = true)
    {
        return self::pdo($router, $configPath, $isCacheObj);
    }

    /**
     * @param $router
     * @param null $configPath
     * @param bool $isCacheObj
     * @return Q_Db_Adapter_Pdo_SqlMap
     * @throws Q_Exception
     */
    public static function pdo($router, $configPath = null, $isCacheObj = true)
    {
        if (empty($router)) {
            throw new Q_Exception('Q_Db Mysql, Not ' . $router);
        }
        $diffKey = md5(serialize($configPath) . '_' . $router);
//        var_dump($diffKey,self::$mysqlDiffKey);
        if ($diffKey == self::$mysqlDiffKey && $isCacheObj) {
//            echo 11;
            return self::$mysql;
        }

        self::$mysqlDiffKey = $diffKey;
//        echo 22;var_dump(self::$mysqlDiffKey);
        $options = array(
            'configPath' => $configPath
        );
        return self::$mysql = new Q_Db_Adapter_Pdo_SqlMap($router, $options);
    }

    /**
     * @param $router
     * @param array $options
     * @param bool $isCacheObj
     * @return Q_Db_Adapter_Pdo_FastSqlMap
     * @throws Q_Exception
     */
    static function fastPdo($router, array $options = array(), $isCacheObj = true)
    {
        if (empty($router)) {
            throw new Q_Exception('Router 不能为空. ');
        }
        if (empty($options['configPath'])) {
            throw new Q_Exception('SQL Map 配置路径不能为空(SQL Map ConfigPath is null.)');
        }
        if (isset(self::$fastPdo[$router]) && $isCacheObj) {
            return self::$fastPdo[$router];
        }
        return self::$fastPdo[$router] = new Q_Db_Adapter_Pdo_FastSqlMap($router, $options);
    }
}