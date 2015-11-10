#ABOUT
angel is a fase php framework
it can be run nginx and php server(base on swoole)

#USER
##Work on nginx:
	edit nginx config file and add you domain to host,and then dir point to \path\to\angel\,

##Work on php 
	\path\to\php index.php 



#CONFIG
	'server' => [
			'class' => '\angel\base\Server',
			'host' => '0,0,0,0', //bind ip
			'port' => 3927,      //run port
			'config' => [ 
					'worker_num' => 4,
					'log_file' => APP_PATH . "/runtime/server.log" 
			] 
	],


