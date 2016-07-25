<?php

/**
 * set sql return method.
 * User: zhanglirong
 * Date: 16-03-28
 * Time: 12:10
 */
class Q_Db_Adapter_Pdo_Finale
{

    /**
     * @var PDO
     */
    private $_pdo;

    /**
     * @var PDOStatement
     */
    private $_statement;

    public function __construct(&$_pdo, &$_statement)
    {
        $this->_pdo = $_pdo;
        $this->_statement = $_statement;
    }

    /**
     * 返回插入ID
     * @return integer
     */
    public function lastInsertId()
    {
        return $this->_pdo->lastInsertId();
    }

    /**
     * 返回执行sql的状态
     * @return bool
     */
    public function status()
    {
        return true;
    }

    /**
     * 返回错误码
     * @return String
     */
    public function errorCode()
    {
        return $this->_pdo->errorCode();
    }

    /**
     * 返回错误信息
     * @return Array
     */
    public function errorInfo()
    {
        return $this->_pdo->errorInfo();
    }

    /**
     * 返回插入影响的行数
     * @return integer
     */
    public function rowCount()
    {
        return $this->_statement->rowCount();
    }

    /**
     * @return bool
     */
    public function nextRowset()
    {
        return $this->_statement->nextRowset();
    }
}