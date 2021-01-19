<?php
require_once 'inc/core.php';

autOnly();
checkAccess(2);
/*

/reports							reports.php
/reports/(date)						reports.php?date=$1
/reports/patient/(id)				reports.php?id_patient=$1
/reports/area/(id_area)				reports.php?id_area=$1
/reports/area/(id_area)/(date)		reports.php?id_area=$1&date=$2
/reports/(view-delete)/(id)			reports.php?mode=viwe&id_report=$1



if(id_area)
{
// level 4
// определенная зона
// +date
}

if(id_report)
{
// определенный репорт
}

if(id_patient)
{
// определенный пациент
// +date
}

if(date && больше ничо)
{
// аччот по всем дням
}

*/