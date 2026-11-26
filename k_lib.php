<?php declare(strict_types=1);
require_once 'lib.php';
require_once 'avatar.php';

const KOM_PODKATALOG = 'komunikator';
const KOM_STRONA     = 50;
const KOM_TEKST_MAX  = 1000;
const KOM_MOTYW      = '@@motyw:';

function kom_uzytkownicy(mysqli $link, string $login): array
{
	$q = mysqli_prepare($link, 'SELECT username, avatar FROM users WHERE username <> ? ORDER BY username');
	mysqli_stmt_bind_param($q, 's', $login);
	mysqli_stmt_execute($q);
	$r = mysqli_stmt_get_result($q);
	$lista = $r ? mysqli_fetch_all($r, MYSQLI_ASSOC) : [];
	mysqli_stmt_close($q);
	return $lista;
}

function kom_uzytkownik_istnieje(mysqli $link, string $login): bool
{
	$q = mysqli_prepare($link, 'SELECT 1 FROM users WHERE username = ?');
	mysqli_stmt_bind_param($q, 's', $login);
	mysqli_stmt_execute($q);
	mysqli_stmt_store_result($q);
	$ok = mysqli_stmt_num_rows($q) > 0;
	mysqli_stmt_close($q);
	return $ok;
}

function kom_odcien(string $login): int
{
	return (int)(crc32(strtolower($login)) % 360);
}

function kom_pobierz(mysqli $link, string $login, bool $admin, int $po = 0, int $przed = 0, int $limit = KOM_STRONA, int $od_min = 0, string $partner = ''): array
{
	$warunki = []; $typy = ''; $par = [];
	if ($partner !== '') {
		$warunki[] = '((m.user = ? AND m.recipient = ?) OR (m.user = ? AND m.recipient = ?))';
		$typy .= 'ssss'; array_push($par, $login, $partner, $partner, $login);
	} elseif (!$admin) {
		$warunki[] = '(m.user = ? OR m.recipient = ?)'; $typy .= 'ss'; array_push($par, $login, $login);
	}
	if ($po > 0)     { $warunki[] = 'm.idk > ?';  $typy .= 'i'; $par[] = $po; }
	if ($przed > 0)  { $warunki[] = 'm.idk < ?';  $typy .= 'i'; $par[] = $przed; }
	if ($od_min > 0) { $warunki[] = 'm.idk >= ?'; $typy .= 'i'; $par[] = $od_min; }
	$sql = 'SELECT m.idk, m.`datetime`, m.message, m.user, m.recipient, u.avatar FROM messages m LEFT JOIN users u ON u.username = m.user'
		. ($warunki ? ' WHERE ' . implode(' AND ', $warunki) : '')
		. ' ORDER BY m.idk ' . ($po > 0 ? 'ASC' : 'DESC') . ' LIMIT ' . max(1, $limit);
	$q = mysqli_prepare($link, $sql);
	if ($par) { mysqli_stmt_bind_param($q, $typy, ...$par); }
	mysqli_stmt_execute($q);
	$r = mysqli_stmt_get_result($q);
	$w = $r ? mysqli_fetch_all($r, MYSQLI_ASSOC) : [];
	mysqli_stmt_close($q);
	return $w;
}

function kom_pobierz_jedna(mysqli $link, int $idk): ?array
{
	$q = mysqli_prepare($link, 'SELECT m.idk, m.`datetime`, m.message, m.user, m.recipient, u.avatar FROM messages m LEFT JOIN users u ON u.username = m.user WHERE m.idk = ?');
	mysqli_stmt_bind_param($q, 'i', $idk);
	mysqli_stmt_execute($q);
	$r = mysqli_stmt_get_result($q);
	$w = $r ? mysqli_fetch_assoc($r) : null;
	mysqli_stmt_close($q);
	return $w ?: null;
}

function kom_widoczna(array $w, string $login, bool $admin): bool
{
	return $admin || $w['user'] === $login || $w['recipient'] === $login;
}

function kom_plik_info(string $user, string $message): ?array
{
	if (!preg_match('#^([A-Za-z0-9_-]{1,20})/' . KOM_PODKATALOG . '/([^/]+)$#', $message, $m)) { return null; }
	if ($m[1] !== $user || !nazwa_elementu_poprawna($m[2])) { return null; }
	$sciezka = DYSK_KATALOG . '/' . $user . '/' . KOM_PODKATALOG . '/' . $m[2];
	$istnieje = is_file($sciezka);
	return [
		'nazwa'    => $m[2],
		'sciezka'  => $sciezka,
		'istnieje' => $istnieje,
		'rozmiar'  => $istnieje ? (int)filesize($sciezka) : 0,
		'typ'      => typ_pliku($m[2]),
	];
}

