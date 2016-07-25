<?php

/**
 * User: parasol.zhang
 * Date: 2015.12.24
 * Time: 16:52
 */
class Q_View_Header_Abstract
{
    protected $_item = array();

    protected $enc = 'UTF-8';

    protected $conditional = '';

    protected $_target_conditional = array();

    protected function _escape($string)
    {
        return htmlspecialchars((string)$string, ENT_COMPAT, $this->enc);
    }
} 