<?php

require($_SERVER['DOCUMENT_ROOT'].'/path/to/phpLinkyAPI.php'); //Linky custom API
$filePath = $_SERVER['DOCUMENT_ROOT'].'/path/to/linkyLog.json'; //Json data file

$enedis_user = 'mylogin';
$enedis_pass = 'myPass';

$_Linky = new Linky($enedis_user, $enedis_pass, true);
if (isset($_Linky->error)) echo '__ERROR__: ', $_Linky->error, "<br>";

$newJson = $_Linky->_data;

if (@file_exists($filePath))
{
    $prevDatas = json_decode(file_get_contents($filePath), true);
}
else
{
    $prevDatas = $newJson;
}


//_____Update current month:
$timezone = 'Europe/Paris';
$today = new DateTime('NOW', new DateTimeZone($timezone));
$thisMonth = $today->format('M Y');

$updateTime = $today->format('d/m/Y H:i:s');
$prevDatas['Update'] = $updateTime;

if ($newJson['months'][$thisMonth] != null) $prevDatas['months'][$thisMonth] = $newJson['months'][$thisMonth];

//if first day of month, update previous month:
if ($today->format('d') == '01')
{
	$prevMonth = clone $today;
	$prevMonth->sub(new DateInterval('P1M'));
	$prevMonth = $prevMonth->format('M Y');
	$prevDatas['months'][$prevMonth] = $newJson['months'][$prevMonth];
}

//_____Update current year:
$thisYear = new DateTime();
$thisYear = $thisYear->format('Y');

if ($newJson['years'][$thisYear] != null) $prevDatas['years'][$thisYear] = $newJson['years'][$thisYear];

//if first day of year, update previous year:
if ($today->format('d/m') == '01/01')
{
	$prevYear = clone $today;
	$prevYear->sub(new DateInterval('P1Y'));
	$prevYear = $prevYear->format('Y');
	$prevDatas['years'][$prevYear] = $newJson['years'][$prevYear];
}

//_____Does yesterday hours exists ?
$yesterday = clone $today;
$yesterday->sub(new DateInterval('P1D'));
$yesterday = $yesterday->format('d/m/Y');
//avoid empty data:
if (!isset($prevDatas['hours'][$yesterday]))
{
	$h = $newJson['hours'][$yesterday]["00:00"];
	if ($h != 'kW' and $h != '-2kW') $prevDatas['hours'][$yesterday] = $newJson['hours'][$yesterday];
}

//_____Add yesterday day:
if (!isset($prevDatas['days'][$yesterday]))
{
	$prevDatas['days'][$yesterday] = $newJson['days'][$yesterday];
}


file_put_contents($filePath, json_encode($prevDatas, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

?>
