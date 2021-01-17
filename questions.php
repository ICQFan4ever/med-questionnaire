<?php
require_once 'inc/core.php';

autOnly();
checkAccess(4);


/* 
modes:
	no: list
	add
	remove
	drafts
		action: restore
*/

if(isset($_GET['mode']))
	{
		// adding new question 
		
		if($_GET['mode'] == 'add')
			{
				$error = array();
				if(isset($_POST['button']))
					{
						# text, positive, negative, alert
						if(isset($_POST['text']))
							{
								$db['text'] = dbFilter($_POST['text'], 1000);
							}
						else
							{
								$error[] = 'Введите текст вопроса';
							}
						
						if(isset($_POST['positive']))
							{
								$db['positive'] = dbFilter($_POST['positive'], 100);
							}
						else
							{
								$error[] = 'Укажите &quot;положительный&quot; вариант ответа (&quot;да&quot;, &quot;было&quot;, &quot;согласен&quot; и т.д.)';
							}
						
						if(isset($_POST['negative']))
							{
								$db['negative'] = dbFilter($_POST['negative'], 100);
							}
						else
							{
								$error[] = 'Укажите &quot;отрицательный&quot; вариант ответа (&quot;нет&quot;, &quot;не было&quot;, &quot;не согласен&quot; и т.д.)';
							}
						
						if(isset($_POST['alert']))
							{
								$db['alert'] = $_POST['alert'] == 1 ? 1 : 2;
							}
						else
							{
								$error[] = 'Выберите &quot;уведомительный&quot; (alert) вариант';
							}
						
						if(isset($_POST['draft']))
							{
								$db['draft'] = 1;
							}
						else
							{
								$db['draft'] = 0;
							}
						
						if(isset($_POST['priority']))
							{
								$db['priority'] = (int)$_POST['priority'];
							}
						
						if(empty($error))
							{
								// Определение приоритета
								if(isset($db['priority']))
									{
										// Проверяем, есть ли вопрос с таким приоритетом
										$q_check = mysql_query("SELECT * FROM `questions` WHERE `priority` = ".$db['priority']);
										if(mysql_num_rows($q_check) != 0)
											{
												// такой вопрос имеется. Понижаем приоритет всех вопросов "ниже" на единицу
												mysql_query("UPDATE `questions` SET `priority` = `priority` - 1 WHERE `priority` <= ".$db['priority']);
											}
									}
								else
									{
										// приоритет не указан, находим максимальное
										$q_max = mysql_query("SELECT MAX(`id`) AS max FROM `questions`");
										if(mysql_num_rows($q_max) == 1)
											{
												$_tmp = mysql_fetch_assoc($q_max);
												$max = $_tmp['max'];
												$db['priority'] = $max + 1;
											}
									}
								
								if(mysql_query("INSERT INTO `questions`(`text`, `positive`, `negative`, `alert`, `priority`, `draft`) VALUES ('".$db['text']."', '".$db['positive']."', '".$db['negative']."', ".$db['alert'].", ".$db['priority'].", ".$db['draft'].")"))
									{
										Redirect('/questions');
									}
								else
									{
										fatalError(mysql_error());
									}
							}
					}
				
				setTitle('Добавить вопрос');
				getHeader();
				showFormError($error);
				$q_max = mysql_query("SELECT MAX(`id`) AS max FROM `questions`");
				if(mysql_num_rows($q_max) == 1)
					{
						$_tmp = mysql_fetch_assoc($q_max);
						$max = $_tmp['max'];
						$prior = 'Текущий максимальный приоритет - <b>'.$max.'</b>';
					}
				else
					{
						$prior = 'Текущий максимальный приоритет отсутствует';
					}
				?>
				
				<div class="row">
					<form action="/questions/add" method="post">

						<div class="col-sm-6" style="margin-bottom: 5px;">
							<textarea class="form-control" name="text" placeholder="Текст вопроса" required="required"></textarea>
						</div>

						<div class="col-sm-6" style="margin-bottom: 5px;">
							<input type="text" class="form-control" name="positive" placeholder="Название положительного ответа" required="required"/>
						</div>
						
						<div class="col-sm-6" style="margin-bottom: 5px;">
							<input type="text" class="form-control" name="negative" placeholder="Название отрицательного ответа" required="required"/>
						</div>

						<div class="col-sm-6">
							На какой вариант ответа формировать уведомление?<br />
							<input type="radio" name="alert" value="1" /> Положительный<br />
							<input type="radio" name="alert" value="2" /> Отрицательный<br />
						</div>
						
						<div class="col-sm-6">
							<?=$prior?><br />
							<input type="text" class="form-control" name="priority" placeholder="Приоритет" required="required"/>
						</div>
						
						<div class="col-sm-6" style="margin-bottom: 5px;">
							<input type="checkbox" name="draft" /> Создать как черновик<br />
						</div>
						
						<div class="col-sm-3">
							<input type="submit" name="button" value="Создать" class="btn btn-primary" />
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
				$q = mysql_query("SELECT * FROM `questions` WHERE `id` = ".$id);
				if(mysql_num_rows($q) == 1)
					{
						$_question = mysql_fetch_assoc($q);
						if($_GET['mode'] == 'draft')
							{
								if(mysql_query("UPDATE `questions` SET `draft` = 1 WHERE `id` = ".$id))
									{
										Redirect('/questions');
									}
								else
									{
										fatalError(mysql_error());
									}
							}

						if($_GET['mode'] == 'edit')
							{
								$error = array();
								
								if(isset($_POST['button']))
									{
										if(isset($_POST['text']))
											{
												$db['text'] = dbFilter($_POST['text'], 1000);
											}
										else
											{
												$error[] = 'Введите текст вопроса';
											}
										
										if(isset($_POST['positive']))
											{
												$db['positive'] = dbFilter($_POST['positive'], 100);
											}
										else
											{
												$error[] = 'Укажите &quot;положительный&quot; вариант ответа (&quot;да&quot;, &quot;было&quot;, &quot;согласен&quot; и т.д.)';
											}
										
										if(isset($_POST['negative']))
											{
												$db['negative'] = dbFilter($_POST['negative'], 100);
											}
										else
											{
												$error[] = 'Укажите &quot;отрицательный&quot; вариант ответа (&quot;нет&quot;, &quot;не было&quot;, &quot;не согласен&quot; и т.д.)';
											}
										
										if(isset($_POST['alert']))
											{
												$db['alert'] = $_POST['alert'] == 1 ? 1 : 2;
											}
										else
											{
												$error[] = 'Выберите &quot;уведомительный&quot; (alert) вариант';
											}
										
										if(isset($_POST['draft']))
											{
												$db['draft'] = 1;
											}
										else
											{
												$db['draft'] = 0;
											}
										
										if(isset($_POST['priority']))
											{
												$db['priority'] = (int)$_POST['priority'];
											}
										else
											{
												$error[] = 'Укажите приоритет';
											}
										
										if(empty($error))
											{
												// var_dump($_POST);
												// var_dump($db); die;
												// Проверяем, есть ли вопрос с таким приоритетом (не учитывая текущий)
												$q_check = mysql_query("SELECT * FROM `questions` WHERE `priority` = ".$db['priority']." AND `id` != ".$_question['id']);
												if(mysql_num_rows($q_check) != 0)
													{
														// такой вопрос имеется. Понижаем приоритет всех вопросов "ниже" на единицу
														mysql_query("UPDATE `questions` SET `priority` = `priority` - 1 WHERE `priority` <= ".$db['priority']." AND `id` != ".$_question['id']);
													}
												
												if(mysql_query("UPDATE `questions` SET `text` = '".$db['text']."', `positive` = '".$db['positive']."', `negative` = '".$db['negative']."', `alert` = ".$db['alert'].", `draft` = ".$db['draft'].", `priority` = '".$db['priority']."' WHERE `id` = ".$_question['id']))
													{
														Redirect('/questions');
													}
												else
													{
														fatalError(mysql_error());
													}
											}
									}
								setTitle('Редактирование вопроса');
								getHeader();
								showFormError($error);
								?>
								<div class="row">
									<form action="/questions/edit/<?=$_question['id']?>" method="post">

										<div class="col-sm-6" style="margin-bottom: 5px;">
											Текст вопроса<br />
											<textarea class="form-control" name="text" placeholder="Текст вопроса" required="required"><?=$_question['text']?></textarea>
										</div>

										<div class="col-sm-6" style="margin-bottom: 5px;">
											Название положительного ответа<br />
											<input type="text" class="form-control" name="positive" placeholder="Название положительного ответа" required="required" value="<?=$_question['positive']?>" />
										</div>
										
										<div class="col-sm-6" style="margin-bottom: 5px;">
											Название отрицательного ответа<br />
											<input type="text" class="form-control" name="negative" placeholder="Название отрицательного ответа" required="required" value="<?=$_question['negative']?>" />
										</div>

										<div class="col-sm-6">
											На какой вариант ответа формировать уведомление?<br />
											<input type="radio" name="alert" value="1"<?=$_question['alert'] == 1 ? ' checked="checked"' : ''?> /> Положительный<br />
											<input type="radio" name="alert" value="2"<?=$_question['alert'] == 2 ? ' checked="checked"' : ''?> /> Отрицательный<br />
										</div>
										
										<div class="col-sm-6">
											Приоритет<br />
											<input type="text" class="form-control" name="priority" placeholder="Приоритет" required="required" value="<?=$_question['priority']?>" />
										</div>
										
										<div class="col-sm-6" style="margin-bottom: 5px;">
											<input type="checkbox" name="draft"<?=$_question['draft'] == 1 ? ' checked="checked"' : ''?> /> Как черновик<br />
										</div>
										
										<div class="col-sm-3">
											<input type="submit" name="button" value="Сохранить" class="btn btn-primary" />
										</div>
									</form>
								</div>
								<br /><a href="/questions" class="btn btn-primary">Назад к списку вопросов</a>
								<?php
								getFooter();
								exit;
							}
					}
				else
					{
						fatalError('Вопрос не найден (wrong id)');
					}
			}
		
		if($_GET['mode'] == 'drafts')
			{
				if(isset($_GET['action']))
					{
						if($_GET['action'] == 'undraft')
							{
								if(isset($_GET['id']))
									{
										$id = (int)$_GET['id'];
										$q = mysql_query("SELECT * FROM `questions` WHERE `id` = ".$id);
										if(mysql_num_rows($q) == 1)
											{
												if(mysql_query("UPDATE `questions` SET `draft` = 0 WHERE `id` = ".$id))
													{
														Redirect('/questions');
													}
												else
													{
														fatalError(mysql_error());
													}
											}
										else
											{
												fatalError('Вопрос не найден (wrong id)');
											}
									}
								else
									{
										fatalError('Вопрос не найден (empty id)');
									}
							}
						Redirect('/questions/drafts');
					}
				setTitle('Черновые вопросы');
				getHeader();
				
				$q = mysql_query("SELECT * FROM `questions` WHERE `draft` = 1 ORDER BY `id` DESC");
				if(mysql_num_rows($q) < 1)
					{
						showError('Нет черновых вопросов');
					}
				else
					{
						// echo '<a href="/questions/add" class="btn btn-primary">Добавить вопрос"</a><br />';
						$cc = 0;
						while($question = mysql_fetch_assoc($q))
							{
								$cc++;
								?>
								<div class="col">
									<?=$cc?>) 
									<?=$question['text']?><br />
									<b>Положительный ответ:</b> <?=$question['positive']?><br />
									<b>Отрицательный ответ:</b> <?=$question['negative']?><br />
									<b>Алерт на ответ:</b> <?=$question['alert'] == 1 ? $question['positive'] : $question['negative']?><br />
									<a href="/questions/edit/<?=$question['id']?>" class="btn btn-success btn-xs">Изменить</a> 
									<a href="/questions/drafts/undraft/<?=$question['id']?>" class="btn btn-primary btn-xs">Опубликовать</a>
								</div>
								<br />
								<?php
							}
					}
				echo '<a href="/questions" class=">Назад к списку вопросов</a>';
				
				getFooter();
				exit;
			}
		Redirect('/questions');
	}

