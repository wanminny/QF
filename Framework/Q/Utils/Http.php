<?php

/**
 * Created by PhpStorm.
 * User: liuziyang
 * Date: 14-2-28
 * Time: 0:47
 */
class Q_Utils_Http
{
    private $method = 'get';

    private $content = '';

    private $httpCode = 500;

    private $parameter = array();

    private $curlInfo = array();

    protected $curlOpt = array(
        'SSL_VERIFYPEER' => false,
        'USERAGENT' => "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1",
        'TIMEOUT' => 60,
        'CONNECTTIMEOUT' => 60
    );

    public function __construct($url, array $opt = array())
    {
        $this->url = $url;
        $this->setCurlOpt($opt);
    }

    /**
     * 设置配置
     * @param array $opt
     * @return $this
     */
    public function setCurlOpt($opt = array())
    {
        foreach ($opt as $k => $v) {
            $this->curlOpt[strtoupper($k)] = $v;
        }
        return $this;
    }

    /**
     * POST设置参数
     * @param array $parameter
     * @return $this
     */
    public function setParameterPost(array $parameter)
    {
        $this->parameter = $parameter;
        return $this;
    }

    /**
     * 请求
     * @param $method
     * @return $this
     */
    public function request($method)
    {
        $this->method = $method;
        $this->exec();
        return $this;
    }

    /**
     * 执行请求
     */
    private function exec()
    {
        $url = $this->url;
        $fields = http_build_query($this->parameter);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, $this->curlOpt['USERAGENT']);
        switch ($this->method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, TRUE);
                if (!empty($fields)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                }
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        if (!empty($this->curlOpt['HTTP_VERSION'])) {
            curl_setopt($ch, CURLOPT_HTTP_VERSION, $url);
        }
        $_cookieFile = '/tmp/CURLCOOKIE.' . md5(uniqid(mt_rand(), true)) . '.cookie';
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_COOKIEJAR, $_cookieFile);
        //curl_setopt($ch, CURLOPT_COOKIEJAR, $_cookieFile);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->curlOpt['SSL_VERIFYPEER']);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->curlOpt['CONNECTTIMEOUT']);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->curlOpt['TIMEOUT']);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        if (!empty($this->curlOpt['HTTPHEADER'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->curlOpt['HTTPHEADER']);
        }
        $this->content = curl_exec($ch);
        $this->curlInfo = curl_getinfo($ch);
        curl_close($ch);
        //unlink($_cookieFile);
        $this->httpCode = $this->curlInfo['http_code'];
        if($this->httpCode != 200) {
        	$this->content = curl_error($ch);
        }
    }

    /**
     * 获取header长度
     * @param $ch
     * @param $header
     * @return int
     */
    public function getHeader($ch, $header)
    {
        return strlen($header);
    }

    /**
     * 获取URL返回信息
     * @param null $opt
     * @return array
     */
    public function getInfo($opt = null)
    {
        if ($opt != null && is_string($opt)) {
            return $this->curlInfo[$opt];
        }
        return $this->curlInfo;
    }

    /**
     * 获取状态
     * @return int
     */
    public function getStatus()
    {
        return $this->httpCode;
    }

    /**
     * 获取返回结果
     * @return string
     */
    public function getBody()
    {
        return $this->content;
    }
} 