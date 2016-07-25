<?php

/**
 * 框架提供的常用函数
 *
 */
class Q_Utils_Function
{

    /**
     * 获知php版本
     *
     * @param String $version
     * @return bool
     */
    static function is_php($version = '5.0.0')
    {
        static $_is_php;
        $version = ( string )$version;
        if (!isset ($_is_php [$version])) {
            $_is_php [$version] = (version_compare(PHP_VERSION, $version) < 0) ? FALSE : TRUE;
        }
        return $_is_php [$version];
    }

    /**
     * 压缩转换成十六进制
     *
     * @param String $string
     * @return String
     */
    static function compress($string)
    {
        return bin2hex(gzdeflate($string, 9));
    }

    /**
     * 转成二进制并解压
     *
     * @param String $string
     * @return String
     */
    static function uncompress($string)
    {
        return gzinflate(pack('H' . strlen($string), $string));
    }

    /**
     * 对URL进行转码(主要防范+)
     *
     * @param String $input
     * @return String
     */
    static function base64_url_encode($input)
    {
        return strtr(base64_encode($input), '+/=', '-_.');
    }

    /**
     * 对URL进行还原
     *
     * @param String $input
     * @return String
     */
    static function base64_url_decode($input)
    {
        return base64_decode(strtr($input, '-_.', '+/='));
    }

    /**
     * 对Str进行转码(主要防范+)
     *
     * @param String $input
     * @return String
     */
    static function base64_str_encode($input)
    {
        return strtr($input, '+/=', '-_.');
    }

    /**
     * 对Str进行还原
     *
     * @param String $input
     * @return String
     */
    static function base64_str_decode($input)
    {
        return strtr($input, '-_.', '+/=');
    }

    /**
     * 判断IP
     *
     * 使用字符比较
     *
     * @param String $str
     * @return bool
     */
    static function is_ip($str)
    {
        if (!strcmp(long2ip(sprintf("%u", ip2long($str))), $str))
            return true;
        else
            return false;
    }

    static function insert($array, $val, $pos = null)
    {
        if (null == $pos) {
            $array [] = $val;
            return $array;
        } else {
            $array2 = array_splice($array, $pos);
            $array [] = $val;
            $array = array_merge($array, $array2);
            return $array;
        }
    }

    /**
     * 安全的数组合并，保持字符串和数字索引，字符串请使用array_merger以提高效率
     *
     * @param array $array
     * @return array
     */
    static function safeMerge($array)
    {
        $num = func_num_args();
        for ($i = 1; $i < $num; $i++) {
            $ary = func_get_arg($i);
            foreach ($ary as $k => $v) {
                $array [$k] = $v;
            }
        }
        return $array;
    }

    /*
     * 组合数组
     */
    static function safeCombine(array $keys, array $values)
    {
        $keys = array_values($keys);
        $values = array_values($values);
        $return = array();
        $counter = count($keys);
        for ($i = 0; $i < $counter; $i++) {
            $return [$keys [$i]] = isset ($values [$i]) ? $values [$i] : false;
        }
        return $return;
    }

    static function headLastModified($url)
    {
        if (!Q_Cache_Backend::isEnabled()) {
            return time();
        }
        $key = 'resHLM.' . md5($url);
        if ($lastModified = Q_Cache_Backend::load($key)) {
            return $lastModified;
        }
        $ZHC = new Zend_Http_Client ($url);
        $lastModified = intval(strtotime($ZHC->request('HEAD')->getHeader('Last-Modified')));
        Q_Cache_Backend::save($key, $lastModified);
        return $lastModified;
    }

    /**
     * 结果数据
     *
     * @param Integer $code
     *            状态码
     * @param mixed $data
     *            数据
     * @param String $message
     *            数据信息Tag
     *
     */
    static function result($code, $message = null, $data = null)
    {
        $dataString = json_encode($data);
        return array(
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'md5' => md5($dataString)
        );
    }

    static function sortLastModified(array $data)
    {
        $lastModified = 0;
        foreach ($data as $url) {
            $_lastModified = Q_Utils_Function::headLastModified($url);
            if ($_lastModified > $lastModified) {
                $lastModified = $_lastModified;
            }
        }
        return $lastModified;
    }

