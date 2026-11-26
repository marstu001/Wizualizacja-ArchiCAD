<?php declare(strict_types=1);
require_once 'lib.php';
wymagaj_logowania();
$tytul = 'Klient CSV – odbiór danych w formacie CSV';
require 'gora.php';
?>
<style>
	.myDiv { border: 1px outset red; padding: .25rem .5rem; margin-bottom: .75rem; }
</style>
<div class="alert alert-secondary d-none" id="brak">Brak pomiarów. Dodaj pierwszy pomiar formularzem.</div>
<div class="myDiv" id="x0"></div>
<div class="myDiv" id="x1"></div>
<div class="myDiv" id="x2"></div>
<div class="myDiv" id="x3"></div>
<div class="myDiv" id="x4"></div>
<div class="myDiv" id="x5"></div>
<div class="text-muted mb-3" id="stan">Łączenie…</div>
<a class="btn btn-primary" href="b_formularz.php">FORMULARZ &ndash; dodaj pomiar</a>

<script>
var evtSource;

function polacz()
{
	evtSource = new EventSource('b_csv_from_db.php');
	evtSource.onopen = function () { document.getElementById('stan').textContent = 'Połączono, dane odświeżają się na żywo.'; };
	evtSource.onmessage = function (e)
	{
		var brak = document.getElementById('brak');
		if (e.data === 'BRAK') { brak.classList.remove('d-none'); return; }
		brak.classList.add('d-none');
		var data = e.data;
		data = data.split("\t");
		x0.textContent = data;
		x1.textContent = data[0];
		x2.textContent = data[1];
		x3.textContent = data[2];
		x4.textContent = data[3];
		x5.textContent = data[4];
	};
	evtSource.onerror = function ()
	{
		evtSource.close();
		console.log('Done!');
		document.getElementById('stan').textContent = 'Połączenie zakończone, ponawiam…';
		setTimeout(polacz, 3000);
	};
}
polacz();
</script>
<?php require 'dol.php'; ?>
