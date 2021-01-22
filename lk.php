<?php
require_once 'inc/core.php';
autOnly();

if(isset($_GET['mode']))
	{
		if($_GET['mode'] == 'edit')
			{
				$error = array();
				
				if(isset($_POST['button']))
					{
						/*
							`id` INT AUTO_INCREMENT PRIMARY KEY,
							`id_area` INT,
							`level` TINYINT,
							`login` TINYTEXT,
							`password` TINYTEXT,
							`session` TINYTEXT,
							`name` TINYTEXT,
							`phone` TINYTEXT,
							`tg_id` BIGINT,
							`last_seen` INT,
							`deleted` TINYINT
						*/
						
						if(isset($_POST['login']))
							{
								$db['login'] = dbFilter($_POST['login'], 100);
								// проверяем не занято ли
								$q_check_login = mysql_query("SELECT * FROM `doctors` WHERE `login` = '".$db['login']."' AND `id` != ".$_INFO['id']);
								if(mysql_num_rows($q_check_login) != 0)
									{
										$error[] = 'Этот логин уже занят';
									}
							}
						else
							{
								$error[] = 'Выберите логин для входа на сайт';
							}
						
						if(isset($_POST['password']))
							{
								$db['password'] = md5(md5($_POST['password']));
								$db['session'] = '';
							}
						else
							{
								$db['password'] = $_INFO['password'];
								$db['session'] = $_INFO['session'];
							}
						
						if(isset($_POST['name']))
							{
								$db['name'] = dbFilter($_POST['name'], 100);
							}
						else
							{
								$error[] = 'Укажите свои ФИО';
							}
						
						if(isset($_POST['phone']))
							{
								$db['phone'] = dbFilter($_POST['phone'], 50);
							}
						else
							{
								$error[] = 'Укажите Ваш номер телефона';
							}
						
						if(isset($_POST['tg_id']))
							{
								$db['tg_id'] = dbFilter($_POST['tg_id'], 100);
							}
						else
							{
								$db['tg_id'] = '';
							}
						
						
						if(empty($error))
							{
								if(mysql_query("UPDATE `doctors` SET `login` = '".$db['login']."', `password` = '".$db['password']."', `session` = '".$db['session']."', `name` = '".$db['name']."', `phone` = '".$db['phone']."', `tg_id` = '".$db['tg_id']."' WHERE `id` = ".$_INFO['id']))
									{
										Redirect('/lk');
									}
								else
									{
										fatalError(mysql_error());
									}
							}
					}
				
				setTitle('Редактировать личные данные');
				getHeader();
				showFormError($error);
				?>
				
				<div class="row">
					<form action="/lk/edit" method="post">

						<div class="col-sm-3" style="margin-bottom: 5px;">
							Логин для входа на сайт:<br />
							<input type="text" class="form-control" name="login" placeholder="Логин" value="<?=$_INFO['login']?>" />
						</div>

						<div class="col-sm-3" style="margin-bottom: 5px;">
							Пароль (оставьте пустым, если не хотите его изменять):<br />
							<input type="password" class="form-control" name="password" />
						</div>
						
						<div class="col-sm-3" style="margin-bottom: 5px;">
							ФИО:<br />
							<input type="text" class="form-control" name="name" value="<?=$_INFO['name']?>" />
						</div>
						
						<div class="col-sm-3" style="margin-bottom: 5px;">
							Номер телефона (в любом формате):<br />
							<input type="text" class="form-control" name="phone" value="<?=$_INFO['phone']?>" />
						</div>
						
						<div class="col-sm-3" style="margin-bottom: 5px;">
							Telegram ID (оставьте пустым, если не знаете, что это):<br />
							<input type="text" class="form-control" name="tg_id" value="<?=$_INFO['tg_id']?>" />
						</div>

						<div class="col-sm-3">
							<input type="submit" name="button" value="Сохранить" class="btn btn-primary" />
						</div>

					</form>
				</div>
				
				<?php
				getFooter();
				exit;
			}
		fatalError('wrong mode');
	}


setTitle('Личный кабинет');
getHeader();
?>

<div class="list-group col-sm-4">
	<a href="/lk/edit" class="list-group-item list-group-item-action">Редактировать мои данные</a>
	<?php
	
	if($_INFO['level'] >= 4)
		{
			?>
				<a href="/areas" class="list-group-item list-group-item-action">Участки</a>
				<a href="/doctors" class="list-group-item list-group-item-action">Мед. работники</a>
				<a href="/questions" class="list-group-item list-group-item-action">Вопросы</a>
			<?php
		}
	?>
	<a href="/patients" class="list-group-item list-group-item-action">Пациенты</a>
	<a href="/reports" class="list-group-item list-group-item-action">Отчеты</a>
</div>
<?php
getFooter();