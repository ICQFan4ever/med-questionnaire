<?php

require_once 'inc/core.php';

setTitle('Inform');
getHeader();

if(isAut())
	{
		Redirect('/lk?frIndex');
	}
else
	{
		showInfo('Воспользуйтесь персональной ссылкой или выполните вход (для мед. работников)');
	}

getFooter();