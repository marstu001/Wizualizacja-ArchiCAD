<?php declare(strict_types=1);
require_once 'p_lib.php';
p_wymagaj_admin();
csrf_wymagaj();
$link = p_polacz();
$akcja = (string)($_POST['akcja'] ?? '');
$idpod = (int)($_POST['idpod'] ?? 0);

if ($akcja === 'zapisz') {
	$idpom = (int)($_POST['idpom'] ?? 0);
	$nazwa = trim((string)($_POST['nazwa'] ?? ''));
	$x1 = (int)($_POST['x1'] ?? 0); $y1 = (int)($_POST['y1'] ?? 0); $x2 = (int)($_POST['x2'] ?? 0); $y2 = (int)($_POST['y2'] ?? 0);
	if ($x1 > $x2) { [$x1, $x2] = [$x2, $x1]; }
	if ($y1 > $y2) { [$y1, $y2] = [$y2, $y1]; }
	$pod = p_zapytanie($link, "SELECT idpod FROM podklad WHERE idpod = ? AND widok = '2d'", 'i', [$idpod]);
	if (!$pod) { flash('danger', 'Wybierz podkład.'); }
	elseif ($nazwa === '' || mb_strlen($nazwa) > 100) { flash('danger', 'Podaj nazwę pomieszczenia (do 100 znaków).'); }
	elseif ($x2 - $x1 < 10 || $y2 - $y1 < 10) { flash('danger', 'Zaznacz prostokąt pomieszczenia na planie.'); }
	else {
		if ($idpom > 0) { p_wykonaj($link, 'UPDATE pomieszczenie SET nazwa = ?, x1 = ?, y1 = ?, x2 = ?, y2 = ? WHERE idpom = ? AND idpod = ?', 'siiiiii', [$nazwa, $x1, $y1, $x2, $y2, $idpom, $idpod]); }
		else { p_wykonaj($link, 'INSERT INTO pomieszczenie (idpod, nazwa, x1, y1, x2, y2) VALUES (?, ?, ?, ?, ?, ?)', 'isiiii', [$idpod, $nazwa, $x1, $y1, $x2, $y2]); }
		foreach (p_zapytanie($link, "SELECT ids, X_srodka AS x, Y_srodka AS y, idnazwa FROM srodek WHERE idpod = ? AND X_srodka IS NOT NULL", 'i', [$idpod]) as $sr) {
			$r = (int)(p_zapytanie($link, 'SELECT rozmiar FROM nazwa_srodka WHERE idnazwa = ?', 'i', [(int)$sr['idnazwa']])[0]['rozmiar'] ?? 80);
			$pom = p_pomieszczenie_dla($link, $idpod, (int)$sr['x'] + $r / 2, (int)$sr['y'] + $r / 2);
			p_wykonaj($link, 'UPDATE srodek SET idpom = ? WHERE ids = ?', 'ii', [$pom ? $pom['idpom'] : null, (int)$sr['ids']]);
		}
		flash('success', 'Zapisano pomieszczenie „' . $nazwa . '”.');
	}
} elseif ($akcja === 'usun') {
	$idpom = (int)($_POST['idpom'] ?? 0);
	p_wykonaj($link, 'UPDATE srodek SET idpom = NULL WHERE idpom = ?', 'i', [$idpom]);
	p_wykonaj($link, 'DELETE FROM pomieszczenie WHERE idpom = ? AND idpod = ?', 'ii', [$idpom, $idpod]);
	flash('success', 'Usunięto pomieszczenie.');
} else {
	flash('danger', 'Nieznana akcja.');
}
header('Location: p_pomieszczenia.php?idpod=' . $idpod);
exit();
