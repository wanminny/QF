<?php

class Q_Cache_Memcached extends Q_Cache_Abstract
{

    public function __construct(array $servers, $persistentID = '')
    {
        if (empty($servers)) {
            throw new Q_Cache_Exception(__CLASS__ . ' :  Servers Is Null');
        }
        $this->_unset();
        $prefix = $this->_prefix();
        $this->servers = $servers;
        #初始化memcached
        $this->initCache($persistentID);
        $this->setPrefix($prefix);
    }

    private function initCache($persistentID = '')
    {
        if (empty($this->_cache)) {
            $this->_cache = new Memcached(posix_getpid());
        }
        if(count($this->_cache->getServerList()) < 1){
	        $this->_cache->addServers($this->servers);
	        $this->_cache->setOptions(array(
						Memcached::OPT_CONNECT_TIMEOUT=>500,
	        			Memcached::OPT_DISTRIBUTION=> Memcached::DISTRIBUTION_CONSISTENT,
						Memcached::OPT_LIBKETAMA_COMPATIBLE=>true,
						Memcached::OPT_REMOVE_FAILED_SERVERS=>true
			));
        }
        return $this->_cache;
    }

    /**
     * 获取系统前缀
     * @return string
     */
    public function _prefix()
    {
        return $this->domain . '.' . $this->class;
    }

    /**
     * 设置域
     * @param String $domin
     * @return Q_Cache_Memcached
     */
    public function setDomin($domin)
    {
        $this->domain = $domin;
        $this->setPrefix($this->_prefix());
        return $this;
    }

    /**
     * 设置class
     * @param String $class
     * @return Q_Cache_Memcached
     */
    public function setClass($class)
    {
        $this->class = $class;
        $this->setPrefix($this->_prefix());
        return $this;
    }

    /**
     * 设置Tag
     * @param String $tagName
     * @return Q_Cache_Memcached
     */
    public function tag($tagName)
    {
        assert(!empty($tagName) || is_string($tagName));
        $this->tagName = $tagName;
        return $this;
    }

    /**
     * 设置前缀
     * @param String $prefix
     * @return Q_Cache_Memcached
     */
    public function setPrefix($prefix)
    {
        assert(is_string($prefix));
        $this->initCache()->setOption(Memcached::OPT_PREFIX_KEY, $prefix . '.');
        return $this;
    }

    /**
     *
     * 删除Tag
     * @param String $tagName
     * @param Integer $delay
     * @return Bool
     */
    public function deleteTag($tagName, $delay = 0)
    {
        assert(!empty($tagName));
        $_tagName = $tagName;
        if (!is_array($tagName)) {
            $_tagName = array($tagName);
        }
        $results = array();
        foreach ($_tagName as $val) {
            $this->tagName = $val;
            $results[$val] = $this->initCache()->delete($this->tagKey($val), $delay);
        }
        $this->_unset();
        return is_array($tagName) ? $results : $results[$tagName];
    }

    /**
     *
     * 设置MC选择
     * @param String $option
     * @param integer $value
     * @return Q_Cache_Memcached
     */
    public function setOption($option, $value)
    {
        $this->initCache()->setOption($option, $value);
        return $this;
    }

    /**
     *
     * 向一个新的key下面增加一个元素
     * @param String $key
     * @param String $value
     * @param Integer $expiration
     */
    public function add($key, $value, $expiration = 0)
    {
        assert(is_string($key));
        $results = $this->initCache()->add($this->getTagPrefix(true) . md5($key), $value, (int)$expiration);
        $this->_unset();
        return $results;
    }

    /**
     *
     * 在指定服务器上的一个新的key下增加一个元素
     * @param String $server
     * @param String $key
     * @param Mixed $value
     * @param Integer $expiration
     * @return bool
     */
    public function addByKey($server, $key, $value, $expiration = 0)
    {
        assert(is_string($server)) && assert(is_string($key));
        return $this->initCache()->addByKey($server, $this->getTagPrefix() . md5($key), $value, $expiration);
    }

