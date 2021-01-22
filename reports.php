<?php
require_once 'inc/core.php';

autOnly();
checkAccess(2);
/*

/reports							reports.php
/reports/(date)						reports.php?date=$1 // L2+ - своё, L4 - всё
+/reports/patient/(id)				reports.php?id_patient=$1 // checkAccess
+/reports/area/(id_area)				reports.php?id_area=$1 // L4
+/reports/area/(id_area)/(date)		reports.php?id_area=$1&date=$2 // L4
+/reports/(view-delete)/(id)			reports.php?mode=view&id_report=$1 // checkAccess




*/
// echo '<pre>';
// var_dump($_GET);
// die;
// Просмотр репортов по конкретной area
if(isset($_GET['id_area']))
	{
		$id_area = (int)$_GET['id_area'];
		$q_areas = mysql_query("SELECT * FROM `areas` WHERE `deleted` = 0 AND `id` = ".$id_area);
		if(mysql_num_rows($q_areas) == 1)
			{
				$_area = mysql_fetch_assoc($q_areas);
				
				// определим дату
				$date = @preg_match('#^(?:[0-9]{4})\-(?:[0-9]{2})\-(?:[0-9]{2})$#', $_GET['date']) ? $_GET['date'] : date('Y-m-d');
				$view_date = explode('-', $date);
				$view_date = array_reverse($view_date);
				$view_date = implode('.', $view_date);
				
				// у нас 2 вида пациентов - прошедшие и не прошедшие. 
				// сделаем один запрос и дважды прогоним его через fetch_assoc
				
				// Прошедшие
				$q_done = mysql_query("SELECT *, `reports`.`id` AS `id_report`, `doctors`.`name` AS `doctor_name`, `patients`.`name` AS `patient_name` FROM `patients` JOIN `reports` ON `reports`.`id_patient` = `patients`.`id` LEFT JOIN `doctors` ON `reports`.`by_doctor` = `doctors`.`id` AND `reports`.`date` = '".$view_date."' WHERE `patients`.`id_area` = ".$_area['id']." ORDER BY `alert` DESC");
				$c_done = mysql_num_rows($q_done);
				// SELECT *, `patients`.`id` as `patient_id`, `reports`.`id` AS `report_id` FROM `patients` JOIN `reports` ON `reports`.`id_patient` = `patients`.`id` AND `reports`.`date` = '20.01.2021' WHERE EXISTS (SELECT * FROM `reports` WHERE `id_patient` = `patients`.`id` AND `date` = '20.01.2021')
				
				
				// Непрошедшие
				$q_undone = mysql_query("SELECT * FROM `patients` WHERE NOT EXISTS (SELECT * FROM `reports` WHERE `id_patient` = `patients`.`id` AND `date` = '".$view_date."') AND `patients`.`id_area` = ".$_area['id']." ORDER BY `patients`.`name` ASC");
				$c_undone = mysql_num_rows($q_undone);
				
				setTitle('Отчет по участку');
				getHeader();
				showInfo('<b>'.$_area['title'].'</b> за '.$view_date);
				?>
				
				Выберите дату:<br />
				<div class="row">
					<form id="form_id">
						<div class="col-sm-3" style="margin-bottom: 5px;">
							<input type="date" class="form-control" value="<?=$date?>" id="date_field_id" />
						</div>
						
						<div class="col-sm-3" style="margin-bottom: 5px;">
							<input type="submit" class="btn btn-primary" value="Просмотр" />
						</div>
					</form>
				</div>
				<script>
				var form = document.getElementById('form_id');

				form.onsubmit = function () 
					{
						var el = document.getElementById('date_field_id');
						console.log('test123');
						document.location.href='/reports/area/<?=$_area["id"]?>/' + el.value;
						return false;
					}
				</script>
				<!--<pre><?="SELECT *, `reports`.`id` AS `id_report`, `doctors`.`name` FROM `patients` JOIN `reports` ON `reports`.`id_patient` = `patients`.`id` LEFT JOIN `doctors` ON `reports`.`by_doctor` = `doctor`.`id` AND `reports`.`date` = '".$view_date."' WHERE `patients`.`id_area` = ".$_area['id']." ORDER BY `alert` DESC"?></pre>-->
				<?php
				if($c_done > 0)
					{
						?>
						<h5>Прошедшие опрос</h5>
						<?php
						while($report = mysql_fetch_assoc($q_done))
							{
								$class = $report['alert'] == '1' ? 'warning' : 'primary';
								?>
								<div class="alert alert-<?=$class?>" onclick="location.href='/reports/view/<?=$report['id_report']?>'">
									<!--id, id_area, name, sid, phone, deleted, id, id_patient, date, time, by_doctor, alert, id_report-->
									<b>ФИО</b>: <a href="/patients/view/<?=$report['id_patient']?>" onclick="return false;"><?=$report['patient_name']?></a><br />
									<b>Телефон</b>: <a href="tel:<?=$report['phone']?>" onclick="return false;"><?=$report['phone']?></a><br />
									<b>Опрос пройден</b>: <?=$view_date == date('d.m.Y') ? date('H:i:s', $report['time']) : date('d.m.Y H:i:s', $report['time'])?>
									<?=$report['alert'] == 1 ? '<br /><b>На часть вопросов получен alert-ответ</b>' : ''?>
									<?=!empty($report['doctor_name']) ? '<br /><i>Опрос пройден медработником '.$report['doctor_name'].'</i>' : ''?>
								</div>
								<?php
							}
					}
				else
					{
						showError('Никто не проходил опрос в указанную дату');
					}
				
				if($c_undone > 0)
					{
						?>
						<h5>Не прошедшие опрос</h5>
						<?php
						while($report = mysql_fetch_assoc($q_undone))
							{
								?>
								<div class="alert alert-secondary">
									<b>ФИО</b>: <a href="/patients/view/<?=$report['id_patient']?>"><?=$report['name']?></a><br />
									<b>Телефон</b>: <a href="tel:<?=$report['phone']?>"><?=$report['phone']?></a>
								</div>
								<?php
							}
					}
				else
					{
						showSuccess('Сегодня все пациенты данного участка прошли опрос. Невероятно!');
					}
				getFooter();
				exit;
			}
	}

