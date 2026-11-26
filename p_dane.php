<?php declare(strict_types=1);
require_once 'p_lib.php';
$s = p_sesja();
if ($s === null) { p_blad_json('Zaloguj się.', 401); }
$link = p_polacz();
$ja = null;
if ($s) {
	$p = p_pracownicy($link, 'p.idu = ?', 'i', [$s['uid']]);
	$ja = ['login' => $s['login'], 'admin' => $s['admin'], 'idp' => $p ? $p[0]['idp'] : null];
}
$zywe = p_zywe($link);
p_json([
	'ok' => true,
	'podklady' => p_podklady($link),
	'pomieszczenia' => p_pomieszczenia($link),
	'nazwy' => p_nazwy_srodkow($link),
	'srodki' => $zywe['srodki'],
	'pracownicy' => $zywe['pracownicy'],
	'stany' => array_map(static fn($k, $v) => ['id' => $k, 'nazwa' => $v['nazwa'], 'ikona' => $v['ikona'], 'ramka' => $v['ramka']], array_keys(P_STANY), P_STANY),
	'ja' => $ja,
	'model' => is_file(__DIR__ . '/p_three/budynek.bin') ? ['skrypt' => p_plik_wersja('p_model3d.js'), 'plik' => p_plik_wersja('p_three/budynek.bin'), 'urz' => is_file(__DIR__ . '/p_three/urzadzenia.bin') ? p_plik_wersja('p_three/urzadzenia.bin') : null, 'q3' => is_file(__DIR__ . '/p_three/q3_urzadzenia.json') ? json_decode(file_get_contents(__DIR__ . '/p_three/q3_urzadzenia.json'), true) : null] : null,
	'csrf' => $s ? csrf_token() : '',
]);
