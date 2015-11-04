<?php



define("ANGEL_PATH", __DIR__);

require ANGEL_PATH.'/Angel.php';


class App  extends \angel\Angel{
}

App::addNamespaceMap("angel", ANGEL_PATH);

spl_autoload_register(['App', 'autoload'], true, true);


?>