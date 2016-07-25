<?php
/**
 * 框架入口文件
 * User: zhanglirong
 * Date: 2015.07.17
 * Time: 11:28
 */

namespace Q;


class Q_Autoloader
{
    public function __construct()
    {
    	//自动加载类文件
        spl_autoload_register(array($this, 'register'));
    }

    private function register($className)
    {
        if (stristr($className, '\\') === FALSE) {
            $classNamePath = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        } else {
            $parts = explode('\\', $className);
            $classNamePath = implode(DIRECTORY_SEPARATOR, $parts) . '.php';
        }
        $includePath = explode(':', get_include_path());
        foreach ($includePath as $path) {
            $classPath = $path . DIRECTORY_SEPARATOR . $classNamePath;
            if (file_exists($classPath)) {
                //Yaf_Loader::import($classPath);
                require_once($classPath);
                return;
            }
        }
    }

    public function tree($directory)
    {
        $mydir = dir($directory);
        while ($file = $mydir->read()) {
            if ((is_dir($directory . DIRECTORY_SEPARATOR . $file)) AND ($file != ".") AND ($file != "..")) {
                $this->tree("$directory/$file");
            } elseif ($file != "." AND $file != "..") {
                echo $directory . DIRECTORY_SEPARATOR . $file;
            }
        }
        $mydir->close();
    }

    public static function loader()
    {
        new Q_Autoloader();
    }
}

Q_Autoloader::loader();