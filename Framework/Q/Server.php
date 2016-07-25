<?php


class Q_Server {
	static function factory($config_name, $section, $node, array $options = array()) {
		if (!isset($options['mode']) || !isset($options['select'])) {
			throw new Q_Server_Exception(' In Options No ( Mode or Select ). ');
		}
		$mode = $options['mode'];
		$select = $options['select'];
		$check = (isset($options['check']) && is_bool($options['check'])) ? $options['check'] : false;
		$server = new Q_Server_Core();
		$server->readerSection($config_name, $section);
		isset($options['hash_key']) ? $server->hashKey($options['hash_key']) : true;
		return $server->loadServer($node, $mode, $select, $check);
	}
}