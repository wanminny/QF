<?php
class Q_Db_Dsn {

	/**
	 * 获取数据库
	 *
	 * @param String $dbname 	数据库名称
	 * @param String $readtag    读、写
	 * @return Array
	 **/
	public static function getDbServer($dbname, $readtag = true, $drive = 'Mysql') {
		$options = array(
			'mode' => $readtag === true ? Q_Server_Core::SERVER_MODE_READ : Q_Server_Core::SERVER_MODE_WRITE,
			'select' => Q_Server_Core::SERVER_SELECT_MODE_RAND
		);
		$servers = Q_Server::factory('db', strtolower($drive), strtolower($dbname), $options);
		if (empty($servers)) {
			throw new Q_Db_Exception('server config is null ,find: ' . $dbname . ' No such config file ');
		}
		$driver_options = array(
			'dbname' => isset($servers['dbname']) ? $servers['dbname'] :  $dbname,
			'driver_options' => array(
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
				PDO::ATTR_PERSISTENT => false,
				PDO::ATTR_TIMEOUT => 30
			),
			'options' => array()//Zend_Db::AUTO_QUOTE_IDENTIFIERS => false

		);
		$servers = array_merge($servers, $driver_options);
		return $servers;
	}
}