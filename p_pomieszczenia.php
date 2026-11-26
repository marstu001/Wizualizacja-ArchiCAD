<?php declare(strict_types=1);
require_once 'p_lib.php';
p_wymagaj_admin();
$link = p_polacz();
$plany = array_values(array_filter(p_podklady($link), static fn($p) => $p['widok'] === '2d'));
$idpod = (int)($_GET['idpod'] ?? ($plany[0]['idpod'] ?? 0));
$plan = null;
foreach ($plany as $p) { if ($p['idpod'] === $idpod) { $plan = $p; } }
if (!$plan && $plany) { $plan = $plany[0]; $idpod = $plan['idpod']; }
$pomieszczenia = array_values(array_filter(p_pomieszczenia($link), static fn($r) => $r['idpod'] === $idpod));
$tytul = 'Pomieszczenia na podkładzie';
require 'gora.php';
p_menu('p_pomieszczenia.php');
p_flash_wypisz();
if (!$plan) { echo '<div class="alert alert-info">Najpierw dodaj podkład.</div>'; require 'dol.php'; exit(); }
?>
<div class="row g-3">
	<div class="col-lg-8">
		<div class="p-karta-ui p-3">
			<form method="get" class="d-flex gap-2 mb-2"><select class="form-select form-select-sm w-auto" name="idpod" onchange="this.form.submit()">
<?php foreach ($plany as $p): ?>
				<option value="<?php echo $p['idpod']; ?>"<?php echo $p['idpod'] === $idpod ? ' selected' : ''; ?>><?php echo e($p['nazwa']); ?></option>
<?php endforeach; ?>
			</select><span class="small text-muted align-self-center">Zaznacz myszą prostokąt na planie, aby ustawić granice pomieszczenia.</span></form>
			<div class="p-wybor" id="p-rysunek" data-szer="<?php echo $plan['szer']; ?>" data-wys="<?php echo $plan['wys']; ?>" style="container-type:inline-size">
				<img alt="" src="<?php echo e($plan['plik']); ?>" draggable="false">
				<div class="p-prostokat" id="p-prost"></div>
			</div>
		</div>
	</div>
	<div class="col-lg-4">
		<form class="p-karta-ui p-3 d-grid gap-2 mb-3" method="post" action="p_pomieszczenie_akcja.php" id="p-forma-pom">
			<?php echo csrf_pole(); ?>
			<input type="hidden" name="akcja" value="zapisz">
			<input type="hidden" name="idpod" value="<?php echo $idpod; ?>">
			<input type="hidden" name="idpom" id="p-idpom" value="0">
			<h2 class="h6 mb-0" id="p-pom-tytul">Nowe pomieszczenie</h2>
			<input class="form-control" type="text" name="nazwa" id="p-nazwa" maxlength="100" placeholder="Nazwa (np. Serwerownia)" required>
			<div class="row g-1">
				<div class="col-3"><input class="form-control form-control-sm" type="number" name="x1" id="p-x1" placeholder="x1" required></div>
				<div class="col-3"><input class="form-control form-control-sm" type="number" name="y1" id="p-y1" placeholder="y1" required></div>
				<div class="col-3"><input class="form-control form-control-sm" type="number" name="x2" id="p-x2" placeholder="x2" required></div>
				<div class="col-3"><input class="form-control form-control-sm" type="number" name="y2" id="p-y2" placeholder="y2" required></div>
			</div>
			<div class="d-flex gap-2"><button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Zapisz</button><button class="btn btn-outline-secondary" type="button" id="p-nowe">Nowe</button></div>
		</form>
		<div class="p-karta-ui p-2" style="max-height:50vh;overflow:auto">
<?php foreach ($pomieszczenia as $r): ?>
			<div class="d-flex align-items-center gap-2 p-1 border-bottom">
				<a href="#" class="flex-grow-1 text-decoration-none p-edytuj" data-r='<?php echo e(json_encode($r, JSON_UNESCAPED_UNICODE)); ?>'><?php echo e($r['nazwa']); ?></a>
				<form method="post" action="p_pomieszczenie_akcja.php" onsubmit="return confirm('Usunąć pomieszczenie?');"><?php echo csrf_pole(); ?><input type="hidden" name="akcja" value="usun"><input type="hidden" name="idpod" value="<?php echo $idpod; ?>"><input type="hidden" name="idpom" value="<?php echo $r['idpom']; ?>"><button class="btn btn-sm btn-link text-danger p-0" type="submit" title="Usuń"><i class="bi bi-trash"></i></button></form>
			</div>
