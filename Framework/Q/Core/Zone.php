<?php
/**
 * 
 * Enter description here ...
 * @author parasol.zhang
 */
class Q_Core_Zone {
	
	private $cacheOB = null;
	
	private $key;
	
	private $cacheNull = false;
	
	private $expiration = 300;
	
	public function __construct() {
		if (empty($this->cache)) {
			$this->cacheOB = Q_Cache::factory('Memcached')->setPrefix('qcore.zone');
		}
	}
	
	/**
	 * 
	 * 开始缓存
	 * @param String $key
	 * @param Integer $expiration
	 * @return bool
	 */
	public function beginCache($key, $expiration = 300) {
		if (empty($key)) {
			return false;
		}
		$this->key = $key;
		$zoneData = $this->cacheOB->get($key);
		if (empty($zoneData)) {
			ob_start();
			$this->expiration = (int) $expiration;
			$this->cacheNull = true;
			return false;
		}
		echo $zoneData;
		return true;
	}
	
	/**
	 * 结束缓存
	 */
	public function endCache() {
		if ($this->cacheNull == true) {
			$zoneData = ob_get_clean();
			if (!empty($zoneData)) {
				$this->cacheOB->set($this->key, $zoneData, $this->expiration);
			}
			echo $zoneData;
		}
	}
	
	/**
	 * @static
	 * @param string $mode
	 * @return Q_Core_Zone
	 */
	static function cache() {
		return new Q_Core_Zone();
	}
}