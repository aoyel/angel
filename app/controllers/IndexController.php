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
		
// 		$db = Angel::$app->db;
		
// 		echo $db->delete("user", ['id'=>333]);
		
// 		var_dump($db->get("user", 333));
		
		//var_dump($db->get("user", 1));		
// 		$res = $db->update("test",[
// 			'name'=>'1',
// 			'value'=>"sad'ds'd'dsasd'dsa'ds'dsa'dsa'dsa",
// 			'description'=>'aa'
// 		])->where(["id"=>["in","1,2,3"]])->execute();
		
// 		$db->table("test")->where([
// 			"id"=>1
// 		])->query();
		//echo "a";
		
// 		$db = Angel::$app->db;
// 		$time = microtime(true);
// 		$db->beginTransaction();
// 		for ($i = 0; $i < 1000 ;$i++){
// 			$db->insert("user",[
// 				"name"=>1,
// 				"password"=>1,
// 				"status"=>1
// 			])->execute();
// 		}
// 		$db->commit();
// 		echo microtime(true) - $time;
		
		
		/**
		 */
		//$db = Angel::app()->db;
		//var_dump($db->query("SHOW FULL COLUMNS FROM `tbl_user`"));
// 		echo $db->getCount("user", ['name'=>1]);
// 		$data = $db->from("user")->limit(5)->orderBy("password","desc")->select();
// 		var_dump($data);
		$this->render();
	}
}
?>