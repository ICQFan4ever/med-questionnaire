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
						# question, positive, negative, alert
						if(isset($_POST['question']))
							{
								$db['question'] = filterText($_POST['question'], 1000);
							}
						else
							{
								$error[] = 'Введите текст вопроса';
							}
						
						if(isset($_POST['positive']))
							{
								$db['positive'] = filterText($_POST['positive'], 100);
							}
						else
							{
								$error[] = 'Укажите &quot;положительный&quot; вариант ответа (&quot;да&quot;, &quot;было&quot;, &quot;согласен&quot; и т.д.)';
							}
						
						if(isset($_POST['negative']))
							{
								$db['negative'] = filterText($_POST['negative'], 100);
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
						
						if(empty($error))
							{
								if(mysql_query("INSERT INTO `questions`(`question`, `positive`, `negative`, `alert`, `draft`) VALUES ('".$db['question']."', '".$db['positive']."', '".$db['negative']."', ".$db['alert'].", ".$db['draft'].")"))
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
				?>
				
				<div class="row">
					<form action="/login" method="post">

						<div class="col-sm-6" style="margin-bottom: 5px;">
							<textarea class="form-control" name="question" placeholder="Текст вопроса" required="required"></textarea>
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
						
						<div class="col-sm-6" style="margin-bottom: 5px;">
							<input type="checkbox" name="draft" /> Создать как черновик<br />
						</div>
						
						<div class="col-sm-3">
							<input type="submit" name="button" value="Вход" class="btn btn-primary" />
						</div>
					</form>
				</div>
				<?php
				
				getFooter();
				exit;
			}
		
		if($_GET['mode'] == 'draft')
			{
				if(isset($_GET['id']))
					{
						$id = (int)$_GET['id'];
						$q = mysql_query("SELECT * FROM `question` WHERE `id` = ".$id);
						if(mysql_num_rows($q) == 1)
							{
								if(mysql_query("UPDATE `question` SET `draft` = 1 WHERE `id` = ".$id))
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
								fatalError('Вопрос не найден');
							}
					}
				else
					{
						fatalError('Вопрос не найден');
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
										$q = mysql_query("SELECT * FROM `question` WHERE `id` = ".$id);
										if(mysql_num_rows($q) == 1)
											{
												if(mysql_query("UPDATE `question` SET `draft` = 0 WHERE `id` = ".$id))
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
												fatalError('Вопрос не найден');
											}
									}
								else
									{
										fatalError('Вопрос не найден');
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
									<?=$cc?>)<br />
									<?=$question['question']?><br />
									<b>Положительный ответ:</b> <?=$question['positive']?><br />
									<b>Отрицательный ответ:</b> <?=$question['negative']?><br />
									<b>Алерт на ответ:</b> <?=$question['alert'] == 1 ? $question['positive'] : $question['negative']?><br />
									<a href="/questions/drafts/undraft/<?=$question['id']?>" class="badge bg-primary">Опубликовать</a>
								</div>
								<br />
								<?php
							}
					}
				echo '<a href="/questions">Назад к списку вопросов</a>';
				
				getFooter();
				exit;
			}
		Redirect('/questions');
	}

setTitle('Вопросы');
getHeader();
$q = mysql_query("SELECT * FROM `questions` WHERE `draft` = 0 ORDER BY `id` ASC");
$c = mysql_num_rows($q);
echo '<a href="/questions/add" class="btn btn-primary">Добавить вопрос</a>';

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
					<?=$cc?>)<br />
					<?=$question['question']?><br />
					<b>Положительный ответ:</b> <?=$question['positive']?><br />
					<b>Отрицательный ответ:</b> <?=$question['negative']?><br />
					<b>Алерт на ответ:</b> <?=$question['alert'] == 1 ? $question['positive'] : $question['negative']?><br />
					<a href="/questions/draft/<?=$question['id']?>" class="badge bg-secondary">В черновики</a>
				</div>
				<br />
				<?php
			}
		echo '<a href="/questions/drafts" class="btn btn-secondary">Неактивные вопросы</a>';
	}
getFooter();