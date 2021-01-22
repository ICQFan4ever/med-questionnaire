<?php

require_once 'inc/core.php';

autOnly();
checkAccess(4);

/*
mode:
	add
	edit +/id
	remove +/id
	restore +/id
	doctors +/id
	patients +/id
*/
//
if(isset($_GET['mode']))
	{
		if($_GET['mode'] == 'add')
			{
				$error = array();
				
				if(isset($_POST['button']))
					{
						if(isset($_POST['title']))
							{
								$db['title'] = dbFilter($_POST['title'], 200);
								$q_check = mysql_query("SELECT * FROM `areas` WHERE `title` = '".$db['title']."'");
								if(mysql_num_rows($q_check) > 0)
									{
										$error[] = 'Такой участок уже есть';
									}
							}
						else
							{
								$error[] = 'Укажите название участка';
							}
						
						if(isset($_POST['tg_id']))
							{
								$db['tg_id'] = dbFilter($_POST['tg_id'], 100);
							}
						else
							{
								$error[] = 'Укажите Telegram ID чата участка';
							}
						
						if(empty($error))
							{
								if(mysql_query("INSERT INTO `areas`(`title`, `tg_id`, `deleted`) VALUES ('".$db['title']."', '".$db['tg_id']."', 0)"))
									{
										Redirect('/areas');
									}
								else
									{
										fatalError(mysql_error());
									}
							}
					}
				
				setTitle('Добавить участок');
				getHeader();
				showFormError($error);
				
				?>
				
				<div class="row">
					<form action="/areas/add" method="post">

						<div class="col-sm-6" style="margin-bottom: 5px;">
							<input type="text" class="form-control" name="title" required="required" placeholder="Название участка" />
						</div>
						
						<div class="col-sm-6" style="margin-bottom: 5px;">
							<input type="text" class="form-control" name="tg_id" required="required" placeholder="Telegram ID" />
						</div>

						<div class="col-sm-6">
							<input type="submit" name="button" value="Добавить" class="btn btn-primary" />
						</div>

					</form>
				</div>
				
				<?php
				
				getFooter();
				exit;
			}
		
		if(isset($_GET['id']))
			{
				$id = (int)$_GET['id'];
				$q = mysql_query("SELECT * FROM `areas` WHERE `id` = ".$id);
				if(mysql_num_rows($q) == 1)
					{
						$_area = mysql_fetch_assoc($q);
						if($_GET['mode'] == 'edit')
							{								
								$error = array();
								if(isset($_POST['button']))
									{
										if(isset($_POST['title']))
											{
												$db['title'] = dbFilter($_POST['title'], 200);
												$q_check = mysql_query("SELECT * FROM `areas` WHERE `title` = '".$db['title']."' AND `id` != ".$_area['id']);
												if(mysql_num_rows($q_check) > 0)
													{
														$error[] = 'Такой участок уже есть';
													}
											}
										else
											{
												$error[] = 'Укажите название участка';
											}
										
										if(isset($_POST['tg_id']))
											{
												$db['tg_id'] = dbFilter($_POST['tg_id'], 100);
											}
										else
											{
												$error[] = 'Укажите Telegram ID чата участка';
											}
										
										if(empty($error))
											{
												$query = "UPDATE `areas` SET `title` = '".$db['title']."', `tg_id` = '".$db['tg_id']."' WHERE `id` = ".$_area['id'];
												if(mysql_query($query))
													{
														Redirect('/areas');
													}
												else
													{
														fatalError(mysql_error().'<br />'.$query);
													}
											}
									}
								
								setTitle('Отредактировать участок');
								getHeader();
								showFormError($error);
								?>
								
								<div class="row">
									<form action="/areas/edit/<?=$_area['id']?>" method="post">

										<div class="col-sm-6" style="margin-bottom: 5px;">
											<input type="text" class="form-control" name="title" required="required" placeholder="Название участка" value="<?=$_area['title']?>" />
										</div>
										
										<div class="col-sm-6" style="margin-bottom: 5px;">
											<input type="text" class="form-control" name="tg_id" required="required" placeholder="Telegram ID" value="<?=$_area['tg_id']?>" />
										</div>

										<div class="col-sm-6">
											<input type="submit" name="button" value="Сохранить" class="btn btn-primary" />
										</div>

									</form>
								</div>
								
								<?php
								getFooter();
								exit;
							}
				
						if($_GET['mode'] == 'remove')
							{
								if(mysql_query("UPDATE `areas` SET `deleted` = 1 WHERE `id` = ".$id))
									{
										Redirect('/areas');
									}
								else
									{
										fatalError(mysql_error());
									}
							}


						if($_GET['mode'] == 'restore')
							{
								if(mysql_query("UPDATE `areas` SET `deleted` = 0 WHERE `id` = ".$id))
									{
										Redirect('/areas');
									}
								else
									{
										fatalError(mysql_error());
									}
							}
							
						if($_GET['mode'] == 'doctors')
							{
								setTitle('Мед. работники на участке "'.$_area['title'].'"');
								getHeader();
								
								$q = mysql_query("SELECT * FROM `doctors` WHERE `id_area` = ".$id." ORDER BY `level` DESC, `id` ASC");
								if(mysql_num_rows($q) < 1)
									{
										showError('На данном участке никого нет');
									}
								else
									{
										$cc = 0;
										while($doctor = mysql_fetch_assoc($q))
											{
												$cc++;
												?>
												<div class="col-6">
													<?=$cc?>) <a href="/doctors/view/<?=$doctor['id']?>"><?=$doctor['name']?></a><br />
													Роль: <?=$doctor['level'] == 3 ? 'Старший участка' : 'Мед. работник'?><br />
													Телефон: <?=$doctor['phone']?>
													<hr />
												</div>
												<?php
											}
									}
								getFooter();
								exit;
							}
						
						if($_GET['mode'] == 'patients')
							{
								setTitle('Пациенты участка "'.$_area['title'].'"');
								getHeader();
								
								$q = mysql_query("SELECT * FROM `patients` WHERE `id_area` = ".$id." ORDER BY `name` ASC");
								if(mysql_num_rows($q) < 1)
									{
										showError('На данном участке отсутствуют пациенты');
									}
								else
									{
										$cc = 0;
										while($patient = mysql_fetch_assoc($q))
											{
												$cc++;
												?>
												<div class="col-6">
													<?=$cc?>) <a href="/patients/view/<?=$doctor['id']?>"><?=$patient['name']?></a><br />
													Телефон: <?=$patient['phone']?><br />
													
													<!--MORE INFO?-->
													
													<hr />
												</div>
												<?php
											}
									}
								getFooter();
								exit;
							}
					}
				else
					{
						fatalError('Участок не найден (wrong_id)');
					}
			}
		fatalError('wrong_mode');
	}

