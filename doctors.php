<?php
require_once 'inc/core.php';

autOnly();
checkAccess(4);

/*
modes:
	add
	view +id
	edit +id
	remove +id
	restore +id
	removed
empty: list
*/

if(isset($_GET['mode']))
	{
		if($_GET['mode'] == 'add')
			{
				$error = array();
				if(isset($_POST['button']))
					{
						if(isset($_POST['name']))
							{
								$db['name'] = dbFilter($_POST['name'], 100);
							}
						else
							{
								$error[] = 'Укажите имя мед. работника';
							}
						
						if(isset($_POST['phone']))
							{
								$db['phone'] = dbFilter($_POST['phone'], 50);
							}
						else
							{
								$db['phone'] = '';
							}
						
						if(isset($_POST['level']))
							{
								$db['level'] = (int)$_POST['level'];
								if($db['level'] < 2 | $db['level'] > 4)
									{
										$error[] = 'Неверный уровень доступа';
									}
								else
									{
										$error[] = 'Некорректный уровень доступа сотрудника';
									}
							}
						else
							{
								$error[] = 'Некорректный уровень доступа сотрудника';
							}
						
						if(isset($_POST['tg_id']))
							{
								$db['tg_id'] = dbFilter($_POST['tg_id'], 50);
							}
						else
							{
								$db['tg_id'] = 0;
							}
						
						if(isset($_POST['id_area']))
							{
								$db['id_area'] = (int)$_POST['id_area'];
								if($db['id_area'] != 0)
									{
										$q_check = mysql_query("SELECT * FROM `areas` WHERE `id` = ".$db['id_area']);
										if(mysql_num_rows($q_check) < 1)
											{
												$error[] = 'Некорректный участок';
											}
									}
							}
						else
							{
								$error[] = 'Выберите участок';
							}
						
						if(empty($error))
							{
								if(mysql_query("INSERT INTO `doctors`(`name`, `phone`, `level`, `tg_id`, `id_area`, `deleted`) VALUES ('".$db['name']."', '".$db['phone']."', ".$db['level'].", '".$db['tg_id']."', ".$db['id_area'].", 0)"))
									{
										$__id = mysql_insert_id();
										$db['login'] = 'doctor_'.$__id;
										$password = passGen(8);
										$db['password'] = md5(md5($password));
										if(mysql_query("UPDATE `doctors` SET `login` = '".$db['login']."', `password` = '".$db['password']."' WHERE `id` = ".$__id))
											{
												setTitle('Создание учетной записи');
												getHeader();
												showSuccess('Учетная запись создана.<br />Логин: '.$db['login'].'<br />Пароль: '.$password);
												echo '<br /><a href="/doctors" class="btn btn-primary">Назад к списку работников</a>';
												getFooter();
												exit;
											}
										else
											{
												fatalError(mysql_error());
											}
									}
								else
									{
										fatalError(mysql_error());
									}
							}
					}
				
				setTitle('Создание учетной записи');
				getHeader();
				showFormError($error);
				?>
				
				<div class="row">
					<form action="/doctors/add" method="post">

						<div class="col-sm-4" style="margin-bottom: 5px;">
							<input type="text" class="form-control" name="name" placeholder="Имя *" />
						</div>

						<div class="col-sm-4" style="margin-bottom: 5px;">
							<input type="text" class="form-control" name="phone" placeholder="Телефон" />
						</div>
						
						<div class="col-sm-4" style="margin-bottom: 5px;">
							<input type="text" class="form-control" name="tg_id" placeholder="Telegram ID" />
						</div>
						
						<div class="col-sm-4" style="margin-bottom: 5px;">
							<select name="level">
								<option value="2">Мед. работник</option>
								<option value="3">Руководитель участка</option>
								<option value="4">Администратор</option>
							</select>
						</div>
						
						<div class="col-sm-4" style="margin-bottom: 5px;">
							<select name="id_area">
								<option value="0">Вне участка (для администратора)</option>
								<?php
								$q_areas = mysql_query("SELECT * FROM `areas` WHERE `deleted` = 0");
								while($area = mysql_fetch_assoc($q_areas))
									{
										?>
										<option value="<?=$area['id']?>"><?=$area['title']?></option>
										<?php
									}
								?>
							</select>
						</div>
						<div class="col-sm-4">
							<input type="submit" name="button" value="Создать" class="btn btn-primary" />
						</div>

					</form>
				</div>
				
				<a href="/doctors" class="btn btn-primary">Назад к списку мед. работников</a>
				
				<?php
				getFooter();
				exit;
			}
		
		if(isset($_GET['id']))
			{
				$id = (int)$_GET['id'];
				$q = mysql_query("SELECT * FROM `doctors` WHERE `id` = ".$id);
				
				if(mysql_num_rows($q) == 1)
					{
						$_doctor = mysql_fetch_assoc($q);
						
						if($_GET['mode'] == 'view')
							{
								$q_a = mysql_query("SELECT * FROM `areas`");
								$areas[0] = 'Вне участка (для администратора)';
								while($inf = mysql_fetch_assoc($q_a))
									{
										$areas[$inf['id']] = $inf['title'];
									}
								
								$roles = array(2 => 'Мед. работник', 3 => 'Руководитель участка', 4 => 'Администратор', 5 => 'System');
									
								setTitle('Работник '.$_doctor['name']);
								getHeader();
								
								if($_doctor['deleted'] == 1)
									{
										showError('Эта учетная запись неактивна');
									}
								?>
								
								<dl class="row">
									<dt class="col-sm-3">Имя сотрудника</dt>
									<dd class="col-sm-9"><?=$_doctor['name']?></dd>
									
									<dt class="col-sm-3">Логин</dt>
									<dd class="col-sm-9"><?=$_doctor['login']?></dd>
									
									<dt class="col-sm-3">Телефон</dt>
									<dd class="col-sm-9"><?=$_doctor['phone']?></dd>
									
									<dt class="col-sm-3">Telegram ID</dt>
									<dd class="col-sm-9"><?=$_doctor['tg_id']?></dd>
									
									<dt class="col-sm-3">Роль</dt>
									<dd class="col-sm-9"><?=$roles[$_doctor['level']]?></dd>
									
									<dt class="col-sm-3">Участок</dt>
									<dd class="col-sm-9"><?=$areas[$_doctor['id_area']]?></dd>
								
								</dl>
								
								<a href="/doctors/edit/<?=$_doctor['id']?>" class="btn btn-success">Редактировать</a> 
								<?=$_doctor['deleted'] == 1 ? '<a href="/doctors/restore/'.$_doctor['id'].'" class="btn btn-primary">Восстановить</a>' : '<a href="/doctors/remove/'.$_doctor['id'].'" class="btn btn-danger">Удалить</a>'?>
								
								<?php
								echo '<br /><a href="/doctors" class="btn btn-primary">Назад к списку сотрудников</a>';
								getFooter();
								exit;
							}
						
						if($_GET['mode'] == 'edit')
							{
								$error = array();
								if(isset($_POST['button']))
									{
										if(isset($_POST['name']))
											{
												$db['name'] = dbFilter($_POST['name'], 100);
											}
										else
											{
												$error[] = 'Укажите имя мед. работника';
											}
										
										if(isset($_POST['phone']))
											{
												$db['phone'] = dbFilter($_POST['phone'], 50);
											}
										else
											{
												$db['phone'] = '';
											}
										
										if(isset($_POST['level']))
											{
												$db['level'] = (int)$_POST['level'];
												if($db['level'] < 2 | $db['level'] > 4)
													{
														$error[] = 'Неверный уровень доступа';
													}
												else
													{
														$error[] = 'Некорректный уровень доступа сотрудника';
													}
											}
										else
											{
												$error[] = 'Некорректный уровень доступа сотрудника';
											}
										
										if(isset($_POST['tg_id']))
											{
												$db['tg_id'] = dbFilter($_POST['tg_id'], 50);
											}
										else
											{
												$db['tg_id'] = 0;
											}
										
										if(isset($_POST['id_area']))
											{
												$db['id_area'] = (int)$_POST['id_area'];
												if($db['id_area'] != 0)
													{
														$q_check = mysql_query("SELECT * FROM `areas` WHERE `id` = ".$db['id_area']);
														if(mysql_num_rows($q_check) < 1)
															{
																$error[] = 'Некорректный участок';
															}
													}
											}
										else
											{
												$error[] = 'Выберите участок';
											}
										
										if(isset($_POST['password']))
											{
												$db['password'] = md5(md5($_POST['password']));
												$db['session'] = '';
											}
										else
											{
												$db['password'] = $_doctor['password'];
												$db['session'] = $_doctor['session'];
											}
										if(empty($error))
											{
												if(mysql_query("UPDATE `doctors` SET `name` = '".$db['name']."', `phone` = '".$db['phone']."', `level` = ".$db['level'].", `tg_id` = '".$db['tg_id']."', `id_area` = ".$db['id_area'].", `password` = '".$db['password']."', `session` = '".$db['session']."' WHERE `id` = ".$_doctor['id']))
													{
														redirect('/doctors');
													}
												else
													{
														fatalError(mysql_error());
													}
											}
									}
								
								setTitle('Редактировать учетную запись');
								getHeader();
								showFormError($error);
								?>
								
								<div class="row">
									<form action="/doctors/edit/<?=$_doctor['id']?>" method="post">
										<div class="col-sm-4" style="margin-bottom: 5px;">
											Логин:<br />
											<input type="text" class="form-control" name="login" disabled="disabled" placeholder="Логин" value="<?=$_doctor['login']?>" />
										</div>
										
										<div class="col-sm-4" style="margin-bottom: 5px;">
											Имя:<br />
											<input type="text" class="form-control" name="name" placeholder="Имя *" value="<?=$_doctor['name']?>" />
										</div>

										<div class="col-sm-4" style="margin-bottom: 5px;">
											Телефон:<br />
											<input type="text" class="form-control" name="phone" placeholder="Телефон" value="<?=$_doctor['phone']?>" />
										</div>
										
										<div class="col-sm-4" style="margin-bottom: 5px;">
											Telegram ID:<br />
											<input type="text" class="form-control" name="tg_id" placeholder="Telegram ID" value="<?=$_doctor['tg_id']?>" />
										</div>
										
										<div class="col-sm-4" style="margin-bottom: 5px;">
											<select name="level">
												<option value="2"<?=$_doctor['level'] == 2 ? ' selected="selected"' : ''?>>Мед. работник</option>
												<option value="3"<?=$_doctor['level'] == 3 ? ' selected="selected"' : ''?>>Руководитель участка</option>
												<option value="4"<?=$_doctor['level'] == 4 ? ' selected="selected"' : ''?>>Администратор</option>
											</select>
										</div>
										
										<div class="col-sm-4" style="margin-bottom: 5px;">
											<select name="id_area">
												<option value="0">Вне участка (для администратора)</option>
												<?php
												$q_areas = mysql_query("SELECT * FROM `areas` WHERE `deleted` = 0");
												while($area = mysql_fetch_assoc($q_areas))
													{
														?>
														<option value="<?=$area['id']?>"<?=$_doctor['id_area'] == $area['id'] ? ' selected="selected"' : ''?>><?=$area['title']?></option>
														<?php
													}
												?>
											</select>
										</div>
										<div class="col-sm-4">
											<input type="submit" name="button" value="Создать" class="btn btn-primary" />
										</div>

									</form>
								</div>
								
								<a href="/doctors" class="btn btn-primary">Назад к списку мед. работников</a>
								
								<?php
								getFooter();
								exit;
							}
						
						if($_GET['mode'] == 'remove')
							{
								if($_INFO['id'] == $_doctor['id'])
									{
										setTitle('??????');
										getHeader();
										?>
										<img src="/desgin/noga.jpg" alt="" />
										<?php
										getFooter();
										exit;
									}
								else
									{
										if($_doctor['level'] > 4 && $_INFO['level'] != 5)
											{
												fatalError('Вы не можете удалить эту учетную запись');
											}
										else
											{
												if(mysql_query("UPDATE `doctors` SET `deleted` = 1 WHERE `id` = ".$_doctor['id']))
													{
														Redirect('/doctors');
													}
												else
													{
														fatalError(mysql_error());
													}
											}
									}
							}
						
						if($_GET['mode'] == 'restore')
							{
								if(mysql_query("UPDATE `doctors` SET `deleted` = 0 WHERE `id` = ".$_doctor['id']))
									{
										Redirect('/doctors');
									}
								else
									{
										fatalError(mysql_error());
									}
							}
					}
				else
					{
						fatalError('Мед. работник не найден');
					}
			}
		
		if($_GET['mode'] == 'removed')
			{
				setTitle('Удаленные работники');
				getHeader();
				
				$q = mysql_query("SELECT * FROM `doctors` WHERE `deleted` = 1");
				
				echo '<a href="/doctors" class="btn btn-primary">Назад к списку работников</a><br />';
				
				if(mysql_num_rows($q) < 1)
					{
						showError('Нет удаленных работников');
					}
				else
					{
						$cc = 0;
						while($doctor = mysql_fetch_assoc($q))
							{
								$cc++;
								?>
								<div class="col">
									<?=$cc?>) <a href="/doctors/view/<?=$doctor['id']?>"><?=$doctor['name']?></a><br />
									<a href="/doctors/edit/<?=$doctor['id']?>" class="btn btn-xs btn-success">Редактировать</a> 
									<a href="/doctors/restore/<?=$doctor['id']?>" class="btn btn-xs btn-primary">Восстановить</a> 
									<hr />
								</div>
								<?php
							}
					}
				
				getFooter();
				exit;
			}
		fatalError('empty_mode');
	}

setTitle('Список мед. работников');
getHeader();

$q = mysql_query("SELECT * FROM `doctors` WHERE `deleted` = 0");
				
if(mysql_num_rows($q) < 1)
	{
		showError('Нет работников');
	}
else
	{
		$cc = 0;
		while($doctor = mysql_fetch_assoc($q))
			{
				$cc++;
				?>
				<div class="col">
					<?=$cc?>) <a href="/doctors/view/<?=$doctor['id']?>"><?=$doctor['name']?></a><br />
					<a href="/doctors/edit/<?=$doctor['id']?>" class="btn btn-xs btn-success">Редактировать</a> 
					<a href="/doctors/remove/<?=$doctor['id']?>" class="btn btn-xs btn-primary">Удалить</a> 
					<hr />
				</div>
				<?php
			}
	}

echo '<br /><a href="/doctors/removed" class="btn btn-secondary">Удаленные сотрудники</a>';

getFooter();