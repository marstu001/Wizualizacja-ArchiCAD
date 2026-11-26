<?php declare(strict_types=1);
require_once 'k_lib.php';
[$uid, $login, $admin] = wymagaj_logowania();
session_write_close();

$partner = (string)($_GET['z'] ?? '');
$przed   = (int)($_GET['przed'] ?? 0);
$od_min  = (int)($_GET['od_min'] ?? 0);
$link = db_polacz();
if (!$link) { kom_json(['ok' => false], 500); }
if ($partner !== '' && !kom_uzytkownik_istnieje($link, $partner)) { kom_json(['ok' => false], 404); }

$limit = $od_min > 0 ? 2000 : KOM_STRONA + 1;
$wiersze = kom_pobierz($link, $login, $admin, 0, $przed, $limit, $od_min, $partner);
$jeszcze = false;
if ($od_min === 0 && count($wiersze) > KOM_STRONA) { array_pop($wiersze); $jeszcze = true; }
$wiersze = array_reverse($wiersze);
$html = '';
foreach ($wiersze as $w) { $html .= kom_render($w, $login, $admin); }
$motyw = null;
if ($partner !== '') {
	$m = kom_motyw_rozmowy($link, $login, $partner);
	if ($m !== null) { $motyw = ['tlo' => $m['tlo'], 'tw' => $m['tw'], 'k' => (object)$m['k']]; }
}
kom_json(['ok' => true, 'html' => $html, 'jeszcze' => $jeszcze, 'ile' => count($wiersze), 'motyw' => $motyw]);
