<?php declare(strict_types=1);
require_once 'lib.php';
wymagaj_logowania();
csrf_wymagaj();

$wroc = isset($_POST['scada']) ? 'b_formularz.php?scada=1' : 'b_formularz.php';
$x = [];
foreach (['x1', 'x2', 'x3', 'x4', 'x5'] as $n) {
	$v = filter_var(str_replace(',', '.', trim((string)($_POST[$n] ?? ''))), FILTER_VALIDATE_FLOAT);
	if ($v === false) {
		flash('danger', 'Pole ' . $n . ' musi być liczbą.');
		header('Location: ' . $wroc);
		exit();
	}
	$x[] = $v;
}
$link = db_polacz();
if (!$link) {
	flash('danger', 'Błąd połączenia z bazą danych.');
	header('Location: ' . $wroc);
	exit();
}
$q = mysqli_prepare($link, 'INSERT INTO pomiary (x1, x2, x3, x4, x5) VALUES (?, ?, ?, ?, ?)');
mysqli_stmt_bind_param($q, 'ddddd', ...$x);
$ok = mysqli_stmt_execute($q);
mysqli_stmt_close($q);
mysqli_close($link);
flash($ok ? 'success' : 'danger', $ok ? 'Dodano pomiar.' : 'Nie udało się dodać pomiaru.');
header('Location: ' . $wroc);
exit();
