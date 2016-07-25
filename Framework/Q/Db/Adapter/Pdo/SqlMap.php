<?php

/**
 * Sql分发.
 * User: zhanglirong
 * Date: 16-03-23
 * Time: 18:14
 */
class Q_Db_Adapter_Pdo_SqlMap extends Q_Db_Adapter_Pdo_Abstract
{

    /**
     * PDO 驱动类型
     * @var string
     */
    protected $_pdoType = 'mysql';


    public function __construct($routing, array $options = array())
    {
        if (empty($options['configPath'])) {
            throw new Q_Db_Exception('not find configPath in $options');
        }
        if (empty($routing) || stristr($routing, '.') === FALSE) {
            throw new Q_Db_Exception('SqlMap init, project it\'s parameter error (null or Incorrect format)');
        }

        $routingtMass = explode('.', $routing);
        $this->dbname = $routingtMass[1];
        // 判断是否使用多库同map
        $this->nameSpace = $sqlMapMid = strtolower((count($routingtMass) == 3) ? $routingtMass[2] : $routingtMass[0]);

        // 组合配置文件地址
        /*$path = array(
            $options['configPath'],
            ucfirst($routingtMass[0]),
            Q_Core_Consts::DB_SQLMAP_PREFIX . $sqlMapMid . '.xml'
        );*/
        /*$sqlMapFile = realpath(implode(DIRECTORY_SEPARATOR, $path));
        if ($sqlMapFile == false) {
            throw new Q_Db_Exception(" ConfigPath Not Find : " . $routing . " \n" . implode(DIRECTORY_SEPARATOR, $path));
        }*/
        // 分析器
        //$this->elementParser = new Q_Db_Adapter_Pdo_ElementParser($sqlMapFile);
    }

    /**
     * 设置 fetch 参数
     * @param array $fetchOptions
     * @return $this
     */
    public function fetchStyle(array $fetchOptions = array())
    {
        $this->fetchOptions = array_merge($this->fetchOptions, $fetchOptions);
        return $this;
    }

    /**
     * 插入信息
     * 默认返回pdoquery对象
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return Q_Db_Adapter_Pdo_Finale
     */
    public function insert($sqlId, $parameterMap = array(), $replaceMap = array())
    {
        return $this->_execute($sqlId, $parameterMap, $replaceMap);
    }

    /**
     * 更新数据
     * 默认返回pdoquery对象
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return Q_Db_Adapter_Pdo_Finale
     */
    public function update($sqlId, $parameterMap = array(), $replaceMap = array())
    {
        return $this->_execute($sqlId, $parameterMap, $replaceMap);
    }

    /**
     * 删除
     * 默认返回pdoquery对象
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return Q_Db_Adapter_Pdo_Finale
     */
    public function delete($sqlId, $parameterMap = array(), $replaceMap = array())
    {
        return $this->_execute($sqlId, $parameterMap, $replaceMap);
    }


    /**
     * 取回结果集中所有字段的值,作为关联数组返回  第一个字段作为码
     *
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return Array
     */
    public function fetchAssoc($sqlId, $parameterMap = array(), $replaceMap = array())
    {
        return $this->_prepare($sqlId, $parameterMap, $replaceMap, __FUNCTION__);
    }


    /**
     * 只取回结果集的第一行
     *
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return Array
     */
    public function fetchRow($sqlId, $parameterMap = array(), $replaceMap = array())
    {
        return $this->_prepare($sqlId, $parameterMap, $replaceMap, __FUNCTION__);
    }

    /**
     * 取回结果集中所有字段的值,作为连续数组返回
     *
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return Array
     */
    public function fetchAll($sqlId, $parameterMap = array(), $replaceMap = array())
    {
        return $this->_prepare($sqlId, $parameterMap, $replaceMap, __FUNCTION__);
    }

    /**
     * 返回第一行  数据使用对象表示
     *
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return Array
     */
    public function fetchObject($sqlId, $parameterMap = array(), $replaceMap = array())
    {
        return $this->_prepare($sqlId, $parameterMap, $replaceMap, __FUNCTION__);
    }

    /**
     * 取出所有数据并放入数组中，其中每条数据使用对象表示
     *
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return Array
     */
    public function fetchAllObject($sqlId, $parameterMap = array(), $replaceMap = array())
    {
        return $this->_prepare($sqlId, $parameterMap, $replaceMap, __FUNCTION__);
    }

    /**
     * 取回所有结果行的第一个字段名
     *
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return Array
     */
    public function fetchCol($sqlId, $parameterMap = array(), $replaceMap = array())
    {
        return $this->_prepare($sqlId, $parameterMap, $replaceMap, __FUNCTION__);
    }

    /**
     * 只取回第一个字段值
     *
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return Array
     */
    public function fetchOne($sqlId, $parameterMap = array(), $replaceMap = array())
    {
        return $this->_prepare($sqlId, $parameterMap, $replaceMap, __FUNCTION__);
    }

    /**
     * 取回一个相关数组,第一个字段值为码
     *
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return Array
     */
    public function fetchPairs($sqlId, $parameterMap = array(), $replaceMap = array())
    {
        return $this->_prepare($sqlId, $parameterMap, $replaceMap, __FUNCTION__);
    }


    /**
     * 设置Cache Tag
     *
     * @param String $tagName
     * @return Q_Db_Adapter_Pdo_SqlMap
     **/
    public function tag($tagName)
    {
        if (!empty($tagName)) {
            $this->cacheTag = $tagName;
        }
        return $this;
    }

    /**
     * 删除一个或多个tag
     * <p>
     *      ->delTags(array('list','info'))
     * </p>
     * @param mixed $tags
     * @return Q_Db_Adapter_Pdo_SqlMap
     */
    public function delTags($tags)
    {
        if (empty($tags)) {
            return $this;
        }
        $this->dbCache()->deleteTag($tags);
        return $this;
    }

    /**
     * 回滚
     * @return bool
     */
    public function rollBack()
    {
        return $this->_connect(false)->rollBack();
    }

    /**
     * 开启事务
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->_connect(false)->beginTransaction();
    }

    /**
     * 提交事务
     * @return bool
     */
    public function commit()
    {
        return $this->_connect(false)->commit();
    }

    /**
     * 关闭数据库连接
     */
    public function close()
    {

    }

    /**
     * 检查事务是否激活
     * @return bool
     */
    public function inTransaction()
    {
        return $this->_connect(false)->inTransaction();
    }

    public function __destruct()
    {
        $this->_resetParameter();
    }

    /**
     * 设置缓存节点
     * @param $cacheNodeName
     * @return $this
     */
    public function cacheNode($cacheNodeName)
    {
        $this->cacheNodeName = $cacheNodeName;
        return $this;
    }

    /**
     * 字符过滤
     * @param $value
     * @param int $parameter_type
     * @return string
     */
    public function quote($value, $parameter_type = PDO::PARAM_STR)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        return $this->_connect()->quote($value, $parameter_type);
    }
} 