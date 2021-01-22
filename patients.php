<?php
require_once 'inc/core.php';

autOnly();
checkAccess(2);
/*
modes:
	add
	view +id
	edit +id
	remove +id
	restore +id
	empty: list
	
		`id` INT AUTO_INCREMENT PRIMARY KEY,
		`id_area` INT,
		`name` TINYTEXT,
		`sid` TINYTEXT,
		`phone` TINYTEXT,
		`deleted` TINYINT
*/

// Сразу запросим все участки
$q_areas = mysql_query("SELECT * FROM `areas` WHERE `deleted` = 0 ORDER BY `id` ASC");
while($area = mysql_fetch_assoc($q_areas))
	{
		$_areas[$area['id']] = $area['title'];
	}

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
								$error[] = 'Укажите ФИО пациента';
							}
						
						if(isset($_POST['phone']))
							{
								$db['phone'] = dbFilter($_POST['phone'], 100);
							}
						else
							{
								$error[] = 'Укажите телефон пациента';
							}
						
						if(isset($_POST['birth_date']))
							{
								if(preg_match('#^(?:[0-9]{4})\-(?:[0-9]{2})\-(?:[0-9]{2})$#', $_POST['birth_date']))
									{
										$db['birth_date'] = explode('-', $_POST['birth_date']);
										$db['birth_date'] = array_reverse($db['birth_date']);
										$db['birth_date'] = implode('.', $db['birth_date']);
									}
								else
									{
										$error[] = 'Некорректный формат предполагаемой даты родов';
									}
							}
						else
							{
								$error[] = 'Укажите предполагаемую дату родов';
							}
						
						if($_INFO['level'] >= 4)
							{
								if(isset($_POST['id_area']))
									{
										$db['id_area'] = (int)$_POST['id_area'];
										if(!isset($_areas[$db['id_area']]))
											{
												$error[] = 'Такого участка не существует';
											}
									}
								else
									{
										$error[] = 'Выберите участок пациента';
									}
							}
						else
							{
								$db['id_area'] = $_INFO['id_area'];
							}
						
						if(empty($error))
							{
								// genering ssid
								$db['sid'] = passGen(12);
								if(mysql_query("INSERT INTO `patients`(`name`, `phone`, `id_area`, `sid`, `deleted`, `birth_date`) VALUES ('".$db['name']."', '".$db['phone']."', ".$db['id_area'].", '".$db['sid']."', 0, '".$db['birth_date']."')"))
									{
										$__id = mysql_insert_id();
										Redirect('/patients/view/'.$__id);
									}
								else
									{
										fatalError(mysql_error());
									}
							}
					}
					
				setTitle('Добавить пациента');
				getHeader();
				showFormError($error);
				?>
				
				<div class="row">
					<form action="/patients/add" method="post">

						<div class="col-sm-3" style="margin-bottom: 5px;">
							<input type="text" class="form-control" name="name" placeholder="ФИО" />
						</div>
						
						<div class="col-sm-3" style="margin-bottom: 5px;">
							<input type="text" class="form-control" name="phone" placeholder="Телефон" />
						</div>
						
						<?php
						if($_INFO['level'] >= 4)
							{
								?>
								<div class="col-sm-3" style="margin-bottom: 5px;">
									<select name="id_area" class="form-control">
									<?php
										foreach($_areas as $id_area => $name)
											{
												?>
												<option value="<?=$id_area?>"><?=$name?></option>
												<?php
											}
									?>
									</select>
								</div>
								<?php
							}
						?>
						
						<div class="col-sm-3">
							Предполагаемая дата родов:<br />
							<input type="date" name="birth_date" class="form-control" />
						</div>

						<div class="col-sm-3">
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
				$q = mysql_query("SELECT * FROM `patients` WHERE `id` = ".$id);
				if(mysql_num_rows($q) == 1)
					{
						$_patient = mysql_fetch_assoc($q);
						
						// Проверка доступа
						
						if($_INFO['id_area'] == $_patient['id_area'] OR $_INFO['level'] >= 4)
							{
								if($_GET['mode'] == 'view')
									{
										setTitle($_patient['name']);
										getHeader();
										
										// переводим предполагаемую дату родов в формат U:
										$birth_unixtime = strtotime($_patient['birth_date']);
										
										// Высчиытваем число дней до родов:
										$birth_days = ceil(($birth_unixtime - time()) / 86400);
										
										?>
										
										<a href="/reports/patient/<?=$_patient['id']?>" class="btn btn-sm btn-primary">Отчеты пациента</a>
										<br /><br />
										
										<dl class="row">
											<dt class="col-sm-3">ФИО</dt>
											<dd class="col-sm-9"><?=$_patient['name']?></dd>
											
											<dt class="col-sm-3">Телефон</dt>
											<dd class="col-sm-9"><a href="tel:<?=$_patient['phone']?>"><?=$_patient['phone']?></a></dd>
											
											<dt class="col-sm-3">Предполагаемая дата родов</dt>
											<dd class="col-sm-9"><span class="badge bg-info text-dark"><?=$_patient['birth_date']?></span> (<span class="badge rounded-pill bg-primary"><?=$birth_days?></span> дн.)</dd>
											
											<dt class="col-sm-3">Участок</dt>
											<dd class="col-sm-9"><?=$_areas[$_patient['id_area']]?></dd>
											
											<dt class="col-sm-3">Ссылка пациента</dt>
											<dd class="col-sm-9"><pre>https://<?=$_SITE['domain']?>/pass/<?=$_patient['sid']?></pre></dd>
											
										</dl>
										<hr />
										
										<a href="/patients/edit/<?=$_patient['id']?>" class="btn btn-sm btn-success">Редактировать</a> 
										<?=$_patient['deleted'] == 0 ? '<a href="/patients/remove/'.$_patient['id'].'" class="btn btn-sm btn-danger">Удалить</a>' : '<a href="/patients/restore/'.$_patient['id'].'" class="btn btn-sm btn-primary">Восстановить</a>'?>
										<br /><br />
										<a href="/patients" class="btn btn-sm btn-primary">Назад к списку пациентов</a>
										<?php
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
														$error[] = 'Укажите ФИО пациента';
													}
												
												if(isset($_POST['phone']))
													{
														$db['phone'] = dbFilter($_POST['phone'], 100);
													}
												else
													{
														$error[] = 'Укажите телефон пациента';
													}
												
												if(isset($_POST['sid']))
													{
														$db['sid'] = passGen(12);
													}
												else
													{
														$db['sid'] = $_patient['sid'];
													}
												
												if($_INFO['level'] >= 4)
													{
														if(isset($_POST['id_area']))
															{
																$db['id_area'] = (int)$_POST['id_area'];
																if(!isset($_areas[$db['id_area']]))
																	{
																		$error[] = 'Такого участка не существует';
																	}
															}
														else
															{
																$error[] = 'Выберите участок пациента';
															}
													}
												else
													{
														$db['id_area'] = $_INFO['id_area'];
													}
												
												if(isset($_POST['birth_date']))
													{
														if(preg_match('#^(?:[0-9]{4})\-(?:[0-9]{2})\-(?:[0-9]{2})$#', $_POST['birth_date']))
															{
																$db['birth_date'] = explode('-', $_POST['birth_date']);
																$db['birth_date'] = array_reverse($db['birth_date']);
																$db['birth_date'] = implode('.', $db['birth_date']);
															}
														else
															{
																$error[] = 'Некорректный формат предполагаемой даты родов';
															}
													}
												else
													{
														$db['birth_date'] = $_patient['birth_date'];
													}
												
												if(empty($error))
													{
														if(mysql_query("UPDATE `patients` SET `name` = '".$db['name']."', `phone` = '".$db['phone']."', `sid` = '".$db['sid']."', `id_area` = ".$db['id_area'].", `birth_date` = '".$db['birth_date']."' WHERE `id` = ".$_patient['id']))
															{
																Redirect('/patients/view/'.$_patient['id']);
															}
														else
															{
																fatalError(mysql_error());
															}
													}
											}
										
										setTitle('Редактировать данные пациента');
										getHeader();
										showFormError($error);
										
										// переводим дату родов в HTML-формат
										$_tmp = explode('.', $_patient['birth_date']);
										$_tmp = array_reverse($_tmp);
										$_patient['birth_date'] = implode('-', $_tmp);
										
										?>
										
										<div class="row">
											<form action="/patients/edit/<?=$_patient['id']?>" method="post">

												<div class="col-sm-3" style="margin-bottom: 5px;">
													ФИО:<br />
													<input type="text" class="form-control" name="name" placeholder="ФИО" value="<?=$_patient['name']?>" />
												</div>
												
												<div class="col-sm-3" style="margin-bottom: 5px;">
													Телефон:<br />
													<input type="text" class="form-control" name="phone" placeholder="Телефон" value="<?=$_patient['phone']?>" />
												</div>
												
												<?php
												if($_INFO['level'] >= 4)
													{
														?>
														<select name="id_area" class="form-control">
														<?php
															foreach($_areas as $id_area => $name)
																{
																	?>
																	<option value="<?=$id_area?>"<?=$_patient['id_area'] == $id_area ? ' "selected="selected"' : ''?>><?=$name?></option>
																	<?php
																}
														?>
														</select>
														<?php
													}
												?>
												
												<div class="col-sm-3">
													Предполагаемая дата родов:<br />
													<input type="date" name="birth_date" value="<?=$_patient['birth_date']?>" class="form-control" />
												</div>
												
												<div class="col-sm-3" style="margin-bottom: 5px;">
													<input type="checkbox" name="sid" /> Сменить секретный код (старая ссылка пациента станет неактивной)
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
								
								if($_GET['mode'] == 'remove')
									{
										if(mysql_query("UPDATE `patients` SET `deleted` = 1 WHERE `id` = ".$_patient['id']))
											{
												Redirect('/patients');
											}
										else
											{
												fatalError(mysql_error());
											}
									}
								
								if($_GET['mode'] == 'restore')
									{
										if(mysql_query("UPDATE `patients` SET `deleted` = 0 WHERE `id` = ".$_patient['id']))
											{
												Redirect('/patients');
											}
										else
											{
												fatalError(mysql_error());
											}
									}
							}
						else
							{
								fatalError('Пациент не найден (access)');
							}
					}
				else
					{
						fatalError('Пациент не найден (db)');
					}
			}
		
		if($_GET['mode'] == 'removed')
			{
				if($_INFO['level'] >= 4)
					{
						if(isset($_GET['id_area']))
							{
								$id_area = (int)$_GET['id_area'];
								$q_area = mysql_query("SELECT * FROM `areas` WHERE `id` = ".$id_area);
								if(mysql_num_rows($q_area) == 1)
									{
										$_area = mysql_fetch_assoc($q_area);
										$title = 'Удаленные пациенты участка '.$_area['title'];
										$q_patients = mysql_query("SELECT * FROM `patients` WHERE `id_area` = ".$_area['id']." AND `deleted` = 1 ORDER BY `name` ASC");
									}
								else
									{
										fatalError('Такого участка нет');
									}
							}
						else
							{
								$title = 'Все удаленные пациенты';
								$_area['title'] = 'Все участки';
								$q_patients = mysql_query("SELECT * FROM `patients` WHERE `deleted` = 1 ORDER BY `name` ASC");
							}
					}
				else
					{
						$q_area = mysql_query("SELECT * FROM `areas` WHERE `id` = ".$_INFO['id_area']);
						if(mysql_num_rows($q_area) == 1)
							{
								$_area = mysql_fetch_assoc($q_area);
								$title = 'Удаленные пациенты вашего участка';
								$q_patients = mysql_query("SELECT * FROM `patients` WHERE `id_area` = ".$_area['id']." AND `deleted` = 1 ORDER BY `name` ASC");
							}
						else
							{
								fatalError('Системная ошибка. Ваш участок не существует. Обратитесь к администратору');
							}
					}
				setTitle($title);
				getHeader();
				echo '<a href="/patients" class="btn btn-sm btn-primary">Назад к списку пациентов</a><br /><br />';
				if($_INFO['level'] >= 4)
					{
						?>
						<div class="row">
							<form action="">
								<div class="col-sm-3" style="margin-bottom: 5px;">
									<select name="change" onchange="location = this.value;" class="form-control">
										<option value="/patients">Все участки</option>
										<?php
										$q_areas = mysql_query("SELECT * FROM `areas` ORDER BY `id` ASC");
										while($tmp = mysql_fetch_assoc($q_areas))
											{
												$_areas[$tmp['id']] = $tmp['title'];
											}
										
										foreach($_areas as $key => $value)
											{
												?>
												<option value="/patients/removed/area/<?=$key?>"<?=isset($_GET['id_area']) && @$id_area == $key ? ' selected="selected"' : ''?>><?=$value?></option>
												<?php
											}
										?>
									</select>
								</div>
							</form>
						</div>
						<?php
					}
				// Начинаем вывод пациентов

				if(mysql_num_rows($q_patients) < 1)
					{
						showError('На участке &quot;'.$_area['title'].'&quot; нет удаленных пациентов');
					}
				else
					{
						$cc = 0;
						while($patient = mysql_fetch_assoc($q_patients))
							{
								$cc++;
								?>
								
								<div class="col">
									<?=$cc?>) <a href="/patients/view/<?=$patient['id']?>" style="font-weight: bold;"><?=$patient['name']?></a><br />
									Телефон: <a href="tel:<?=$patient['phone']?>"><?=$patient['phone']?></a><br />
									<?=$_INFO['level'] >= 4 ? '<b>'.$_areas[$patient['id_area']].'</b><br />' : ''?>
									<a href="/patients/edit/<?=$patient['id']?>" class="btn btn-xs btn-success">Редактировать</a> 
									<a href="/patients/restore/<?=$patient['id']?>" class="btn btn-xs btn-primary">Восстановить</a>
									<hr />
								</div>
								<?php
							}
					}

				getFooter();
				exit;
			}
		
		fatalError('wrong mode');
	}


