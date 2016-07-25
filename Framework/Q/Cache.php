<?php

class Q_Cache
{

    private static $_cache;

    /**
     * @var array
     */
    private static $options = array(
        'mode' => Q_Server_Core::SERVER_MODE_PROXY,
        'select' => Q_Server_Core::SERVER_SELECT_MODE_STATIC
    );

    /**
     * @static
     * @param string $driver
     * @param array $options
     * @param string $node
     * @return mixed
     * @throws Q_Exception
     */
    public static function factory($driver = 'Memcached', array $options = array(), $nodeName = 'servers')
    {
        $driver = ucfirst($driver);
        $className = 'Q_Cache_' . $driver;
        if (!class_exists($className)) {
            throw new Q_Exception(' Cache Not ' . $className);
        }
        $options = array_merge(self::$options, $options);
        $servers = Q_Server::factory('cache', strtolower($driver), $nodeName, $options);
        $persistentID = empty($options['persistent_id']) ? '' : $options['persistent_id'];
        return new $className($servers, $persistentID);
    }
}