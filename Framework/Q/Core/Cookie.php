<?php

class Q_Core_Cookie
{
    /**
     * 取得所有的cookie值
     *
     * @return array
     * @since 0.2.2
     */
    public function all()
    {
        return $_COOKIE;
    }

    /**
     * 获取 $_COOKIE
     * @param String $name
     * @return String
     */
    public static function getCookie($name, $default = null)
    {
        return isset ($_COOKIE [$name]) ? $_COOKIE [$name] : $default;
    }

    /**
     * 设置cookie
     *
     * @param String $name
     * @param String $domain
     * @param Mixed $value
     * @param Integer $expire (0:Session、-1:删除、time():过期时间 )
     * @param String $path
     * @param bool $httponly
     */
    public static function setCookie($name, $domain, $value = '', $expire = 0, $path = "/", $httponly = false, $secureAuto = false)
    {
        if ($secureAuto == false) {
            $secure = $_SERVER ['SERVER_PORT'] == 443 ? true : false;
        } else {
            $secure = true;
        }
        if ($expire == 0) {
            $expire = 0;
        } else if ($expire == -1) {
            $expire = time() - 3600;
        }
        setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
}