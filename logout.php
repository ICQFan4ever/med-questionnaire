<?php
require_once 'inc/core.php';
autOnly();

setcookie('session', '', time() - 86400, '/', $_SITE['domain']);

if(isset($_GET['all']))
	{
		mysql_query("UPDATE `doctors` SET `session` = '' WHERE `id` = ".$_INFO['id']);
	}

Redirect('/?logout');