    /**
     * 取GB2312字符串首字母,原理是GBK汉字是按拼音顺序编码的.
     *
     * @param String $input
     * @return String
     *
     */
    static function getLetter($input)
    {
        $dict = array(
            'a' => 0xB0C4,
            'b' => 0xB2C0,
            'c' => 0xB4ED,
            'd' => 0xB6E9,
            'e' => 0xB7A1,
            'f' => 0xB8C0,
            'g' => 0xB9FD,
            'h' => 0xBBF6,
            'j' => 0xBFA5,
            'k' => 0xC0AB,
            'l' => 0xC2E7,
            'm' => 0xC4C2,
            'n' => 0xC5B5,
            'o' => 0xC5BD,
            'p' => 0xC6D9,
            'q' => 0xC8BA,
            'r' => 0xC8F5,
            's' => 0xCBF9,
            't' => 0xCDD9,
            'w' => 0xCEF3,
            'x' => 0xD188,
            'y' => 0xD4D0,
            'z' => 0xD7F9
        );
        $str_1 = substr($input, 0, 1);
        if ($str_1 >= chr(0x81) && $str_1 <= chr(0xfe)) {
            $num = hexdec(bin2hex(substr($input, 0, 2)));
            foreach ($dict as $k => $v) {
                if ($v >= $num) {
                    break;
                }
            }
            return $k;
        } else {
            return $str_1;
        }
    }

    /**
     *
     *
     * Api json返回数据
     *
     * @param Integer $code
     * @param String $app_secret
     * @param String $message
     * @param mixed $data
     * @return String
     */
    static function apiJsonResult($code, $app_secret = null, $message = null, $data = null)
    {
        $dataString = json_encode($data);
        $result = array(
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'sign' => strtoupper(md5($dataString . $app_secret))
        );
        return $result;
    }

    /**
     * 过滤参数
     *
     * @param string $param
     *            参数
     * @param boolean $filter
     *            是否过滤
     * @return mixed
     */
    static function filterParam($param, $filter = true)
    {
        if (!is_array($param) && !is_object($param)) {
            if (ini_get("magic_quotes_gpc")) {
                $param = stripslashes($param);
            }
            return $filter ? htmlspecialchars(trim($param)) : $param;
        }
        foreach ($param as $key => $value) {
            $param [$key] = Q_Utils_Function::filterParam($value, $filter);
        }
        return $param;
    }

    /**
     * 浮点比较(相等)
     *
     * @param floats $f1
     * @param floats $f2
     * @param integer $precision
     * @return boolean
     */
    static function floatcmp($f1, $f2, $precision = 10) // are 2 floats equal
    {
        $e = pow(10, $precision);
        $i1 = intval($f1 * $e);
        $i2 = intval($f2 * $e);
        return ($i1 == $i2);
    }

    /**
     * 浮点比较(大于)
     *
     * @param floats $big
     * @param floats $small
     * @param integer $precision
     * @return boolean
     */
    static function floatgtr($big, $small, $precision = 10) // is one float bigger than another
    {
        $e = pow(10, $precision);
        $ibig = intval($big * $e);
        $ismall = intval($small * $e);
        return ($ibig > $ismall);
    }

    /**
     * 浮点比较(大于等于)
     *
     * @param floats $big
     * @param floats $small
     * @param integer $precision
     * @return boolean
     */
    static function floatgtre($big, $small, $precision = 10) // is on float bigger or equal to another
    {
        $e = pow(10, $precision);
        $ibig = intval($big * $e);
        $ismall = intval($small * $e);
        return ($ibig >= $ismall);
    }


    /**
     * 获取唯一ID串
     * @return string
     */
    static function uniqid()
    {
        return md5(self::getServerIp() . '_' . $_SERVER['HTTP_HOST'] . '_' . self::getClientIp() . '_' . uniqid());
    }

    /**
     * 获取客户端IP地址
     * @return string
     */
    static function getClientIp()
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $clientIp = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $clientIp = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR')) {
            $clientIp = getenv('REMOTE_ADDR');
        } else {
            $clientIp = $_SERVER['REMOTE_ADDR'];
        }
        return $clientIp;
    }

    /**
     * 获取服务器端IP地址
     * @return string
     */
    static function getServerIp()
    {
        if (isset($_SERVER)) {
            if ($_SERVER['SERVER_ADDR']) {
                $serverIp = $_SERVER['SERVER_ADDR'];
            } else {
                $serverIp = $_SERVER['LOCAL_ADDR'];
            }
        } else {
            $serverIp = getenv('SERVER_ADDR');
        }
        return $serverIp;
    }

    /**
     * 生成唯一ID(模仿Mongo ID)
     * @return string
     */
    static function generateIdHex()
    {
        static $i = 0;
        $i OR $i = mt_rand(1, 0x7FFFFF);
        return sprintf("%08x%06x%04x%06x",
            time() & 0xFFFFFFFF,
            crc32(substr((string)gethostname(), 0, 256)) >> 8 & 0xFFFFFF,
            getmypid() & 0xFFFF,
            $i = $i > 0xFFFFFE ? 1 : $i + 1
        );
    }
}