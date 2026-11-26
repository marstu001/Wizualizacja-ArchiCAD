<?php declare(strict_types=1);
require_once 'lib.php';
wymagaj_logowania();
$tytul = 'Zadanie 9 – Komunikator';
require 'gora.php';
$ostrzezenie = (string)($_SESSION['ostrzezenie'] ?? '');
unset($_SESSION['ostrzezenie']);
if ($ostrzezenie !== '') { echo '<div class="alert alert-danger" role="alert"><i class="bi bi-exclamation-triangle-fill"></i> <strong>' . e($ostrzezenie) . '</strong></div>' . "\n"; }
?>
<style>
.kom-start { position: relative; overflow: hidden; border-radius: 1.2rem; background: linear-gradient(135deg, #0d6efd 0%, #6f42c1 100%); color: #fff; padding: 2.5rem 2rem; min-height: 22rem; display: flex; align-items: center; box-shadow: 0 6px 24px rgba(13,110,253,.25); }
.kom-start-tekst { position: relative; z-index: 2; max-width: 32rem; }
.kom-start-ikona { width: 4.2rem; height: 4.2rem; border-radius: 50%; background: rgba(255,255,255,.2); display: inline-flex; align-items: center; justify-content: center; font-size: 2.2rem; margin-bottom: 1rem; }
.kom-start h2 { font-size: 2.4rem; font-weight: 700; margin: 0 0 .4rem; }
.kom-start p { font-size: 1.25rem; opacity: .9; margin-bottom: 1.6rem; }
.kom-start .btn { border-radius: 2rem; padding: .65rem 1.6rem; font-weight: 600; }
.kom-start .btn-light { color: #0d6efd; }
.kom-start .btn-outline-light:hover { color: #6f42c1; }
.kom-start-podglad { position: absolute; right: 3rem; top: 50%; transform: translateY(-50%) rotate(-3deg); width: 22rem; display: flex; flex-direction: column; gap: .6rem; z-index: 1; }
.kom-start-podglad span { display: inline-block; padding: .55rem 1rem; border-radius: 1.2rem; font-size: 1.05rem; max-width: 80%; box-shadow: 0 3px 10px rgba(0,0,0,.18); }
.kom-start-podglad .a { align-self: flex-start; background: #fff; color: #1c1e21; }
.kom-start-podglad .b { align-self: flex-end; background: #00c2ff; color: #06283d; }
.kom-start-podglad .c { align-self: flex-start; background: #fff; color: #1c1e21; font-size: 2rem; padding: .2rem .8rem; }
@media (max-width: 991.98px) { .kom-start-podglad { display: none; } .kom-start { min-height: 0; } .kom-start h2 { font-size: 1.9rem; } }
</style>
<div class="kom-start">
	<div class="kom-start-tekst">
		<div class="kom-start-ikona"><i class="bi bi-chat-dots-fill"></i></div>
		<h2>Komunikator</h2>
		<p>Wiadomości, zdjęcia, filmy i pliki na żywo</p>
		<div class="d-flex flex-wrap gap-2">
			<a class="btn btn-light btn-lg" href="k_index1.php"><i class="bi bi-chat-dots"></i> Otwórz komunikator</a>
			<a class="btn btn-outline-light btn-lg" href="k_select.php"><i class="bi bi-cloud-upload"></i> Wyślij plik</a>
		</div>
	</div>
	<div class="kom-start-podglad" aria-hidden="true">
		<span class="a">Cześć! Co słychać? 👋</span>
		<span class="b">Wszystko gra, zobacz zdjęcie 📷</span>
		<span class="c">😍</span>
	</div>
</div>
<?php require 'dol.php'; ?>
