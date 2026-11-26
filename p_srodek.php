<?php declare(strict_types=1);
require_once 'p_lib.php';
$s = p_wymagaj();
$link = p_polacz();
$ids = (int)($_GET['ids'] ?? 0);
$edycja = null;
if ($ids > 0) {
	$w = p_srodki($link, 's.ids = ?', 'i', [$ids]);
	if (!$w) { flash('danger', 'Nie znaleziono środka.'); header('Location: p_tabela.php'); exit(); }
	$edycja = $w[0];
}
$plany = array_values(array_filter(p_podklady($link), static fn($p) => $p['widok'] === '2d'));
$pomieszczenia = p_pomieszczenia($link);
$nazwy = p_nazwy_srodkow($link);
$typy = p_zapytanie($link, 'SELECT idtyp, nazwa FROM typ_srodka ORDER BY idtyp');
$numery = [];
foreach ($nazwy as $n) {
	$maks = 0;
	foreach (p_zapytanie($link, 'SELECT identyfikator FROM srodek WHERE identyfikator REGEXP ?', 's', ['^' . preg_quote($n['prefiks'], '/') . '[0-9]+$']) as $r) {
		$maks = max($maks, (int)substr($r['identyfikator'], strlen($n['prefiks'])));
	}
	$numery[$n['idnazwa']] = $maks + 1;
}

$wart = $edycja ?? ['nazwa' => '', 'identyfikator' => '', 'idnazwa' => $nazwy[0]['idnazwa'] ?? 0, 'idtyp' => $nazwy[0]['idtyp'] ?? 1, 'idpod' => $plany[0]['idpod'] ?? 0, 'idpom' => null, 'koszt' => '', 'data' => date('Y-m-d'), 'x' => null, 'y' => null, 'uwagi' => '', 'stawka_amort' => 20, 'status' => 'aktywny'];
$blad = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	csrf_wymagaj();
	$idnazwa = (int)($_POST['idnazwa'] ?? 0);
	$idtyp = (int)($_POST['idtyp'] ?? 0);
	$nazwa = trim((string)($_POST['nazwa'] ?? ''));
	$ident = trim((string)($_POST['identyfikator'] ?? ''));
	$koszt = (float)str_replace(',', '.', (string)($_POST['koszt'] ?? '0'));
	$stawka = (float)str_replace(',', '.', (string)($_POST['stawka_amort'] ?? '20'));
	$data = (string)($_POST['data'] ?? '');
	$uwagi = trim((string)($_POST['uwagi'] ?? ''));
	$idpod = (int)($_POST['idpod'] ?? 0);
	$x = ($_POST['x'] ?? '') === '' ? null : (int)$_POST['x'];
	$y = ($_POST['y'] ?? '') === '' ? null : (int)$_POST['y'];
	$wybrana = (int)($_POST['idpom'] ?? 0);
	$wart = array_merge($wart, ['idnazwa' => $idnazwa, 'idtyp' => $idtyp, 'nazwa' => $nazwa, 'identyfikator' => $ident, 'koszt' => $_POST['koszt'] ?? '', 'data' => $data, 'uwagi' => $uwagi, 'idpod' => $idpod, 'x' => $x, 'y' => $y, 'stawka_amort' => $stawka, 'idpom' => $wybrana ?: null]);

	$nazwa_ik = array_values(array_filter($nazwy, static fn($n) => $n['idnazwa'] === $idnazwa))[0] ?? null;
	$dt = DateTime::createFromFormat('Y-m-d', $data);
	if ($nazwa_ik === null) { $blad = 'Wybierz rodzaj środka.'; }
	elseif ($nazwa === '' || mb_strlen($nazwa) > 150) { $blad = 'Podaj nazwę środka (do 150 znaków).'; }
	elseif (!preg_match('/^[A-Za-z0-9._-]{1,40}$/', $ident)) { $blad = 'Identyfikator może zawierać litery, cyfry oraz znaki . _ - (do 40 znaków).'; }
	elseif (!$dt || $dt->format('Y-m-d') !== $data) { $blad = 'Podaj poprawną datę rejestracji.'; }
	elseif ($koszt < 0) { $blad = 'Wartość początkowa nie może być ujemna.'; }
	elseif ($stawka < 0 || $stawka > 100) { $blad = 'Stawka amortyzacji musi być w zakresie 0–100%.'; }
	else {
		$dupl = p_zapytanie($link, 'SELECT ids FROM srodek WHERE identyfikator = ? AND ids <> ?', 'si', [$ident, $ids]);
		if ($dupl) { $blad = 'Środek o takim identyfikatorze już istnieje.'; }
	}
	if ($blad === '') {
		$pod = null;
		foreach ($plany as $p) { if ($p['idpod'] === $idpod) { $pod = $p; } }
		if ($x !== null && $y !== null && $pod) {
			$r = $nazwa_ik['rozmiar'];
			$x = max(0, min($x, $pod['szer'] - $r)); $y = max(0, min($y, $pod['wys'] - $r));
			$pom = p_pomieszczenie_dla($link, $idpod, $x + $r / 2, $y + $r / 2);
			$idpom = $pom ? $pom['idpom'] : ($wybrana ?: null);
		} else {
			$x = $y = null; $idpod = $pod ? $idpod : 0; $idpom = $wybrana ?: null;
		}
		$idpod_sql = $idpod ?: null;
		$datatime = $data . ' ' . date('H:i:s');
		if ($edycja) {
			$ok = p_wykonaj($link, 'UPDATE srodek SET idtyp = ?, idnazwa = ?, nazwa = ?, identyfikator = ?, koszt = ?, `datatime` = ?, idpod = ?, idpom = ?, X_srodka = ?, Y_srodka = ?, uwagi = ?, stawka_amort = ? WHERE ids = ?', 'iissdsiiiisdi', [$idtyp, $idnazwa, $nazwa, $ident, $koszt, substr((string)$edycja['data'], 0, 10) === $data ? (string)$edycja['data'] : $datatime, $idpod_sql, $idpom, $x, $y, $uwagi, $stawka, $ids]);
			$nowe_ids = $ids;
		} else {
			$ok = p_wykonaj($link, 'INSERT INTO srodek (idp, idpod, idpom, idtyp, idnazwa, nazwa, identyfikator, koszt, `datatime`, X_srodka, Y_srodka, uwagi, status, stawka_amort) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'aktywny\', ?)', 'iiiiissdsiisd', [(p_pracownicy($link, 'p.idu = ?', 'i', [$s['uid']])[0]['idp'] ?? null), $idpod_sql, $idpom, $idtyp, $idnazwa, $nazwa, $ident, $koszt, $datatime, $x, $y, $uwagi, $stawka]);
			$nowe_ids = (int)mysqli_insert_id($link);
		}
		if ($ok) {
			flash('success', $edycja ? 'Zapisano zmiany środka ' . $ident . '.' : 'Dodano środek ' . $ident . '.');
			header('Location: ' . ($x !== null ? 'p_wizualizacja.php?ids=' . $nowe_ids : 'p_tabela.php'));
			exit();
		}
		$blad = 'Nie udało się zapisać środka.';
	}
}