function kom_motyw(string $message): ?array
{
	if (strpos($message, KOM_MOTYW) !== 0) { return null; }
	$d = json_decode(substr($message, strlen(KOM_MOTYW)), true);
	if (!is_array($d)) { return null; }
	$kolor = fn($c) => is_string($c) && ($c === '' || preg_match('/^#[0-9a-fA-F]{6}$/', $c)) ? $c : '';
	$k = [];
	foreach ((array)($d['k'] ?? []) as $u => $c) {
		if (is_string($u) && login_poprawny($u)) { $k[$u] = $kolor($c); }
	}
	$o = is_string($d['o'] ?? null) ? preg_replace('/[\x00-\x1F\x7F]/u', '', $d['o']) : '';
	return [
		'tlo' => max(0, min(8, (int)($d['tlo'] ?? 0))),
		'tw'  => $kolor($d['tw'] ?? ''),
		'k'   => $k,
		'o'   => mb_strimwidth((string)$o, 0, 90, '', 'UTF-8'),
	];
}

function kom_motyw_json(array $m): string
{
	return json_encode(['tlo' => $m['tlo'], 'tw' => $m['tw'], 'k' => (object)$m['k'], 'o' => $m['o']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function kom_motyw_rozmowy(mysqli $link, string $a, string $b): ?array
{
	$wzor = KOM_MOTYW . '%';
	$q = mysqli_prepare($link, 'SELECT message FROM messages WHERE message LIKE ? AND ((user = ? AND recipient = ?) OR (user = ? AND recipient = ?)) ORDER BY idk DESC LIMIT 1');
	mysqli_stmt_bind_param($q, 'sssss', $wzor, $a, $b, $b, $a);
	mysqli_stmt_execute($q);
	mysqli_stmt_bind_result($q, $msg);
	$m = mysqli_stmt_fetch($q) ? kom_motyw((string)$msg) : null;
	mysqli_stmt_close($q);
	return $m;
}

function kom_czas(string $dt): string
{
	$t = strtotime($dt);
	if ($t === false) { return $dt; }
	return date('Y-m-d', $t) === date('Y-m-d') ? date('H:i', $t) : date('d.m.Y H:i', $t);
}

function kom_podglad(string $user, string $message): string
{
	if (kom_motyw($message) !== null) { return '🎨 Zmieniono motyw'; }
	$plik = kom_plik_info($user, $message);
	if ($plik === null) { return mb_strimwidth(str_replace(["\r", "\n"], ' ', $message), 0, 60, '…', 'UTF-8'); }
	switch ($plik['typ']) {
		case 'obraz': return '📷 Zdjęcie';
		case 'wideo': return '🎬 Film';
		case 'audio': return '🎵 Dźwięk';
		case 'pdf':   return '📄 ' . $plik['nazwa'];
	}
	return '📎 ' . $plik['nazwa'];
}

function kom_tylko_emoji(string $t): bool
{
	$t = trim($t);
	if ($t === '' || mb_strlen($t) > 12) { return false; }
	return @preg_match('/^[\p{Extended_Pictographic}\x{200D}\x{FE0F}\x{1F3FB}-\x{1F3FF}\s]+$/u', $t) === 1;
}

function kom_render(array $w, string $login, bool $admin): string
{
	$idk  = (int)$w['idk'];
	$user = (string)$w['user'];
	$odb  = (string)$w['recipient'];
	$wlasna = $user === $login;
	$partner = $wlasna ? $odb : $user;
	$plik = kom_plik_info($user, (string)$w['message']);
	$h    = kom_odcien($user);
	$ts   = (int)strtotime((string)$w['datetime']);

	$motyw = kom_motyw((string)$w['message']);
	if ($motyw !== null) {
		return '<div class="kom-msg kom-sys" data-idk="' . $idk . '" data-partner="' . e($partner) . '" data-od="' . e($user) . '" data-do="' . e($odb) . '" data-ts="' . $ts . '" data-dzien="' . date('Y-m-d', $ts) . '" data-podglad="🎨 Zmieniono motyw" data-motyw="' . e(kom_motyw_json($motyw)) . '">'
			. '<div class="kom-sys-tekst"><i class="bi bi-palette"></i> <strong>' . e($user) . '</strong> ' . e($motyw['o'] !== '' ? $motyw['o'] : 'zmienił(a) motyw rozmowy') . '</div></div>';
	}

	$klasa = '';
	$czytaj = false;
	if ($plik === null) {
		$emoji = kom_tylko_emoji((string)$w['message']);
		$tresc = '<div class="kom-dymek' . ($emoji ? ' kom-emoji' : '') . '"><div class="kom-tekst">' . nl2br(e((string)$w['message']), false) . '</div></div>';
		$czytaj = !$emoji;
	} elseif (!$plik['istnieje']) {
		$tresc = '<div class="kom-dymek"><span class="fst-italic opacity-75"><i class="bi bi-file-earmark-x"></i> ' . e($plik['nazwa']) . '</span></div>';
	} else {
		$src = 'k_plik.php?id=' . $idk . '&inline=1';
		$pobierz = 'k_plik.php?id=' . $idk;
		$opis = '<div class="kom-plik-opis"><a href="' . $pobierz . '" title="Pobierz">' . e($plik['nazwa']) . '</a> &middot; ' . e(format_rozmiaru($plik['rozmiar'])) . '</div>';
		switch ($plik['typ']) {
			case 'obraz':
				$tresc = '<div class="kom-dymek kom-media"><a href="' . $src . '" class="kom-podglad" target="_blank" rel="noopener"><img class="kom-obraz" loading="lazy" src="' . $src . '" alt="' . e($plik['nazwa']) . '"></a></div>';
				break;
			case 'wideo':
				$tresc = '<div class="kom-dymek kom-media"><video class="kom-wideo" src="' . $src . '" controls autoplay muted playsinline preload="metadata"></video></div>';
				break;
			case 'audio':
				$tresc = '<div class="kom-dymek kom-audio-dymek"><audio class="kom-audio" src="' . $src . '" controls preload="none"></audio>' . $opis . '</div>';
				break;
			case 'pdf':
				$tresc = '<div class="kom-dymek"><a class="kom-plik-karta" href="' . $src . '" target="_blank" rel="noopener"><i class="bi ' . ikona_pliku($plik['nazwa']) . '"></i><span>Otwórz PDF</span></a>' . $opis . '</div>';
				break;
			default:
				$tresc = '<div class="kom-dymek"><a class="kom-plik-karta" href="' . $pobierz . '"><i class="bi ' . ikona_pliku($plik['nazwa']) . '"></i><span>Pobierz</span></a>' . $opis . '</div>';
		}
		if ($plik['typ'] === 'obraz' || $plik['typ'] === 'wideo') { $tresc .= $opis; }
	}

	$akcje = '';
	if ($czytaj) { $akcje .= '<button type="button" class="kom-akcja kom-czytaj" title="Przeczytaj na głos"><i class="bi bi-volume-up"></i></button>'; }
	if ($wlasna || $admin) { $akcje .= '<button type="button" class="kom-akcja kom-usun" title="Usuń"><i class="bi bi-trash"></i></button>'; }

	return '<div class="kom-msg ' . ($wlasna ? 'wlasna' : 'obca') . '" data-idk="' . $idk . '" data-partner="' . e($partner) . '" data-od="' . e($user) . '" data-do="' . e($odb) . '" data-ts="' . $ts . '" data-dzien="' . date('Y-m-d', $ts) . '" data-podglad="' . e(kom_podglad($user, (string)$w['message'])) . '" style="--h:' . $h . '">'
		. '<span class="kom-awatar">' . avatar_html($w['avatar'] ?? null, 30) . '</span>'
		. '<div class="kom-kolumna">'
		. '<div class="kom-autor"><span class="kom-nick">' . e($user) . '</span><span class="kom-do"> <i class="bi bi-arrow-right-short"></i> ' . e($odb) . '</span></div>'
		. $tresc
		. '<div class="kom-czas" title="' . e((string)$w['datetime']) . '">' . e(kom_czas((string)$w['datetime'])) . '</div>'
		. '</div>'
		. '<div class="kom-akcje">' . $akcje . '</div>'
		. '</div>';
}

function kom_czaty(mysqli $link, string $login, array $przeczytane): array
{
	$ludzie = kom_uzytkownicy($link, $login);
	$ostatnie = [];
	$q = mysqli_prepare($link, 'SELECT m.idk, m.`datetime`, m.message, m.user, m.recipient FROM messages m JOIN (SELECT IF(user = ?, recipient, user) AS partner, MAX(idk) AS mx FROM messages WHERE user = ? OR recipient = ? GROUP BY partner) t ON t.mx = m.idk');
	mysqli_stmt_bind_param($q, 'sss', $login, $login, $login);
	mysqli_stmt_execute($q);
	$r = mysqli_stmt_get_result($q);
	while ($r && ($w = mysqli_fetch_assoc($r))) { $ostatnie[$w['user'] === $login ? $w['recipient'] : $w['user']] = $w; }
	mysqli_stmt_close($q);

	$licz = mysqli_prepare($link, 'SELECT COUNT(*) FROM messages WHERE user = ? AND recipient = ? AND idk > ? AND message NOT LIKE \'@@motyw:%\'');
	$wynik = [];
	foreach ($ludzie as $u) {
		$nazwa = (string)$u['username'];
		$w = $ostatnie[$nazwa] ?? null;
		$od = (int)($przeczytane[$nazwa] ?? 0);
		mysqli_stmt_bind_param($licz, 'ssi', $nazwa, $login, $od);
		mysqli_stmt_execute($licz);
		mysqli_stmt_bind_result($licz, $nowe);
		mysqli_stmt_fetch($licz);
		mysqli_stmt_free_result($licz);
		$wynik[] = [
			'user'   => $nazwa,
			'avatar' => avatar_html($u['avatar'] ?? null, 46),
			'odcien' => kom_odcien($nazwa),
			'idk'    => $w ? (int)$w['idk'] : 0,
			'ts'     => $w ? (int)strtotime((string)$w['datetime']) : 0,
			'moja'   => $w ? $w['user'] === $login : false,
			'podglad' => $w ? kom_podglad((string)$w['user'], (string)$w['message']) : '',
			'nowe'   => (int)$nowe,
		];
	}
	mysqli_stmt_close($licz);
	usort($wynik, fn($a, $b) => [$b['idk'], $a['user']] <=> [$a['idk'], $b['user']]);
	return $wynik;
}

function kom_wydaj_plik(string $plik, bool $inline): void
{
	$nazwa   = basename($plik);
	$roz     = strtolower(pathinfo($nazwa, PATHINFO_EXTENSION));
	$inline  = $inline && isset(MIME_INLINE[$roz]);
	$mime    = $inline ? MIME_INLINE[$roz] : 'application/octet-stream';
	$rozmiar = (int)filesize($plik);

	session_write_close();
	while (ob_get_level() > 0) { ob_end_clean(); }
	if (function_exists('apache_setenv')) { @apache_setenv('no-gzip', '1'); }
	@ini_set('zlib.output_compression', '0');
	@set_time_limit(0);

	$start = 0; $koniec = $rozmiar - 1;
	if ($rozmiar > 0 && isset($_SERVER['HTTP_RANGE']) && preg_match('/^bytes=(\d*)-(\d*)$/', trim($_SERVER['HTTP_RANGE']), $m) && ($m[1] !== '' || $m[2] !== '')) {
		if ($m[1] === '') { $start = max(0, $rozmiar - (int)$m[2]); }
		else { $start = (int)$m[1]; if ($m[2] !== '') { $koniec = min($koniec, (int)$m[2]); } }
		if ($start > $koniec || $start >= $rozmiar) {
			http_response_code(416);
			header('Content-Range: bytes */' . $rozmiar);
			exit();
		}
		http_response_code(206);
		header('Content-Range: bytes ' . $start . '-' . $koniec . '/' . $rozmiar);
	}

	$ascii = preg_replace('/[^A-Za-z0-9._-]/', '_', $nazwa);
	header('Content-Type: ' . $mime);
	header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $ascii . '"; filename*=UTF-8\'\'' . rawurlencode($nazwa));
	header('Content-Length: ' . ($koniec - $start + 1));
	header('Accept-Ranges: bytes');
	header('X-Content-Type-Options: nosniff');
	if ($roz !== 'pdf') { header('Content-Security-Policy: sandbox'); }
	header('Cache-Control: private, max-age=0, must-revalidate');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', (int)filemtime($plik)) . ' GMT');
	if ($_SERVER['REQUEST_METHOD'] === 'HEAD' || $rozmiar === 0) { exit(); }

	$f = fopen($plik, 'rb');
	if ($f === false) { exit(); }
	fseek($f, $start);
	$zostalo = $koniec - $start + 1;
	while ($zostalo > 0 && !feof($f)) {
		$czesc = fread($f, (int)min(1048576, $zostalo));
		if ($czesc === false || $czesc === '') { break; }
		echo $czesc;
		$zostalo -= strlen($czesc);
		flush();
		if (connection_aborted()) { break; }
	}
	fclose($f);
	exit();
}

function kom_json($dane, int $kod = 200): void
{
	http_response_code($kod);
	header('Content-Type: application/json; charset=utf-8');
	header('Cache-Control: no-store');
	echo json_encode($dane, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit();
}

function kom_zapisz(mysqli $link, string $tresc, string $user, string $odbiorca): int
{
	$q = mysqli_prepare($link, 'INSERT INTO messages (message, user, recipient) VALUES (?, ?, ?)');
	mysqli_stmt_bind_param($q, 'sss', $tresc, $user, $odbiorca);
	$ok = mysqli_stmt_execute($q);
	$id = (int)mysqli_stmt_insert_id($q);
	mysqli_stmt_close($q);
	return $ok ? $id : 0;
}