    /**
     *
     * 向服务器池中增加一个服务器
     * @param String $host
     * @param Intger $port
     * @param Integer $weight
     * @return bool
     */
    public function addServer($host, $port = 11211, $weight)
    {
        assert(is_string($host));
        return $this->initCache()->addServer((string)$host, (int)$port, (int)$weight);
    }

    /**
     *
     * 向服务器池中增加多台服务器
     * @param array $servers
     */
    public function addServers(array $servers)
    {
        assert(!empty($servers));
        return $this->initCache()->addServers($servers);
    }

    /**
     *
     * 向已存在元素后追加数据
     * @param String $key
     * @param Mixed $val
     * @throws Q_Cache_Exception
     * @return bool
     */
    public function append($key, $val)
    {
        $compression = $this->getOption(Memcached::OPT_COMPRESSION);
        if ($compression == true) {
            throw new Q_Cache_Exception(' 当前memcached是压缩模式，不能使用追加功能. ');
        }
        return $this->initCache()->append($this->getTagPrefix() . md5($key), $val);
    }

    /**
     *
     * 向指定服务器上已存在元素后追加数据
     * @param String $server
     * @param String $key
     * @param Mixed $val
     * @return bool
     */
    public function appendByKey($server, $key, $val)
    {
        assert(is_string($server)) && assert(is_string($key));
        return $this->initCache()->appendByKey($server, $this->getTagPrefix() . md5($key), $val);
    }

    /**
     *
     * 减小数值元素的值
     * @param String $key
     * @param Integer $offset
     * @return bool
     */
    public function decrement($key, $offset = 1)
    {
        assert(is_string($key)) && assert(is_int($offset));
        return $this->initCache()->decrement($this->getTagPrefix() . md5($key), $offset);
    }

    /**
     *
     * 删除一个元素
     * @param String $key
     * @param Integer $time
     * @return bool
     */
    public function delete($key, $time = 0)
    {
        assert(is_string($key)) && assert(is_int($time));
        return $this->initCache()->delete($this->getTagPrefix() . md5($key), $time);
    }

    /**
     *
     * 抓取下一个结果
     * @throws Q_Cache_Exception
     */
    public function fetch()
    {
        return $this->initCache()->fetch();
    }

    /**
     *
     * 抓取所有剩余的结果
     * @return array
     */
    public function fetchAll()
    {
        return $this->initCache()->fetchAll();
    }

    /**
     *
     * 作废缓存中的所有元素
     * @param Integer $delay
     * @return bool
     */
    public function flush($delay = 0)
    {
        assert(is_int($delay));
        return $this->initCache()->flush($delay);
    }

    /**
     *
     * 检索一个元素
     * @param String $key
     * @param null $callback
     * @return  Mixed
     */
    public function get($key, $callback = NULL)
    {
        assert(is_string($key));
        $returns = $this->initCache()->get($this->getTagPrefix() . md5($key), $callback);
        $this->_unset();
        return $returns;
    }

    /**
     *
     * 向Memcached服务端发出一个检索keys指定的多个key对应元素的请求。这个方法不会等待响应而是立即返回
     * @param array $keys
     * @param bool $with_cas
     * @param String || NULL $callback
     * @return Q_Cache_Memcached
     */
    public function getDelayed(array $keys, $with_cas = true, $callback = NULL)
    {
        assert(!empty($keys));
        $_keyPrefix = array_fill(0, count($keys), $this->getTagPrefix());
        $realKey = array_map(array($this, '_realKey'), $_keyPrefix, $keys);
        $this->initCache()->getDelayed($realKey, $with_cas, $callback);
        return $this;
    }

    /**
     *
     * 检索多个元素
     * @param array $keys
     * @param null $cas_tokens
     * @param Integer $flags
     * @return array
     */
    public function getMulti(array $keys, &$cas_tokens = NULL, $flags = Memcached::GET_PRESERVE_ORDER)
    {
        assert(!empty($keys));
        $_keyPrefix = array_fill(0, count($keys), $this->getTagPrefix());
        $realKey = array_map(array($this, '_realKey'), $_keyPrefix, $keys);
        $_mc_returns = $this->initCache()->getMulti($realKey, $cas_tokens, $flags);
        if (empty($_mc_returns)) {
            return $_mc_returns;
        }
        $_keyList = array_combine($realKey, $keys);
        $returns = array();
        foreach ($_mc_returns as $key => $val) {
            empty($_keyList[$key]) ? false : $returns[$_keyList[$key]] = $val;
        }
        return $returns;
    }