setTitle('Участки');
getHeader();

$q = mysql_query("SELECT * FROM `areas` WHERE `deleted` = 0 ORDER BY `title` ASC");

echo '<a href="/areas/add" class="btn btn-primary">Добавить участок</a><br />';

if(mysql_num_rows($q) < 1)
	{
		showError('Нет активных участков');
	}
else
	{
		while($area = mysql_fetch_assoc($q))
			{
				$c_doctors = mysql_num_rows(mysql_query("SELECT * FROM `doctors` WHERE `deleted` = 0 AND `id_area` = ".$area['id']));
				$c_patients = mysql_num_rows(mysql_query("SELECT * FROM `patients` WHERE `deleted` = 0 AND `id_area` = ".$area['id']));
				?>
				
				<div class="col">
					<b><?=$area['title']?></b><br />
					Мед. работников: <a href="/areas/doctors/<?=$area['id']?>" class="btn btn-xs btn-primary"><?=$c_doctors?></a><br />
					Пациентов: <a href="/areas/doctors/<?=$area['id']?>" class="btn btn-xs btn-primary"><?=$c_patients?></a><br />
					<a href="/areas/edit/<?=$area['id']?>" class="btn btn-xs btn-success">Изменить</a> 
					<a href="/areas/remove/<?=$area['id']?>" class="btn btn-xs btn-danger" onclick="return confirm('Удаляем? Действие обратимо')">Удалить</a> 
				</div>
				<br />
				
				<?php
			}
	}

$q_deleted = mysql_query("SELECT * FROM `areas` WHERE `deleted` = 1 ORDER BY `title` ASC");
if(mysql_num_rows($q_deleted) > 0)
	{
		echo '<hr /><br /><h4>Удаленные участки</h4>';
		
		while($area = mysql_fetch_assoc($q_deleted))
			{
				$c_doctors = mysql_num_rows(mysql_query("SELECT * FROM `doctors` WHERE `deleted` = 0 AND `id_area` = ".$area['id']));
				$c_patients = mysql_num_rows(mysql_query("SELECT * FROM `patients` WHERE `deleted` = 0 AND `id_area` = ".$area['id']));
				?>
				
				<div class="col">
					<b><?=$area['title']?></b><br />
					Мед. работников: <a href="/areas/doctors/<?=$area['id']?>" class="btn btn-xs btn-primary"><?=$c_doctors?></a><br />
					Пациентов: <a href="/areas/patients/<?=$area['id']?>" class="btn btn-xs btn-primary"><?=$c_patients?></a><br />
					<a href="/areas/edit/<?=$area['id']?>" class="btn btn-xs btn-success">Изменить</a> 
					<a href="/areas/restore/<?=$area['id']?>" class="btn btn-xs btn-primary">Восстановить</a> 
				</div>
				<br />
				
				<?php
			}
	}

getFooter();