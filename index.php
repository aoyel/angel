<?php
define("APP_PATH", __DIR__);
define("APP_DEBUG", true);
define("XHPROF_DEBUG", false);

require APP_PATH.'/vendor/autoload.php';
require APP_PATH.'/angel/App.php';
$config = include APP_PATH.'/config/web.php';

if(XHPROF_DEBUG)
	xhprof_enable();

(new \angel\base\Application($config))->run();

if(XHPROF_DEBUG){
	$xhprof_data = xhprof_disable();
	include_once APP_PATH . "/xhprof_lib/utils/xhprof_lib.php";
	include_once APP_PATH . "/xhprof_lib/utils/xhprof_runs.php";
	$xhprof_runs = new XHProfRuns_Default();
	$run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_foo");
}