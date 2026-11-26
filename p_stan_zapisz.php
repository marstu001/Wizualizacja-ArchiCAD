<?php declare(strict_types=1);
require_once 'p_lib.php';
$s = p_sesja();
if ($s === null) { p_blad_json('Zaloguj się, aby zmieniać stan pracownika.', 401); }
csrf_wymagaj();
$link = p_polacz();
$idp = (int)($_POST['idp'] ?? 0);
$stan = (int)($_POST['stan'] ?? -1);
if (!isset(P_STANY[$stan])) { p_blad_json('Nieprawidłowy stan.'); }
$pr = p_pracownicy($link, 'p.idp = ?', 'i', [$idp]);
if (!$pr) { p_blad_json('Nie znaleziono pracownika.', 404); }
p_wykonaj($link, 'UPDATE pracownik SET stan = ? WHERE idp = ?', 'ii', [$stan, $idp]);
p_json(['ok' => true, 'stan' => $stan, 'stan_nazwa' => P_STANY[$stan]['nazwa'], 'pracownik' => $pr[0]['nazwisko']]);
