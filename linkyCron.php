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

//Update current month:
$timezone = 'Europe/Paris';
$today = new DateTime('NOW', new DateTimeZone($timezone));
$thisMonth = $today->format('M Y');

$updateTime = $today->format('d/m/Y H:i:s');
$prevDatas['Update'] = $updateTime;

if ($newJson['months'][$thisMonth] != null) $prevDatas['months'][$thisMonth] = $newJson['months'][$thisMonth];

//Update current year:
$thisYear = new DateTime();
$thisYear = $thisYear->format('Y');

if ($newJson['years'][$thisYear] != null) $prevDatas['years'][$thisYear] = $newJson['years'][$thisYear];

//Does yesterday hours exists ?
$yesterday = clone $today;
$yesterday->sub(new DateInterval('P1D'));
$yesterday = $yesterday->format('d/m/Y');
if (!isset($prevDatas['hours'][$yesterday]))
{
	//avoid empty data:
	$h = $newJson['hours'][$yesterday]["00:00"];
	if ($h != 'kW' and $h != '-2kW') $prevDatas['hours'][$yesterday] = $newJson['hours'][$yesterday];
}

//Add yesterday day:
if (!isset($prevDatas['days'][$yesterday]))
{
	$prevDatas['days'][$yesterday] = $newJson['days'][$yesterday];
}


file_put_contents($filePath, json_encode($prevDatas, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

?>
