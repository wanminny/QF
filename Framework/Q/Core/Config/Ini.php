<?php
/**
 * User: parasol.zhang
 * Date: 15-12-16
 * Time: 23:27
 */

class Q_Core_Config_Ini implements Q_Core_Config_Interface
{

    protected $_data;

    protected $_section;

    protected $_sectionData;

    protected $_nestSeparator = '.';

    public function __construct($filename, $section = null)
    {
        $this->section($section);
        $this->_data = $this->_parseIniFile($filename);
        $this->_processSection();
    }

    public function section($section)
    {
        if ($section != null && (is_string($section) || is_int($section))) {
            $this->_section = $section;
        }
        return $this;
    }

    public function get($key, $default = null)
    {
        $result = $default;
        if (array_key_exists($this->_section, $this->_sectionData) && array_key_exists($key, $this->_sectionData[$this->_section])) {
            $result = $this->_sectionData[$this->_section][$key];
        }
        return $result;
    }

    public function getSectionData()
    {
        $result = array();
        if (array_key_exists($this->_section, $this->_sectionData)) {
            $result = $this->_sectionData[$this->_section];
        }
        return $result;
    }

    public function count()
    {
        $result = 0;
        if (array_key_exists($this->_section, $this->_sectionData)) {
            $result = count($this->_sectionData[$this->_section]);
        }
        return $result;
    }

    protected function _parseIniFile($filename)
    {
        return parse_ini_file($filename, true);
    }

    protected function _processSection()
    {
        $allSection = array();
        foreach ($this->_data as $key => $val) {
            foreach ($val as $_key => $_val) {
                $process = $this->_processKey($_key);
                if (empty($process)) {
                    continue;
                }
                $allSection[$key][$process['prefix']][$process['key']] = $_val;
            }
        }
        $this->_sectionData = $allSection;
    }

    protected function _processKey($key)
    {
        if (strpos($key, $this->_nestSeparator) !== false) {
            $pieces = explode($this->_nestSeparator, $key, 2);
            if (strlen($pieces[0]) && strlen($pieces[1])) {
                return array(
                    'prefix' => $pieces[0],
                    'key' => $pieces[1]
                );
            }
            throw new Q_Exception("ini key '$key' error.");
        }
        return array();
    }
} 