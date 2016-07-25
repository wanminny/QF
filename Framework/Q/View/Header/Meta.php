<?php

/**
 * User: parasol.zhang
 * Date: 15-1-14
 * Time: 8:45
 */
class Q_View_Header_Meta extends Q_View_Header_Abstract
{

    private $_requiredKeys = array();

    protected $_modifierKey = array('content', 'name', 'http-equiv', 'charset', 'property', 'lang', 'scheme');

    public function appendMeta($content, $metaKey, $metaVal, $modifiers = array())
    {
        assert(is_scalar($content));
        assert(is_scalar($metaKey));
        assert(is_scalar($metaVal));
        $this->_requiredKeys[] = array(
            'required' => array(
                'content' => $content,
                'type_name' => $metaKey,
                'type_val' => $metaVal
            ),
            'modifiers' => $this->_checking($modifiers)
        );
        return $this;
    }

    private function _checking(array $modifiers)
    {
        $_modifiers = array();
        foreach ($modifiers as $key => $val) {
            if (isset($this->_modifierKey[$key])) {
                $_modifiers[] = array(
                    $key => $val
                );
            }
        }
        return $_modifiers;
    }

    public function __toString()
    {
        $html = '';
        foreach ($this->_requiredKeys as $_key => $_val) {
            $modifiersString = '';
            foreach ($_val['modifiers'] as $m_key => $m_val) {
                $modifiersString .= $m_key . '="' . $this->_escape($m_val) . '" ';
            }
            $tpl = '<meta %s="%s" content="%s" %s/>'.PHP_EOL;
            $html .= sprintf(
                $tpl,
                $_val['required']['type_name'],
                $this->_escape($_val['required']['type_val']),
                $this->_escape($_val['required']['content']),
                $modifiersString
            );
        }
        return $html;
    }
}