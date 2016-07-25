<?php

class Q_Action extends Yaf_Controller_Abstract
{
    private $_headlink;

    private $_headscript;

    private $_headTitle;

    private $_headmeta;

    private $_qdebug;

    private $_qdebugMicrotime;

    /**
     * Meta
     * @return Q_View_Header_Meta
     */
    public function _headMeta()
    {
        if (empty($this->_headmeta)) {
            $this->_headmeta = new Q_View_Header_Meta();
            $this->getView()->assign("_headmeta", $this->_headmeta);
        }
        return $this->_headmeta;
    }

    /**
     * Script
     * @return Q_View_Header_Script
     */
    public function _headScript()
    {
        if (empty($this->_headscript)) {
            $this->_headscript = new Q_View_Header_Script();
            $this->getView()->assign("_headScript", $this->_headscript);
        }
        return $this->_headscript;
    }

    /**
     *
     * @return Q_View_Header_Link
     */
    public function _headLink()
    {
        if (empty($this->_headlink)) {
            $this->_headlink = new Q_View_Header_Link();
            $this->getView()->assign("_headLink", $this->_headlink);
        }
        return $this->_headlink;
    }

    /**
     * Title
     * @return Q_View_Header_Title
     */
    public function _headTitle($title = null)
    {
        if (empty($this->_headTitle)) {
            $this->_headTitle = new Q_View_Header_Title();
            $this->getView()->assign("_headTitle", $this->_headTitle);
        }
        return $this->_headTitle->headTitle($title);
    }

    public function helpHeader($code = 200, $msg = '')
    {
        header("content-type: text/html; charset=utf-8");
        $codeStr = "";
        switch ($code) {
            case 404 :
                $codeStr = " 404 Not Found";
                break;
            case 403 :
                $codeStr = " 403 Forbidden";
                break;
            case 500 :
                $codeStr = " 500 Internal Server Error";
                break;
            default :
                $codeStr = " 200 OK";
                break;
        }
        header("HTTP/1.0" . $codeStr);
        echo $msg;
    }

    /**
     * js 跳转 并 提示
     *
     * @param String $url
     * @param String $expression
     */
    protected function helpJsRedirect($message = '', $expression = "history.back()")
    {
        header("content-type: text/html; charset=utf-8");
        if ($message != "") {
            $message = Q_Utils_String::format()->addslash($message);
            $message = str_replace("\n", "\\n", $message);
            echo "<script language=\"javascript\">";
            echo "alert(\"{$message}\");";
            echo "</script>";
        }
        if ($expression != "") {
            echo "<script language=\"javascript\">\n";
            echo $expression . "\n";
            echo "</script>";
        }
        exit();
    }

    /**
     * 跳转
     *
     * @param String $url
     * @param String $message
     */
    protected function helpGo($url, $message = '')
    {
        if (!empty($message)) {
            header("content-type: text/html; charset=utf-8");
            $message = Q_Utils_String::format()->addslash($message);
            $message = str_replace("\n", "\\n", $message);
            echo "<script language=\"javascript\">";
            echo "alert(\"{$message}\");";
            echo "</script>";
        }
        header('Location: ' . $url);
        exit();
    }

    /**
     * refresh跳转
     *
     * @param String $url
     * @param String $message
     * @param Integer $content
     */
    protected function helpRefresh($url, $message = '', $content = 0, $show = false)
    {
        if (!empty($message)) {
            header("content-type: text/html; charset=utf-8");
            $message = Q_Utils_String::format()->addslash($message);
            $message = str_replace("\n", "\\n", $message);
            echo "<script language=\"javascript\">";
            echo "alert(\"{$message}\");";
            echo "</script>";
        }
        echo "<script language=\"javascript\">";
        echo "window.location.href='{$url}';";
        echo "</script>";
        if ($show == false) {
            exit();
        }
    }

    /**
     * JSON输出
     *
     * @param String $caption
     * @param Integer $code
     * @param mixed $content
     */
    protected function helpJsonResult($code, $message = null, $data = null)
    {
        header('Content-type: application/json');
        echo json_encode(Q_Utils_Function::result($code, $message, $data));
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        exit();
    }

    /**
     * JSON输出
     *
     * @param String $caption
     * @param Integer $code
     * @param mixed $content
     */
    protected function helpJson($data = null)
    {
        header('Content-type: application/json');
        echo json_encode($data);
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        exit();
    }

    /**
     * JSONP Callback输出,用于远程调用
     *
     * @param String $caption
     * @param Integer $code
     * @param mixed $content
     */
    protected function helpJsonCallbackResult($callbackString, $code, $message = null, $data = null)
    {
        echo $callbackString . "(";
        echo json_encode(Q_Utils_Function::result($code, $message, $data));
        echo ")";
        exit();
    }


    /**
     * 转义', "
     *
     * @param string $string
     * @return string
     */
    protected function helpAddslashes($string)
    {
        return get_magic_quotes_gpc() ? $string : addslashes($string);
    }

    /**
     * refresh跳转
     *
     * @param String $url
     * @param String $message
     * @param Integer $content
     */
    protected function helpRefreshNotExit($url, $message = '', $content = 0)
    {
        if (!empty($message)) {
            header("content-type: text/html; charset=utf-8");
            $message = Q_Utils_String::format()->addslash($message);
            $message = str_replace("\n", "\\n", $message);
            echo "<script language=\"javascript\">";
            echo "alert(\"{$message}\");";
            echo "</script>";
        }
        echo "<meta http-equiv=\"refresh\" content=\"{$content};url={$url}\">";
    }

    private function _g($key, $default = '')
    {
        return $this->getRequest()->getQuery($key, $default);
    }

    public function _debugStart($debug = 'xhprof')
    {
        if ($this->_g('_qdebug') == 'action') {
            $this->_qdebugMicrotime = microtime(true);
            $this->_qdebug = new Q_Debug_Xhprof_Stat();
            $this->_qdebug->setUrl('http://xhprof.debug.yohobuy.com')->start();
        }
    }

    public function _debugEnd()
    {
        if ($this->_g('_qdebug') == 'action') {
            $this->_qdebug->end();
            echo '<br /> Action Time : ' . (microtime(true) - $this->_qdebugMicrotime) . '<br />';
        }
    }

    /**
     * 开启session
     */
    public function sessionStart()
    {
        $sessionID = session_id();
        if (empty($sessionID)) {
            $qinSession = new Q_Core_Session();
            //因为要兼容老站点认证信息，暂时不做session共享
//             session_set_save_handler(
//                 array(&$qinSession, "open"),
//                 array(&$qinSession, "close"),
//                 array(&$qinSession, "read"),
//                 array(&$qinSession, "write"),
//                 array(&$qinSession, "destroy"),
//                 array(&$qinSession, "gc")
//             );
            session_start();
        }
    }
}