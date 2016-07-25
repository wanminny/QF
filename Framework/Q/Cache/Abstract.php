<?php
abstract class Q_Cache_Abstract implements Q_Cache_Interface
{

    /**
     *
     * 域 & 前缀
     * @var String
     */
    protected $domain = 'qf.domain';

    /**
     * 接点
     *
     * @var String
     */
    protected $class = 'default';

    /**
     * Server List
     *
     * @var Array
     */
    protected $servers = array();

    /**
     * 前缀
     * @var string
     */
    protected $keyPrefix = '';

    /**
     * Tag
     * @var string
     */
    protected $tagName = '';


    /**
     * Memcached Obj
     *
     * @var Memcached
     */
    protected $_cache;

    /**
     *
     * tag 前缀
     * @return String
     */
    protected function getTagPrefix($rw_mode = false)
    {
        if (empty($this->tagName)) {
            return '';
        }
        $key = $this->tagKey($this->tagName);
        $_tag_val = $this->_cache->get($key);
        if (empty($_tag_val) && $rw_mode == true) {
            $_tag_val = md5(microtime() . mt_rand() . uniqid());
            $this->_cache->set($key, $_tag_val, 0);
        }
        return empty($_tag_val) ? '' : $_tag_val . '.';
    }

    /**
     *
     * Tag key
     * @param String $tagName
     * @return String
     */
    protected function tagKey($tagName)
    {
        return 'cache.tag.' . md5($tagName);
    }

    /**
     * 初始化数据
     */
    protected function _unset()
    {
        $this->tagName = '';
        $this->domain = 'qf.domain';
        $this->class = 'default';
        $this->keyPrefix = '';
    }
}