$tytul = $edycja ? 'Edycja środka ' . $edycja['identyfikator'] : 'Dodawanie środka trwałego lub wyposażenia';
require 'gora.php';
p_menu('p_srodek.php');
if ($blad !== '') { echo '<div class="alert alert-danger">' . e($blad) . '</div>'; }
?>
<form method="post" class="row g-3" id="p-forma-srodek" autocomplete="off">
	<?php echo csrf_pole(); ?>
	<div class="col-lg-5">
		<div class="p-karta-ui p-3 d-grid gap-3">
			<div>
				<label class="form-label" for="idnazwa">Rodzaj</label>
				<div class="input-group"><span class="input-group-text"><img id="p-ik" class="p-tab-ikona m-0" alt="" src=""></span>
				<select class="form-select" name="idnazwa" id="idnazwa">
<?php foreach ($nazwy as $n): ?>
					<option value="<?php echo $n['idnazwa']; ?>"<?php echo (int)$wart['idnazwa'] === $n['idnazwa'] ? ' selected' : ''; ?>><?php echo e($n['nazwa']); ?></option>
<?php endforeach; ?>
				</select></div>
			</div>
			<div>
				<label class="form-label" for="idtyp">Typ</label>
				<select class="form-select" name="idtyp" id="idtyp">
<?php foreach ($typy as $t): ?>
					<option value="<?php echo (int)$t['idtyp']; ?>"<?php echo (int)$wart['idtyp'] === (int)$t['idtyp'] ? ' selected' : ''; ?>><?php echo e($t['nazwa']); ?></option>
