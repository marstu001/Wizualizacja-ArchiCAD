<?php declare(strict_types=1);
require_once 'p_lib.php';
$s = p_wymagaj();
$link = p_polacz();
$plany = array_values(array_filter(p_podklady($link), static fn($p) => $p['widok'] === '2d'));
$pomieszczenia = p_pomieszczenia($link);
$pr = null;
$pole_login = true;
$blad = '';
$wart = ['login' => '', 'nazwisko' => '', 'stan' => 1, 'idpod' => $plany[0]['idpod'] ?? 0, 'x' => null, 'y' => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	csrf_wymagaj();
	$login = trim((string)($_POST['login'] ?? ''));
	$haslo = (string)($_POST['haslo'] ?? '');
	$nazwisko = trim((string)($_POST['nazwisko'] ?? ''));
	$stan = (int)($_POST['stan'] ?? 1);
	$idpod = (int)($_POST['idpod'] ?? 0);
	$x = ($_POST['x'] ?? '') === '' ? null : (int)$_POST['x'];
	$y = ($_POST['y'] ?? '') === '' ? null : (int)$_POST['y'];
	$wart = ['login' => $login, 'nazwisko' => $nazwisko, 'stan' => $stan, 'idpod' => $idpod, 'x' => $x, 'y' => $y];
	if ($login === '' || $haslo === '') { $blad = 'Podaj login i hasło.'; }
	elseif (!login_poprawny($login)) { $blad = 'Login może mieć do 20 znaków: litery bez polskich znaków, cyfry, _ oraz -.'; }
	elseif (!isset(P_STANY[$stan])) { $blad = 'Wybierz stan.'; }
	elseif (p_zapytanie(p_glowna(), 'SELECT id FROM users WHERE username = ?', 's', [$login])) { $blad = 'Użytkownik o takim loginie już istnieje.'; }
	$foto = null;
	if ($blad === '') {
		[$foto, $blad_foto] = p_foto_zapisz($_FILES['foto'] ?? ['error' => UPLOAD_ERR_NO_FILE], $login);
		if ($blad_foto !== null) { $blad = $blad_foto; }
	}
	if ($blad === '') {
		$pod = null;
		foreach ($plany as $p) { if ($p['idpod'] === $idpod) { $pod = $p; } }
		if ($x !== null && $y !== null && $pod) { $x = max(0, min($x, $pod['szer'] - 60)); $y = max(0, min($y, $pod['wys'] - 60)); } else { $x = $y = null; }
		$hash = p_haslo_zapisz($link, $haslo);
		$glowna = p_glowna();
		if (!p_wykonaj($glowna, 'INSERT INTO users (username, password) VALUES (?, ?)', 'ss', [$login, $hash])) { $blad = 'Nie udało się utworzyć konta.'; }
		else {
			$idu = (int)mysqli_insert_id($glowna);
			dysk_katalog_domowy($login);
			p_wykonaj($link, 'INSERT INTO pracownik (idu, idpod, nazwisko, haslo, stan, X_pracownika, Y_pracownika, nazwa_pliku_fotki) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', 'iissiiis', [$idu, $x === null ? null : $idpod, $nazwisko !== '' ? $nazwisko : $login, $hash, $stan, $x, $y, (string)$foto]);
			flash('success', 'Dodano pracownika: ' . $login . '.');
			header('Location: p_pracownicy.php');
			exit();
		}
		p_foto_usun((string)$foto);
	}
}
$tytul = 'Dodaj pracownika';
require 'gora.php';
p_menu('p_pracownicy.php');
if ($blad !== '') { echo '<div class="alert alert-danger">' . e($blad) . '</div>'; }
?>
<form method="post" enctype="multipart/form-data" autocomplete="off">
	<?php echo csrf_pole(); ?>
<?php require 'p_pracownik_forma.php'; ?>
	<div class="d-flex gap-2 mt-3">
		<button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Dodaj pracownika</button>
		<a class="btn btn-outline-secondary" href="p_pracownicy.php">Anuluj</a>
	</div>
</form>
<?php require 'dol.php'; ?>
