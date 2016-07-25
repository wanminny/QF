<?php

class Q_View_Header_Link extends Q_View_Header_Abstract
{
    protected $_mediaTypes = array(
        'charset', 'href', 'hreflang', 'id', 'media', 'rel', 'rev', 'type', 'title', 'extras'
    );

    protected $_mediaList = array(
        'rel="stylesheet"',
        'type="text/css"',
        'media="screen"'
    );

    public function offsetSetFile($index, $src, $conditional = '')
    {
        $this->_item[$index] = $src;
        if (!empty($conditional)) {
            $this->_target_conditional[$index] = $conditional;
        }
        return $this;
    }

    public function appendFile($src)
    {
        $this->_item[] = $src;
    }

    public function setMedia(array $media)
    {
        foreach ($media as $_media => $val) {
            if (isset($this->_mediaTypes[$_media]) && !empty($val) && is_string($val)) {
                $this->_mediaList[] = sprintf(' %s="%s"', $_media, htmlspecialchars($val, ENT_COMPAT, $this->enc));
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
        $_mediaString = implode(' ', $this->_mediaList);
        foreach ($this->_item as $key => $val) {
            if (empty($val)) {
                continue;
            }
            $linkString = '<link href="' . $val . '" ' . $_mediaString . ' />' . PHP_EOL;
            if (isset($this->_target_conditional[$key])) {
                $html .= '<!--[if ' . $this->_target_conditional[$key] . ']> ' . PHP_EOL . $linkString . '<![endif]-->' . PHP_EOL;
            } else {
                $html .= $linkString;
            }
        }
        if (!empty($this->conditional) && empty($this->_target_conditional)) {
            $html = '<!--[if ' . $this->conditional . ']>' . PHP_EOL . $html . '    <![endif]-->' . PHP_EOL;
        }
        return $html;
    }
}