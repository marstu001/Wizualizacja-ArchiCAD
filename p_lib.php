<?php declare(strict_types=1);
require_once 'lib.php';

const P_STANY = [
	0 => ['nazwa' => 'brak',                     'ikona' => '',   'ramka' => 'brak'],
	1 => ['nazwa' => 'praca lokalna',            'ikona' => '🏢', 'ramka' => 'zielona'],
	2 => ['nazwa' => 'praca zdalna online',      'ikona' => '🔌', 'ramka' => 'zielona'],
	4 => ['nazwa' => 'dyżur pod telefonem',      'ikona' => '📞', 'ramka' => 'zielona'],
	5 => ['nazwa' => 'praca lokalna u klienta',  'ikona' => '⛏️', 'ramka' => 'zielona'],
	6 => ['nazwa' => 'po pracy',                 'ikona' => '🌙', 'ramka' => 'szara'],
	7 => ['nazwa' => 'urlop',                    'ikona' => '☀️', 'ramka' => 'zolta'],
	8 => ['nazwa' => 'L4',                       'ikona' => '🛏️', 'ramka' => 'czerwona'],
];
const P_STATUSY = ['aktywny' => 'aktywny', 'w magazynie' => 'w magazynie', 'zlikwidowany' => 'zlikwidowany', 'zniszczony' => 'zniszczony', 'skradziony' => 'skradziony'];
const P_RODZAJE = [
	'odpis'        => 'odpis amortyzacyjny',
	'przeniesienie' => 'przeniesienie do magazynu',
	'likwidacja'   => 'likwidacja',
	'zniszczenie'  => 'zniszczenie',
	'kradziez'     => 'kradzież',
	'inne'         => 'inne zdarzenie',
];
const P_FOTO_KATALOG = 'p_foto';
const P_PODKLAD_KATALOG = 'podklady';
const P_IKONY_KATALOG = 'p_ikony';

function p_polacz(): mysqli
{
	$link = db_polacz();
	if (!$link) { http_response_code(500); exit('Błąd połączenia z bazą danych.'); }
	return $link;
}

function p_glowna(): mysqli
{
	static $link = null;
	if ($link === null) {
		$link = db_polacz();
		if (!$link) { http_response_code(500); exit('Błąd połączenia z bazą użytkowników.'); }
	}
	return $link;
}

