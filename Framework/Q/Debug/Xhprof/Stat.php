<?php

class Q_Debug_Xhprof_Stat
{
    private $url = 'http://xhprof.test.com';

    private $log_path = '/Data/tmp/xhprof';

    private $log_file = '/qin.xhprof.log';

    public function __construct($url = 'http://xhprof.test.com')
    {
        include_once __DIR__ . "/utils/xhprof_lib.php";
        include_once __DIR__ . "/utils/xhprof_runs.php";
        $this->url = $url;
    }

    public function start()
    {
        xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
    }

    public function end($namespace = 'xhprof_foo')
    {
        $xhprof_data = xhprof_disable();
        $xhprof_runs = new XHProfRuns_Default();
        $run_id = $xhprof_runs->save_run($xhprof_data, $namespace);
        echo "<br /><a href='" . $this->url . "/index.php?run=" . $run_id . "&source=" . $namespace . "' target='_blank'><font color='red'>-= 查看 =-</font></a>";
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function diff()
    {

    }
}