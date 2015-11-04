<?php

namespace app\controllers;
use app\components\BaseController;

class IndexController extends BaseController{
	
	public function actionIndex(){
		setcookie("haha","haha");
		setcookie("aa","cc");
		session_start();
		$_SESSION['a'] = "c";
		$this->render();
	}
}
?>