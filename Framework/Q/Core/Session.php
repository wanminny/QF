<?php

/**
 * Created by PhpStorm.
 * User: parasol.zhang
 * Date: 16-1-19
 * Time: 15:23
 */
class Q_Core_Session implements Q_Core_SessionHandlerInterface
{
    const SESSION_PREFIX = 'qin.session';

    private $maxLifeTime = 1800;

    public function __construct($maxLifeTime = 1800, $cookieDomain = NULL)
    {
        if (phpversion() >= '5.4') {
            register_shutdown_function('session_write_close');
        }
        $this->maxLifeTime = (int)$maxLifeTime;
        // 设置生存周期
        ini_set('session.gc_maxlifetime', $this->maxLifeTime);
        // 设置域
        $_cookieDomain = $this->currentDomain();
        if ($cookieDomain != null || !empty($cookieDomain)) {
            $_cookieDomain = $cookieDomain;
        }
        ini_set('session.cookie_domain', $_cookieDomain);
        ini_set('session.use_cookies', 1);
        ini_set('session.cookie_path', '/');
    }

    public function _cache()
    {
        return Q_Cache::factory('Memcached')->setPrefix(self::SESSION_PREFIX);
    }

    /**
     * 获取Domain
     * @return string
     */
    public function currentDomain()
    {
        if (!isset($_SERVER['HTTP_HOST'])) {
            return '';
        }
        if (ip2long($_SERVER['HTTP_HOST']) == false) {
            return '.' . preg_replace("/^(.*\.)?([^.]*\..*)$/", "$2", $_SERVER['HTTP_HOST']);
        }
        return $_SERVER['HTTP_HOST'];
    }

    public function close()
    {
        return true;
    }

    public function destroy($sessionID)
    {
        return $this->_cache()->delete($sessionID);
    }

    public function gc($maxlifetime)
    {
        return true;
    }

    public function open($savePath, $sessionID)
    {
        return true;
    }

    public function read($sessionID)
    {
        $return = '';
        $sessionData = $this->_cache()->get($sessionID);
        if ($sessionData) {
            if ((int)($sessionData['modified'] + $this->maxLifeTime) > time()) {
                $return = $sessionData['data'];
            } else {
                $this->destroy($sessionID);
            }
        }
        return $return;
    }

    public function write($sessionID, $sessionData)
    {
        $sessionDate = array('modified' => time(), 'data' => (string)$sessionData, 'lifetime' => $this->maxLifeTime);
        return $this->_cache()->set($sessionID, $sessionDate, $this->maxLifeTime);
    }

    public function __destruct()
    {
        session_write_close();
    }

    /**
     * 开启Session并设置NameSpace
     */
    static function start($namespace)
    {
        $sessionID = session_id();
        if (empty($sessionID)) {
            $qinSession = new Q_Core_Session();
            session_set_save_handler(
                array(&$qinSession, "open"),
                array(&$qinSession, "close"),
                array(&$qinSession, "read"),
                array(&$qinSession, "write"),
                array(&$qinSession, "destroy"),
                array(&$qinSession, "gc")
            );
            session_start();
        }
        return new Q_Core_SessionNamespace ($namespace);
    }

    /**
     * 开启session
     */
    static function sessionStart()
    {
        $sessionID = session_id();
        if (empty($sessionID)) {
            $qinSession = new Q_Core_Session();
            session_set_save_handler(
                array(&$qinSession, "open"),
                array(&$qinSession, "close"),
                array(&$qinSession, "read"),
                array(&$qinSession, "write"),
                array(&$qinSession, "destroy"),
                array(&$qinSession, "gc")
            );
            session_start();
        }
    }
} 