function p_ikona_url(string $plik): string
{
	static $wbudowane = [
		'kamera.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64"><circle cx="32" cy="32" r="30" fill="#ffffff" stroke="#1f3a5f" stroke-width="3"/><path d="M10 22 h30 a4 4 0 0 1 4 4 v8 a4 4 0 0 1 -4 4 h-30 z" fill="#1f3a5f"/><circle cx="27" cy="30" r="6" fill="#19a7a8"/><circle cx="27" cy="30" r="2.4" fill="#dbe7f3"/><path d="M44 28 l10 -5 v16 l-10 -5 z" fill="#6b7a8c"/><rect x="18" y="38" width="8" height="10" rx="1" fill="#1f3a5f"/><rect x="14" y="47" width="16" height="4" rx="2" fill="#1f3a5f"/><circle cx="14" cy="26" r="1.6" fill="#f59f00"/></svg>',
		'access_point.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64"><circle cx="32" cy="32" r="30" fill="#ffffff" stroke="#1f3a5f" stroke-width="3"/><rect x="14" y="38" width="36" height="11" rx="4" fill="#1f3a5f"/><circle cx="22" cy="43.5" r="1.8" fill="#19a7a8"/><circle cx="28" cy="43.5" r="1.8" fill="#f59f00"/><g fill="none" stroke="#19a7a8" stroke-width="3.2" stroke-linecap="round"><path d="M21 31 q11 -10 22 0"/><path d="M26 26 q6 -5 12 0" transform="translate(0 -3)"/></g><circle cx="32" cy="32" r="2.6" fill="#1f3a5f"/></svg>',
		'ekspres.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64"><circle cx="32" cy="32" r="30" fill="#ffffff" stroke="#1f3a5f" stroke-width="3"/><rect x="16" y="14" width="26" height="8" rx="3" fill="#1f3a5f"/><rect x="18" y="22" width="22" height="22" rx="3" fill="#6b7a8c"/><rect x="22" y="26" width="14" height="6" rx="2" fill="#dbe7f3"/><rect x="24" y="36" width="10" height="8" rx="1" fill="#19a7a8"/><rect x="14" y="44" width="30" height="5" rx="2" fill="#1f3a5f"/><path d="M45 28 h5 a4 4 0 0 1 0 9 h-5 z" fill="#f59f00"/><path d="M47 24 q-2 -3 0 -6 M51 24 q-2 -3 0 -6" stroke="#6b7a8c" stroke-width="2" fill="none" stroke-linecap="round"/></svg>',
		'trafo.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64"><circle cx="32" cy="32" r="30" fill="#ffffff" stroke="#1f3a5f" stroke-width="3"/><rect x="11" y="14" width="42" height="36" rx="4" fill="#1f3a5f"/><g fill="none" stroke="#f59f00" stroke-width="3.2"><circle cx="25" cy="30" r="9"/><circle cx="39" cy="30" r="9" stroke="#19a7a8"/></g><g stroke="#dbe7f3" stroke-width="2.4" stroke-linecap="round"><path d="M17 44 h6"/><path d="M41 44 h6"/></g></svg>',
		'akumulator.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64"><circle cx="32" cy="32" r="30" fill="#ffffff" stroke="#1f3a5f" stroke-width="3"/><rect x="10" y="22" width="40" height="26" rx="3" fill="#1f3a5f"/><rect x="50" y="30" width="5" height="10" rx="1.5" fill="#1f3a5f"/><rect x="14" y="26" width="7" height="18" rx="1" fill="#2f9e44"/><rect x="24" y="26" width="7" height="18" rx="1" fill="#2f9e44"/><rect x="34" y="26" width="7" height="18" rx="1" fill="#74b816"/><path d="M45 28 l-4 8 h4 l-3 8" stroke="#f59f00" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'agregat.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64"><circle cx="32" cy="32" r="30" fill="#ffffff" stroke="#1f3a5f" stroke-width="3"/><rect x="9" y="24" width="30" height="22" rx="3" fill="#1f3a5f"/><rect x="39" y="28" width="14" height="14" rx="2" fill="#6b7a8c"/><circle cx="46" cy="35" r="4" fill="#dbe7f3"/><rect x="13" y="19" width="6" height="5" fill="#6b7a8c"/><rect x="7" y="46" width="48" height="5" rx="2" fill="#1f3a5f"/><path d="M26 27 l-6 10 h6 l-3 9 l10 -12 h-6 l4 -7 z" fill="#f59f00"/></svg>',
		'szr.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64"><circle cx="32" cy="32" r="30" fill="#ffffff" stroke="#1f3a5f" stroke-width="3"/><rect x="10" y="15" width="44" height="34" rx="3" fill="#1f3a5f"/><rect x="13" y="18" width="38" height="28" rx="2" fill="none" stroke="#6b7a8c" stroke-width="1.6"/><text x="32" y="38" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-weight="700" font-size="15" fill="#ffffff">SZR</text><g stroke="#f59f00" stroke-width="2.4" stroke-linecap="round"><path d="M19 52 h6"/><path d="M39 52 h6"/></g></svg>',
		'rnr.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64"><circle cx="32" cy="32" r="30" fill="#ffffff" stroke="#1f3a5f" stroke-width="3"/><rect x="10" y="15" width="44" height="34" rx="3" fill="#1f3a5f"/><rect x="13" y="18" width="38" height="28" rx="2" fill="none" stroke="#6b7a8c" stroke-width="1.6"/><text x="32" y="38" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-weight="700" font-size="15" fill="#ffffff">RNR</text><g stroke="#f59f00" stroke-width="2.4" stroke-linecap="round"><path d="M19 52 h6"/><path d="M39 52 h6"/></g></svg>',
		'rng.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64"><circle cx="32" cy="32" r="30" fill="#ffffff" stroke="#1f3a5f" stroke-width="3"/><rect x="10" y="15" width="44" height="34" rx="3" fill="#1f3a5f"/><rect x="13" y="18" width="38" height="28" rx="2" fill="none" stroke="#6b7a8c" stroke-width="1.6"/><text x="32" y="38" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-weight="700" font-size="15" fill="#ffffff">RNG</text><g stroke="#f59f00" stroke-width="2.4" stroke-linecap="round"><path d="M19 52 h6"/><path d="M39 52 h6"/></g></svg>',
		'rg.svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64"><circle cx="32" cy="32" r="30" fill="#ffffff" stroke="#1f3a5f" stroke-width="3"/><rect x="10" y="15" width="44" height="34" rx="3" fill="#1f3a5f"/><rect x="13" y="18" width="38" height="28" rx="2" fill="none" stroke="#6b7a8c" stroke-width="1.6"/><text x="32" y="38" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-weight="700" font-size="19" fill="#ffffff">RG</text><g stroke="#f59f00" stroke-width="2.4" stroke-linecap="round"><path d="M19 52 h6"/><path d="M39 52 h6"/></g></svg>',
	];
	if (isset($wbudowane[$plik])) { return 'data:image/svg+xml;charset=utf-8,' . rawurlencode($wbudowane[$plik]); }
	return P_IKONY_KATALOG . '/' . $plik;
}

