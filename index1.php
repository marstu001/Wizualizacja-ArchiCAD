<?php declare(strict_types=1);
require_once 'lib.php';
wymagaj_logowania();
$tytul = 'AJAX';
require 'gora.php';
?>
<p class="fs-4">Informacja: <span class="status fw-bold"></span></p>
<div class="alert alert-danger d-none" id="bladAjax">Nie udało się pobrać danych z pliku JSON. Szczegóły błędu są w konsoli przeglądarki.</div>

<form class="row g-2 align-items-center mt-4" id="formImie" style="max-width: 28rem;">
	<div class="col">
		<input type="text" class="form-control" id="noweImie" maxlength="50" placeholder="Nowa wartość pola imie" required>
	</div>
	<div class="col-auto">
		<button class="btn btn-primary" type="submit">Zapisz w pliku JSON</button>
	</div>
</form>

<script>
(function ()
{
	var status = $('.status'),
		poll = function ()
		{
			$.ajax(
			{
				url: 'my_file.json',
				dataType: 'json',
				type: 'get',
				cache: false,
				success: function (data) { status.text(data.imie); $('#bladAjax').addClass('d-none'); },
				error: function (xhr, tekst, blad) { console.log('Error!', tekst, blad); $('#bladAjax').removeClass('d-none'); }
			});
		},
		pollInterval = setInterval(function () { poll(); }, 2000);
	poll();

	$('#formImie').on('submit', function (zdarzenie)
	{
		zdarzenie.preventDefault();
		$.post('zapisz_json.php', { imie: $('#noweImie').val(), csrf: <?php echo json_encode(csrf_token()); ?> }, function () { $('#noweImie').val(''); }, 'json');
	});
})();
</script>
<?php require 'dol.php'; ?>
