<?php declare(strict_types=1);
require_once 'p_lib.php';
$s = p_wymagaj();
$link = p_polacz();
$q = trim((string)($_GET['q'] ?? ''));
$typ = (int)($_GET['typ'] ?? 0);
$pom = (int)($_GET['pom'] ?? 0);
$status = (string)($_GET['status'] ?? '');
$pokaz = (string)($_GET['wszystkie'] ?? '') === '1';

$war = []; $typy = ''; $dane = [];
if ($q !== '') { $war[] = '(s.identyfikator LIKE ? OR s.nazwa LIKE ? OR s.uwagi LIKE ?)'; $typy .= 'sss'; $l = '%' . $q . '%'; array_push($dane, $l, $l, $l); }
if ($typ > 0) { $war[] = 's.idnazwa = ?'; $typy .= 'i'; $dane[] = $typ; }
if ($pom > 0) { $war[] = 's.idpom = ?'; $typy .= 'i'; $dane[] = $pom; }
if ($status !== '' && isset(P_STATUSY[$status])) { $war[] = 's.status = ?'; $typy .= 's'; $dane[] = $status; }
elseif (!$pokaz) { $war[] = "s.status IN ('aktywny', 'w magazynie')"; }
$srodki = p_srodki($link, implode(' AND ', $war), $typy, $dane);
$nazwy = p_nazwy_srodkow($link);
$pomieszczenia = p_pomieszczenia($link);
$suma = 0.0; $suma_b = 0.0;
foreach ($srodki as $x) { $suma += $x['koszt']; $suma_b += $x['wartosc']; }
$zdarzenia = [];
foreach (p_zapytanie($link, 'SELECT z.ids, z.rodzaj, z.kwota, z.opis, z.`datatime` AS data, p.nazwisko FROM zdarzenie z LEFT JOIN pracownik p ON p.idp = z.idp ORDER BY z.idz DESC LIMIT 200') as $z) { $zdarzenia[(int)$z['ids']][] = $z; }

$tytul = 'Tabela środków trwałych i wyposażenia';
$publiczna = true;
require 'gora.php';
p_menu('p_tabela.php');
p_flash_wypisz();
?>
<form class="row g-2 mb-3 align-items-end" method="get">
	<div class="col-12 col-md-3"><label class="form-label small mb-0" for="q">Szukaj</label><input class="form-control form-control-sm" type="search" name="q" id="q" value="<?php echo e($q); ?>" placeholder="identyfikator, nazwa, uwagi"></div>
	<div class="col-6 col-md-2"><label class="form-label small mb-0" for="typ">Rodzaj</label>
		<select class="form-select form-select-sm" name="typ" id="typ"><option value="0">wszystkie</option>
<?php foreach ($nazwy as $n): ?>
			<option value="<?php echo $n['idnazwa']; ?>"<?php echo $typ === $n['idnazwa'] ? ' selected' : ''; ?>><?php echo e($n['nazwa']); ?></option>
<?php endforeach; ?>
		</select></div>
	<div class="col-6 col-md-3"><label class="form-label small mb-0" for="pom">Pomieszczenie</label>
		<select class="form-select form-select-sm" name="pom" id="pom"><option value="0">wszystkie</option>
<?php foreach ($pomieszczenia as $r): ?>
			<option value="<?php echo $r['idpom']; ?>"<?php echo $pom === $r['idpom'] ? ' selected' : ''; ?>><?php echo e($r['nazwa']); ?></option>
<?php endforeach; ?>
		</select></div>
	<div class="col-6 col-md-2"><label class="form-label small mb-0" for="status">Status</label>
		<select class="form-select form-select-sm" name="status" id="status"><option value="">używane</option>
<?php foreach (P_STATUSY as $k => $v): ?>
			<option value="<?php echo e($k); ?>"<?php echo $status === $k ? ' selected' : ''; ?>><?php echo e($v); ?></option>
