<?php

/**
 * User: parasol.zhang
 * Date: 15-12-19
 * Time: 15:57
 */
class Q_Db_Adapter_Pdo_Statement
{
    /**
     * pdo数据类型
     * @var Array
     */
    protected $paramType = array(
        'integer' => PDO::PARAM_INT,
        'int' => PDO::PARAM_INT,
        'boolean' => PDO::PARAM_BOOL,
        'bool' => PDO::PARAM_BOOL,
        'string' => PDO::PARAM_STR,
        'null' => PDO::PARAM_NULL,
        'object' => PDO::PARAM_LOB,
        'float' => PDO::PARAM_STR,
        'double' => PDO::PARAM_STR
    );

    /**
     * @var PDOStatement
     */
    private $statement;

    public function __construct(&$statement)
    {
        $this->statement = $statement;
    }


    /**
     * 取出所有数据并放入数组中，其中每条数据使用对象表示
     *
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return Array
     */
    public function fetchAllObject()
    {
        return $this->statement->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * 返回第一行  数据使用对象表示
     *
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return Array
     */
    public function fetchObject($class_name = "stdClass", array $ctor_args = null)
    {
        return $this->statement->fetchObject($class_name, $ctor_args);
    }


    public function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        return $this->statement->fetch($fetch_style, $cursor_orientation, $cursor_offset);
    }

    /**
     * 只取回结果集的第一行
     *
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return Array
     */
    public function fetchRow()
    {
        return $this->statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 取回一个相关数组,第一个字段值为码
     *
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return Array
     */
    public function fetchPairs()
    {
        $pairsData = array();
        $result = $this->statement->fetchAll();
        foreach ($result as $v) {
            if (count($v) < 2) {
                throw new Q_Db_Exception('SQLSTATE[HY000]: General error: PDO::FETCH_KEY_PAIR fetch mode requires the result set to contain extactly 2 columns');
            }
            $indexKey = each($v);
            $indexVal = each($v);
            $pairsData[$indexKey['value']] = $indexVal['value'];
        }
        return $pairsData;
        return $this->statement->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * 只取回第一个字段值
     *
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return Array
     */
    public function fetchOne($columnNumber = 0)
    {
        return $this->statement->fetchColumn($columnNumber);
    }

    /**
     * 取回所有结果行的第一个字段名
     *
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return Array
     */
    public function fetchCol()
    {
        return $this->statement->fetchAll(PDO::FETCH_COLUMN, 0);
    }


    /**
     * 取回结果集中所有字段的值,作为关联数组返回  第一个字段作为码
     *
     * @param string $sqlId sql定义ID
     * @param array $parameterMap 参数集
     * @param array $replaceMap 替换列表
     * @return Array
     */
    public function fetchAssoc()
    {
        $result = $this->statement->fetchAll(PDO::FETCH_ASSOC);
        $data = array();
        foreach ($result as $val) {
            $tmp = array_values($val);
            $data[$tmp[0]] = $val;
        }
        return $data;
    }

    /**
     * 取回结果集中所有字段的值,作为连续数组返回
     * @param int $fetch_style
     * @param mixed $fetch_argument
     * @param array $ctor_args
     * @return array
     */
    public function fetchAll($fetch_style = null, $fetch_argument = null, array $ctor_args = array())
    {
        return $this->statement->fetchAll($fetch_style);
    }

    /**
     * 绑定参数
     * @param $stmt
     * @param array $params
     * @throws Q_Db_Exception
     */
    public function bindParams(array $params)
    {
        foreach ($params as $param => $value) {
            if (is_array($value) || is_object($value)) {
                throw new Q_Db_Exception ('bindParams: Parameter values are array & objects should not');
            }
            if (is_int($param)) {
                ++$param;
            }
            $this->_bindParam($param, $value, $this->paramType [strtolower(gettype($value))]);
        }
    }

    public function _bindParam($parameter, $variable, $type = null, $length = null, $options = null)
    {
        try {
            if (($type === null) && ($length === null) && ($options === null)) {
                return $this->statement->bindParam($parameter, $variable);
            } else {
                return $this->statement->bindParam($parameter, $variable, $type, $length, $options);
            }
        } catch (PDOException $e) {
            throw new Q_Db_Exception(' ' . $parameter . ' : 绑定错误 ( ' . $e->getMessage() . ' ) ');
        }
    }
} 