    /**
     *
     * 组织数据
     * @param String $keyPrefix
     * @param String $key
     * @return String
     */
    private function _realKey($keyPrefix, $key)
    {
        return empty($keyPrefix) ? md5($key) : $keyPrefix . md5($key);
    }

    /**
     *
     * 获取Memcached的选项值
     * @param Integer $option
     * @return bool
     */
    public function getOption($option)
    {
        return $this->initCache()->getOption($option);
    }

    /**
     *
     * 返回最后一次操作的结果代码
     * @return string
     */
    public function getResultCode()
    {
        return $this->initCache()->getResultCode();
    }

    /**
     *
     * 返回最后一次操作的结果描述消息
     * @return String
     */
    public function getResultMessage()
    {
        return $this->initCache()->getResultMessage();
    }

    /**
     * 获取一个key所映射的服务器信息
     * @param String $server_key
     * @return array
     */
    public function getServerByKey($server_key)
    {
        $compatible = $this->initCache()->getOption(Memcached::OPT_LIBKETAMA_COMPATIBLE);
        $compatible == false ? $this->initCache()->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true) : true;
        return $this->initCache()->getServerByKey($server_key);
    }

    /**
     *
     * 获取服务器池中的服务器列表
     * @return array
     */
    public function getServerList()
    {
        return $this->initCache()->getServerList();
    }

    /**
     *
     * 获取服务器池的统计信息
     * @return array
     */
    public function getStats()
    {
        return $this->initCache()->getStats();
    }

    /**
     *
     * 增加数值元素的值
     * @param String $key
     * @param Integer $offset
     * @return bool
     */
    public function increment($key, $offset = 1)
    {
        assert(is_string($key)) && assert(is_int($offset));
        return $this->initCache()->increment($this->getTagPrefix() . md5($key), $offset);
    }

    /**
     *
     * 向一个已存在的元素前面追加数据
     * @param String $key
     * @param Mixed $val
     * @throws Q_Cache_Exception
     */
    public function prepend($key, $val)
    {
        $compression = $this->getOption(Memcached::OPT_COMPRESSION);
        if ($compression == true) {
            throw new Q_Cache_Exception(' 当前memcached是压缩模式，不能使用追加功能. ');
        }
        return $this->initCache()->prepend($this->getTagPrefix() . md5($key), $val);
    }

    /**
     *
     * 替换已存在key下的元素
     * @param String $key
     * @param Mixed $value
     * @param Integer $expiration
     * @return bool
     */
    public function replace($key, $value, $expiration = 0)
    {
        assert(is_string($key));
        return $this->initCache()->replace($this->getTagPrefix() . md5($key), $value, $expiration);
    }

    /**
     *
     * 存储一个元素
     * @param String $key
     * @param Mixed $value
     * @param Integer $expiration
     * @return Mixed
     */
    public function set($key, $value, $expiration = 0)
    {
        assert(is_string($key));
        //print_R($this->initCache());die;
        $results = $this->initCache()->set($this->getTagPrefix(true) . md5($key), $value, $expiration);
        $this->_unset();
        return $results;
    }

    /**
     *
     * 存储多个元素
     * @param array $items
     * @param Integer $expiration
     * @return array
     */
    public function setMulti(array $items, $expiration = 0)
    {
        assert(!empty($items));
        $_prefix = $this->getTagPrefix(true);
        $results = array();
        foreach ($items as $key => $val) {
            $results[$this->_realKey($_prefix, $key)] = $val;
        }
        return $this->initCache()->setMulti($results, $expiration);
    }

    /**
     *
     * 结束
     */
    public function __destruct()
    {
        $this->_unset();
        unset($this->_cache);
        $this->servers = array();
    }
}
