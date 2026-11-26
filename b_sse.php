<?php declare(strict_types=1);
require_once 'lib.php';

function sse_strumien(callable $format): void
{
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
	while (!connection_aborted())
	{
		$x = null;
		$rezultat = mysqli_query($polaczenie, 'SELECT * FROM pomiary ORDER BY id DESC LIMIT 1');
		if ($rezultat && ($wiersz = mysqli_fetch_array($rezultat))) {
			$x = [$wiersz[1], $wiersz[2], $wiersz[3], $wiersz[4], $wiersz[5]];
		}
		echo 'data:' . ($x === null ? 'BRAK' : $format($x)), "\n\n";
		while (ob_get_level() > 0) { ob_end_flush(); }
		flush();
		sleep(1);
	}
	mysqli_close($polaczenie);
}
