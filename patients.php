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


if(isset($_GET['mode']))
	{
		if($_GET['mode'] == 'add')
			{
				// Сразу запросим все участки
				$q_areas = mysql_query("SELECT * FROM `areas` WHERE `deleted` = 0 ORDER BY `id` ASC");
				while($area = mysql_fetch_assoc($q_areas))
					{
						$_areas[$area['id']] = $area['title'];
					}
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
								if(mysql_query("INSERT INTO `patients`(`name`, `phone`, `id_area`, `sid`, `deleted`) VALUES ('".$db['name']."', '".$db['phone']."', ".$db['id_area'].", '".$db['sid']."', 0)"))
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
								<?php
							}
						?>

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
												
												if(empty($error))
													{
														if(mysql_query("UPDATE `patients` SET `name` = '".$db['name']."', `phone` = '".$db['phone']."', `sid` = '".$db['sid']."', `id_area` = ".$db['id_area']." WHERE `id` = ".$_patient['id']))
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
										
										?>
										
										<div class="row">
											<form action="/patients/edit/<?=$_patient['id']?>" method="post">

												<div class="col-sm-3" style="margin-bottom: 5px;">
													ФИО:<br />
													<input type="text" class="form-control" name="name" placeholder="ФИО" value="<?=$_patient['name']?>" />
												</div>
												
												<div class="col-sm-3" style="margin-bottom: 5px;">
													Телефон:<br />
													<input type="text" class="form-control" name="phone" placeholder="Телефон" value="<?=$_patient['name']?>" />
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
						$title = 'Пациенты участка '.$area['title'];
						$q_patients = mysql_query("SELECT * FROM `patients` WHERE `id_area` = ".$_area['id']." ORDER BY `name` ASC");
					}
				else
					{
						fatalError('Такого участка нет');
					}
			}
		else
			{
				$title = 'Все пациенты';
				$q_patients = mysql_query("SELECT * FROM `patients` WHERE `id_area` = ".$id_area." ORDER BY `name` ASC");
			}
	}
else
	{
		$q_area = mysql_query("SELECT * FROM `areas` WHERE `id` = ".$_INFO['id_area']);
		if(mysql_num_rows($q_area) == 1)
			{
				$_area = mysql_fetch_assoc($q_area);
				$title = 'Пациенты вашего участка';
				$q_patients = mysql_query("SELECT * FROM `patients` WHERE `id_area` = ".$_area['id']." ORDER BY `name` ASC");
			}
		else
			{
				fatalError('Системная ошибка. Ваш участок не существует. Обратитесь к администратору');
			}
	}

setTitle($title);
getHeader();

echo '<a href="/patients/add" class="btn btn-primary">Добавить пациента</a><br />';

if($_INFO['level'] >= 4)
	{
		?>
		<form action="">
			<select name="change" onchange="location = this.value;" class="form-control">
				<?php
				$q_areas = mysql_query("SELECT * FROM `areas` ORDER BY `id` ASC");
				while($tmp = mysql_fetch_assoc($q_areas))
					{
						$_areas[$tmp['id']] = $tmp['title'];
					}
				
				foreach($_areas as $key => $value)
					{
						?>
						<option value="/patients/<?=$key?>"><?=$value?></option>
						<?php
					}
				?>
			</select>
		</form>
		<?php
	}
// Начинаем вывод пациентов

if(mysql_num_rows($q_patients) < 1)
	{
		showError('Нет пациентов для отображения');
	}
else
	{
		$cc = 0;
		while($patient = mysql_fetch_assoc($q_patients))
			{
				$cc++;
				?>
				
				<div class="col">
					<?=$cc?>) <a href="/patients/view/<?=$patient['id']?>"><?=$patient['name']?></a><br />
					Телефон: <a href="tel:<?=$patient['phone']?>"><?=$patient['name']?></a><br />
					// todo: проходила ли опрос сегодня
					<hr />
				</div>
				<?php
			}
	}

getFooter();