<?php declare(strict_types=1);
require_once 'p_lib.php';
$s = p_wymagaj();
csrf_wymagaj();
$link = p_polacz();
$ids = (int)($_POST['ids'] ?? 0);
$akcja = (string)($_POST['akcja'] ?? '');
$opis = mb_substr(trim((string)($_POST['opis'] ?? '')), 0, 500);
$powrot = 'p_tabela.php';

$w = p_srodki($link, 's.ids = ?', 'i', [$ids]);
if (!$w) { flash('danger', 'Nie znaleziono środka.'); header('Location: ' . $powrot); exit(); }
$sr = $w[0];
$idp = p_pracownicy($link, 'p.idu = ?', 'i', [$s['uid']])[0]['idp'] ?? null;

function p_zdarzenie(mysqli $link, int $ids, ?int $idp, string $rodzaj, ?float $kwota, string $opis): void
{
	p_wykonaj($link, 'INSERT INTO zdarzenie (ids, idp, rodzaj, kwota, opis) VALUES (?, ?, ?, ?, ?)', 'iisds', [$ids, $idp, $rodzaj, $kwota, $opis]);
}

$zdejmij = 'UPDATE srodek SET status = ?, idpod = NULL, X_srodka = NULL, Y_srodka = NULL WHERE ids = ?';

switch ($akcja) {
	case 'odpis':
		$domyslna = round($sr['koszt'] * $sr['stawka_amort'] / 100, 2);
		$kwota = ($_POST['kwota'] ?? '') === '' ? $domyslna : round((float)str_replace(',', '.', (string)$_POST['kwota']), 2);
		if ($kwota <= 0) { flash('danger', 'Kwota odpisu musi być większa od zera.'); break; }
		if ($kwota > $sr['wartosc'] + 0.001) { flash('danger', 'Odpis nie może przekraczać wartości bieżącej (' . p_wartosc_pln($sr['wartosc']) . ').'); break; }
		p_zdarzenie($link, $ids, $idp, 'odpis', $kwota, $opis);
		flash('success', 'Zarejestrowano odpis ' . p_wartosc_pln($kwota) . ' dla ' . $sr['identyfikator'] . '.');
		break;
	case 'przeniesienie':
		$idpom = (int)($_POST['idpom'] ?? 0);
		$pom = p_zapytanie($link, 'SELECT idpom, nazwa FROM pomieszczenie WHERE idpom = ?', 'i', [$idpom]);
		if (!$pom) { flash('danger', 'Wybierz magazyn.'); break; }
		p_wykonaj($link, 'UPDATE srodek SET status = ?, idpod = NULL, X_srodka = NULL, Y_srodka = NULL, idpom = ? WHERE ids = ?', 'sii', ['w magazynie', $idpom, $ids]);
		p_zdarzenie($link, $ids, $idp, 'przeniesienie', null, 'Przeniesiono do: ' . $pom[0]['nazwa'] . ($opis !== '' ? '. ' . $opis : ''));
		flash('success', $sr['identyfikator'] . ' został przeniesiony do magazynu (' . $pom[0]['nazwa'] . ').');
		break;
	case 'likwidacja':
	case 'zniszczenie':
	case 'kradziez':
		$status = ['likwidacja' => 'zlikwidowany', 'zniszczenie' => 'zniszczony', 'kradziez' => 'skradziony'][$akcja];
		p_wykonaj($link, $zdejmij, 'si', [$status, $ids]);
		p_zdarzenie($link, $ids, $idp, $akcja, null, $opis);
		flash('success', 'Zapisano: ' . P_RODZAJE[$akcja] . ' dla ' . $sr['identyfikator'] . '.');
		break;
	case 'inne':
		if ($opis === '') { flash('danger', 'Opisz zdarzenie.'); break; }
		p_zdarzenie($link, $ids, $idp, 'inne', null, $opis);
		flash('success', 'Zapisano zdarzenie dla ' . $sr['identyfikator'] . '.');
		break;
	case 'przywroc':
		p_wykonaj($link, 'UPDATE srodek SET status = ? WHERE ids = ?', 'si', ['aktywny', $ids]);
		p_zdarzenie($link, $ids, $idp, 'inne', null, 'Przywrócono do użytkowania' . ($opis !== '' ? '. ' . $opis : ''));
		flash('success', $sr['identyfikator'] . ' wrócił do użytkowania. Ustaw jego położenie na planie.');
		break;
	case 'usun':
		p_wykonaj($link, 'DELETE FROM zdarzenie WHERE ids = ?', 'i', [$ids]);
		p_wykonaj($link, 'DELETE FROM srodek WHERE ids = ?', 'i', [$ids]);
		flash('success', 'Usunięto środek ' . $sr['identyfikator'] . '.');
		break;
	default:
		flash('danger', 'Nieznana akcja.');
}
header('Location: ' . $powrot);
exit();
