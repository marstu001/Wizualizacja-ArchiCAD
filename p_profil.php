<?php declare(strict_types=1);
require_once 'p_lib.php';
$s = p_wymagaj();
$link = p_polacz();
$idp = (int)($_GET['idp'] ?? 0);
$plany = array_values(array_filter(p_podklady($link), static fn($p) => $p['widok'] === '2d'));
$pomieszczenia = p_pomieszczenia($link);
if ($idp > 0) { $lista = p_pracownicy($link, 'p.idp = ?', 'i', [$idp]); }
else { $lista = p_pracownicy($link, 'p.idu = ?', 'i', [$s['uid']]); }
$pole_login = false;
$blad = '';
if (!$lista) {
	$haslo_u = p_zapytanie(p_glowna(), 'SELECT password FROM users WHERE id = ?', 'i', [$s['uid']])[0]['password'] ?? '';
	p_wykonaj($link, 'INSERT INTO pracownik (idu, idpod, nazwisko, haslo, stan, nazwa_pliku_fotki) VALUES (?, NULL, ?, ?, 1, \'\')', 'iss', [$s['uid'], $s['login'], $haslo_u]);
	$lista = p_pracownicy($link, 'p.idu = ?', 'i', [$s['uid']]);
}
$pr = $lista[0];
$wart = ['nazwisko' => $pr['nazwisko'], 'stan' => $pr['stan'], 'idpod' => $pr['idpod'] ?? ($plany[0]['idpod'] ?? 0), 'x' => $pr['x'], 'y' => $pr['y']];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	csrf_wymagaj();
	$nazwisko = trim((string)($_POST['nazwisko'] ?? ''));
	$stan = (int)($_POST['stan'] ?? 1);
	$idpod = (int)($_POST['idpod'] ?? 0);
	$x = ($_POST['x'] ?? '') === '' ? null : (int)$_POST['x'];
	$y = ($_POST['y'] ?? '') === '' ? null : (int)$_POST['y'];
	$wart = ['nazwisko' => $nazwisko, 'stan' => $stan, 'idpod' => $idpod, 'x' => $x, 'y' => $y];
	if (!isset(P_STANY[$stan])) { $blad = 'Wybierz stan.'; }
	[$foto, $blad_foto] = p_foto_zapisz($_FILES['foto'] ?? ['error' => UPLOAD_ERR_NO_FILE], $pr['login']);
	if ($blad_foto !== null) { $blad = $blad_foto; }
	if ($blad === '') {
		$pod = null;
		foreach ($plany as $p) { if ($p['idpod'] === $idpod) { $pod = $p; } }
		if ($x !== null && $y !== null && $pod) { $x = max(0, min($x, $pod['szer'] - 60)); $y = max(0, min($y, $pod['wys'] - 60)); } else { $x = $y = null; }
		p_wykonaj($link, 'UPDATE pracownik SET nazwisko = ?, stan = ?, idpod = ?, X_pracownika = ?, Y_pracownika = ? WHERE idp = ?', 'siiiii', [$nazwisko !== '' ? $nazwisko : $pr['login'], $stan, $x === null ? null : $idpod, $x, $y, $pr['idp']]);
		if ($foto !== null) {
			$stare = basename((string)$pr['foto']);
			p_wykonaj($link, 'UPDATE pracownik SET nazwa_pliku_fotki = ? WHERE idp = ?', 'si', [$foto, $pr['idp']]);
			p_foto_usun($stare);
		}
		flash('success', 'Zapisano profil.');
		header('Location: p_wizualizacja.php?idp=' . $pr['idp']);
		exit();
	}
	p_foto_usun((string)$foto);
}
$pr = p_pracownicy($link, 'p.idp = ?', 'i', [$pr['idp']])[0];
$tytul = 'Profil pracownika: ' . $pr['login'];
require 'gora.php';
p_menu('p_profil.php');
if ($blad !== '') { echo '<div class="alert alert-danger">' . e($blad) . '</div>'; }
?>
<form method="post" enctype="multipart/form-data" autocomplete="off">
	<?php echo csrf_pole(); ?>
<?php require 'p_pracownik_forma.php'; ?>
	<div class="d-flex gap-2 mt-3">
		<button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Zapisz</button>
		<a class="btn btn-outline-secondary" href="p_wizualizacja.php">Anuluj</a>
	</div>
</form>
<?php require 'dol.php'; ?>
