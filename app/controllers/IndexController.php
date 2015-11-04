<?php

namespace app\controllers;
use app\components\BaseController;

class IndexController extends BaseController{
	
	public function actionIndex(){
		return $this->render();
	}
}
?>