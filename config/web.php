<?php
return [
	'basePath'=>APP_PATH,
	'appNamespace'=>'app',
	'applicationPath'=>APP_PATH.DIRECTORY_SEPARATOR."app",
	
	'redis'=>[
		'host'=>'localhost',
		'port'=>''
	],
	
	'components'=>[
		'cache'=>[
			'class'=>'\angel\cache\FileCache',
			'directoryLevel'=>1,
			'keyPrefix'=>'__cache'
		],
		'server'=>[
			'class'=>'\angel\base\Server',
			'host'=>'0,0,0,0',
			'port'=>3927,
			'config'=>[
					'worker_num'=>4,
					'log_file'=>APP_PATH."/runtime/server.log"
			]	
		],
	],
	
	
		
];