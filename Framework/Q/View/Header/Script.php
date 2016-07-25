<?php

class Q_View_Header_Script extends Q_View_Header_Abstract
{
    protected $_attributes = array(
        'charset', 'defer', 'language', 'src'
    );

    protected $_attrList = array(
        'charset="UTF-8"',
        'type="text/javascript"',
    );

    protected $_fieldsFile = array();

    public function offsetSetFile($index, $src, $conditional = '')
    {
        $this->_item[$index] = $src;
        if (!empty($conditional)) {
            $this->_target_conditional[$index] = $conditional;
        }
        return $this;
    }

    /**
     * 保留输出文件
     * @param array $fieldsIDS
     * @return $this
     */
    public function fieldsFile(array $fieldsIDS)
    {
        $this->_fieldsFile = $fieldsIDS;
        return $this;
    }

    public function appendFile($src)
    {
        $this->_item[] = $src;
    }

    public function setAttr(array $attr)
    {
        foreach ($attr as $_attr => $val) {
            if (isset($this->_attrList[$_attr]) && !empty($val) && is_string($val)) {
                $this->_attrList[] = sprintf(' %s="%s"', $_attr, htmlspecialchars($val, ENT_COMPAT, $this->enc));
            }
        }
        return $this;
    }

    public function conditional($conditional = 'lt IE 9')
    {
        assert(is_string($conditional));
        $this->conditional = $conditional;
        return $this;
    }

    public function __toString()
    {

        $html = '';
        $_attrString = implode(' ', $this->_attrList);
        foreach ($this->_item as $key => $val) {
            if (empty($val)) {
                continue;
            }
            $_script = '<script src="' . $val . '" ' . $_attrString . '></script>' . PHP_EOL;
            if (isset($this->_target_conditional[$key])) {
                $html .= '<!--[if ' . $this->_target_conditional[$key] . ']> ' . PHP_EOL . $_script . '<![endif]-->' . PHP_EOL;
            } else {
                $html .= $_script;
            }
        }
        if (!empty($this->conditional) && empty($this->_target_conditional)) {
            $html = '<!--[if ' . $this->conditional . ']> ' . PHP_EOL . $html . '<![endif]-->' . PHP_EOL;
        }
        return $html;
    }
}