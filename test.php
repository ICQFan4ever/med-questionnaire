<?php

require_once 'inc/core.php';

$tg = new tg($_TG['api']);


var_dump($tg -> send(41851891, 'test'));