<?php endforeach; ?>
		</div>
	</div>
</div>
<script>
(function ()
{
	var pole = document.getElementById('p-rysunek'), obraz = pole.querySelector('img'), prost = document.getElementById('p-prost');
	var SZ = parseInt(pole.getAttribute('data-szer'), 10), WY = parseInt(pole.getAttribute('data-wys'), 10);
	var POM = <?php echo json_encode($pomieszczenia, JSON_UNESCAPED_UNICODE); ?>;
	var wej = { x1: document.getElementById('p-x1'), y1: document.getElementById('p-y1'), x2: document.getElementById('p-x2'), y2: document.getElementById('p-y2') };
	POM.forEach(function (r)
	{
		var d = document.createElement('div'); d.className = 'p-stale';
		d.style.left = r.x1 / SZ * 100 + '%'; d.style.top = r.y1 / WY * 100 + '%'; d.style.width = (r.x2 - r.x1) / SZ * 100 + '%'; d.style.height = (r.y2 - r.y1) / WY * 100 + '%';
		d.textContent = r.nazwa;
		pole.appendChild(d);
	});
	function pokaz()
	{
		var x1 = parseInt(wej.x1.value, 10), y1 = parseInt(wej.y1.value, 10), x2 = parseInt(wej.x2.value, 10), y2 = parseInt(wej.y2.value, 10);
		if ([x1, y1, x2, y2].some(isNaN)) { prost.style.display = 'none'; return; }
		prost.style.display = 'block';
		prost.style.left = Math.min(x1, x2) / SZ * 100 + '%'; prost.style.top = Math.min(y1, y2) / WY * 100 + '%';
		prost.style.width = Math.abs(x2 - x1) / SZ * 100 + '%'; prost.style.height = Math.abs(y2 - y1) / WY * 100 + '%';
	}
	var start = null;
	function wsp(e) { var r = obraz.getBoundingClientRect(); return [Math.round(Math.max(0, Math.min(1, (e.clientX - r.left) / r.width)) * SZ), Math.round(Math.max(0, Math.min(1, (e.clientY - r.top) / r.height)) * WY)]; }
	pole.addEventListener('pointerdown', function (e) { start = wsp(e); pole.setPointerCapture(e.pointerId); wej.x1.value = start[0]; wej.y1.value = start[1]; wej.x2.value = start[0]; wej.y2.value = start[1]; pokaz(); e.preventDefault(); });
	pole.addEventListener('pointermove', function (e) { if (!start) { return; } var k = wsp(e); wej.x2.value = k[0]; wej.y2.value = k[1]; pokaz(); });
	pole.addEventListener('pointerup', function () { start = null; });
	Object.keys(wej).forEach(function (k) { wej[k].addEventListener('input', pokaz); });
	function nowe() { document.getElementById('p-idpom').value = 0; document.getElementById('p-nazwa').value = ''; Object.keys(wej).forEach(function (k) { wej[k].value = ''; }); document.getElementById('p-pom-tytul').textContent = 'Nowe pomieszczenie'; pokaz(); }
	document.getElementById('p-nowe').addEventListener('click', nowe);
	Array.prototype.slice.call(document.querySelectorAll('.p-edytuj')).forEach(function (a)
	{
		a.addEventListener('click', function (e)
		{
			e.preventDefault();
			var r = JSON.parse(a.getAttribute('data-r'));
			document.getElementById('p-idpom').value = r.idpom; document.getElementById('p-nazwa').value = r.nazwa;
			wej.x1.value = r.x1; wej.y1.value = r.y1; wej.x2.value = r.x2; wej.y2.value = r.y2;
			document.getElementById('p-pom-tytul').textContent = 'Edycja: ' + r.nazwa; pokaz();
		});
	});
})();
</script>
<?php require 'dol.php'; ?>
