<?php
/**
 * User: lirong.zhang
 * Date: 15-12-16
 * Time: 23:33
 */

interface Q_Core_Config_Interface
{
    public function get($key, $default = null);

    public function count();
} 