// Просмотр (и удаление) конкретного репорта
if(isset($_GET['id_report']))
	{
		// die('here');
		$id_report = (int)$_GET['id_report'];
		// сразу проверяем права доступа
		$q_report = mysql_query("SELECT *, `reports`.`id` as `id_report`, `patients`.`id` AS `id_patient` FROM `reports` JOIN `patients` ON `reports`.`id_patient` = `patients`.`id` WHERE `reports`.`id` = ".$id_report);
		// id, id_patient, date, time, by_doctor, alert, id, id_area, name, sid, phone, deleted, id_report, id_patient
		
		if(mysql_num_rows($q_report) == 1)
			{
				// die('here');sedf;lksdp[;fk
				$_report = mysql_fetch_assoc($q_report);
				if($_INFO['level'] >= 4 OR $_INFO['id_area'] == $_report['id_area'])
					{
						// die('here');
						// success. Определяем mode
						if($_GET['mode'] == 'view')
							{
								setTitle('Просмотр отчета');
								getHeader();
								
								?>
								<a href="/reports/patient/<?=$_report['id_patient']?>" class="btn btn-sm btn-primary">Посмотреть все отчеты по пациенту</a><br /><br />
								<div class="alert alert-primary">
									<b>ФИО</b>: <a href="/patients/view/<?=$_report['id_patient']?>"><?=$_report['name']?></a><br />
									<b>Телефон</b>: <a href="tel:<?=$report['phone']?>"><?=$_report['phone']?></a><br />
									<b>Опрос пройден</b>: <?=$_report['date'] == date('d.m.Y') ? date('H:i:s', $_report['time']) : date('d.m.Y H:i:s', $_report['time'])?><br />
									<?=$_report['alert'] == 1 ? '<br /><b>На часть вопросов получен alert-ответ</b>' : ''?>
								</div>
								<h5>Ответы пользователя</h5>
								<?php
								// запрашиваем список вопросов-ответов
								$q_answers = mysql_query("SELECT * FROM `questions_answers` JOIN `questions` ON `questions_answers`.`id_question` = `questions`.`id` WHERE `id_patient` = ".$_report['id_patient']." AND `questions_answers`.`date` = '".$_report['date']."' ORDER BY `questions`.`priority` ASC");
								// echo "<pre>SELECT * FROM `questions_answers` JOIN `questions` ON `questions_answers`.`id_question` = `questions`.`id` WHERE `id_patient` = ".$_report['id_patient']."' AND `questions_answers`.`date` = '".$_report['date']."' ORDER BY `questions`.`priority` ASC</pre>";
								if(mysql_num_rows($q_answers) > 0)
									{
										// Выводим 
										// id, id_question, id_patient, answer, time, date, id_report, id, text, alert, positive, negative, draft, priority
										while($answer = mysql_fetch_assoc($q_answers))
											{
												$class = $answer['answer'] == $answer['alert'] ? 'warning' : 'primary';
												?>
												<div class="alert alert-<?=$class?>">
													<b>Вопрос: </b><?=$answer['text']?><br />
													<b>Ответ: </b><?=$answer['answer'] == 1 ? $answer['positive'] : $answer['negative']?><br />
													<b>Alert: </b><?=$answer['alert'] == 1 ? $answer['positive'] : $answer['negative']?>
												</div>
												<?php
											}
										echo '<br /><a href="/reports/delete/'.$_report['id_report'].'" class="btn btn-sm btn-danger"  onclick="return confirm(\'Внимание: данное действие НЕОБРАТИМО! Удаление отчета позволит пациенту пройти его повторно. Удалить?\')">Удалить отчет</a>';
									}
								else
									{
										showError('По данному отчету не зафиксировано ответов');
									}
								getFooter();
								exit;
							}
						
						if($_GET['mode'] == 'delete')
							{
								// удаляем репорт
								mysql_query("DELETE FROM `reports` WHERE `id` = ".$_report['id_report']);
								// удаляем все ответы на вопросы
								mysql_query("DELETE FROM `questions_answers` WHERE `id_patient` = ".$_report['id_patient']." AND `date` = '".$_report['date']."'");
								// логгируем
								mysql_query("INSERT INTO `log`(`text`, `time`) VALUES ('".$_INFO['name']." удалил отчет пациента ".$_report['name']." за ".$_report['date']."')");
								// редиректим к списку всех отчетов
								Redirect('/reports');
							}
						
						fatalError('wrong_mode');
					}
				else
					{
						fatalError('У Вас недостаточно прав доступа для просмотра данного отчета');
					}
			}
		else
			{
				fatalError('Такой отчет не существует');
			}
	}

