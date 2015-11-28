<?php

/**
 * logq.class.php
 * 将日志写入redis
 * 
 * @author finepvol
 * 
 */
class Logq {

	// 服务器ip
	public $server_ip;
	
	// 用户ip
	public $client_ip;
	
	// 是否开启log
	public $is_log;
	
	// 记录日志标识失效时间
	public $log_expire;

	/**
	 * 构 造 函 数
	 */
	function __construct() {

		try {
			$this->redisdb = new Redisdb_Redisdb();
			$this->ip = new Ip_Ip();
			
			$this->redisdb->_server();

			// 关闭log
			if (isset($_GET["logq_stop"])) {
				$this->redisdb->expire("is_web_logq", 0);
				return;
			}

			// 获取服务器ip
			if ($_SERVER['SERVER_ADDR']) {
				$this->server_ip = $_SERVER['SERVER_ADDR'];
			} else {
				$this->server_ip = $_SERVER['LOCAL_ADDR'];
			}
			
			$this->client_ip = $this->ip->get_ip();
			
			// 开始记录log
			$is_web_logq = $this->redisdb->get("is_web_logq");
			if ($is_web_logq) {
				$this->is_log = true;
				return;
			}

			// 打开log
			if (isset($_GET["logq_start"])) {

				$this->is_log = true;
				$this->redisdb->set("is_web_logq", "true");
				if (isset($_GET['logq_time'])) {
					$this->log_expire = intval($_GET['logq_time']);
				} else {
					$this->log_expire = 1800; // 默认开启半小时日志
				}
				$this->redisdb->expire("is_web_logq", $this->log_expire);
				return;
			}

			$this->is_log = false;
		} catch (Exception $e) {
			
		}
	}

	/**
	 * 记录日志
	 * @params
	 * file 日志文件名称
	 * method 方法名
	 * info 日志内容
	 * lever 日志等级 推荐日志等级（error, warning, notice, log, 默认为log）
	 */
	public function info($file, $method, $info, $lever = "log") {

		try {
			// 如果打开了log
			if ($this->is_log) {
				$info = array(
						"file" => $file,
						"lever" => $lever,
						"time" => date("Y-m-d H:i:s"),
						"server" => $this->server_ip,
						"client" => $this->client_ip,
						"method" => $method,
						"info" => $info
				);

				$this->redisdb->rpush("web_logq", base64_encode(serialize($info)));
			}
		} catch (Exception $e) {
			
		}
	}

}
