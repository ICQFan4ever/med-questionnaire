<?php
require_once 'inc/core.php';

$hours['start'] = '15';
$hours['end'] = '20';
$_today = date('d.m.Y');
$_time = time();

// debug
$_DEBUG = 1;

if(isset($_GET['sid']))
	{
		// проверка sid
		$sid = dbFilter($_GET['sid'], 50);
		$q = mysql_query("SELECT * FROM `patients` WHERE `sid` = '".$sid."'");
		if(mysql_num_rows($q) == 1)
			{
				$_PATIENT = mysql_fetch_assoc($q);
				
				$error = array();
				if(isset($_POST['button']))
					{
						// проверка, пройден ли опрос сегодня + DEBUG
						$q_check = mysql_query("SELECT * FROM `reports` WHERE `id_patient` = ".$_PATIENT['id']." AND `date` = '".$_today."'");
						if(mysql_num_rows($q_check) == 0 && $_DEBUG != 1)
							{
								// получаем список вопросов из БД
								$q_questions = mysql_query("SELECT * FROM `questions` WHERE `deleted` = 0 ORDER BY `priority` ASC, `id` DESC");
								$cc = 0; // счетчик вопросов (для вывода ошибки)
								$alert = false; // переменная алерта
								$insert = 'INSERT INTO `questions_answers`(`id_question`, `id_patient`, `answer`, `time`, `date`) VALUES ';
								$data = array();
								
								while($question = mysql_fetch_assoc($q_questions))
									{
										$cc++;
										if(isset($_POST['question_'.$question['id']]))
											{
												$answer = (int)$_POST['question_'.$question['id']];
												if($answer == 1 OR $answer == 2)
													{
														// готовим query
														/*
																`id` INT AUTO_INCREMENT PRIMARY KEY,
																`id_question` INT,
																`id_patient` INT,
																`answer` INT,
																`time` INT,
																`date` TINYTEXT
														*/
														$data[$cc] = "(".$question['id'].", ".$_PATIENT['id'].", ".$answer.", ".$_time.", '".$_today."')";
													}
												else
													{
														$error[] = 'Некорретный ответ на вопрос №'.$cc;
													}
											}
										else
											{
												$error[] = 'Пожалуйста, выберите ответ на вопрос №'.$cc;
											}
									}
								
								if(empty($error))
									{
										// сперва выполняем insert пройденного опроса
										/*
												`id` INT AUTO_INCREMENT PRIMARY KEY,
												`id_patient` INT,
												`date` TINYTEXT,
												`time` INT
										*/
										if(mysql_query("INSERT INTO `reports`(`id_patient`, `date`, `time`) VALUES (".$_PATIENT['id'].", '".$_today."', ".$_time.")"))
											{
												$__id_report = mysql_insert_id();
												// склеиваем запрос
												$query = $insert.implode(', ', $data);
												if(mysql_query($query))
													{
														setTitle('Спасибо');
														getHeader();
														showSuccess('Ответы на вопросы сохранены. Спасибо');
														////////////// ALERT!!!!
														getFooter();
														exit;
													}
												else
													{
														// откатываем изменения
														if(mysql_query("DELETE FROM `reports` WHERE `id` = ".$__id_report))
															{
																fatalError('Ошибка при сохранении ответов (2). Попробуйте снова. Если проблема повторится - ожидайте звонка медицинского работника. Информация передана администраторам.');
															}
														else
															{
																fatalError('Ошибка при сохранении ответов (3). Ошибка отмены изменений. Вы не можете пройти опрос сегодня. Ожидайте звонка медицинского работника. Информация передана администраторам.');
															}
													}
											}
										else
											{
												fatalError('Ошибка при сохранении ответов (1). Попробуйте снова. Если проблема повторится - ожидайте звонка медицинского работника. Информация передана администраторам.');
											}
									}
							}
						else
							{
								fatalError('Вы уже проходили опрос сегодня. Если Вы заполнили часть данных ошибочно и хотите их изменить - свяжитесь с медицинским работником.');
							}
					}
				
				// проверяем, не пройден ли тест сегодня
				$q_check = mysql_query("SELECT * FROM `reports` WHERE `id_patient` = ".$_PATIENT['id']." AND `date` = '".$_today."'");
				if(mysql_num_rows($q_check) == 0  && $_DEBUG != 1)
					{
						setTitle('Прохождение опроса');
						getHeader();
						showSuccess('На каждый вопрос выберите вариант ответа, наиболее соответствующий Вашему мнению.');
						showFormError($error);
						// запросим список вопросов
						$q_q = mysql_query("SELECT * FROM `questions` WHERE `deleted` = 0 ORDER BY `priority` ASC, `id` DESC");
						if(mysql_num_rows($q_q) > 0)
							{
								?>
								
								<div class="row">
									<form action="/pass/<?=$sid?>" method="post">
										<?php
										while($question = mysql_fetch_assoc($q_q))
											{
												?>
												<div class="col-sm-6" style="margin-bottom: 5px;">
													<b><?=$question['text']?></b><br />
													<select name="question_<?=$question['id']?>" class="form-control">
														<option value="1"><?=$question['positive']?></option>
														<option value="2"><?=$question['negative']?></option>
													</select>
													<hr />
												</div>
												
												<?php
											}
										?>
										<div class="col-sm-3">
											<input type="submit" name="button" value="Вход" class="btn btn-primary" />
										</div>
									</form>
								</div>
								<?php
							}
						else
							{
								showError('Сейчас нет активных вопросов');
							}
						
						getFooter();
						exit;
					}
				else
					{
						fatalError('Вы уже проходили опрос сегодня. Если Вы заполнили часть данных ошибочно и хотите их изменить - свяжитесь с медицинским работником.');
					}
			}
		else
			{
				fatalError('Возможно, Вы воспользовались неверной ссылкой. Обратитесь к медицинскому работнику, если уверены, что это произошло по ошибке.');
			}
	}
else
	{
		fatalError('Возможно, Вы воспользовались неверной ссылкой. Обратитесь к медицинскому работнику, если уверены, что это произошло по ошибке.');
	}