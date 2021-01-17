<?php
require_once 'inc/core.php';
autOnly();
setTitle('Личный кабинет');

getHeader();
?>

<div class="list-group">
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
</div>
<?php
getFooter();