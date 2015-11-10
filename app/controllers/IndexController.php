<?php

namespace app\controllers;
use app\components\BaseController;
use angel\helper\StringHelper;

class IndexController extends BaseController{
	public function actionIndex(){
// 		for($i = 0; $i < 1000 ;$i++){
// 			\App::$app->respone->setcookie(StringHelper::randString(6,StringHelper::STRING_RAND_TYPE_UPPER_WORD),StringHelper::randString(5,StringHelper::STRING_RAND_TYPE_UPPER_WORD));
// 		}
		session_start();
		$_SESSION['a'] = 1;
		return $this->render("index");
	}
	
	public function actionCreate(){
		session_start();
		$this->ajaxMsg(1, $_SESSION['a']);
	}
}
?>