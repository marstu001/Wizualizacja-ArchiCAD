<?php declare(strict_types=1);
ini_set('display_errors', '1'); error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');
require __DIR__ . '/db.php';
echo "Baza: " . DB_NAME . ", użytkownik: " . DB_USER . "\n";
$link = db_polacz();
if (!$link) { exit("BŁĄD połączenia z bazą: " . mysqli_connect_error() . "\n"); }
echo "Połączenie z bazą: OK\n";
$t = [];
$r = mysqli_query($link, 'SHOW TABLES');
while ($w = mysqli_fetch_row($r)) { $t[] = $w[0]; }
echo "Tabele (" . count($t) . "): " . implode(', ', $t) . "\n";
foreach (['users' => ['username', 'password', 'bledne', 'blokada_do', 'avatar'], 'goscieportalu' => ['ipaddress', 'login', 'wynik', 'przegladarka'], 'break_ins' => ['ipaddress', 'login']] as $tab => $kol) {
	$r = mysqli_query($link, "SHOW COLUMNS FROM `$tab`");
	if (!$r) { echo "BRAK tabeli $tab\n"; continue; }
	$m = [];
	while ($w = mysqli_fetch_row($r)) { $m[] = $w[0]; }
	$brak = array_diff($kol, $m);
	echo "Tabela $tab: " . ($brak ? 'BRAKUJE kolumn: ' . implode(', ', $brak) : 'kolumny OK') . "\n";
}
$r = mysqli_query($link, "SELECT username, LEFT(password, 7) FROM users");
if ($r) { while ($w = mysqli_fetch_row($r)) { echo "Użytkownik: {$w[0]} ({$w[1]}...)\n"; } }
echo "\n--- próba logowania admin/admin ---\n";
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = ['user' => 'admin', 'pass' => 'admin', 'ajax' => '1'];
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
register_shutdown_function(function () { $o = ob_get_clean(); echo $o . "\n"; });
ob_start();
include __DIR__ . '/weryfikuj3.php';
