<?php

namespace app\controllers;
use app\components\BaseController;
use angel\Angel;

class IndexController extends BaseController{
	public function actionIndex(){
// 		$t = microtime(true);
// 		for ($i=0;$i<1000000;$i++){
// 			\App::debug("this is logger test,current number is:".rand(1, 100));
// 		}		
		//echo microtime(true) - $t;	
		
		//INSERT INTO tbl_test (`id`, `name`, `value`, `description`) VALUES (`1`,`小明`,`dsd`,`aa`)
		
		$db = Angel::$app->db;
		
		$res = $db->update("test",[
			'name'=>'1',
			'value'=>"sad'ds'd'dsasd'dsa'ds'dsa'dsa'dsa",
			'description'=>'aa'
		])->where(["id"=>["in","1,2,3"]])->execute();
		
		$db->table("test")->where([
			"id"=>1
		])->query();
		
		
		
		//var_dump($db);
	
		//$this->render();
	}
}
?>