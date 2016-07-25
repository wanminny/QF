<?php

/**
 * User: parasol.zhang
 * Date: 15-9-18
 * Time: 4:44
 */
class Q_Db_Adapter_Pdo_FastSqlMap extends Q_Db_Adapter_Pdo_SqlMap
{
    public function __construct($routing, array $options = array())
    {
        if (stristr($routing, '.') === FALSE) {
            throw new Q_Db_Exception('Routing Format Error.( module.dbName.mapFile )');
        }
        $routingtMass = explode('.', $routing);
        if (count($routingtMass) == 2) {
            list($module, $dbName) = $routingtMass;
        } elseif (count($routingtMass) == 3) {
            list($module, $dbName, $mapFile) = $routingtMass;
        } else {
            throw new Q_Db_Exception('Routing Format Error,2 Or 3 Parameters.');
        }
        $this->dbname = $dbName;
        $this->elementParser = new Q_Db_Adapter_Pdo_CodeParser();
    }

    /**
     * @param $sqlId
     * @param array $parameterMap
     * @param null $replaceMap
     * @return Q_Db_Adapter_Pdo_Finale
     */
    protected function _execute($sql, $parameterMap = array(), $replaceMap = null)
    {
        $sql = $this->elementParser->setReplaceMap($replaceMap)->sql($sql);
        #DB Start (False)
        $pdo = $this->_connect(false);
        $statement = $pdo->prepare($sql);
        $_statement = new Q_Db_Adapter_Pdo_Statement($statement);
        $_statement->bindParams($parameterMap);
        $statement->execute();
        $this->delCache();
        return new Q_Db_Adapter_Pdo_Finale($pdo, $statement);
    }

    /**
     * 获取当前执行sql
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return String
     */
    public function getSql($sql, $parameterMap = array(), $replaceMap = array())
    {
        $sql = $this->elementParser->setReplaceMap($replaceMap)->sql($sql);
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
                if (is_string($val)) {
                    $val = "'{$val}'";
                }
                $asSql = substr_replace($asSql, $val, $strPos, 1);
            }
        }
        return $asSql;
    }

    /**
     * @param $sqlId
     * @param $parameterMap
     * @param $replaceMap
     * @param $act
     * @return array|Mixed
     */
    protected function _prepare($sql, $parameterMap, $replaceMap, $act)
    {
        if ($this->cacheStatus === true) {
            $this->dbCache();
            if (empty($this->cacheKey)) {
                $this->_makeKey($sql, $parameterMap, $replaceMap);
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
        $sql = $this->elementParser->setReplaceMap($replaceMap)->sql($sql);
        // db start
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
                $this->cacheObj->tag($this->cacheTag)->set($this->cacheKey, $result, $this->cacheExpire);
            } else {
                $this->cacheObj->set($this->cacheKey, $result, $this->cacheExpire);
            }
        }
        $this->_resetParameter();
        return $result;
    }
} 