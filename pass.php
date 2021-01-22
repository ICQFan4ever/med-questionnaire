<?php
require_once 'inc/core.php';

$hours['start'] = '15';
$hours['end'] = '20';
$_today = date('d.m.Y');
$_time = time();

// debug
$_DEBUG = 0;

if(isset($_GET['sid']))
	{
		// проверка sid
		$sid = dbFilter($_GET['sid'], 50);
		$q = mysql_query("SELECT * FROM `patients` WHERE `sid` = '".$sid."'");
		if(mysql_num_rows($q) == 1)
			{
				$_PATIENT = mysql_fetch_assoc($q);
				// запрос на определение зоны
				$q_area = mysql_query("SELECT * FROM `areas` WHERE `id` = ".$_PATIENT['id_area']);
				$_AREA = mysql_fetch_assoc($q_area);
				
				// ALERT!
				if(isset($_GET['alert']))
					{
						$message = '<b>Внимание! Нажата кнопка экстренной помощи!</b>'.PHP_EOL;
						$message .= 'Пациент: '.$_PATIENT['name'].PHP_EOL;
						$message .= 'Телефон: '.$_PATIENT['phone'].PHP_EOL;
						$message .= 'Предполагаемая дата родов: '.$_PATIENT['birth_date'].PHP_EOL;
						
						$tg = new Tg($_TG['api']);
						$tg -> send($_AREA['tg_id'], $message);
						
						setTitle('Запрос отправлен');
						getHeader();
						showWarning('Информация передана на участок, ожидайте звонка');
						getFooter();
						exit;
					}
				
				$error = array();
				if(isset($_POST['button']))
					{
						// проверка, пройден ли опрос сегодня + DEBUG
						$q_check = mysql_query("SELECT * FROM `reports` WHERE `id_patient` = ".$_PATIENT['id']." AND `date` = '".$_today."'");
						if(mysql_num_rows($q_check) == 0 OR $_DEBUG == 1)
							{
								// получаем список вопросов из БД
								$q_questions = mysql_query("SELECT * FROM `questions` WHERE `draft` = 0 ORDER BY `priority` ASC, `id` DESC");
								$cc = 0; // счетчик вопросов (для вывода ошибки)
								$alert = false; // переменная алерта
								$insert = 'INSERT INTO `questions_answers`(`id_question`, `id_patient`, `answer`, `time`, `date`) VALUES ';
								$data = array();
								
								while($question = mysql_fetch_assoc($q_questions))
									{
										$cc++;
										$db['alert'] = 0; // до перезаписывания
										if(isset($_POST['question_'.$question['id']]))
											{
												$answer = (int)$_POST['question_'.$question['id']];
												if($answer == 1 OR $answer == 2)
													{
														// die('passed!');
														// чекаем, проверяем alert
														if($answer == $question['alert'])
															{
																$alert = true;
																$db['alert'] = 1;
																$alert_text[] = '"'.$question['text'].'" - "'.($answer == 1 ? $question['positive'] : $question['negative']).'"';
															}
														// готовим query
														/*
																`id` INT AUTO_INCREMENT PRIMARY KEY,
																`id_question` INT,
																`id_patient` INT,
																`id_report` INT,
																`answer` INT,
																`time` INT,
																`date` TINYTEXT
														*/
														$data[$cc] = "(".$question['id'].", ".$_PATIENT['id'].", ".$answer.", ".$_time.", '".$_today."')";
														// var_dump($data); die;
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
								
								// var_dump($error); die;
								
								if(empty($error))
									{
										// var_dump($data); die;
										// сперва выполняем insert пройденного опроса
										/*
												`id` INT AUTO_INCREMENT PRIMARY KEY,
												`id_patient` INT,
												`date` TINYTEXT,
												`time` INT
										*/
										
										// фиксируем, не врач ли заполняет анкету?
										$db['by_doctor'] = isAut() ? $_INFO['id'] : 0;
										
										if(mysql_query("INSERT INTO `reports`(`id_patient`, `date`, `time`, `alert`, `by_doctor`) VALUES (".$_PATIENT['id'].", '".$_today."', ".$_time.", ".$db['alert'].", ".$db['by_doctor'].")"))
											{
												$__id_report = mysql_insert_id();
												// склеиваем запрос
												$ins = implode(', ', $data);
												$query = $insert.$ins;
												// fatalError($query);
												if(mysql_query($query))
													{
														setTitle('Спасибо');
														getHeader();
														showSuccess('Ответы на вопросы сохранены. Спасибо');
														////////////// ALERT!!!!
														if($alert)
															{
																$alerts = implode(PHP_EOL, $alert_text);
																$message = 'Внимание, при прохождении теста выбран alert-ответ!'.PHP_EOL;
																$message .= $alerts.PHP_EOL;
																$message .= 'ФИО пациента: <b>'.$_PATIENT['name'].'</b>'.PHP_EOL;
																$message .= 'Участок: <b>'.$_AREA['title'].'</b>'.PHP_EOL;
																$message .= 'Телефон: '.$_PATIENT['phone'].PHP_EOL;
																
																$message .= PHP_EOL;
																$message .= 'Ссылка на отчет: https://'.$_SITE['domain'].'/reports/view/'.$__id_report;
																$tg = new Tg($_TG['api']);
																$tg -> send($_AREA['tg_id'], $message);
															}
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
				if(mysql_num_rows($q_check) == 0 OR $_DEBUG == 1)
					{
						setTitle('Прохождение опроса');
						getHeader();
						?>
						
						<a href="/pass/<?=$sid?>/alert" class="btn btn-danger">Мне срочно требуется помощь</a>
						
						<?php
						showSuccess('На каждый вопрос выберите вариант ответа, наиболее соответствующий Вашему мнению.');
						showFormError($error);
						// запросим список вопросов
						$q_q = mysql_query("SELECT * FROM `questions` WHERE `draft` = 0 ORDER BY `priority` ASC, `id` DESC");
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
											<input type="submit" name="button" value="Сохранить" class="btn btn-primary" />
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
						setTitle('Ошибка');
						getHeader();
						echo '<a href="/pass/'.$sid.'/alert" class="btn btn-danger">Мне срочно требуется помощь</a><br /><br />';
						showError('Вы уже проходили опрос сегодня. Если Вы заполнили часть данных ошибочно и хотите их изменить - свяжитесь с медицинским работником.');
						getFooter();
						exit;
					}
			}
		else
			{
				setTitle('Ошибка');
				getHeader();
				showError('Возможно, Вы воспользовались неверной ссылкой. Обратитесь к медицинскому работнику, если уверены, что это произошло по ошибке.');
				getFooter();
				exit;
			}
	}
else
	{
		setTitle('Ошибка');
		getHeader();
		showError('Возможно, Вы воспользовались неверной ссылкой. Обратитесь к медицинскому работнику, если уверены, что это произошло по ошибке.');
		getFooter();
		exit;
	}