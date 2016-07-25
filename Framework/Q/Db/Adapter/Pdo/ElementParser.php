<?php


class Q_Db_Adapter_Pdo_ElementParser
{

    /**
     * 设定Map元素
     *
     * @var Array
     */
    private $mapElementsConfig = array(
        'Insert',
        'Update',
        'Select',
        'Delete',
        'Command'
    );

    /**
     * 创建一个临时文件夹
     *
     * @var unknown_type
     */
    private $tmp = 'tmp';

    /**
     * map 文件
     *
     * @var String
     */
    private $_sqlMapXmlFile;

    private $_resourceFiles;

    /**
     * 替换列表
     *
     * @var Array
     */
    private $replaceMap = array();

    /**
     * 元素Map
     *
     * @var Array
     */
    private $elementMap = array();

    /**
     * Cache
     *
     * @var Q_Db_Cache
     */
    private $cache;

    private $sqlStr;

    /**
     * 替换参数验证
     *
     * @var String
     */
    private $replaceFilters = '/(?=select\s|insert\s|delete\s|update\s)/i';


    /**
     * 获取元素
     *
     */
    public function element($sqlId)
    {
        return $this->elementMap[$sqlId];
    }


    /**
     * 获取所有对象
     *
     * @return Array
     */
    public function elementMap()
    {
        return $this->elementMap;
    }

    /**
     * 设置替换列表
     *
     * @param array $replaceMap 替换列表（关联数组）
     */
    public function setReplaceMap(array $replaceMap)
    {
        if (is_array($replaceMap)) {
            $newMap = array();
            foreach ($replaceMap as $key => $value) {
                if (is_array($value)) {
                    throw new Q_Db_Exception(' replaceMap value Should not be array ');
                }
                if (preg_match($this->replaceFilters, $value) == 1) {
                    throw new Q_Db_Exception(' replaceMap should not contain select、insert、delete、update ');
                }
                $newMap["#" . $key . "#"] = $value;
            }
            //return $newMap;
            $this->replaceMap = $newMap;
        }
    }

    /**
     * 获取Sql
     *
     * @return String
     */
    public function sql($sqlId)
    {
        /*if (stristr($sqlId, '.') === FALSE || empty($sqlId)) {
            throw new Q_Db_Exception(' sqlId Incorrect format ');
        }*/
        if (!is_array($this->replaceMap)) {
            throw new Q_Db_Exception(' replaceMap Incorrect format ');
        }
        $this->sqlStr = strtr($sqlId, $this->replaceMap);//strtr($this->element($sqlId), $this->replaceMap);
        return $this->sqlStr;
    }

    /**
     * 获取map标签元素配置
     *
     * @return Array
     */
    public function getMapElementsConfig()
    {
        return $this->mapElementsConfig;
    }
}