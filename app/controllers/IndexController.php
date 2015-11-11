<?php

namespace app\controllers;
use app\components\BaseController;
use angel\helper\StringHelper;

class IndexController extends BaseController{
	public function actionIndex(){
		\App::$app->session->set("a", 1);
		\App::$app->respone->setcookie("test","testkey");
		return $this->render("index");
	}
	
	public function actionCreate(){
		$this->ajaxMsg(1, \App::$app->session->get("a"));
	}
}
?>