<?php endforeach; ?>
		</select></div>
	<div class="col-6 col-md-2 d-flex gap-2"><button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-funnel"></i> Filtruj</button><a class="btn btn-sm btn-outline-secondary" href="p_tabela.php">Wyczyść</a></div>
</form>
<div class="table-responsive p-karta-ui">
	<table class="table table-hover align-middle mb-0" id="p-tabela">
		<thead class="table-light"><tr><th>Id</th><th>Środek</th><th>Typ</th><th>Pomieszczenie</th><th>Data</th><th class="text-end">Wartość początkowa</th><th class="text-end">Wartość bieżąca</th><th>Status</th><th>Położenie</th><?php if ($s): ?><th></th><?php endif; ?></tr></thead>
		<tbody>
<?php foreach ($srodki as $x): ?>
			<tr>
				<td><b><?php echo e($x['identyfikator']); ?></b></td>
				<td><img class="p-tab-ikona" alt="" src="<?php echo e($x['plik']); ?>"><?php echo e($x['nazwa']); ?></td>
				<td><?php echo e((string)$x['typ']); ?></td>
				<td><?php echo e((string)($x['pomieszczenie'] ?? '—')); ?></td>
				<td><?php echo e(substr((string)$x['data'], 0, 10)); ?></td>
				<td class="text-end"><?php echo e(p_wartosc_pln($x['koszt'])); ?></td>
				<td class="text-end"><?php echo e(p_wartosc_pln($x['wartosc'])); ?></td>
				<td><span class="badge text-bg-<?php echo $x['status'] === 'aktywny' ? 'success' : ($x['status'] === 'w magazynie' ? 'info' : 'secondary'); ?>"><?php echo e($x['status']); ?></span></td>
				<td><?php if ($x['x'] !== null): ?><a href="p_wizualizacja.php?ids=<?php echo $x['ids']; ?>" title="Pokaż na planie"><i class="bi bi-geo-alt-fill"></i> <?php echo $x['x']; ?>, <?php echo $x['y']; ?></a><?php else: ?><span class="text-muted">poza planem</span><?php endif; ?></td>
<?php if ($s): ?>
				<td class="text-nowrap">
					<a class="btn btn-sm btn-outline-primary" href="p_srodek.php?ids=<?php echo $x['ids']; ?>" title="Edytuj"><i class="bi bi-pencil"></i></a>
					<button class="btn btn-sm btn-outline-secondary p-zdarzenie" type="button" data-bs-toggle="modal" data-bs-target="#p-modal-zdarzenie" data-ids="<?php echo $x['ids']; ?>" data-ident="<?php echo e($x['identyfikator']); ?>" data-odpis="<?php echo e((string)round($x['koszt'] * $x['stawka_amort'] / 100, 2)); ?>" data-status="<?php echo e($x['status']); ?>" title="Zdarzenia i amortyzacja"><i class="bi bi-journal-text"></i></button>
					<form method="post" action="p_srodek_akcja.php" class="d-inline" onsubmit="return confirm('Usunąć środek <?php echo e($x['identyfikator']); ?> na stałe?');"><?php echo csrf_pole(); ?><input type="hidden" name="akcja" value="usun"><input type="hidden" name="ids" value="<?php echo $x['ids']; ?>"><button class="btn btn-sm btn-outline-danger" type="submit" title="Usuń"><i class="bi bi-trash"></i></button></form>
				</td>
<?php endif; ?>
			</tr>
<?php if (!empty($zdarzenia[$x['ids']])): ?>
			<tr class="table-light"><td></td><td colspan="<?php echo $s ? 9 : 8; ?>" class="small text-muted py-1">
<?php foreach (array_slice($zdarzenia[$x['ids']], 0, 3) as $z): ?>
				<div><?php echo e(substr((string)$z['data'], 0, 10)); ?> — <?php echo e(P_RODZAJE[$z['rodzaj']] ?? $z['rodzaj']); ?><?php echo $z['kwota'] !== null ? ' ' . e(p_wartosc_pln((float)$z['kwota'])) : ''; ?><?php echo $z['opis'] !== '' ? ': ' . e($z['opis']) : ''; ?><?php echo $z['nazwisko'] ? ' (' . e($z['nazwisko']) . ')' : ''; ?></div>