if(isset($_GET['id_patient']))
	{
		// die('here');
		$id_patient = (int)$_GET['id_patient'];
		
		// сразу проверим права доступа и запросим инфу об участке
		$q_patient = mysql_query("SELECT * FROM `patients` JOIN `areas` ON `patients`.`id_area` = `areas`.`id` WHERE `patients`.`id` = ".$id_patient);
		if(mysql_num_rows($q_patient) == 1)
			{
				$_patient = mysql_fetch_assoc($q_patient);
				if($_patient['id_area'] == $_INFO['id_area'] OR $_INFO['level'] >= 4)
					{
						setTitle('Просмотр отчетов пациента');
						getHeader();
						?>
						
						<div class="alert alert-secondary">
							<b>ФИО</b>: <a href="/patients/view/<?=$_patient['id']?>"><?=$_patient['name']?></a><br />
							<b>Телефон</b>: <a href="tel:<?=$_patient['phone']?>"><?=$_patient['phone']?></a><br />
							<b>Участок</b>: <a href="/areas/view/<?=$_patient['id_area']?>"><?=$_patient['title']?></a>
						</div>
				
						<?php
						// формируем список отчетов о пациенте
						$q_reports = mysql_query("SELECT * FROM `reports` WHERE `id_patient` = ".$_patient['id']." ORDER BY `id` DESC");
						if(mysql_num_rows($q_reports) > 0)
							{
								while($report = mysql_fetch_assoc($q_reports))
									{
										$class = $report['answer'] == $report['alert'] ? 'warning' : 'primary';
										?>
										<div class="alert alert-<?=$class?>" onclick="location.href='/reports/view/<?=$report['id']?>'">
											<?=date('d.m.Y H:i:s', $report['time'])?>
										</div>
										<?php
									}
							}
						else
							{
								showError('Нет отчетов по данному пациенту');
							}
						
						getFooter();
						exit;
					}
				else
					{
						fatalError('У Вас недостаточно прав доступа для просмотра отчетов данного пациента');
					}
			}
		else
			{
				fatalError('Пациент не найден');
			}
	}