// определим, на кого запрашиваем информацию
if($_INFO['level'] >= 4)
	{
		if(isset($_GET['id_area']))
			{
				$id_area = (int)$_GET['id_area'];
				$q_area = mysql_query("SELECT * FROM `areas` WHERE `id` = ".$id_area);
				if(mysql_num_rows($q_area) == 1)
					{
						$_area = mysql_fetch_assoc($q_area);
						$title = 'Пациенты участка '.$_area['title'];
						$q_patients = mysql_query("SELECT * FROM `patients` WHERE `id_area` = ".$_area['id']." AND `deleted` = 0 ORDER BY `name` ASC");
					}
				else
					{
						fatalError('Такого участка нет');
					}
			}
		else
			{
				$title = 'Все пациенты';
				$_area['title'] = 'Все пациенты';
				$q_patients = mysql_query("SELECT *, `reports`.`id` AS `id_report` FROM `patients` LEFT JOIN `reports` ON `reports`.`id_patient` = `patients`.`id` AND `reports`.`date` = '".date('d.m.Y')."' WHERE `patients`.`deleted` = 0 ORDER BY `patients`.`name` ASC");
			}
	}
else
	{
		$q_area = mysql_query("SELECT * FROM `areas` WHERE `id` = ".$_INFO['id_area']);
		if(mysql_num_rows($q_area) == 1)
			{
				$_area = mysql_fetch_assoc($q_area);
				$title = 'Пациенты вашего участка';
				$q_patients = mysql_query("SELECT *, `reports`.`id` AS `id_report` FROM `patients` LEFT JOIN `reports` ON `reports`.`id_patient` = `patients`.`id` AND `reports`.`date` = '".date('d.m.Y')."' WHERE `patients`.`deleted` = 0 AND `patients`.`id_area` = ".$_area['id']." ORDER BY `patients`.`name` ASC");
			}
		else
			{
				fatalError('Системная ошибка. Ваш участок не существует. Обратитесь к администратору');
			}
	}