function p_plik_wersja(string $sciezka): string
{
	$f = __DIR__ . '/' . $sciezka;
	return is_file($f) ? $sciezka . '?v=' . filemtime($f) : $sciezka;
}

function p_sesja(): ?array
{
	sesja_start();
	if (empty($_SESSION['loggedin']) || empty($_SESSION['user_id'])) { return null; }
	return ['uid' => (int)$_SESSION['user_id'], 'login' => (string)$_SESSION['username'], 'admin' => !empty($_SESSION['admin'])];
}

function p_wymagaj(): array
{
	$s = p_sesja();
	if ($s === null) { header('Location: logowanie.php'); exit(); }
	return $s;
}

function p_wymagaj_admin(): array
{
	$s = p_wymagaj();
	if (!$s['admin']) { http_response_code(403); exit('Brak uprawnień.'); }
	return $s;
}

function p_json($dane, int $kod = 200): void
{
	http_response_code($kod);
	header('Content-Type: application/json; charset=utf-8');
	header('Cache-Control: no-store');
	echo json_encode($dane, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit();
}

function p_blad_json(string $tekst, int $kod = 400): void
{
	p_json(['ok' => false, 'blad' => $tekst], $kod);
}

function p_zapytanie(mysqli $link, string $sql, string $typy = '', array $dane = []): array
{
	$q = mysqli_prepare($link, $sql);
	if (!$q) { return []; }
	if ($typy !== '') { mysqli_stmt_bind_param($q, $typy, ...$dane); }
	mysqli_stmt_execute($q);
	$r = mysqli_stmt_get_result($q);
	$wiersze = $r ? mysqli_fetch_all($r, MYSQLI_ASSOC) : [];
	mysqli_stmt_close($q);
	return $wiersze;
}

function p_wykonaj(mysqli $link, string $sql, string $typy = '', array $dane = []): bool
{
	$q = mysqli_prepare($link, $sql);
	if (!$q) { return false; }
	if ($typy !== '') { mysqli_stmt_bind_param($q, $typy, ...$dane); }
	$ok = mysqli_stmt_execute($q);
	mysqli_stmt_close($q);
	return $ok;
}

function p_haslo_zapisz(mysqli $link, string $haslo): string
{
	$wiersze = p_zapytanie(p_glowna(), "SELECT password FROM users WHERE username NOT IN ('pracownik1','pracownik2','pracownik3','admin','serwisant') LIMIT 20");
	$jawne = false;
	foreach ($wiersze as $w) {
		if (strpos((string)$w['password'], '$2y$') !== 0) { $jawne = true; break; }
	}
	return $jawne ? $haslo : password_hash($haslo, PASSWORD_DEFAULT);
}

function p_pomieszczenie_dla(mysqli $link, int $idpod, float $cx, float $cy): ?array
{
	$w = p_zapytanie($link, 'SELECT idpom, nazwa FROM pomieszczenie WHERE idpod=? AND ? BETWEEN x1 AND x2 AND ? BETWEEN y1 AND y2 ORDER BY (x2-x1)*(y2-y1) ASC LIMIT 1', 'idd', [$idpod, $cx, $cy]);
	return $w ? ['idpom' => (int)$w[0]['idpom'], 'nazwa' => (string)$w[0]['nazwa']] : null;
}

function p_podklady(mysqli $link): array
{
	$pod = p_zapytanie($link, 'SELECT idpod, nazwa, nazwa_pliku, widok, idpod_plan, szer, wys, ma, mb, mc, md, me, mf, kolejnosc FROM podklad ORDER BY kolejnosc, idpod');
	$war = p_zapytanie($link, 'SELECT idw, idpod, nazwa, nazwa_pliku FROM warstwa ORDER BY kolejnosc, idw');
	$wyn = [];
	foreach ($pod as $p) {
		$w = [];
		foreach ($war as $x) { if ((int)$x['idpod'] === (int)$p['idpod']) { $w[] = ['idw' => (int)$x['idw'], 'nazwa' => $x['nazwa'], 'plik' => p_plik_wersja(P_PODKLAD_KATALOG . '/' . $x['nazwa_pliku'])]; } }
		$wyn[] = [
			'idpod' => (int)$p['idpod'], 'nazwa' => $p['nazwa'], 'plik' => p_plik_wersja(P_PODKLAD_KATALOG . '/' . $p['nazwa_pliku']), 'widok' => $p['widok'],
			'plan' => $p['idpod_plan'] === null ? null : (int)$p['idpod_plan'], 'szer' => (int)$p['szer'], 'wys' => (int)$p['wys'],
			'm' => $p['ma'] === null ? null : [(float)$p['ma'], (float)$p['mb'], (float)$p['mc'], (float)$p['md'], (float)$p['me'], (float)$p['mf']],
			'warstwy' => $w,
		];
	}
	return $wyn;
}

function p_pomieszczenia(mysqli $link): array
{
	$w = p_zapytanie($link, 'SELECT idpom, idpod, nazwa, x1, y1, x2, y2 FROM pomieszczenie ORDER BY idpod, nazwa');
	foreach ($w as &$x) { foreach (['idpom', 'idpod', 'x1', 'y1', 'x2', 'y2'] as $k) { $x[$k] = (int)$x[$k]; } }
	return $w;
}

function p_nazwy_srodkow(mysqli $link): array
{
	$w = p_zapytanie($link, 'SELECT idnazwa, nazwa, nazwa_pliku, prefiks, rozmiar, idtyp FROM nazwa_srodka ORDER BY nazwa');
	foreach ($w as &$x) { $x['idnazwa'] = (int)$x['idnazwa']; $x['rozmiar'] = (int)$x['rozmiar']; $x['idtyp'] = (int)$x['idtyp']; $x['plik'] = p_ikona_url($x['nazwa_pliku']); unset($x['nazwa_pliku']); }
	return $w;
}

function p_srodki(mysqli $link, string $warunek = '', string $typy = '', array $dane = []): array
{
	$sql = 'SELECT s.ids, s.nazwa, s.identyfikator, s.idnazwa, s.idtyp, s.idpod, s.idpom, s.koszt, s.`datatime` AS data, s.X_srodka AS x, s.Y_srodka AS y, s.uwagi, s.status, s.stawka_amort,
		n.nazwa AS nazwa_typ, n.nazwa_pliku, n.rozmiar, t.nazwa AS typ, p.nazwa AS pomieszczenie,
		s.koszt - COALESCE((SELECT SUM(z.kwota) FROM zdarzenie z WHERE z.ids = s.ids AND z.rodzaj = \'odpis\'), 0) AS wartosc
		FROM srodek s JOIN nazwa_srodka n ON n.idnazwa = s.idnazwa LEFT JOIN typ_srodka t ON t.idtyp = s.idtyp LEFT JOIN pomieszczenie p ON p.idpom = s.idpom';
	if ($warunek !== '') { $sql .= ' WHERE ' . $warunek; }
	$sql .= ' ORDER BY s.identyfikator';
	$w = p_zapytanie($link, $sql, $typy, $dane);
	foreach ($w as &$x) {
		$x['ids'] = (int)$x['ids']; $x['idnazwa'] = (int)$x['idnazwa']; $x['idtyp'] = (int)$x['idtyp'];
		$x['idpod'] = $x['idpod'] === null ? null : (int)$x['idpod']; $x['idpom'] = $x['idpom'] === null ? null : (int)$x['idpom'];
		$x['x'] = $x['x'] === null ? null : (int)$x['x']; $x['y'] = $x['y'] === null ? null : (int)$x['y'];
		$x['koszt'] = (float)$x['koszt']; $x['wartosc'] = (float)$x['wartosc']; $x['stawka_amort'] = (float)$x['stawka_amort']; $x['rozmiar'] = (int)$x['rozmiar'];
		$x['plik'] = p_ikona_url($x['nazwa_pliku']); unset($x['nazwa_pliku']);
	}
	return $w;
}

function p_pracownicy(mysqli $link, string $warunek = '', string $typy = '', array $dane = []): array
{
	$sql = 'SELECT p.idp, p.idu, p.idpod, p.nazwisko, p.stan, p.X_pracownika AS x, p.Y_pracownika AS y, p.nazwa_pliku_fotki AS foto
		FROM pracownik p';
	if ($warunek !== '') { $sql .= ' WHERE ' . $warunek; }
	$sql .= ' ORDER BY p.nazwisko';
	$w = p_zapytanie($link, $sql, $typy, $dane);
	$loginy = [];
	foreach (p_zapytanie(p_glowna(), 'SELECT id, username FROM users') as $u) { $loginy[(int)$u['id']] = (string)$u['username']; }
	foreach ($w as $k => $x0) { if (!isset($loginy[(int)$x0['idu']])) { unset($w[$k]); } }
	$w = array_values($w);
	foreach ($w as &$x) {
		$x['login'] = $loginy[(int)$x['idu']];
		$x['idp'] = (int)$x['idp']; $x['idu'] = (int)$x['idu']; $x['stan'] = (int)$x['stan'];
		$x['idpod'] = $x['idpod'] === null ? null : (int)$x['idpod'];
		$x['x'] = $x['x'] === null ? null : (int)$x['x']; $x['y'] = $x['y'] === null ? null : (int)$x['y'];
		$x['foto'] = $x['foto'] !== '' ? P_FOTO_KATALOG . '/' . $x['foto'] : P_FOTO_KATALOG . '/domyslny.png';
		foreach (['jpg', 'jpeg', 'webp'] as $rozszerzenie) {
			$wlasne = preg_replace('/\.[A-Za-z0-9]+$/', '', $x['foto']) . '.' . $rozszerzenie;
			if (is_file(__DIR__ . '/' . $wlasne)) { $x['foto'] = $wlasne; break; }
		}
		$x['stan_nazwa'] = P_STANY[$x['stan']]['nazwa'] ?? '';
	}
	return $w;
}

function p_zywe(mysqli $link): array
{
	$s = p_srodki($link);
	$wyn = [];
	foreach ($s as $x) {
		$wyn[] = ['ids' => $x['ids'], 'idpod' => $x['idpod'], 'x' => $x['x'], 'y' => $x['y'], 'status' => $x['status'], 'idpom' => $x['idpom'], 'pomieszczenie' => $x['pomieszczenie'], 'nazwa' => $x['nazwa'], 'identyfikator' => $x['identyfikator'], 'wartosc' => $x['wartosc'], 'koszt' => $x['koszt'], 'uwagi' => $x['uwagi'], 'data' => $x['data'], 'plik' => $x['plik'], 'rozmiar' => $x['rozmiar'], 'idnazwa' => $x['idnazwa'], 'typ' => $x['typ'], 'nazwa_typ' => $x['nazwa_typ']];
	}
	return ['srodki' => $wyn, 'pracownicy' => p_pracownicy($link)];
}

function p_wartosc_pln(float $v): string
{
	return number_format($v, 2, ',', ' ') . ' zł';
}

function p_foto_zapisz(array $plik, string $login): array
{
	if (($plik['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) { return [null, null]; }
	if ($plik['error'] !== UPLOAD_ERR_OK) { return [null, 'Nie udało się przesłać zdjęcia.']; }
	if ($plik['size'] > 5 * 1024 * 1024) { return [null, 'Zdjęcie jest za duże (maksymalnie 5 MB).']; }
	$info = @getimagesize($plik['tmp_name']);
	$typy = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
	if (!$info || !isset($typy[$info[2]])) { return [null, 'Zdjęcie musi być w formacie JPG, PNG, GIF lub WEBP.']; }
	if (!is_dir(P_FOTO_KATALOG) && !@mkdir(P_FOTO_KATALOG, 0755, true)) { return [null, 'Nie można zapisać zdjęcia na serwerze.']; }
	$nazwa = preg_replace('/[^A-Za-z0-9_-]/', '', $login) . '_' . bin2hex(random_bytes(4)) . '.' . $typy[$info[2]];
	if (!move_uploaded_file($plik['tmp_name'], P_FOTO_KATALOG . '/' . $nazwa)) { return [null, 'Nie można zapisać zdjęcia na serwerze.']; }
	return [$nazwa, null];
}

function p_foto_usun(string $nazwa): void
{
	if ($nazwa !== '' && preg_match('/^[A-Za-z0-9_-]+_[0-9a-f]{8}\.(jpg|png|gif|webp)$/', $nazwa)) { @unlink(P_FOTO_KATALOG . '/' . $nazwa); }
}

function p_menu(string $aktywna): void
{
	$s = p_sesja();
	$poz = [
		'index.php' => ['bi-grid', 'Start'],
	];
	if ($s) {
		$poz['p_wizualizacja.php'] = ['bi-map', 'Wizualizacja'];
		$poz['p_tabela.php'] = ['bi-table', 'Tabela środków'];
		$poz['p_srodek.php'] = ['bi-plus-circle', 'Dodaj środek'];
		$poz['p_stany.php'] = ['bi-people', 'Stany pracowników'];
		$poz['p_pracownicy.php'] = ['bi-people-fill', 'Pracownicy'];
		$poz['p_profil.php'] = ['bi-person-badge', 'Mój profil'];
	}
	if ($s && $s['admin']) {
		$poz['p_podklady.php'] = ['bi-layers', 'Podkłady'];
		$poz['p_pomieszczenia.php'] = ['bi-bounding-box', 'Pomieszczenia'];
	}
	echo '<link rel="stylesheet" href="' . p_plik_wersja('p_styl.css') . '">';
	echo '<ul class="nav nav-pills p-menu mb-3">';
	foreach ($poz as $plik => [$ikona, $tekst]) {
		echo '<li class="nav-item"><a class="nav-link' . ($plik === $aktywna ? ' active' : '') . '" href="' . e($plik) . '"><i class="bi ' . e($ikona) . '"></i> ' . e($tekst) . '</a></li>';
	}
	if (!$s) {
		echo '<li class="nav-item ms-auto"><a class="nav-link" href="logowanie.php"><i class="bi bi-box-arrow-in-right"></i> Zaloguj się</a></li>';
	}
	echo '</ul>';
}

function p_flash_wypisz(): void
{
	foreach (flash_pobierz() as [$typ, $tekst]) {
		echo '<div class="alert alert-' . e($typ) . ' alert-dismissible fade show" role="alert">' . e($tekst) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button></div>';
	}
}

function p_obraz_zapisz(array $plik, string $katalog, string $prefiks): array
{
	if (($plik['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) { return [null, 0, 0, 'Wybierz plik z obrazem.']; }
	if ($plik['error'] !== UPLOAD_ERR_OK) { return [null, 0, 0, 'Nie udało się przesłać pliku (limit serwera: ' . ini_get('upload_max_filesize') . ').']; }
	if ($plik['size'] > 15 * 1024 * 1024) { return [null, 0, 0, 'Plik jest za duży (maksymalnie 15 MB).']; }
	$info = @getimagesize($plik['tmp_name']);
	$typy = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
	if (!$info || !isset($typy[$info[2]])) { return [null, 0, 0, 'Plik musi być obrazem JPG, PNG, GIF lub WEBP.']; }
	if (!is_dir($katalog) && !@mkdir($katalog, 0755, true)) { return [null, 0, 0, 'Nie można zapisać pliku na serwerze.']; }
	$nazwa = $prefiks . '_' . bin2hex(random_bytes(5)) . '.' . $typy[$info[2]];
	if (!move_uploaded_file($plik['tmp_name'], $katalog . '/' . $nazwa)) { return [null, 0, 0, 'Nie można zapisać pliku na serwerze.']; }
	return [$nazwa, (int)$info[0], (int)$info[1], null];
}

function p_plik_podkladu_usun(string $nazwa): void
{
	if (preg_match('/^(pod|war)_[0-9a-f]{10}\.(jpg|png|gif|webp)$/', $nazwa)) { @unlink(P_PODKLAD_KATALOG . '/' . $nazwa); }
}

function p_elementy_wyboru(mysqli $link, int $pomin_srodek = 0, int $pomin_prac = 0): array
{
	$w = [];
	foreach (p_srodki($link, "s.status = 'aktywny' AND s.X_srodka IS NOT NULL") as $x) {
		if ($x['ids'] === $pomin_srodek) { continue; }
		$w[] = ['idpod' => $x['idpod'], 'x' => $x['x'], 'y' => $x['y'], 'r' => $x['rozmiar'], 'plik' => $x['plik'], 'et' => $x['identyfikator'], 'prac' => false];
	}
	foreach (p_pracownicy($link) as $x) {
		if ($x['idp'] === $pomin_prac || $x['x'] === null || $x['stan'] === 0) { continue; }
		$w[] = ['idpod' => $x['idpod'], 'x' => $x['x'], 'y' => $x['y'], 'r' => 60, 'plik' => $x['foto'], 'et' => $x['nazwisko'], 'prac' => true];
	}
	return $w;
}
