<?php

require_once 'settings.php';

@mysql_connect($_DB['server'], $_DB['username'], $_DB['password']) or die(mysql_error());
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
				var_dump($a);
			}
		
		public function send($id_chat, $text, $parse_mode = '', $id_message = '')
			{
				$toSend = array();
				$toSend['chat_id'] = $id_chat;
				$toSend['parse_mode'] = empty($parse_mode) ? 'HTML' : 'Markdown';
				$toSend['reply_to_id_message'] = empty($id_message) ? '' : $id_message;
				$toSend['text'] = $text;
				
				return $send = $this -> request('sendMessage', $toSend);
			}
	}


function setTitle($title = 'Default Title')
	{
		define('TITLE', $title);
	}

function getHeader()
	{
		$_PAGE['title'] = defined('TITLE') ? TITLE : 'Опросник';
		?>
<!doctype html>
<html lang="en" class="h-100">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
		<meta name="generator" content="Hugo 0.79.0">
		<title><?=$_PAGE['title']?></title>

		



		<!-- Bootstrap core CSS -->
		<link href="/design/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">

		<!-- Favicons -->
		<meta name="theme-color" content="#7952b3">


		<style>
			.bd-placeholder-img {
				font-size: 1.125rem;
				text-anchor: middle;
				-webkit-user-select: none;
				-moz-user-select: none;
				user-select: none;
			}

			@media (min-width: 768px) {
				.bd-placeholder-img-lg {
				font-size: 3.5rem;
				}
			}
			.btn-group-xs > .btn, .btn-xs {
				padding: .40rem .4rem;
				font-size: .7rem;
				line-height: .5;
				border-radius: .2rem;
			}
		</style>


		<!-- Custom styles for this template -->
		<link href="sticky-footer-navbar.css" rel="stylesheet">
	</head>
	<body class="d-flex flex-column h-100">

		<header>
			<!-- Fixed navbar -->
			<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
				<div class="container">
					<a class="navbar-brand" href="/">KZ</a>
					<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
					</button>
					<div class="collapse navbar-collapse" id="navbarCollapse">
						<?php
						if(isAut())
							{
								?>
						<ul class="navbar-nav me-auto mb-2 mb-md-0">
							<li class="nav-item">
								<a class="nav-link" href="/lk">Личный кабинет</a>
							</li>
						</ul>
						<ul class="navbar-nav ml-auto">
							<li class="nav-item dropdown">
								<a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-bs-toggle="dropdown" aria-expanded="false">Выход</a>
								<ul class="dropdown-menu" aria-labelledby="dropdown01">
									<li><a class="dropdown-item" href="/logout">На этом устройстве</a></li>
									<li><a class="dropdown-item" href="/logout/everywhere">Везде</a></li>
								</ul>
							</li>
						</ul>
								<?php
							}
						else
							{
								?>
								<ul class="navbar-nav ml-auto">
									<li class="nav-item">
										<a class="nav-link" href="/login">Вход</a>
									</li>
								</ul>
								<?php
							}
						?>
						
					</div>
				</div>
			</nav>
		</header>

		<!-- Begin page content -->
		<main class="flex-shrink-0">
		<div class="container">
			<br /><h1 class="mt-5"><?=$_PAGE['title']?></h1>
	<?php
	}

function getFooter()
	{
		?>

</div>
</main>

<footer class="footer mt-auto py-3 bg-light">
	<!--<div class="container">
	<span class="text-muted">Place sticky footer content here.</span>
	</div>-->
</footer>


<script src="/design/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>

</body>
</html>
	<?php
	}

function Redirect($loc = '/')
	{
		header('Location: '.$loc);
		die;
	}

if(isset($_COOKIE['session']))
	{
		if(preg_match('#^([a-fA-F0-9]{32})$#iu', $_COOKIE['session']))
			{
				$db['search'] = $_COOKIE['session'];
				$q = mysql_query("SELECT * FROM `doctors` WHERE `session` = '".$db['search']."'");
				
				if(mysql_num_rows($q) == 1)
					{
						$aut = true;
						$_INFO = mysql_fetch_assoc($q);
						define('LEVEL', $_INFO['level']);
					}
				else
					{
						$aut = false;
					}
			}
		else
			{
				$aut = false;
			}
	}
else
	{
		$aut = false;
	}

define('AUT', $aut);

function isAut()
	{
		return AUT;
	}

function autOnly()
	{
		if(!isAut())
			{
				Redirect('/?autOnly');
			}
	}

function checkAccess($req = 1)
	{
		if(!isAut())
			{
				Redirect('/?noAutForAccessCheck');
			}
		else
			{
				if(LEVEL < $req)
					{
						fatalError('У вас нет прав для просмотра этой страницы');
					}
			}
	}

function showError($text = '')
	{
		echo '<br /><div class="alert alert-danger" role="alert">'.$text.'</div><br />';
	}

function showSuccess($text = '')
	{
		echo '<br /><div class="alert alert-success" role="alert">'.$text.'</div><br />';
	}

function showWarning($text = '')
	{
		echo '<br /><div class="alert alert-warning" role="alert">'.$text.'</div><br />';
	}

function showFormError($array)
	{
		if(empty($array))
			{
				echo '';
			}
		else
			{
				echo showError(implode('<br />', $array));
			}
	}

function fatalError($text = 'Ошибка')
	{
		setTitle('Ошибка');
		getHeader();
		echo showError($text);
		getFooter();
		exit;
	}

function dbFilter($string, $length)
	{
		return mb_substr(htmlspecialchars(mysql_real_escape_string($string)), 0, $length, 'utf-8');
	}

function passGen($length = 8)
	{
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMOPQRSTUVWXYZ0123456789';
		$len = mb_strlen($chars, 'utf-8');
		$return = '';
		for($i = 1; $i <= $length; $i++)
			{
				$return .= $chars[mt_rand(0, $len - 1)];
			}
		
		return $return;
	}