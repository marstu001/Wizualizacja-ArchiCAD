<?php declare(strict_types=1);
require_once 'lib.php';
wymagaj_logowania();
csrf_wymagaj();

$text1 = trim((string)($_POST['text1'] ?? ''));
if ($text1 === '' || mb_strlen($text1) > 255) {
	flash('danger', 'Podaj tekst do 255 znaków.');
	header('Location: formularz.php');
	exit();
}
$link = db_polacz();
if (!$link) {
	flash('danger', 'Błąd połączenia z bazą danych.');
	header('Location: formularz.php');
	exit();
}
$q = mysqli_prepare($link, 'INSERT INTO ajax_from_db (text1) VALUES (?)');
mysqli_stmt_bind_param($q, 's', $text1);
$ok = mysqli_stmt_execute($q);
mysqli_stmt_close($q);
mysqli_close($link);
flash($ok ? 'success' : 'danger', $ok ? 'Dodano rekord.' : 'Nie udało się dodać rekordu.');
header('Location: formularz.php');
exit();
