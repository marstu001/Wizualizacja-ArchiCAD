<?php declare(strict_types=1);
require_once 'lib.php';
wymagaj_logowania();
$tytul = 'Odczyt z AJAX – baza danych';
require 'gora.php';
?>
<div id="date" class="fs-4 fw-bold"></div>
<div class="alert alert-secondary d-none" id="brak">Brak wpisów. Dodaj pierwszy tekst w formularzu.</div>

<div class="text-muted mb-3" id="stan"></div>
<a class="btn btn-primary" href="formularz.php">Formularz &ndash; dodaj wpis</a>

<script>
(function ()
{
	var poll = function ()
	{
		$.ajax(
		{
			url: 'tekst_z_bazy.php',
			dataType: 'json',
			type: 'get',
			cache: false,
			success: function (data)
			{
				var pusto = data.text === null;
				$('#brak').toggleClass('d-none', !pusto);
				$('#date').text(pusto ? '' : data.text);
				$('#stan').text('Odświeżono: ' + new Date().toLocaleTimeString());
			},
			error: function (xhr, tekst, blad) { console.log('Error!', tekst, blad); $('#stan').text('Błąd pobierania danych'); }
		});
	};
	setInterval(poll, 2000);
	poll();
})();
</script>
<?php require 'dol.php'; ?>
