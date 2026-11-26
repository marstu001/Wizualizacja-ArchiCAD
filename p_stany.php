<?php declare(strict_types=1);
require_once 'p_lib.php';
$s = p_wymagaj();
$link = p_polacz();
$pracownicy = p_pracownicy($link);
$moj = null;
foreach ($pracownicy as $p) { if ($p['idu'] === $s['uid']) { $moj = $p['idp']; } }
$tytul = 'Stany pracowników';
require 'gora.php';
p_menu('p_stany.php');
?>
<div class="row g-3">
	<div class="col-lg-4">
		<form class="p-karta-ui p-3 d-grid gap-3" id="p-forma-stan" data-csrf="<?php echo e(csrf_token()); ?>">
			<div><label class="form-label" for="p-prac">Pracownik</label>
				<select class="form-select" id="p-prac">
<?php foreach ($pracownicy as $p): ?>
					<option value="<?php echo $p['idp']; ?>"<?php echo $p['idp'] === $moj ? ' selected' : ''; ?>><?php echo e($p['nazwisko']); ?></option>
<?php endforeach; ?>
				</select></div>
			<div><label class="form-label" for="p-stan">Stan</label>
				<select class="form-select" id="p-stan">
<?php foreach (P_STANY as $k => $v): ?>
					<option value="<?php echo $k; ?>"><?php echo e(($v['ikona'] !== '' ? $v['ikona'] . ' ' : '') . $v['nazwa']); ?></option>
<?php endforeach; ?>
				</select></div>
			<button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Zmień stan</button>
			<div class="alert d-none mb-0" id="p-wynik" role="alert"></div>
		</form>
	</div>
	<div class="col-lg-8">
		<div class="table-responsive p-karta-ui">
			<table class="table align-middle mb-0" id="p-tab-prac">
				<thead class="table-light"><tr><th></th><th>Pracownik</th><th>Stan</th><th>Położenie</th></tr></thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>
<script>
(function ()
{
	var STANY = <?php echo json_encode(array_map(static fn($k, $v) => ['id' => $k, 'nazwa' => $v['nazwa'], 'ikona' => $v['ikona']], array_keys(P_STANY), P_STANY), JSON_UNESCAPED_UNICODE); ?>;
	var tbody = document.querySelector('#p-tab-prac tbody');
	var sel = document.getElementById('p-prac'), stan = document.getElementById('p-stan');
	function esc(t) { var d = document.createElement('div'); d.textContent = t === null || t === undefined ? '' : String(t); return d.innerHTML; }
	function nazwa(id) { var s = STANY.filter(function (x) { return x.id === id; })[0]; return s ? (s.ikona ? s.ikona + ' ' : '') + s.nazwa : ''; }
	function rysuj(lista)
	{
		var h = '';
		lista.forEach(function (w)
		{
			h += '<tr class="p-pracownik-wiersz"><td><img alt="" src="' + esc(w.foto) + '"></td><td><b>' + esc(w.nazwisko) + '</b><div class="small text-muted">' + esc(w.login) + '</div></td><td>' + esc(nazwa(w.stan)) + '</td><td>' + (w.x !== null && w.stan !== 0 ? '<a href="p_wizualizacja.php?idp=' + w.idp + '"><i class="bi bi-geo-alt-fill"></i> pokaż na planie</a>' : '<span class="text-muted">—</span>') + '</td></tr>';
		});
		tbody.innerHTML = h;
	}
	function ustawStan() { var i = sel.value; var w = window.__lista && window.__lista.filter(function (x) { return String(x.idp) === i; })[0]; if (w) { stan.value = w.stan; } }
	var pierwsza = true;
	function zywe(lista) { window.__lista = lista; rysuj(lista); if (pierwsza) { pierwsza = false; ustawStan(); } }
	fetch('p_dane.php', { credentials: 'same-origin', cache: 'no-store' }).then(function (r) { return r.json(); }).then(function (d) { zywe(d.pracownicy); });
	if (window.EventSource) {
		var es = new EventSource('p_stream.php');
		es.addEventListener('zywe', function (e) { zywe(JSON.parse(e.data).pracownicy); });
	}
	sel.addEventListener('change', ustawStan);
	var forma = document.getElementById('p-forma-stan'), wynik = document.getElementById('p-wynik');
	forma.addEventListener('submit', function (e)
	{
		e.preventDefault();
		var fd = new FormData();
		fd.append('csrf', forma.getAttribute('data-csrf')); fd.append('idp', sel.value); fd.append('stan', stan.value);
		fetch('p_stan_zapisz.php', { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (d)
		{
			wynik.className = 'alert mb-0 ' + (d.ok ? 'alert-success' : 'alert-danger');
			wynik.textContent = d.ok ? d.pracownik + ': ' + d.stan_nazwa : (d.blad || 'Nie udało się zmienić stanu.');
		}).catch(function (x) { console.log('Error!', x); wynik.className = 'alert alert-danger mb-0'; wynik.textContent = 'Nie udało się zmienić stanu.'; });
	});
})();
</script>
<?php require 'dol.php'; ?>
