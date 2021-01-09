<?php
require_once 'inc/core.php';

if(isAut())
	{
		Redirect('/?autAlready');
	}

$error = array();

if(isset($_POST['button']))
	{	
		if(isset($_POST['login']) && isset($_POST['password']))
			{
				$query['login'] = dbFilter($_POST['login'], 30);
				$query['password'] = md5(md5($_POST['password']));
				
				$q = mysql_query("SELECT * FROM `doctors` WHERE `login` = '".$query['login']."' AND `password` = '".$query['password']."'");
				if(mysql_num_rows($q) == 1)
					{
						$_INFO = mysql_fetch_assoc($q);
						if(!empty($_INFO['session']))
							{
								$session = $_INFO['session'];
								setcookie('session', $session, time() + 86400 * 3, '/', $_SITE['domain']);
							}
						else
							{
								$session = md5(microtime().md5(time().date('H:i:s')).md5(microtime().md5(microtime()).rand(10000000, 99999999).mt_rand(100,99).md5(md5(time().date('d.m.Y')))));
								setcookie('session', $session, time() + 86400 * 3, '/', $_SITE['domain']);
								mysql_query("UPDATE `doctors` SET `session` = '".$session."' WHERE `id` = ".$_INFO['id']);
							}
						Redirect('/lk');
					}
			}
		else
			{
				$error[] = 'Введите имя пользователя и пароль';
			}
	}

setTitle('Вход');
getHeader();

showFormError($error);

?>


<div class="row">
	<form action="/login" method="post">

		<div class="col-sm-3" style="margin-bottom: 5px;">
			<input type="text" class="form-control" name="login" placeholder="Логин" />
		</div>

		<div class="col-sm-3" style="margin-bottom: 5px;">
			<input type="password" class="form-control" name="password" placeholder="Пароль" />
		</div>

		<div class="col-sm-3">
			<input type="submit" name="button" value="Вход" class="btn btn-primary" />
		</div>

	</form>
</div>

<?php

getFooter();