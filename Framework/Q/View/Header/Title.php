<?php

/**
 * User: parasol.zhang
 * Date: 14-1-14
 * Time: 8:37
 */
class Q_View_Header_Title extends Q_View_Header_Abstract
{
    private $_title = '';

    public function headTitle($title)
    {
        $this->_title = $title;
        return $this;
    }

    public function __toString()
    {
        return '<title>' . $this->_escape($this->_title) . '</title>' . PHP_EOL;
    }
} 