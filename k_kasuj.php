<?php declare(strict_types=1);
require_once 'k_lib.php';
[$uid, $login, $admin] = wymagaj_logowania();
csrf_wymagaj();

$idk  = (int)($_POST['idk'] ?? 0);
$link = db_polacz();
if (!$link) { kom_json(['ok' => false, 'blad' => 'Błąd połączenia z bazą danych.'], 500); }
$w = $idk > 0 ? kom_pobierz_jedna($link, $idk) : null;
if (!$w) { kom_json(['ok' => false, 'blad' => 'Nie ma takiej wiadomości.'], 404); }
if ($w['user'] !== $login && !$admin) { kom_json(['ok' => false, 'blad' => 'Możesz usuwać tylko własne wiadomości.'], 403); }

$plik = kom_plik_info((string)$w['user'], (string)$w['message']);
$q = mysqli_prepare($link, 'DELETE FROM messages WHERE idk = ?');
mysqli_stmt_bind_param($q, 'i', $idk);
$ok = mysqli_stmt_execute($q);
mysqli_stmt_close($q);
if ($ok && $plik !== null && $plik['istnieje']) { @unlink($plik['sciezka']); }
kom_json(['ok' => (bool)$ok]);
