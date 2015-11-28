<?php

/*
 * 从redis中的日志写入文件
 * @Author fanlibing
 * 
 */

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

class cron_logq {
	
	/*
	 * 构造函数 
	 */

	function __construct() {
		$this->redisdb = new Redisdb_Redisdb();
	}

	/*
	 * 作业入口 
	 */

	function run($argv) {
		
		while(true){
			
			// 从redis读取记录
			$row = $this->redisdb->_server()->lpop('web_logq');
			echo $row."\r\n";
			if(empty($row)){
				exit;
			}
			$log = unserialize(base64_decode($row));
			$file = "/home/work/log/" . date("Y-m-d") . "_" . $log['file'] . "_" . $log['method'] . ".txt";
			$fileHandle = fopen($file, "a+");
			
			// 区分文件记入日志
			$info = "[".$log['lever']."] [".$log['time']."] [server] ".$log['server']." [client] ".$log['client']." [info]".$log['info']."\r\n";
			echo $info;
			fwrite($fileHandle, $info);
			fclose($fileHandle);
			
		}
		
	}

}

$cron = new cron_logq();
try {
	$cron->run($argv);
} catch (ZException $e) {
	
}
