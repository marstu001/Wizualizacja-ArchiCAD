<?php declare(strict_types=1);
require_once 'k_lib.php';
[$uid, $login, $admin] = wymagaj_logowania();
csrf_wymagaj();

$ajax      = !empty($_POST['ajax']);
$post      = trim((string)($_POST['post'] ?? ''));
$odbiorca  = (string)($_POST['recipient'] ?? '');

$blad = '';
$motyw = kom_motyw($post);
if (strpos($post, KOM_MOTYW) === 0) {
	if ($motyw === null) { $blad = 'Niepoprawny motyw.'; }
	else { $post = KOM_MOTYW . kom_motyw_json($motyw); }
}
if ($blad !== '') {}
elseif ($post === '') { $blad = 'Wpisz treść wiadomości.'; }
elseif ($motyw === null && mb_strlen($post) > KOM_TEKST_MAX) { $blad = 'Wiadomość może mieć najwyżej ' . KOM_TEKST_MAX . ' znaków.'; }
elseif ($odbiorca === '') { $blad = 'Wybierz adresata.'; }
elseif ($odbiorca === $login) { $blad = 'Wybierz innego użytkownika niż Ty.'; }

$link = db_polacz();
if (!$link) { $blad = $blad ?: 'Błąd połączenia z bazą danych.'; }
if ($blad === '' && !kom_uzytkownik_istnieje($link, $odbiorca)) { $blad = 'Wybrany adresat nie istnieje.'; }

$idk = 0;
if ($blad === '') {
	$idk = kom_zapisz($link, $post, $login, $odbiorca);
	if ($idk === 0) { $blad = 'Nie udało się zapisać wiadomości.'; }
}

if ($ajax) {
	if ($blad !== '') { kom_json(['ok' => false, 'blad' => $blad], 422); }
	$w = kom_pobierz_jedna($link, $idk);
	kom_json(['ok' => true, 'idk' => $idk, 'html' => $w ? kom_render($w, $login, $admin) : '']);
}
if ($blad !== '') { flash('danger', $blad); }
header('Location: k_index1.php');
exit();
