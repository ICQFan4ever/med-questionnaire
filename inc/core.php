<?php

require_once 'settings.php';

@mysql_connect($_DB['host'], $_DB['user'], $_DB['password']) or die(mysql_error());
mysql_select_db($_DB['db']) or die(mysql_error());
mysql_set_charset($_DB['charset']);


class tg
	{
		public $api;
		
		public function __construct($api)
			{
				$this -> api = $api;
			}
		
		public function request($method, $args)
			{
				$url = 'https://api.telegram.org/bot'.$this -> api.'/';
				$args['method'] = $method;
				
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($args));
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
				$a = curl_exec($ch);