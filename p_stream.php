<?php declare(strict_types=1);
require_once 'p_lib.php';
sesja_start();
if (empty($_SESSION['loggedin']) || empty($_SESSION['user_id'])) { http_response_code(401); exit(); }
session_write_close();
ignore_user_abort(false);
set_time_limit(0);
header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-store');
header('X-Accel-Buffering: no');
while (ob_get_level() > 0) { ob_end_flush(); }
$link = p_polacz();
echo "retry: 2000\n\n";
$ostatni = '';
$start = time();
while (time() - $start < 50) {
	$dane = json_encode(p_zywe($link), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	$sygnatura = md5((string)$dane);
	if ($sygnatura !== $ostatni) {
		echo "event: zywe\ndata: " . $dane . "\n\n";
		$ostatni = $sygnatura;
	} else {
		echo ": ping\n\n";
	}
	flush();
	if (connection_aborted()) { break; }
	sleep(1);
}
