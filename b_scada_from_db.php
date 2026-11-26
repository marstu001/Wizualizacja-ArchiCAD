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

set_time_limit(0);
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');

$ostatnie_id = 0;
$pierwszy = true;
while (!connection_aborted())
{
	if ($pierwszy) {
		$sql = 'SELECT * FROM (SELECT id, x1, x2, x3, x4, x5, `datetime` FROM pomiary ORDER BY id DESC LIMIT 30) t ORDER BY id ASC';
	} else {
		$sql = 'SELECT id, x1, x2, x3, x4, x5, `datetime` FROM pomiary WHERE id > ' . $ostatnie_id . ' ORDER BY id ASC LIMIT 100';
	}
	$rezultat = mysqli_query($polaczenie, $sql);
	$liczba = 0;
	while ($rezultat && ($w = mysqli_fetch_row($rezultat))) {
		echo "data:" . implode("\t", $w), "\n\n";
		$ostatnie_id = (int)$w[0];
		$liczba++;
	}
	if ($pierwszy && $liczba === 0) {
		echo "data:BRAK\n\n";
	}
	$pierwszy = false;
	while (ob_get_level() > 0) { ob_end_flush(); }
	flush();
	sleep(1);
}
mysqli_close($polaczenie);
