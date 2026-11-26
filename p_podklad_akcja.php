<?php declare(strict_types=1);
require_once 'p_lib.php';
p_wymagaj_admin();
csrf_wymagaj();
$link = p_polacz();
$akcja = (string)($_POST['akcja'] ?? '');

function p_usun_podklad(mysqli $link, int $idpod): void
{
	foreach (p_zapytanie($link, 'SELECT idpod FROM podklad WHERE idpod_plan = ?', 'i', [$idpod]) as $d) { p_usun_podklad($link, (int)$d['idpod']); }
	foreach (p_zapytanie($link, 'SELECT nazwa_pliku FROM warstwa WHERE idpod = ?', 'i', [$idpod]) as $w) { p_plik_podkladu_usun((string)$w['nazwa_pliku']); }
	p_wykonaj($link, 'DELETE FROM warstwa WHERE idpod = ?', 'i', [$idpod]);
	p_wykonaj($link, 'DELETE FROM pomieszczenie WHERE idpod = ?', 'i', [$idpod]);
	p_wykonaj($link, 'UPDATE srodek SET idpod = NULL, X_srodka = NULL, Y_srodka = NULL, idpom = NULL WHERE idpod = ?', 'i', [$idpod]);
	p_wykonaj($link, 'UPDATE pracownik SET idpod = NULL, X_pracownika = NULL, Y_pracownika = NULL WHERE idpod = ?', 'i', [$idpod]);
	$p = p_zapytanie($link, 'SELECT nazwa_pliku FROM podklad WHERE idpod = ?', 'i', [$idpod]);
	if ($p) { p_plik_podkladu_usun((string)$p[0]['nazwa_pliku']); }
	p_wykonaj($link, 'DELETE FROM podklad WHERE idpod = ?', 'i', [$idpod]);
}

if ($akcja === 'dodaj_podklad') {
	$nazwa = trim((string)($_POST['nazwa'] ?? ''));
	$widok = (string)($_POST['widok'] ?? '2d') === '3d' ? '3d' : '2d';
	$plan = (int)($_POST['idpod_plan'] ?? 0);
	$m = [];
	foreach (['ma', 'mb', 'mc', 'md', 'me', 'mf'] as $k) { $m[$k] = (string)($_POST[$k] ?? '') === '' ? null : (float)str_replace(',', '.', (string)$_POST[$k]); }
	if ($nazwa === '' || mb_strlen($nazwa) > 100) { flash('danger', 'Podaj nazwę podkładu (do 100 znaków).'); }
	elseif ($widok === '3d' && (!$plan || in_array(null, $m, true))) { flash('danger', 'Podkład quasi-3D wymaga wskazania planu 2D i sześciu współczynników przekształcenia.'); }
	else {
		[$plik, $szer, $wys, $blad] = p_obraz_zapisz($_FILES['plik'] ?? ['error' => UPLOAD_ERR_NO_FILE], P_PODKLAD_KATALOG, 'pod');
		if ($blad !== null) { flash('danger', $blad); }
		else {
			$kol = (int)(p_zapytanie($link, 'SELECT COALESCE(MAX(kolejnosc), 0) + 1 AS n FROM podklad')[0]['n'] ?? 1);
			p_wykonaj($link, 'INSERT INTO podklad (nazwa, nazwa_pliku, widok, idpod_plan, szer, wys, ma, mb, mc, md, me, mf, kolejnosc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 'sssiiiddddddi', [$nazwa, $plik, $widok, $widok === '3d' ? $plan : null, $szer, $wys, $m['ma'], $m['mb'], $m['mc'], $m['md'], $m['me'], $m['mf'], $kol]);
			flash('success', 'Dodano podkład „' . $nazwa . '”.');
		}
	}
} elseif ($akcja === 'dodaj_warstwe') {
	$idpod = (int)($_POST['idpod'] ?? 0);
	$nazwa = trim((string)($_POST['nazwa'] ?? ''));
	if (!p_zapytanie($link, 'SELECT idpod FROM podklad WHERE idpod = ?', 'i', [$idpod])) { flash('danger', 'Wybierz podkład.'); }
	elseif ($nazwa === '' || mb_strlen($nazwa) > 100) { flash('danger', 'Podaj nazwę warstwy (do 100 znaków).'); }
	else {
		[$plik, , , $blad] = p_obraz_zapisz($_FILES['plik'] ?? ['error' => UPLOAD_ERR_NO_FILE], P_PODKLAD_KATALOG, 'war');
		if ($blad !== null) { flash('danger', $blad); }
		else {
			$kol = (int)(p_zapytanie($link, 'SELECT COALESCE(MAX(kolejnosc), 0) + 1 AS n FROM warstwa WHERE idpod = ?', 'i', [$idpod])[0]['n'] ?? 1);
			p_wykonaj($link, 'INSERT INTO warstwa (idpod, nazwa, nazwa_pliku, kolejnosc) VALUES (?, ?, ?, ?)', 'issi', [$idpod, $nazwa, $plik, $kol]);
			flash('success', 'Dodano warstwę „' . $nazwa . '”.');
		}
	}
} elseif ($akcja === 'usun_podklad') {
	$idpod = (int)($_POST['idpod'] ?? 0);
	if (p_zapytanie($link, 'SELECT idpod FROM podklad WHERE idpod = ?', 'i', [$idpod])) { p_usun_podklad($link, $idpod); flash('success', 'Usunięto podkład wraz z warstwami i pomieszczeniami. Środki, które na nim stały, są teraz poza planem.'); }
} elseif ($akcja === 'usun_warstwe') {
	$idw = (int)($_POST['idw'] ?? 0);
	$w = p_zapytanie($link, 'SELECT nazwa_pliku FROM warstwa WHERE idw = ?', 'i', [$idw]);
	if ($w) { p_plik_podkladu_usun((string)$w[0]['nazwa_pliku']); p_wykonaj($link, 'DELETE FROM warstwa WHERE idw = ?', 'i', [$idw]); flash('success', 'Usunięto warstwę.'); }
} else {
	flash('danger', 'Nieznana akcja.');
}
header('Location: p_podklady.php');
exit();
