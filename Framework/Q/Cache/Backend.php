<?php
/**
 * 后端缓存
 *
 * example：
 * <pre>
 *
 * </pre>
 *
 * @name Q_Cache_Backend
 * @version $index (  2015-9-12 上午12:27:24  )
 * @package Q.Cache.Backend
 * @author zhanglirong
 * @since 1.0 
 */

class Q_Cache_Backend
{

    /**
     * 判断是否开启了apc.enabled选项
     *
     * 用来取代 isEnabed
     *
     * @return boolean
     * @since 0.2.1
     */
    public static function isEnabled()
    {
        return (bool)ini_get("apc.enabled");
    }

    /**
     *
     * 获取缓存
     * @param String $key
     * @return boolean
     */
    static public function load($key)
    {
        return apc_fetch($key);
    }

    /**
     *
     * 存入缓存
     * @param String $key
     * @param Mixed $value
     * @param Integer $expire
     * @return boolean
     */
    static public function save($key, $value, $expire = 7200)
    {
        return apc_store($key, $value, $expire);
    }

    /**
     *
     * 清除
     * @param String $key
     * @return boolean
     */
    static public function remove($key)
    {
        return apc_delete($key);
    }

    /**
     * 缓存文件
     * @static
     * @param $fileName
     * @return bool
     */
    static public function compileFile($fileName, $atomic = true)
    {
        return apc_compile_file($fileName, $atomic);
    }

    /**
     * 删除缓存文件
     * @static
     * @param $fileName
     * @return bool|string[]
     */
    static public function deleteFile($fileName)
    {
        return apc_delete_file($fileName);
    }

    /**
     * 添加一个缓存
     * @static
     * @param $key
     * @param $var
     * @param $ttl
     * @return bool
     */
    static public function add($key, $var, $ttl = 0)
    {
        return apc_add($key, $var, $ttl);
    }

    /**
     * 将数据转成二进制文件存储
     * @static
     * @param array $files
     * @param array $user_vars
     * @return bool|null|string
     */
    static public function binDump(array $files, array $user_vars)
    {
        return apc_bin_dump($files, $user_vars);
    }

    /**
     * 载入文件转成二进制文件存储
     * @static
     * @param array $files
     * @param array $user_vars
     * @param $filename
     * @param $context
     * @param int $flags
     * @return bool|int
     */
    static public function binDumpFile(array $files, array $user_vars, $filename, $context, $flags = 0)
    {
        return apc_bin_dumpfile($files, $user_vars, $filename, $flags, $context);
    }

    /**
     * 加载二级制数据
     * @static
     * @param $data
     * @param $flags
     * @return bool APC_BIN_VERIFY_MD5 | APC_BIN_VERIFY_CRC32
     */
    static public function binLoad($data, $flags)
    {
        return apc_bin_load($data, $flags);
    }

    /**
     * 加载二级制文件
     * @static
     * @param $filename
     * @param $context
     * @param $flags
     * @return bool APC_BIN_VERIFY_CRC32 | APC_BIN_VERIFY_MD5
     */
    static public function binLoadFile($filename, $context, $flags)
    {
        return apc_bin_loadfile($filename, $context, $flags);
    }

    static public function cas($key, $old, $new)
    {
        return apc_cas($key, $old, $new);
    }

    static public function dec($key, &$success, $step = 1)
    {
        return apc_dec($key, $step, $success);
    }

    static public function define($key, array $constants, $case_sensitive = true)
    {
        return apc_define_constants($key, $constants, $case_sensitive);
    }

    /**
     * 检查APC中是否存在某个或者某些key
     * @static
     * @param $keys
     * @return bool|string[]
     */
    static public function exists($keys)
    {
        return apc_exists($keys);
    }

    static public function inc($key, &$success, $step = 1)
    {
        return apc_inc($key, $step, $success);
    }

    static public function loadConstants($key, $case_sensitive = true)
    {
        return apc_load_constants($key, $case_sensitive);
    }
}