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
			}
		
		public function send($id_chat, $text, $parse_mode = '', $id_message = '')
			{
				$toSend = array();
				$toSend['id_chat'] = $id_chat;
				$toSend['parse_mode'] = empty($parse_mode) ? 'HTML' : 'Markdown';
				$toSend['reply_to_id_message'] = empty($id_message) ? '' : $id_message;
				$toSend['text'] = $text;
				
				$send = $this -> request('sendMessage', $toSend);
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
		</style>


		<!-- Custom styles for this template -->
		<link href="sticky-footer-navbar.css" rel="stylesheet">
	</head>
	<body class="d-flex flex-column h-100">

		<header>
			<!-- Fixed navbar -->
			<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
				<div class="container-fluid">
					<a class="navbar-brand" href="#">Title</a>
					<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
					</button>
					<div class="collapse navbar-collapse" id="navbarCollapse">
						<ul class="navbar-nav me-auto mb-2 mb-md-0">
							<li class="nav-item active">
								<a class="nav-link" aria-current="page" href="#">Home</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="#">Link</a>
							</li>
							<li class="nav-item">
								<a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
							</li>
						</ul>
						<!--<form class="d-flex">
							<input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
							<button class="btn btn-outline-success" type="submit">Search</button>
						</form>-->
					</div>
				</div>
			</nav>
		</header>

		<!-- Begin page content -->
		<main class="flex-shrink-0">
		<div class="container">
			<h1 class="mt-5"><?=$_PAGE['title']?></h1>
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


</body>
</html>
	<?php
	}