<?php endforeach; ?>
				</select>
			</div>
			<div class="row g-3">
				<div class="col-sm-7"><label class="form-label" for="nazwa">Nazwa</label><input class="form-control" type="text" name="nazwa" id="nazwa" maxlength="150" required value="<?php echo e((string)$wart['nazwa']); ?>"></div>
				<div class="col-sm-5"><label class="form-label" for="identyfikator">Identyfikator</label><input class="form-control" type="text" name="identyfikator" id="identyfikator" maxlength="40" required value="<?php echo e((string)$wart['identyfikator']); ?>"></div>
			</div>
			<div class="row g-3">
				<div class="col-sm-6"><label class="form-label" for="data">Data rejestracji</label><input class="form-control" type="date" name="data" id="data" required value="<?php echo e(substr((string)$wart['data'], 0, 10)); ?>"></div>
				<div class="col-sm-6"><label class="form-label" for="koszt">Wartość początkowa (zł)</label><input class="form-control" type="number" step="0.01" min="0" name="koszt" id="koszt" required value="<?php echo e((string)$wart['koszt']); ?>"></div>
			</div>
			<div class="row g-3">
				<div class="col-sm-6"><label class="form-label" for="stawka_amort">Roczna stawka amortyzacji (%)</label><input class="form-control" type="number" step="0.01" min="0" max="100" name="stawka_amort" id="stawka_amort" value="<?php echo e((string)$wart['stawka_amort']); ?>"></div>
				<div class="col-sm-6"><label class="form-label" for="idpom">Pomieszczenie</label>
					<select class="form-select" name="idpom" id="idpom"><option value="">ustal z położenia</option>
<?php foreach ($pomieszczenia as $r): ?>
						<option value="<?php echo $r['idpom']; ?>"<?php echo (int)($wart['idpom'] ?? 0) === $r['idpom'] ? ' selected' : ''; ?>><?php echo e($r['nazwa']); ?></option>
<?php endforeach; ?>
					</select></div>
			</div>
			<div><label class="form-label" for="uwagi">Uwagi</label><textarea class="form-control" name="uwagi" id="uwagi" rows="2"><?php echo e((string)$wart['uwagi']); ?></textarea></div>
		</div>
	</div>
	<div class="col-lg-7">
		<div class="p-karta-ui p-3 d-grid gap-3">
			<div class="row g-3 align-items-end">
				<div class="col-sm-5"><label class="form-label" for="idpod">Podkład</label>
					<select class="form-select" name="idpod" id="idpod">
<?php foreach ($plany as $p): ?>
						<option value="<?php echo $p['idpod']; ?>"<?php echo (int)$wart['idpod'] === $p['idpod'] ? ' selected' : ''; ?>><?php echo e($p['nazwa']); ?></option>
<?php endforeach; ?>
					</select></div>
				<div class="col-6 col-sm-3"><label class="form-label" for="x">X</label><input class="form-control" type="number" min="0" name="x" id="x" value="<?php echo e((string)($wart['x'] ?? '')); ?>"></div>
				<div class="col-6 col-sm-3"><label class="form-label" for="y">Y</label><input class="form-control" type="number" min="0" name="y" id="y" value="<?php echo e((string)($wart['y'] ?? '')); ?>"></div>
			</div>
			<div class="fw-semibold">Pomieszczenie: <span class="text-primary" id="p-pom-wyj">—</span></div>
			<div id="p-wybor"></div>
		</div>
		<div class="d-flex gap-2 mt-3">
			<button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Zapisz</button>
			<a class="btn btn-outline-secondary" href="p_tabela.php">Anuluj</a>
		</div>
	</div>
</form>
<script src="<?php echo p_plik_wersja('p_wybor.js'); ?>"></script>
<script>
(function ()
{
	var nazwy = <?php echo json_encode($nazwy, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
	var numery = <?php echo json_encode($numery); ?>;
	var edycja = <?php echo $edycja ? 'true' : 'false'; ?>;
	var sel = document.getElementById('idnazwa'), ik = document.getElementById('p-ik'), typ = document.getElementById('idtyp');
	var nazwa = document.getElementById('nazwa'), ident = document.getElementById('identyfikator');
	var wybor = PWybor(document.getElementById('p-wybor'), { plany: <?php echo json_encode($plany, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>, pomieszczenia: <?php echo json_encode($pomieszczenia, JSON_UNESCAPED_UNICODE); ?>, wejX: '#x', wejY: '#y', wejPlan: '#idpod', pomWyj: '#p-pom-wyj', rozmiar: 80, elementy: <?php echo json_encode(p_elementy_wyboru($link, $ids), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?> });
	function rodzaj() { return nazwy.filter(function (n) { return String(n.idnazwa) === sel.value; })[0]; }
	function zmiana(nowy)
	{
		var n = rodzaj(); if (!n) { return; }
		ik.src = n.plik;
		wybor.ustawRozmiar(n.rozmiar);
		if (nowy) {
			typ.value = n.idtyp;
			var nr = numery[n.idnazwa];
			nazwa.value = n.nazwa + ' ' + nr;
			ident.value = n.prefiks + nr;
		}
	}
	sel.addEventListener('change', function () { zmiana(!edycja || true); });
	zmiana(!edycja && nazwa.value === '');
})();
</script>
<?php require 'dol.php'; ?>
