<?php declare(strict_types=1);
session_start();
if (isset($_SESSION['loggedin']))
{
	header('Location: index.php');
	exit();
}
$blad  = isset($_GET['blad']) ? $_GET['blad'] : '';
$login = isset($_GET['login']) ? $_GET['login'] : '';
$formularz = true;
$publiczna  = true;
require 'gora.php';
?>
		<form method="post" action="rejestracja_zapisz.php" enctype="multipart/form-data" id="formRejestracja">
			<h1 class="h3 mb-3 fw-normal">Rejestracja</h1>

			<?php if ($blad !== ''): ?>
			<div class="alert alert-danger" role="alert"><?php echo htmlentities($blad, ENT_QUOTES, "UTF-8"); ?></div>
			<?php endif; ?>

			<div class="form-floating">
				<input type="text" class="form-control" id="floatingInput" name="user" placeholder="login" maxlength="20" pattern="[A-Za-z0-9_\-]{1,20}" title="Litery bez polskich znaków, cyfry, _ lub -" autocomplete="username" value="<?php echo htmlentities($login, ENT_QUOTES, 'UTF-8'); ?>" required>
				<label for="floatingInput">Login</label>
				<i class="bi bi-check-circle-fill ptaszek" id="ptaszekLogin"></i>
			</div>
			<div class="form-text text-start small mb-2" id="opisLogin"></div>
			<div class="form-floating">
				<input type="password" class="form-control srodek" id="floatingPassword" name="pass" placeholder="Hasło" maxlength="20" required>
				<label for="floatingPassword">Hasło</label>
				<i class="bi bi-check-circle-fill ptaszek" id="ptaszekHaslo"></i>
			</div>
			<div class="form-floating">
				<input type="password" class="form-control" id="floatingPassword2" name="pass2" placeholder="Powtórz hasło" maxlength="20" required>
				<label for="floatingPassword2">Powtórz hasło</label>
				<i class="bi bi-check-circle-fill ptaszek" id="ptaszekHaslo2"></i>
			</div>
			<div class="form-text text-start small mb-2" id="opisHasla">Hasło: od 4 do 20 znaków.</div>

			<div class="form-text text-start mb-3">Login (do 20 znaków: litery bez polskich znaków, cyfry, <code>_</code>, <code>-</code>) staje się nazwą Twojego katalogu macierzystego na dysku.</div>

			<div class="mb-3 text-start">
				<label for="avatar" class="form-label">Avatar (opcjonalnie)</label>
				<input class="form-control" type="file" id="avatar" name="avatar" accept="image/png, image/jpeg">
				<div class="form-text">PNG lub JPEG do 2 MB. Jeśli nie wybierzesz pliku, zostanie ikona domyślna &ndash; avatar ustawisz też później po zalogowaniu.</div>
			</div>

			<button class="w-100 btn btn-lg btn-primary" type="submit">Zarejestruj się</button>
			<p class="mt-3 mb-3">Masz już konto? <a href="logowanie.php">Zaloguj się</a></p>
		</form>
<script>
(function ()
{
	var login = document.getElementById('floatingInput');
	var haslo = document.getElementById('floatingPassword');
	var haslo2 = document.getElementById('floatingPassword2');
	var opisLogin = document.getElementById('opisLogin');
	var opisHasla = document.getElementById('opisHasla');
	var form = document.getElementById('formRejestracja');
	var loginWolny = false;
	var licznik = null;

	function ptak(id, wlacz) { document.getElementById(id).classList.toggle('on', wlacz); }

	function sprawdzHasla()
	{
		var h = haslo.value, h2 = haslo2.value;
		var poprawne = h.length >= 4 && h.length <= 20;
		var zgodne = poprawne && h2 !== '' && h === h2;
		ptak('ptaszekHaslo', poprawne);
		ptak('ptaszekHaslo2', zgodne);
		if (h === '') { opisHasla.className = 'form-text text-start small mb-2'; opisHasla.textContent = 'Hasło: od 4 do 20 znaków.'; }
		else if (!poprawne) { opisHasla.className = 'form-text text-danger text-start small mb-2'; opisHasla.textContent = 'Hasło jest za krótkie (minimum 4 znaki).'; }
		else if (h2 === '') { opisHasla.className = 'form-text text-success text-start small mb-2'; opisHasla.innerHTML = '<i class="bi bi-check-circle-fill"></i> Hasło poprawne. Powtórz je w następnym polu.'; }
		else if (!zgodne) { opisHasla.className = 'form-text text-danger text-start small mb-2'; opisHasla.textContent = 'Hasła nie są takie same.'; }
		else { opisHasla.className = 'form-text text-success text-start small mb-2'; opisHasla.innerHTML = '<i class="bi bi-check-circle-fill"></i> Hasło poprawne, oba pola są zgodne.'; }
		return zgodne;
	}

	function sprawdzLogin()
	{
		loginWolny = false;
		ptak('ptaszekLogin', false);
		var l = login.value;
		if (l === '') { opisLogin.textContent = ''; return; }
		if (!/^[A-Za-z0-9_-]{1,20}$/.test(l)) { opisLogin.className = 'form-text text-danger text-start small mb-2'; opisLogin.textContent = 'Dozwolone: litery bez polskich znaków, cyfry, _ oraz -.'; return; }
		clearTimeout(licznik);
		licznik = setTimeout(function ()
		{
			fetch('sprawdz_login.php?login=' + encodeURIComponent(l), { credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (w)
				{
					if (login.value !== l) { return; }
					loginWolny = !!w.wolny;
					ptak('ptaszekLogin', loginWolny);
					opisLogin.className = 'form-text text-start small mb-2 ' + (loginWolny ? 'text-success' : 'text-danger');
					opisLogin.textContent = loginWolny ? 'Login jest wolny.' : 'Ten login jest już zajęty.';
				})
				.catch(function () { opisLogin.textContent = ''; });
		}, 300);
	}

	login.addEventListener('input', sprawdzLogin);
	haslo.addEventListener('input', sprawdzHasla);
	haslo2.addEventListener('input', sprawdzHasla);
	form.addEventListener('submit', function (z)
	{
		if (!sprawdzHasla()) { z.preventDefault(); haslo.focus(); }
	});
	if (login.value !== '') { sprawdzLogin(); }
})();
</script>
<?php require 'dol.php'; ?>
