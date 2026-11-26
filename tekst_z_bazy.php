<?php declare(strict_types=1);
require_once 'lib.php';
sesja_start();
if (empty($_SESSION['loggedin'])) {
	http_response_code(403);
	exit();
}
session_write_close();
$polaczenie = db_polacz();
if (!$polaczenie) {
	http_response_code(500);
	exit();
}
$text1 = null;
$rezultat = mysqli_query($polaczenie, 'SELECT text1 FROM ajax_from_db ORDER BY id DESC LIMIT 1');
if ($rezultat && ($wiersz = mysqli_fetch_row($rezultat))) {
	$text1 = (string)$wiersz[0];
}
mysqli_close($polaczenie);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode(['text' => $text1], JSON_UNESCAPED_UNICODE);
