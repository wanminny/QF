<?php

/**
 * User: parasol.zhang
 * Date: 15-12-18
 * Time: 10:06
 */
class Q_Core_SessionNamespace
{

    protected $_namespace = "Default";

    protected static $_namespaceLocks = array();

    protected static $_writable = false;

    public function __construct($namespace = 'Default', $singleInstance = false)
    {
        if ($namespace === '') {
            throw new Q_Exception('Session Namespace 不能为空.');
        }
        if ($namespace[0] == "_") {
            throw new Q_Exception('Session Namespace 不能已_开头.');
        }
        $this->_namespace = $namespace;
    }

    /**
     * 锁定Session NameSpace
     */
    public function lock()
    {
        self::$_namespaceLocks[$this->_namespace] = true;
    }

    /**
     * 解锁Session NameSpace
     */
    public function unlock()
    {
        unset(self::$_namespaceLocks[$this->_namespace]);
    }

    /**
     * 解锁全部Session NameSpace
     */
    public static function unlockAll()
    {
        self::$_namespaceLocks = array();
    }

    /**
     * 检查Session NameSpace是否有锁
     * @return bool
     */
    public function isLocked()
    {
        return isset(self::$_namespaceLocks[$this->_namespace]);
    }

    /**
     * 释放所有session
     */
    public function unsetAll()
    {
        return $this->_namespaceUnset($this->_namespace);
    }

    /**
     * 释放所有session
     * @param $namespace
     * @param null $name
     */
    private function _namespaceUnset($namespace, $name = null)
    {
        $name = (string)$name;
        if ($name === '') {
            unset($_SESSION[$namespace]);
        } else {
            unset($_SESSION[$namespace][$name]);
        }
    }

    /**
     * 存入一个Session
     * @param $name
     * @param $value
     * @throws Q_Exception
     */
    public function __set($name, $value)
    {
        if (isset(self::$_namespaceLocks[$this->_namespace])) {
            throw new Q_Exception('This session/namespace has been marked as read-only.');
        }
        if ($name === '') {
            throw new Q_Exception("The '$name' key must be a non-empty string");
        }
        $name = (string)$name;
        $_SESSION[$this->_namespace][$name] = $value;
    }

    /**
     * 获取一个Session
     * @param $name
     * @return mixed
     * @throws Q_Exception
     */
    public function __get($name)
    {
        if (isset(self::$_namespaceLocks[$this->_namespace])) {
            throw new Q_Exception('This session/namespace has been marked as read-only.');
        }
        if ($name === '') {
            throw new Q_Exception("The '$name' key must be a non-empty string");
        }
        return $this->_namespaceGet($this->_namespace, $name);
    }

    /**
     * 注销session
     * @param $name
     * @throws Q_Exception
     */
    public function __unset($name)
    {
        if ($name === '') {
            throw new Q_Exception("The '$name' key must be a non-empty string");
        }

        return $this->_namespaceUnset($this->_namespace, $name);
    }

    /**
     * @param $namespace
     * @param null $name
     * @return mixed
     */
    private function _namespaceGet($namespace, $name = null)
    {
        if ($name === null) {
            return empty($_SESSION[$namespace]) ? NULL : $_SESSION[$namespace];
        }
        return empty($_SESSION[$namespace][$name]) ? NULL : $_SESSION[$namespace][$name];
    }

    /**
     * 判断Session是否存在
     * @param $name
     * @return bool
     * @throws Q_Exception
     */
    public function __isset($name)
    {
        if ($name === '') {
            throw new Q_Exception("The '$name' key must be a non-empty string");
        }
        return $this->_namespaceIsset($this->_namespace, $name);
    }

    /**
     * @param $namespace
     * @param null $name
     * @return bool
     * @throws Q_Exception
     */
    private function _namespaceIsset($namespace, $name = null)
    {
        if ($name === null) {
            return isset($_SESSION[$namespace]);
        }
        return isset($_SESSION[$namespace][$name]);
    }
} 