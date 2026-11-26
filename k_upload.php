<?php declare(strict_types=1);
require_once 'k_lib.php';
[$uid, $login, $admin] = wymagaj_logowania();
csrf_wymagaj();

$ajax     = !empty($_POST['ajax']);
$odbiorca = (string)($_POST['recipient'] ?? '');
$bledy    = [];
$html     = [];

$link = db_polacz();
if (!$link) { $bledy[] = 'Błąd połączenia z bazą danych.'; }
elseif ($odbiorca === '') { $bledy[] = 'Wybierz adresata.'; }
elseif ($odbiorca === $login) { $bledy[] = 'Wybierz innego użytkownika niż Ty.'; }
elseif (!kom_uzytkownik_istnieje($link, $odbiorca)) { $bledy[] = 'Wybrany adresat nie istnieje.'; }

$pliki = [];
if (!$bledy && !empty($_FILES['plik'])) {
	$f = $_FILES['plik'];
	if (is_array($f['name'])) {
		foreach ($f['name'] as $i => $n) { $pliki[] = ['name' => $n, 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]]; }
	} else {
		$pliki[] = $f;
	}
}
if (!$bledy && !$pliki) { $bledy[] = 'Wybierz plik do wysłania.'; }

$komunikaty_bledu = [
	UPLOAD_ERR_INI_SIZE   => 'plik przekracza limit serwera (' . ini_get('upload_max_filesize') . ')',
	UPLOAD_ERR_FORM_SIZE  => 'plik jest za duży',
	UPLOAD_ERR_PARTIAL    => 'plik został przesłany tylko częściowo',
	UPLOAD_ERR_NO_FILE    => 'nie wybrano pliku',
	UPLOAD_ERR_NO_TMP_DIR => 'brak katalogu tymczasowego na serwerze',
	UPLOAD_ERR_CANT_WRITE => 'serwer nie może zapisać pliku',
];

if (!$bledy) {
	$dom = dysk_katalog_domowy($login);
	$cel = $dom !== null ? $dom . '/' . KOM_PODKATALOG : null;
	if ($cel === null || (!is_dir($cel) && !@mkdir($cel, 0755))) {
		$bledy[] = 'Nie udało się utworzyć katalogu użytkownika na serwerze.';
	} else {
		foreach ($pliki as $p) {
			$nazwa_oryg = (string)$p['name'];
			if ((int)$p['error'] !== UPLOAD_ERR_OK) { $bledy[] = $nazwa_oryg . ': ' . ($komunikaty_bledu[(int)$p['error']] ?? 'błąd przesyłania'); continue; }
			if ((int)$p['size'] === 0) { $bledy[] = $nazwa_oryg . ': plik jest pusty'; continue; }
			$nazwa = nazwa_pliku_czysta($nazwa_oryg);
			if ($nazwa === '') { $bledy[] = $nazwa_oryg . ': niepoprawna nazwa pliku'; continue; }
			$nazwa = dysk_wolna_nazwa($cel, $nazwa);
			if (!is_uploaded_file((string)$p['tmp_name']) || !move_uploaded_file((string)$p['tmp_name'], $cel . '/' . $nazwa)) {
				$bledy[] = $nazwa_oryg . ': nie udało się zapisać pliku';
				continue;
			}
			$idk = kom_zapisz($link, $login . '/' . KOM_PODKATALOG . '/' . $nazwa, $login, $odbiorca);
			if ($idk === 0) { @unlink($cel . '/' . $nazwa); $bledy[] = $nazwa_oryg . ': nie udało się zapisać wiadomości'; continue; }
			$w = kom_pobierz_jedna($link, $idk);
			if ($w) { $html[] = ['idk' => $idk, 'html' => kom_render($w, $login, $admin)]; }
		}
	}
}

if ($ajax) {
	kom_json(['ok' => $html !== [] && !$bledy, 'wyslano' => count($html), 'wiadomosci' => $html, 'bledy' => $bledy], $html || !$bledy ? 200 : 422);
}
if ($html) { flash('success', 'Wysłano plików: ' . count($html) . '.'); }
foreach ($bledy as $b) { flash('danger', $b); }
header('Location: k_index1.php');
exit();
