<?php declare(strict_types=1);
require_once 'lib.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$login = (string)($_GET['login'] ?? '');
if (!login_poprawny($login)) {
	echo json_encode(['wolny' => false]);
	exit();
}
$link = db_polacz();
if (!$link) {
	echo json_encode(['wolny' => false]);
	exit();
}
$q = mysqli_prepare($link, 'SELECT id FROM users WHERE username = ?');
mysqli_stmt_bind_param($q, 's', $login);
mysqli_stmt_execute($q);
mysqli_stmt_store_result($q);
$wolny = mysqli_stmt_num_rows($q) === 0;
mysqli_stmt_close($q);
mysqli_close($link);
echo json_encode(['wolny' => $wolny]);
