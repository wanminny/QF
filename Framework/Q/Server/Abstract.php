<?php
/**
 * Enter description here...
 *
 * example：
 * <pre>
 *
 * </pre>
 *
 * @name Q_Server_Abstract
 * @version (  2015-12-14 下午01:35:46  )
 * @package Abstract.php
 * @author lr.zhang i@zhanglirong.cn
 */

abstract class Q_Server_Abstract implements Q_Server_Interface
{

    protected $config_path;

    /**
     *
     * 是否验证服务器
     * @var bool
     */
    protected $check_server = false;

    /**
     *
     * hash key
     * @var String
     */
    protected $hash_key;

    /**
     * 解析服务器字符
     *
     * @param String $ips
     * @return Array
     */
    protected function parseServerString($ips)
    {
        $tmpary = explode(',', str_replace(array(
            ' ',
            "\n"
        ), '', $ips));
        if (empty($tmpary)) {
            throw new Q_Server_Exception(__CLASS__ . ': Server Config Format String Error.');
        }
        $serverResults = array();
        foreach ($tmpary as $k => $v) {
            $results = $this->makeServer($v);
            if (!empty($results)) {
                $serverResults[] = $results;
            }
        }
        return $serverResults;
    }

    /**
     * 组合服务器地址
     * @param String $serverStr
     * @return Array
     */
    private function makeServer($serverStr)
    {
        $servers = explode(':', $serverStr);
        $optCount = count($servers);
        $results = array();
        switch ($optCount) {
            case 2 :
                list($host, $port) = $servers;
                $results = array(
                    'host' => (string)$host,
                    'port' => (int)intval($port)
                );
                break;
            case 3 :
                list($host, $port, $weight) = $servers;
                $results = array(
                    'host' => (string)$host,
                    'port' => (int)intval($port),
                    'weight' => (int)intval($weight)
                );
                break;
            default :
                $results = array();
        }
        return $results;
    }

    public function hashServer(array $servers)
    {
        if (count($servers) < 3) {
            throw new Q_Server_Exception(' Server count Less than 3. Can not be used HashServer ( Hash ).');
        }
        if (empty($this->hash_key)) {
            throw new Q_Server_Exception(' Hash Key is Null. ');
        }
        $servers_hosts = array();
        foreach ($servers as $k => $v) {
            $servers_hosts[] = $v['host'] . ':' . $v['port'];
        }
        $hash = new Q_Core_Flexi_Hash();
        $hash->addTargets($servers_hosts);
        $ip = array(
            'host' => '',
            'port' => ''
        );
        $hit_ip = $hash->lookup($this->hash_key);
        list($ip['host'], $ip['port']) = explode(':', $hit_ip);
        return $ip;
    }

    /**
     * 随机选择服务器并验证
     *
     * @param array $servers
     * @param String $type
     * @return Array
     */
    public function randServer(array $servers)
    {
        $hitServer = array();
        while (true) {
            shuffle($servers);
            if (empty($servers)) {
                break;
            }
            $server_num = count($servers);
            $randNum = $server_num > 2 ? mt_rand(0, $server_num - 1) : 0;
            $hitServer = $servers[$randNum];
            if ($this->check_server === false) {
                break;
            }
            unset($servers[$randNum]);
            if (Q_Server_Monitor::run()->check($hitServer["host"], $hitServer["port"])) {
                break;
            }
        }
        return $hitServer;
    }
}