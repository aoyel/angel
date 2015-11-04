<?php
define("APP_PATH", __DIR__);
define("APP_DEBUG", true);

require APP_PATH.'/vendor/angel/App.php';
$config = include APP_PATH.'/config/web.php';


(new \angel\base\Application($config))->run();

