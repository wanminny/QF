<?php

class Q_Cache_Redis extends Q_Cache_Abstract
{

    /**
     *
     * Enter description here ...
     * @var Redis
     */
    private $redis;

    private $timeout = 2.5;

    public function __construct(array $servers, $persistentID = '')
    {
        if (empty($servers)) {
            throw new Q_Cache_Exception('redis server is null.');
        }

        if(empty($this->redis)){
            $this->redis = new Redis();
            $prefix = $this->_prefix();
//            var_dump($servers);
            $this->redis->pconnect($servers[0]['host'], $servers[0]['port'], $this->timeout);
//            $this->setPrefix($prefix);
        }
       return $this->redis;
    }



    /**
     * 设置前缀
     * @param String $prefix
     * @return Q_Cache_Memcached
     */
    public function setPrefix($prefix)
    {
        assert(is_string($prefix));
        $this->redis->setOption(Redis::OPT_PREFIX, $prefix . '.');
        return $this;
    }

    /**
     * 设置Tag
     * @param String $tagName
     * @return Q_Cache_Memcached
     */
    public function tag($tagName)
    {
//        assert(!empty($tagName) || is_string($tagName));
        $this->tagName = $tagName;
        return $this;
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
     *
     * 返回key所关联的字符串值,如果key不存在则返回特殊值nil。
     * @param String $key
     * @return Mixed or nil
     */
    public function get($key)
    {
        assert(is_string($key));
        return unserialize($this->redis->get($key));
    }

    /**
     *
     * 将字符串值value关联到key
     * @param String $key
     * @param Mixed $val
     * @return bool
     */
    public function set($key, $val)
    {
        assert(is_string($key));

        return $this->redis->set($key, serialize($val));
    }

    /**
     *
     * 同时设置一个或多个key-value对。
     * @param array $keys
     * @return bool
     */
    public function mset(array $keys)
    {
        return $this->redis->mset($keys);
    }

    /**
     *
     * 返回所有(一个或多个)给定key的值.如果某个指定key不存在，那么返回特殊值nil。因此，该命令永不失败。
     * @param array $keys
     * @return Mixed
     */
    public function mget(array $keys)
    {
        return $this->redis->mget($keys);
    }

    /**
     *
     * 返回key中字符串值的子字符串，字符串的截取范围由start和end两个偏移量决定(包括start和end在内)。
     * 负数偏移量表示从字符串最后开始计数，-1表示最后一个字符，-2表示倒数第二个，以此类推
     * @param String $key
     * @param Integer $start
     * @param Integer $end
     * @return String
     */
    public function getRange($key, $start, $end)
    {
        assert(is_string($key));
        return $this->redis->getRange($key, $start, $end);
    }

    /**
     *
     * 删除数据 ( 返回删除个数 )
     * @param String $key
     * @return bool
     */
    public function del($key)
    {
        assert(is_string($key));
        return $this->redis->del($key);
    }

    /**
     * 查找符合给定模式的key。
     *
     * 可以使用正则
     * =========================================
     * *命中数据库中所有key
     * h?llo命中hello， hallo and hxllo等
     * h*llo命中hllo和heeeeello等
     * h[ae]llo命中hello和hallo，但不命中hillo
     * =========================================
     * KEYS的速度非常快，但在一个大的数据库中使用它仍然可能造成性能问题，如果你需要从一个数据集中查找特定的key，你最好还是用集合(set)结构。
     * @param String $keys
     * @return Mixed
     */
    public function keys($keys)
    {
        return $this->redis->keys($keys);
    }

    /**
     *
     * 选择数据库
     * @param String $db
     * @return bool
     */
    public function select($db = 9)
    {
        return $this->redis->select($db);
    }

    /**
     *
     * 获取 hash 集合中的键值
     * @param String $hashName
     * @param String $key
     * @param Mixed $val
     * @return Mixed
     */
    public function hget($hashName, $key, $val)
    {
        assert(is_string($hashName)) && assert(is_string($key));
        return $this->redis->hget($hashName, $key, $val);
    }

    /**
     *
     * 将哈希表key中的域field的值设为value。
     * @param String $hashName
     * @param String $key
     * @param Mixed $val
     * @return bool
     */
    public function hset($hashName, $key, $val)
    {
        assert(is_string($hashName)) && assert(is_string($key));
        return $this->redis->hset($hashName, $key, $val);
    }

    /**
     *
     * 排序
     *
     * @param String $key
     * @param array $options
     * 'by' => 'some_pattern_*',
     * 'limit' => array(0, 1),
     * 'get' => 'some_other_pattern_*' or an array of patterns,
     * 'sort' => 'asc' or 'desc',
     * 'alpha' => TRUE,
     * 'store' => 'external-key'
     * @return array
     */
    public function sort($key, array $options = array())
    {
        assert(is_string($key));
        return $this->redis->sort($key, $options);
    }

    /**
     *
     * 从当前数据库中随机返回(不删除)一个key。
     * @return String or Mixed
     */
    public function randomkey()
    {
        return $this->redis->randomKey();
    }

    /**
     *
     * 返回给定key的剩余生存时间(time to live)(以秒为单位)。
     * @param String $key
     * @return Integer
     */
    public function ttl($key)
    {
        assert(is_string($key));
        return $this->redis->ttl($key);
    }

    /**
     *
     * 检查给定key是否存在
     * @param String $key
     * @return bool
     */
    public function exists($key)
    {
        assert(is_string($key));
        return $this->redis->exists($key);
    }

    /**
     *
     * 移动key 到另外一个数据库
     * @param String $key
     * @param Integer $dbName
     * @return bool
     */
    public function move($key, $dbName)
    {
        assert(is_string($key));
        return $this->redis->move($key, $dbName);
    }

    /**
     *
     * 将key改名为newkey
     * @param String $key
     * @param String $newKey
     * @return bool
     */
    public function rename($key, $newKey)
    {
        assert(is_string($key)) && assert(is_string($newKey));
        return $this->redis->rename($key, $newKey);
    }

    /**
     *
     * 返回key所储存的值的类型
     * @param String $key
     * @return Mixed
     * ================================
     * none(key不存在) string(字符串) list(列表) set(集合) zset(有序集) hash(哈希表)
     * ================================
     */
    public function type($key)
    {
        return $this->redis->type($key);
    }

    /**
     *
     * 为给定key设置生存时间
     * @param String $key
     * @param Integer $expire
     * @return bool
     */
    public function setTimeout($key, $expire)
    {
        assert(is_string($key)) && assert(is_int($expire));
        return $this->redis->setTimeout($key, $expire);
    }

    /**
     *
     * 不同在于EXPIREAT命令接受的时间参数是UNIX时间戳(unix timestamp)。
     * @param String $key
     * @param Integer $expire
     * @return bool
     */
    public function expireAt($key, $expire)
    {
        assert(is_string($key)) && assert(is_int($expire));
        return $this->redis->expireAt($key, $expire);
    }

    /**
     *
     * 移除给定key的生存时间
     * @param String $key
     * @return bool
     */
    public function persist($key)
    {
        assert(is_string($key));
        return $this->redis->persist($key);
    }

    /**
     *
     * 将值value关联到key，并将key的生存时间设为seconds(以秒为单位)
     * @param String $key
     * @param Mixed $val
     * @param Integer $expire
     * @return bool
     */
    public function setex($key, $val, $expire)
    {
        assert(is_string($key)) && assert(is_int($expire));
        return $this->redis->setex($key, $expire, $val);
    }

    /**
     *
     * 如果key已经存在并且是一个字符串，APPEND命令将value追加到key原来的值之后
     * @param String $key
     * @param Mixed $val
     * @return bool
     */
    public function append($key, $val)
    {
        assert(is_string($key));
        return $this->redis->append($key, $val);
    }

    /**
     *
     * 将给定key的值设为value，并返回key的旧值
     * @param String $key
     * @param Mixed $val
     * @return Mixed
     */
    public function getSet($key, $val)
    {
        assert(is_string($key));
        return $this->redis->getSet($key, $val);
    }

    /**
     *
     * 返回key所储存的字符串值的长度
     * @param String $key
     * @return integer
     */
    public function strlen($key)
    {
        return $this->redis->strlen($key);
    }

    /**
     *
     * 将key中储存的数字值减一
     * @param String $key
     * @return Integer
     */
    public function decr($key)
    {
        assert(is_string($key));
        return $this->redis->decr($key);
    }

    /**
     *
     * 将key所储存的值减去减量decrement。
     * @param String $key
     * @param Integer $value
     * @return intger
     */
    public function decrBy($key, $value = 1)
    {
        assert(is_string($key)) && assert(is_int($value));
        return $this->redis->decrBy($key, $value);
    }

    /**
     *
     * 将key中储存的数字值增一
     * @param String $key
     * @param Integer $val
     * @return Integer
     */
    public function incrBy($key, $val = 1)
    {
        return $this->redis->incrBy($key, $val);
    }

    /**
     *
     * 同时将多个field - value(域-值)对设置到哈希表key中
     * @param String $key
     * @param array $vals
     * @return bool
     */
    public function hMset($hashKey, array $keys)
    {
        assert(is_string($hashKey));
        return $this->redis->hMset($hashKey, $keys);
    }

    /**
     *
     * 返回哈希表key中，一个或多个给定域的值
     * @param String $hashKey
     * @param array $keys
     * @return Mixed
     */
    public function hmGet($hashKey, array $keys)
    {
        assert(is_string($hashKey));
        return $this->redis->hmGet($hashKey, $keys);
    }

    /**
     *
     * 返回哈希表key中，所有的域和值
     * @param String $hashKey
     * @return Mixed
     */
    public function hGetAll($hashKey)
    {
        assert(is_string($hashKey));
        return $this->redis->hGetAll($hashKey);
    }

    /**
     *
     * 删除哈希表key中的一个或多个指定域
     * @param String $hashKey
     * @return bool
     */
    public function hDel($hashKey)
    {
        $this->redis->hDel($hashKey);
    }

    /**
     *
     * 返回哈希表key中域的数量
     * @param String $hashKey
     * @return Integer
     */
    public function hLen($hashKey)
    {
        return $this->redis->hLen($hashKey);
    }

    /**
     *
     * 查看哈希表key中，给定域field是否存在
     * @param String $hashKey
     * @return bool
     */
    public function hExists($hashKey)
    {
        return $this->redis->hExists($hashKey);
    }

    /**
     *
     * 为哈希表key中的域field的值加上增量increment。
     * @param String $hashKey
     * @param String $key
     * @param Integer $member
     * @return Integer
     */
    public function hincrby($hashKey, $key, $member)
    {
        return $this->redis->hIncrBy($hashKey, $key, $member);
    }

    /**
     *
     * 返回哈希表key中的所有域
     * @param String $hashKey
     * @return array
     */
    public function hKeys($hashKey)
    {
        return $this->redis->hKeys($hashKey);
    }

    /**
     *
     * 返回哈希表key中的所有值
     * @param String $hashKey
     * @return Array
     */
    public function hVals($hashKey)
    {
        return $this->redis->hVals($hashKey);
    }
    ###########################
    # 表 List
    ###########################
    /**
     *
     * 将值value插入到列表key的表头
     * @param String $key
     * @param Mixed $value
     * @return bool
     */
    public function lPush($key, $value)
    {
        assert(is_string($key));
        return $this->redis->lPush($key, $value);
    }

    /**
     *
     * 将值value插入到列表key的表头，当且仅当key存在并且是一个列表
     * @param String $key
     * @param Mixed $value
     * @return bool
     */
    public function lPushx($key, $value)
    {
        assert(is_string($key));
        return $this->redis->lPushx($key, $value);
    }

    /**
     *
     * 将值value插入到列表key的表尾
     * @param String $key
     * @param Mixed $value
     * @return bool
     */
    public function rPush($key, $value)
    {
        assert(is_string($key));
        return $this->redis->rPush($key, $value);
    }

    /**
     *
     * 将值value插入到列表key的表尾，当且仅当key存在并且是一个列表
     * @param String $key
     * @param Mixed $value
     * @return bool
     */
    public function rPushx($key, $value)
    {
        assert(is_string($key));
        return $this->redis->rPushx($key, $value);
    }

    /**
     *
     * 移除并返回列表key的头元素
     * @param String $key
     * @return bool or nil
     */
    public function lPop($key)
    {
        return $this->redis->lPop($key);
    }

    /**
     *
     * 移除并返回列表key的尾元素
     * @param String $key
     * @return bool or nil
     */
    public function rPop($key)
    {
        return $this->redis->rPop($key);
    }

    /**
     *
     * BLPOP是列表的阻塞式(blocking)弹出原语
     * ===================================
     * 类似 Gearman 等待移除
     * ===================================
     * @param array $keys
     * @param Integer $timeout
     * @return array
     */
    public function blPop(array $keys, $timeout = 2)
    {
        return $this->redis->blPop($keys, (int)$timeout);
    }

    /**
     *
     * BRPOP是列表的阻塞式(blocking)弹出原语。
     * ===================================
     * 类似 Gearman 等待移除
     * ===================================
     * @param array $keys
     * @param Integer $timeout
     *
     */
    public function brPop(array $keys, $timeout = 2)
    {
        return $this->redis->brPop($keys, (int)$timeout);
    }

    /**
     * TODO
     * 返回列表key的长度。
     */
    public function llen()
    {

    }

    /**
     *
     * 返回列表key中指定区间内的元素，区间以偏移量start和stop指定。
     * @param String $key
     * @param Integer $start
     * @param Integer $end
     * @return array
     */
    public function lRange($key, $start = 0, $end = 0)
    {
        return $this->redis->lRange($key, (int)$start, (int)$end);
    }

    /**
     *
     * 根据参数count的值，移除列表中与参数value相等的元素
     * ============================================
     * count的值可以是以下几种：
     * count > 0: 从表头开始向表尾搜索，移除与value相等的元素，数量为count
     * count < 0: 从表尾开始向表头搜索，移除与value相等的元素，数量为count的绝对值
     * count = 0: 移除表中所有与value相等的值
     * ============================================
     * @param String $key
     * @param String $value
     * @param Integer $count
     * @return Integer
     */
    public function lRem($key, $value, $count)
    {
        $this->redis->lRem((string)$key, (string)$value, (int)$count);
    }

    /**
     *
     * 将列表key下标为index的元素的值甚至为value
     * (当index参数超出范围，或对一个空列表(key不存在)进行LSET时，返回一个错误)
     * @param String $key
     * @param Integer $index
     * @param String $value
     * @return bool
     */
    public function lSet($key, $index, $value)
    {
        return $this->redis->lSet((string)$key, (int)$index, (string)$value);
    }

    /**
     *
     * 对一个列表进行修剪(trim)，就是说，让列表只保留指定区间内的元素，不在指定区间之内的元素都将被删除
     * @param String $key
     * @param Integer $start
     * @param Integer $stop
     * @return bool
     */
    public function lTrim($key, $start, $stop)
    {
        return $this->redis->lTrim((string)$key, (int)$start, (int)$stop);
    }

    /**
     *
     * 返回列表key中，下标为index的元素
     * @param String $key
     * @param Integer $index
     * @return bool or nil
     */
    public function lGet($key, $index)
    {
        return $this->redis->lGet((string)$key, (int)$index);
    }

    /**
     * TODO
     * Enter description here ...
     */
    public function rpoplpush()
    {

    }

    ##################################################################
    # SET
    ##################################################################
    /**
     *
     * 将一个或多个member元素加入到集合key当中，已经存在于集合的member元素将被忽略
     * @param String $key
     * @param Mixed $value
     * @return bool
     */
    public function sAdd($skey, $value)
    {
        return $this->redis->sAdd($skey, $value);
    }

    /**
     *
     * ( 扩展 ) 将一个或多个member元素加入到集合key当中，已经存在于集合的member元素将被忽略
     * @param String $key
     * @param Mixed $value
     * @param Integer $expiration
     * @return bool
     */
    public function sAdd2($skey, $value, $expiration = 0)
    {
        $result = $this->redis->sAdd($skey, $value);
        $this->redis->setTimeout($skey, $expiration);
        return $result;
    }

    /**
     *
     * 移除集合key中的一个或多个member元素，不存在的member元素会被忽略
     * @param String $key
     * @param String $member
     * @return bool
     */
    public function sRem($skey, $member)
    {
        return $this->redis->sRem((string)$skey, (string)$member);
    }

    /**
     *
     * 返回集合key中的所有成员
     * @param String $key
     * @return array
     */
    public function sMembers($skey)
    {
        return $this->redis->sMembers((string)$skey);
    }

    /**
     *
     * 判断member元素是否是集合key的成员
     * @param String $key
     * @param String $value
     */
    public function sIsMember($skey, $value)
    {
        return $this->redis->sIsMember((string)$skey, (string)$value);
    }

    /**
     *
     * 返回集合key的基数(集合中元素的数量)
     * @param String $skey
     * @return Integer
     */
    public function sCard($skey)
    {
        return $this->redis->sCard((string)$skey);
    }

    /**
     *
     * 将member元素从source集合移动到destination集合
     * @param String $srcKey
     * @param String $dstKey
     * @param String $member
     * @return bool
     */
    public function sMove($srcKey, $dstKey, $member)
    {
        return $this->redis->sMove((string)$srcKey, (string)$dstKey, (string)$member);
    }

    /**
     *
     * 移除并返回集合中的一个随机元素
     * @param String $skey
     * @return string or bool
     */
    public function sPop($skey)
    {
        return $this->redis->sPop((string)$skey);
    }

    /**
     *
     * 返回集合中的一个随机元素。
     * @param String $skey
     * @return array or nil
     */
    public function sRandMember($skey)
    {
        return $this->redis->sRandMember((string)$skey);
    }

    /**
     * func_get_args
     * 返回一个集合的全部成员，该集合是所有给定集合的交集
     * @param array $skey
     * @return array
     */
    public function sInter(array $skey)
    {
        return $this->redis->sInter($skey);
    }
    #TODO
    //返回一个集合的全部成员，该集合是所有给定集合的并集
    public function sUnion(array $skey)
    {
        return $this->redis->sUnion($skey);
    }

    //返回一个集合的全部成员，该集合是所有给定集合的差集
    public function sDiff(array $skey)
    {
        return $this->redis->sDiff($skey);
    }
    ########################################################
    # 有序集(Sorted Set)
    ########################################################
    /**
     *
     * 将一个或多个member元素及其score值加入到有序集key当中
     * @param String $zKey
     * @param Integer $score
     * @param String $value
     * @return Integer
     */
    public function zAdd($zKey, $score, $value)
    {
        assert(is_string($zKey)) && assert(is_int($score)) && assert(is_string($value));
        return $this->redis->zAdd($zKey, $score, $value);
    }

    /**
     *
     * 移除有序集key中的一个或多个成员，不存在的成员将被忽略
     * @param String $zKey
     * @param String $member
     * @return Integer
     */
    public function zRem($zKey, $member)
    {
        return $this->redis->zRem((string)$zKey, (string)$member);
    }

    /**
     *
     * 返回有序集key的基数
     * @param String $zKey
     * @return Integer
     */
    public function zSize($zKey)
    {
        return $this->redis->zSize((string)$zKey);
    }

    /**
     *
     * 返回有序集key中，score值在min和max之间(默认包括score值等于min或max)的成员
     * @param String $zKey
     * @param Integer $start
     * @param Integer $end
     * @return array
     */
    public function zCount($zKey, $start, $end)
    {
        return $this->redis->zCount($zKey, $start, $end);
    }

    /**
     *
     * 返回有序集key中，成员member的score值
     * @param String $zKey
     * @param String $member
     * @return String
     */
    public function zScore($zKey, $member)
    {
        return $this->redis->zScore($zKey, $member);
    }

    /**
     *
     * 为有序集key的成员member的score值加上增量increment
     * @param String $zKey
     * @param Integer $value
     * @param String $member
     * @return Integer
     */
    public function zIncrBy($zKey, $value, $member)
    {
        return $this->redis->zIncrBy($zKey, $value, $member);
    }

    /**
     *
     * 返回有序集key中，指定区间内的成员
     * @param String $zKey
     * @param Integer $start
     * @param Integer $end
     * @param bool $withscores
     * @return bool ( 默认False无键值/True有键值 )
     */
    public function zRange($zKey, $start, $end, $withscores = false)
    {
        return $this->redis->zRange($zKey, $start, $end, $withscores);
    }

    /**
     *
     * 返回有序集key中，指定区间内的成员
     * 其中成员的位置按score值递减(从大到小)来排列
     * @param String $zKey
     * @param Integer $start
     * @param Integer $end
     * @param bool $withscores
     * @return bool ( 默认False无键值/True有键值 )
     */
    public function zRevRange($zKey, $start, $end, $withscores = false)
    {
        return $this->redis->zRevRange($zKey, $start, $end, $withscores);
    }

    /**
     *
     * 返回有序集key中，所有score值介于min和max之间(包括等于min或max)的成员。有序集成员按score值递增(从小到大 or 从大到小)次序排列
     * @param String $zKey
     * @param Integer $start
     * @param Integer $end
     * @param array $options
     * @return array
     * =========================================================
     * $redis->zRangeByScore('key', 0, 3);
     * array('val0', 'val2')
     * $redis->zRangeByScore('key', 0, 3, array('withscores' => TRUE);
     * array('val0' => 0, 'val2' => 2)
     * $redis->zRangeByScore('key', 0, 3, array('limit' => array(1, 1));
     * array('val2' => 2)
     * $redis->zRangeByScore('key', 0, 3, array('limit' => array(1, 1));
     * array('val2')
     * $redis->zRangeByScore('key', 0, 3, array('withscores' => TRUE, 'limit' => array(1, 1));
     * array('val2' => 2)
     * =========================================================
     *
     */
    public function zRangeByScore($zKey, $start, $end, array $options)
    {
        return $this->redis->zRangeByScore($zKey, $start, $end, $options);
    }

    /**
     *
     * 返回有序集key中成员member的排名。其中有序集成员按score值递增(从小到大 or 从大到小)顺序排列
     * @param String $zKey
     * @param String $member
     * @param String $order ( desc or asc )
     * @return array
     */
    public function zRank($zKey, $member, $order = 'desc')
    {
        return $order == 'desc' ? $this->redis->zRank($zKey, $member) : $this->redis->zRevRank($zKey, $member);
    }

    /**
     * 移除有序集key中，指定排名(rank)区间内的所有成员
     * 区间分别以下标参数start和stop指出，包含start和stop在内
     * @param String $zKey
     * @param Integer $start
     * @param Integer $end
     * @return Integer
     */
    public function zRemRangeByRank($zKey, $start, $end)
    {
        return $this->redis->zRemRangeByRank($zKey, $start, $end);
    }


    /**
     * 移除有序集key中，指定(socre)区间内的所有成员
     * 区间分别以下标参数start和stop指出，包含start和stop在内
     * @param String $zKey
     * @param Integer $start
     * @param Integer $end
     * @return Integer
     */
    public function zRemRangeByScore($zKey, $start, $end)
    {
        return $this->redis->zRemRangeByScore($zKey, $start, $end);
    }

    public function zRevRangeByScore($zkey, $start, $end, array $options)
    {
        return $this->redis->zRevRangeByScore($zkey, $start, $end, $options);
    }

    /**
     * 发布消息
     *
     * @param String $channel
     * @param String $message
     * @return Integer
     */
    public function publish($channel, $message)
    {
        return $this->redis->publish($channel, $message);
    }

    /**
     * 订阅消息
     * @param String $channel
     * @return String
     */
    public function subscribe(array $channel, $callback)
    {
        return $this->redis->subscribe($channel, $callback);
    }

    /**
     * 退订
     * @param String $channel
     */
    public function unsubscribe($channel)
    {
        return $this->redis->unsubscribe($channel);
    }

    /**
     * 按照模式匹配订阅多个频道
     *
     * @param String $pattern (如：news.* 可订阅news.开头的所有频道)
     */
    public function psubscribe($pattern)
    {
        return $this->redis->psubscribe($pattern);
    }

    /**
     * 退订给定模式的所有渠道
     *
     * @param String $pattern
     */
    public function punsubscribe($pattern)
    {
        return $this->redis->punsubscribe($pattern);
    }

    public function pubsub($pattern)
    {
        return $this->redis->pubsub($pattern);
    }
}