setTitle('Вопросы');
getHeader();
$q = mysql_query("SELECT * FROM `questions` WHERE `draft` = 0 ORDER BY `priority` ASC, `id` DESC");
$c = mysql_num_rows($q);
echo '<a href="/questions/add" class="btn btn-primary">Добавить вопрос</a><br />';

if($c < 1)
	{
		showError('Нет активных вопросов');
	}
else
	{
		$cc = 0;
		while($question = mysql_fetch_assoc($q))
			{
				$cc++;
				?>
				<div class="col">
					<?=$cc?>) 
					<?=$question['text']?><br />
					<b>Положительный ответ:</b> <?=$question['positive']?><br />
					<b>Отрицательный ответ:</b> <?=$question['negative']?><br />
					<b>Алерт на ответ:</b> <?=$question['alert'] == 1 ? $question['positive'] : $question['negative']?><br />
					<b>Приоритет:</b> <?=$question['priority']?><br />
					<a href="/questions/edit/<?=$question['id']?>" class="btn btn-success btn-xs">Изменить</a>
					<a href="/questions/draft/<?=$question['id']?>" class="btn btn-danger btn-xs">Удалить</a>
				</div>
				<br />
				<?php
			}
	}
echo '<a href="/questions/drafts" class="btn btn-secondary">Неактивные вопросы</a>';
getFooter();