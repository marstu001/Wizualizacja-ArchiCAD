<?php declare(strict_types=1);
require_once 'p_lib.php';
$s = p_sesja();
if ($s === null) { p_blad_json('Zaloguj się, aby zmieniać położenie.', 401); }
csrf_wymagaj();
$link = p_polacz();
$typ = (string)($_POST['typ'] ?? '');
$id = (int)($_POST['id'] ?? 0);
$zdejmij = !empty($_POST['zdejmij']);
$x = (int)round((float)($_POST['x'] ?? 0));
$y = (int)round((float)($_POST['y'] ?? 0));
$idpod = (int)($_POST['idpod'] ?? 0);

$pod = p_zapytanie($link, "SELECT idpod, szer, wys FROM podklad WHERE idpod = ? AND widok = '2d'", 'i', [$idpod]);
if (!$zdejmij && !$pod) { p_blad_json('Nieznany podkład.'); }

if ($typ === 'srodek') {
	$sr = p_srodki($link, 's.ids = ?', 'i', [$id]);
	if (!$sr) { p_blad_json('Nie znaleziono środka.', 404); }
	if ($zdejmij) {
		p_wykonaj($link, 'UPDATE srodek SET idpod = NULL, X_srodka = NULL, Y_srodka = NULL, idpom = NULL WHERE ids = ?', 'i', [$id]);
		p_json(['ok' => true]);
	}
	$r = $sr[0]['rozmiar'];
	$x = max(0, min($x, (int)$pod[0]['szer'] - $r));
	$y = max(0, min($y, (int)$pod[0]['wys'] - $r));
	$pom = p_pomieszczenie_dla($link, $idpod, $x + $r / 2, $y + $r / 2);
	$idpom = $pom ? $pom['idpom'] : null;
	$status = $sr[0]['status'] === 'aktywny' ? 'aktywny' : $sr[0]['status'];
	p_wykonaj($link, 'UPDATE srodek SET idpod = ?, X_srodka = ?, Y_srodka = ?, idpom = ?, status = ? WHERE ids = ?', 'iiiisi', [$idpod, $x, $y, $idpom, $status, $id]);
	p_json(['ok' => true, 'x' => $x, 'y' => $y, 'pomieszczenie' => $pom ? $pom['nazwa'] : '']);
}

if ($typ === 'pracownik') {
	$pr = p_pracownicy($link, 'p.idp = ?', 'i', [$id]);
	if (!$pr) { p_blad_json('Nie znaleziono pracownika.', 404); }
	if ($zdejmij) {
		p_wykonaj($link, 'UPDATE pracownik SET idpod = NULL, X_pracownika = NULL, Y_pracownika = NULL WHERE idp = ?', 'i', [$id]);
		p_json(['ok' => true]);
	}
	$r = 60;
	$x = max(0, min($x, (int)$pod[0]['szer'] - $r));
	$y = max(0, min($y, (int)$pod[0]['wys'] - $r));
	p_wykonaj($link, 'UPDATE pracownik SET idpod = ?, X_pracownika = ?, Y_pracownika = ? WHERE idp = ?', 'iiii', [$idpod, $x, $y, $id]);
	p_json(['ok' => true, 'x' => $x, 'y' => $y]);
}

p_blad_json('Nieznany typ elementu.');
