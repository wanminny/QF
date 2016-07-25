<?php

/**
 * 惠新宸 yac  
 * User: parasol.zhang
 * Date: 16-1-25
 * Time: 22:52
 */
class Q_Cache_Yac
{
    static private $_yac = Null;

    static function isEnabled()
    {
        return extension_loaded('yac');
    }

    /**
     * @return Yac
     */
    static function initYac()
    {
        if (!extension_loaded('yac')) {
            throw new Exception('没有Yac插件.');
        }
        if (empty(self::$_yac)) {
            self::$_yac = new Yac();
        }
        return self::$_yac;
    }

    static function set($key, $value)
    {
        return self::initYac()->set($key, $value);
    }

    static function get($key)
    {
        return self::initYac()->get($key);
    }

    static function delete($key)
    {
        return self::initYac()->delete($key);
    }

    static function add($key, $val)
    {
        return self::initYac()->add($key, $val);
    }

    static function dump($int)
    {
        return self::initYac()->dump($int);
    }

    static function flush()
    {
        return self::initYac()->flush();
    }
} 