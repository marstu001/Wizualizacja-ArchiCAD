<?php declare(strict_types=1);
require_once 'k_lib.php';
sesja_start();
if (empty($_SESSION['loggedin']) || empty($_SESSION['user_id'])) {
	http_response_code(403);
	exit();
}
$login = (string)$_SESSION['username'];
$admin = !empty($_SESSION['admin']);
session_write_close();

$od = isset($_SERVER['HTTP_LAST_EVENT_ID']) ? (int)$_SERVER['HTTP_LAST_EVENT_ID'] : (int)($_GET['od'] ?? 0);

$link = db_polacz();
if (!$link) {
	http_response_code(500);
	exit();
}

function kom_ile(mysqli $link, string $login, bool $admin, int $do): int
{
	if ($admin) {
		$q = mysqli_prepare($link, 'SELECT COUNT(*) FROM messages WHERE idk <= ?');
		mysqli_stmt_bind_param($q, 'i', $do);
	} else {
		$q = mysqli_prepare($link, 'SELECT COUNT(*) FROM messages WHERE idk <= ? AND (user = ? OR recipient = ?)');
		mysqli_stmt_bind_param($q, 'iss', $do, $login, $login);
	}
	mysqli_stmt_execute($q);
	mysqli_stmt_bind_result($q, $n);
	mysqli_stmt_fetch($q);
	mysqli_stmt_close($q);
	return (int)$n;
}

set_time_limit(0);
ignore_user_abort(false);
header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');
while (ob_get_level() > 0) { ob_end_flush(); }
echo "retry: 1500\n\n";
flush();

$licznik = kom_ile($link, $login, $admin, $od);
$start   = time();
while (!connection_aborted() && time() - $start < 50) {
	$nowe = kom_pobierz($link, $login, $admin, $od, 0, 50);
	foreach ($nowe as $w) {
		$od = (int)$w['idk'];
		$licznik++;
		$dane = json_encode([
			'idk'   => $od,
			'html'  => kom_render($w, $login, $admin),
			'tekst' => kom_plik_info((string)$w['user'], (string)$w['message']) === null && kom_motyw((string)$w['message']) === null ? (string)$w['message'] : '',
		], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		echo 'id: ' . $od . "\nevent: msg\ndata: " . $dane . "\n\n";
	}
	$ile = kom_ile($link, $login, $admin, $od);
	if ($ile !== $licznik) {
		$licznik = $ile;
		echo "event: reload\ndata: 1\n\n";
	}
	if (!$nowe) { echo ": ping\n\n"; }
	flush();
	sleep(1);
}
mysqli_close($link);
