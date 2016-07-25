<?php

/**
 * Class Q_Db_Adapter_Pdo_CodeParser
 */
class Q_Db_Adapter_Pdo_CodeParser
{
    /**
     * 替换列表
     * @var array
     */
    private $replaceMap = array();

    /**
     * 替换参数验证
     * @var string
     */
    private $replaceFilters = '/(?=select\s|insert\s|delete\s|update\s)/i';

    /**
     * 设置替换列表
     * @param array $replaceMap 替换列表（关联数组）
     * @throws Q_Db_Exception
     */
    function setReplaceMap(array $replaceMap)
    {
        $newMap = array();
        foreach ($replaceMap as $key => $value) {
            if (is_array($value)) {
                throw new Q_Db_Exception('replaceMap value Should not be array ');
            }
            if (preg_match($this->replaceFilters, $value) == 1) {
                throw new Q_Db_Exception('replaceMap should not contain select、insert、delete、update ');
            }
            $newMap["#" . $key . "#"] = $value;
        }
        $this->replaceMap = $newMap;
        return $this;
    }

    /**
     * 获取Sql
     * @param $sql
     * @return string
     * @throws Q_Db_Exception
     */
    function sql($sql)
    {
        return strtr($sql, $this->replaceMap);
    }
}