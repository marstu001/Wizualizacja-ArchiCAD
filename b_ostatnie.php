<?php declare(strict_types=1);
require_once 'lib.php';
wymagaj_logowania();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$wiersze = [];
$link = db_polacz();
if ($link) {
	$r = mysqli_query($link, 'SELECT id, x1, x2, x3, x4, x5, `datetime` FROM pomiary ORDER BY id DESC LIMIT 10');
	while ($r && ($w = mysqli_fetch_row($r))) { $wiersze[] = $w; }
	mysqli_close($link);
}
echo json_encode($wiersze);
