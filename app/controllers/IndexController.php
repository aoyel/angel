<?php

namespace app\controllers;
use app\components\BaseController;
use angel\Angel;

class IndexController extends BaseController{
	public function actionIndex(){
		return $this->render("index");
	}
	
	public function actionCreate(){
		$this->ajaxMsg(1, ['aa']);
	}
}
?>