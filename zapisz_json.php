<?php declare(strict_types=1);
require_once 'lib.php';
wymagaj_logowania();
csrf_wymagaj();

header('Content-Type: application/json; charset=utf-8');
$imie = trim((string)($_POST['imie'] ?? ''));
if ($imie === '' || mb_strlen($imie) > 50) {
	http_response_code(422);
	echo json_encode(['ok' => false]);
	exit();
}
$ok = file_put_contents(__DIR__ . '/my_file.json', json_encode(['imie' => $imie], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n", LOCK_EX) !== false;
echo json_encode(['ok' => $ok]);