<?php endforeach; ?>
			</td></tr>
<?php endif; ?>
<?php endforeach; ?>
<?php if (!$srodki): ?>
			<tr><td colspan="10" class="text-center text-muted py-4">Brak środków spełniających kryteria.</td></tr>
<?php endif; ?>
		</tbody>
		<tfoot class="table-light"><tr><th colspan="5">Razem: <?php echo count($srodki); ?></th><th class="text-end"><?php echo e(p_wartosc_pln($suma)); ?></th><th class="text-end"><?php echo e(p_wartosc_pln($suma_b)); ?></th><th colspan="<?php echo $s ? 3 : 2; ?>"></th></tr></tfoot>
	</table>
</div>
<?php if ($s): ?>
<div class="modal fade" id="p-modal-zdarzenie" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog"><form class="modal-content" method="post" action="p_srodek_akcja.php">
		<?php echo csrf_pole(); ?>
		<input type="hidden" name="ids" id="p-m-ids" value="">
		<div class="modal-header"><h5 class="modal-title">Zdarzenia: <span id="p-m-ident"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zamknij"></button></div>
		<div class="modal-body d-grid gap-3">
			<div><label class="form-label" for="p-m-akcja">Co się stało?</label>
				<select class="form-select" name="akcja" id="p-m-akcja">
					<option value="odpis">Odpis amortyzacyjny</option>
					<option value="przeniesienie">Przeniesienie do magazynu</option>
					<option value="likwidacja">Likwidacja</option>
					<option value="zniszczenie">Zniszczenie</option>
					<option value="kradziez">Kradzież</option>
					<option value="inne">Inne zdarzenie</option>
					<option value="przywroc">Przywrócenie do użytkowania</option>
					<option value="usun">Usunięcie z ewidencji</option>
				</select></div>
			<div data-dla="odpis"><label class="form-label" for="p-m-kwota">Kwota odpisu (zł)</label><input class="form-control" type="number" step="0.01" min="0" name="kwota" id="p-m-kwota"></div>
			<div data-dla="przeniesienie" class="d-none"><label class="form-label" for="p-m-pom">Magazyn</label>
				<select class="form-select" name="idpom" id="p-m-pom">
<?php foreach ($pomieszczenia as $r): ?>
					<option value="<?php echo $r['idpom']; ?>"><?php echo e($r['nazwa']); ?></option>
<?php endforeach; ?>
				</select></div>
			<div><label class="form-label" for="p-m-opis">Opis</label><textarea class="form-control" name="opis" id="p-m-opis" rows="2" maxlength="500"></textarea></div>
		</div>
		<div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Anuluj</button><button type="submit" class="btn btn-primary">Zapisz</button></div>
	</form></div>
</div>
<script>
(function ()
{
	var akcja = document.getElementById('p-m-akcja');
	function pola() { var a = akcja.value; Array.prototype.slice.call(document.querySelectorAll('[data-dla]')).forEach(function (e) { e.classList.toggle('d-none', e.getAttribute('data-dla') !== a); }); }
	akcja.addEventListener('change', pola);
	document.getElementById('p-tabela').addEventListener('click', function (e)
	{
		var b = e.target.closest('.p-zdarzenie');
		if (!b) { return; }
		document.getElementById('p-m-ids').value = b.getAttribute('data-ids');
		document.getElementById('p-m-ident').textContent = b.getAttribute('data-ident');
		document.getElementById('p-m-kwota').value = b.getAttribute('data-odpis');
		akcja.value = b.getAttribute('data-status') === 'aktywny' || b.getAttribute('data-status') === 'w magazynie' ? 'odpis' : 'przywroc';
		pola();
	});
})();
</script>
<?php endif; ?>
<?php require 'dol.php'; ?>
