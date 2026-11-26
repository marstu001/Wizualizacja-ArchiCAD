<?php declare(strict_types=1);
session_start();
if (isset($_SESSION['loggedin']))
{
	header('Location: index.php');
	exit();
}
$blad = isset($_GET['blad']) ? $_GET['blad'] : '';
$info = isset($_GET['info']) ? $_GET['info'] : '';
$formularz = true;
$publiczna  = true;
require 'gora.php';
?>
		<form method="post" action="weryfikuj3.php" id="formLogowanie">
			<h1 class="h3 mb-3 fw-normal">Zaloguj się</h1>

			<?php if ($info !== ''): ?>
			<div class="alert alert-success" role="alert"><?php echo htmlentities($info, ENT_QUOTES, "UTF-8"); ?></div>
			<?php endif; ?>
			<div class="alert alert-danger d-none" role="alert" id="bladAjax"></div>
			<?php if ($blad !== ''): ?>
			<div class="alert alert-danger" role="alert" id="bladLogowania"><?php echo htmlentities($blad, ENT_QUOTES, "UTF-8"); ?></div>
			<?php endif; ?>

			<div class="form-floating">
				<input type="text" class="form-control" id="floatingInput" name="user" placeholder="login" maxlength="20" required>
				<label for="floatingInput">Login</label>
			</div>
			<div class="form-floating">
				<input type="password" class="form-control" id="floatingPassword" name="pass" placeholder="Hasło" maxlength="20" required>
				<label for="floatingPassword">Hasło</label>
				<i class="bi bi-check-circle-fill ptaszek" id="ptaszekHaslo" title="Hasło poprawne"></i>
			</div>
			<div class="form-text text-success text-start mb-2 d-none" id="hasloPoprawne"><i class="bi bi-check-circle-fill"></i> Hasło poprawne &ndash; trwa logowanie&hellip;</div>

			<button class="w-100 btn btn-lg btn-primary" type="submit" id="przyciskLogowania">Zaloguj się</button>
			<p class="mt-3 mb-3">Nie masz konta? <a href="rejestruj.php">Zarejestruj się</a></p>
		</form>
<script>
(function ()
{
	var form = document.getElementById('formLogowanie');
	var przycisk = document.getElementById('przyciskLogowania');
	var blad = document.getElementById('bladAjax');
	var ptaszek = document.getElementById('ptaszekHaslo');
	var komunikat = document.getElementById('hasloPoprawne');
	form.addEventListener('submit', function (zdarzenie)
	{
		zdarzenie.preventDefault();
		var stary = document.getElementById('bladLogowania');
		if (stary) { stary.classList.add('d-none'); }
		blad.classList.add('d-none');
		ptaszek.classList.remove('on');
		komunikat.classList.add('d-none');
		przycisk.disabled = true;
		var dane = new FormData(form);
		dane.append('ajax', '1');
		fetch(form.action, { method: 'POST', body: dane, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
			.then(function (r) { return r.json(); })
			.then(function (w)
			{
				if (w.ok)
				{
					ptaszek.classList.add('on');
					komunikat.classList.remove('d-none');
					setTimeout(function () { window.location.href = w.url; }, 900);
					return;
				}
				blad.textContent = w.blad;
				blad.classList.remove('d-none');
				document.getElementById('floatingPassword').value = '';
				document.getElementById('floatingPassword').focus();
				przycisk.disabled = false;
			})
			.catch(function ()
			{
				blad.textContent = 'Brak odpowiedzi serwera. Spróbuj ponownie.';
				blad.classList.remove('d-none');
				przycisk.disabled = false;
			});
	});
})();
</script>
<?php require 'dol.php'; ?>