// "пустой" mode 
// определим дату
$date = @preg_match('#^(?:[0-9]{4})\-(?:[0-9]{2})\-(?:[0-9]{2})$#', $_GET['date']) ? $_GET['date'] : date('Y-m-d');
$view_date = explode('-', $date);
$view_date = array_reverse($view_date);
$view_date = implode('.', $view_date);

// два типа запросов: для админов и юзверей
if($_INFO['level'] >= 4)
	{
		setTitle('Статистика отчетов по участкам');
		getHeader();
		
		$q_areas = mysql_query("SELECT * FROM `areas` ORDER BY `title`");
		if(mysql_num_rows($q_areas) > 0)
			{
				?>
				Выберите дату:<br />
				<div class="row">
					<form id="form_id">
						<div class="col-sm-3" style="margin-bottom: 5px;">
							<input type="date" class="form-control" value="<?=$date?>" id="date_field_id" />
						</div>
						
						<div class="col-sm-3" style="margin-bottom: 5px;">
							<input type="submit" class="btn btn-primary" value="Просмотр" />
						</div>
					</form>
				</div>
				<script>
				var form = document.getElementById('form_id');

				form.onsubmit = function () 
					{
						var el = document.getElementById('date_field_id');
						console.log('test123');
						document.location.href='/reports/' + el.value;
						return false;
					}
				</script>
				
				<?php
				$cc = 0;
				while($area = mysql_fetch_assoc($q_areas))
					{
						$cc++;
						// считаем пациентов текущей area и сразу же джойним запрос на reports
						$q_p = mysql_query("SELECT * FROM `patients` LEFT JOIN `reports` ON `patients`.`id` = `reports`.`id_patient` WHERE `patients`.`id_area` = ".$area['id']." AND `reports`.`date` = '".$view_date."'");
						$c_completed = 0;
						while($inf = mysql_fetch_assoc($q_p))
							{
								$c_completed++;
							}
						
						// общее числов поцыэнтов
						$c_total = mysql_num_rows(mysql_query("SELECT * FROM `patients` WHERE `id_area` = ".$area['id']));
						$c_incompleted = $c_total - $c_completed;
						
						if($cc % 3 == 1)
							{
								// starting row
								?>
								<div class="row">
								<?php
							}
						?>
						
						<div class="col-sm-4">
							<div class="alert alert-secondary">
								<a href="/reports/area/<?=$area['id']?>"><?=$area['title']?></a><br />
								Пройдено опросов: <span class="badge rounded-pill bg-success"><?=$c_completed?></span>, не пройдено: <span class="badge rounded-pill bg-secondary"><?=$c_incompleted?></span>
								<!--<pre><?="SELECT * FROM `patients` LEFT JOIN `reports` ON `patients`.`id` = `report`.`id_patient` WHERE `patients`.`id_area` = ".$area['id']." AND `reports`.`date` = '".$view_date."'"?></pre>-->
							</div>
						</div>
						
						<?php
						if($cc % 3 == 0)
							{
								// ending row
								?>
								</div>
								<?php
							}
					}
			}
		else
			{
				showError('Нет активных участков');
			}
		
		getFooter();
		exit;
	}
else
	{
		Redirect('/reports/area/'.$_INFO['id_area']);
	}