setTitle($title);
getHeader();

echo '<a href="/patients/add" class="btn btn-primary">Добавить пациента</a><br /><br />';

if($_INFO['level'] >= 4)
	{
		?>
		<div class="row">
			<form action="">
				<div class="col-sm-3" style="margin-bottom: 5px;">
					<select name="change" onchange="location = this.value;" class="form-control">
						<option value="/patients">Все участки</option>
						<?php
						$q_areas = mysql_query("SELECT * FROM `areas` ORDER BY `id` ASC");
						while($tmp = mysql_fetch_assoc($q_areas))
							{
								$_areas[$tmp['id']] = $tmp['title'];
							}
						
						foreach($_areas as $key => $value)
							{
								?>
								<option value="/patients/<?=$key?>"<?=isset($_GET['id_area']) && @$id_area == $key ? ' selected="selected"' : ''?>><?=$value?></option>
								<?php
							}
						?>
					</select>
				</div>
			</form>
		</div>
		<?php
	}
// Начинаем вывод пациентов

if(mysql_num_rows($q_patients) < 1)
	{
		showError('На участке &quot;'.$_area['title'].'&quot; нет пациентов');
	}
else
	{
		$cc = 0;
		while($patient = mysql_fetch_assoc($q_patients))
			{
				$cc++;
				$birth_unixtime = strtotime($patient['birth_date']);			
				// Высчиытваем число дней до родов:
				$birth_days = ceil(($birth_unixtime - time()) / 86400);
				?>
				
				<div class="col">
					<?=$cc?>) <a href="/patients/view/<?=$patient['id']?>" style="font-weight: bold;"><?=$patient['name']?></a><br />
					Телефон: <a href="tel:<?=$patient['phone']?>"><?=$patient['phone']?></a><br />
					Роды: <span class="badge bg-info text-dark"><?=$patient['birth_date']?></span> - <span class="badge rounded-pill bg-primary"><?=$birth_days?></span> дн.<br />
					<?=$_INFO['level'] >= 4 ? '<b>'.$_areas[$patient['id_area']].'</b><br />' : ''?>
					Опрос сегодня: <?=empty($patient['id_report']) ? '<span class="badge bg-warning text-dark">не пройден</span>' : '<span class="badge bg-info text-dark">пройден</span><br /><a href="/reports/patient/'.$patient['id_report'].'" class="btn btn-xs btn-primary">Просмотреть отчет</a>'?><br />
					<a href="/patients/edit/<?=$patient['id']?>" class="btn btn-xs btn-success">Редактировать</a> 
					<a href="/patients/remove/<?=$patient['id']?>" class="btn btn-xs btn-danger">Удалить</a>
					<hr />
				</div>
				<?php
			}
	}
echo '<br /><a href="/patients/removed" class="btn btn-sm btn-secondary">Удаленные пациенты</a><br />';

getFooter();