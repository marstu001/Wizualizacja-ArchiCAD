<?php declare(strict_types=1);
require_once 'k_lib.php';
[$uid, $login, $admin] = wymagaj_logowania();

$idk  = (int)($_GET['id'] ?? 0);
$link = db_polacz();
if (!$link) { http_response_code(500); exit('Błąd bazy danych.'); }
$w = $idk > 0 ? kom_pobierz_jedna($link, $idk) : null;
mysqli_close($link);
if (!$w || !kom_widoczna($w, $login, $admin)) { http_response_code(404); exit('Nie ma takiego pliku.'); }
$plik = kom_plik_info((string)$w['user'], (string)$w['message']);
if ($plik === null || !$plik['istnieje']) { http_response_code(404); exit('Nie ma takiego pliku.'); }
$pelna = dysk_pelna((string)$w['user'], [KOM_PODKATALOG, $plik['nazwa']]);
if ($pelna === null || !is_file($pelna)) { http_response_code(404); exit('Nie ma takiego pliku.'); }
kom_wydaj_plik($pelna, isset($_GET['inline']));
