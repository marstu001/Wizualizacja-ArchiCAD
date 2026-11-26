(function ()
{
	var app = document.getElementById('kom-app');
	if (!app) { return; }
	var $ = function (id) { return document.getElementById(id); };
	var csrf = app.getAttribute('data-csrf');
	var login = app.getAttribute('data-login');
	var ostatni = parseInt(app.getAttribute('data-ostatni'), 10) || 0;
	var limitSerwera = app.getAttribute('data-limit');

	var panelRozmowy = $('kom-rozmowy'), szukaj = $('kom-szukaj'), stan = $('kom-stan');
	var czat = $('kom-czat'), brakCzatu = $('kom-brak-czatu');
	var przewijak = $('kom-wiadomosci'), lista = $('kom-lista'), pusto = $('kom-pusto');
	var starszeBlok = $('kom-starsze-blok'), starszeBtn = $('kom-starsze'), nowePigulka = $('kom-nowe');
	var forma = $('kom-forma'), pole = $('kom-tekst'), adresat = $('kom-adresat');
	var blad = $('kom-blad'), postep = $('kom-postep');
	var tytulStrony = document.title;

	var czaty = [];
	var aktywny = null;
	var maSzerzej = true;
	var jeszczeStarszych = false;
	var ladujeStarsze = false;
	var nieprzeczytaneNowe = 0;
	var zrodlo = null;

	function zapamietaj(k, v) { try { localStorage.setItem('kom_' + k, v); } catch (e) {} }
	function pamietane(k) { try { return localStorage.getItem('kom_' + k); } catch (e) { return null; } }
	function przeczytane() { try { return JSON.parse(pamietane('r_' + login) || '{}') || {}; } catch (e) { return {}; } }
	function zapiszPrzeczytane(m) { zapamietaj('r_' + login, JSON.stringify(m)); }

	function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
	function pokazBlad(t) { blad.textContent = t; blad.classList.toggle('d-none', !t); }
	function wDokument(html) { var t = document.createElement('template'); t.innerHTML = html.trim(); return t.content.firstElementChild; }
	function wszystkie() { return aktywny === '*'; }

	function czasSkrot(ts)
	{
		if (!ts) { return ''; }
		var d = new Date(ts * 1000), t = new Date();
		var dzien = function (x) { return x.getFullYear() + '-' + x.getMonth() + '-' + x.getDate(); };
		if (dzien(d) === dzien(t)) { return ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2); }
		var wczoraj = new Date(t.getTime() - 86400000);
		if (dzien(d) === dzien(wczoraj)) { return 'wczoraj'; }
		return ('0' + d.getDate()).slice(-2) + '.' + ('0' + (d.getMonth() + 1)).slice(-2);
	}

	function odcien(s)
	{
		var h = 0;
		for (var i = 0; i < s.length; i++) { h = (h * 31 + s.charCodeAt(i)) >>> 0; }
		return h % 360;
	}

	function znajdzCzat(u) { for (var i = 0; i < czaty.length; i++) { if (czaty[i].user === u) { return czaty[i]; } } return null; }

	function lacznieNowych()
	{
		var n = 0;
		czaty.forEach(function (c) { n += c.nowe; });
		return n;
	}

	function odswiezTytul()
	{
		var n = lacznieNowych();
		document.title = n > 0 ? '(' + n + ') ' + tytulStrony : tytulStrony;
	}

	function rysujRozmowy()
	{
		var fraza = szukaj.value.trim().toLowerCase();
		var html = '';
		var wszyscy = !fraza || 'wszystkie wiadomości'.indexOf(fraza) >= 0;
		if (wszyscy) {
			html += '<button type="button" class="kom-rozmowa kom-r-wszyscy' + (wszystkie() ? ' aktywna' : '') + '" data-z="*" style="--h:215">'
				+ '<span class="kom-r-awatar"><i class="bi bi-people-fill"></i></span>'
				+ '<span class="kom-r-tekst"><div class="kom-r-nazwa">Wszystkie wiadomości</div><div class="kom-r-podglad">Własne i adresowane do Ciebie</div></span></button>';
		}
		czaty.forEach(function (c)
		{
			if (fraza && c.user.toLowerCase().indexOf(fraza) < 0) { return; }
			var podglad = c.idk ? (c.moja ? 'Ty: ' : '') + c.podglad : 'Zacznij rozmowę';
			html += '<button type="button" class="kom-rozmowa' + (aktywny === c.user ? ' aktywna' : '') + (c.nowe ? ' nowe' : '') + '" data-z="' + esc(c.user) + '" style="--h:' + c.odcien + '">'
				+ '<span class="kom-r-awatar">' + c.avatar + '</span>'
				+ '<span class="kom-r-tekst"><div class="kom-r-nazwa">' + esc(c.user) + '</div><div class="kom-r-podglad">' + esc(podglad) + '</div></span>'
				+ '<span class="kom-r-bok"><span>' + czasSkrot(c.ts) + '</span>' + (c.nowe ? '<span class="kom-licznik">' + (c.nowe > 99 ? '99+' : c.nowe) + '</span>' : '') + '</span></button>';
		});
		if (!html) { html = '<div class="text-muted text-center p-3">Brak wyników</div>'; }
		panelRozmowy.innerHTML = html;
		odswiezTytul();
	}

	function pobierzCzaty()
	{
		return fetch('k_czaty.php?r=' + encodeURIComponent(JSON.stringify(przeczytane())), { credentials: 'same-origin', cache: 'no-store' })
			.then(function (r) { return r.json(); })
			.then(function (d)
			{
				if (!d.ok) { return; }
				czaty = d.czaty;
				if (aktywny && aktywny !== '*') {
					var c = znajdzCzat(aktywny);
					if (c) { c.nowe = 0; }
				}
				rysujRozmowy();
				var sel = adresat, bylo = sel.value;
				var znane = {};
				for (var i = 0; i < sel.options.length; i++) { znane[sel.options[i].value] = true; }
				czaty.forEach(function (c)
				{
					if (!znane[c.user]) { var o = document.createElement('option'); o.value = c.user; o.textContent = c.user; sel.appendChild(o); }
				});
				sel.value = bylo;
			})
			.catch(function (x) { console.log('Error!', x); });
	}

	function oznaczPrzeczytane(partner, idk)
	{
		if (!partner || partner === '*' || !idk) { return; }
		var m = przeczytane();
		if ((m[partner] || 0) < idk) { m[partner] = idk; zapiszPrzeczytane(m); }
		var c = znajdzCzat(partner);
		if (c && c.nowe) { c.nowe = 0; rysujRozmowy(); }
	}

	function najwiekszyObcy(partner)
	{
		var max = 0;
		lista.querySelectorAll('.kom-msg').forEach(function (el)
		{
			var i = parseInt(el.getAttribute('data-idk'), 10);
			if (i > max && (partner === '*' || el.getAttribute('data-partner') === partner)) { max = i; }
		});
		return max;
	}

	function oznaczWidoczne()
	{
		if (document.hidden || !aktywny) { return; }
		if (wszystkie()) {
			var wg = {};
			lista.querySelectorAll('.kom-msg.obca').forEach(function (el)
			{
				var p = el.getAttribute('data-partner'), i = parseInt(el.getAttribute('data-idk'), 10);
				if (el.getAttribute('data-do') === login && (!wg[p] || wg[p] < i)) { wg[p] = i; }
			});
			Object.keys(wg).forEach(function (p) { oznaczPrzeczytane(p, wg[p]); });
		} else {
			oznaczPrzeczytane(aktywny, najwiekszyObcy(aktywny));
		}
	}

	function malejaco() { return przewijak.classList.contains('malejaco'); }
	function przyDnie() { return malejaco() ? przewijak.scrollTop < 120 : przewijak.scrollHeight - przewijak.scrollTop - przewijak.clientHeight < 140; }
	function naDol() { przewijak.scrollTop = malejaco() ? 0 : przewijak.scrollHeight; nowePigulka.classList.add('d-none'); nieprzeczytaneNowe = 0; }

	function odswiezPusto()
	{
		pusto.classList.toggle('d-none', lista.querySelectorAll('.kom-msg').length > 0);
	}

	function separatory()
	{
		lista.querySelectorAll('.kom-dzien').forEach(function (e) { e.remove(); });
		var poprzedni = '';
		var dzis = new Date(), wczoraj = new Date(dzis.getTime() - 86400000);
		var iso = function (d) { return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2); };
		lista.querySelectorAll('.kom-msg').forEach(function (el)
		{
			var dz = el.getAttribute('data-dzien');
			if (dz !== poprzedni) {
				var s = document.createElement('div');
				s.className = 'kom-dzien';
				if (dz === iso(dzis)) { s.textContent = 'Dzisiaj'; }
				else if (dz === iso(wczoraj)) { s.textContent = 'Wczoraj'; }
				else { s.textContent = new Date(el.getAttribute('data-ts') * 1000).toLocaleDateString('pl-PL', { day: 'numeric', month: 'long', year: 'numeric' }); }
				lista.insertBefore(s, el);
				poprzedni = dz;
			}
		});
	}

	function dodajDoWidoku(html, nowa, wlasna)
	{
		var el = wDokument(html);
		if (!el) { return null; }
		var idk = el.getAttribute('data-idk');
		if (lista.querySelector('.kom-msg[data-idk="' + idk + '"]')) { return null; }
		var przyklejony = przyDnie();
		if (nowa) { el.classList.add('nowa'); }
		var wstaw = null;
		var elementy = lista.querySelectorAll('.kom-msg');
		for (var i = elementy.length - 1; i >= 0; i--) {
			if (parseInt(elementy[i].getAttribute('data-idk'), 10) < parseInt(idk, 10)) { wstaw = elementy[i]; break; }
		}
		if (wstaw) { wstaw.after(el); } else { lista.insertBefore(el, lista.firstChild); }
		separatory();
		odswiezPusto();
		if (przyklejony || wlasna) { naDol(); }
		else {
			nieprzeczytaneNowe++;
			nowePigulka.querySelector('span').textContent = nieprzeczytaneNowe === 1 ? 'Nowa wiadomość' : 'Nowe wiadomości (' + nieprzeczytaneNowe + ')';
			nowePigulka.classList.remove('d-none');
		}
		return el;
	}

	function naglowek()
	{
		var nazwa = $('kom-czat-nazwa'), opis = $('kom-czat-opis'), av = $('kom-czat-awatar');
		czat.classList.toggle('wszystkie', wszystkie());
		adresat.classList.toggle('d-none', !wszystkie());
		if (wszystkie()) {
			nazwa.textContent = 'Wszystkie wiadomości';
			opis.textContent = 'Własne i adresowane do Ciebie';
			av.innerHTML = '<span class="kom-r-awatar" style="background:linear-gradient(135deg,#0d6efd,#6f42c1);color:#fff"><i class="bi bi-people-fill" style="font-size:20px!important;color:#fff!important"></i></span>';
		} else {
			var c = znajdzCzat(aktywny);
			nazwa.textContent = aktywny;
			opis.textContent = '';
			av.innerHTML = '<span class="kom-r-awatar" style="--h:' + odcien(aktywny) + '">' + (c ? c.avatar.replace(/(width|height)="\d+"/g, '$1="40"').replace(/font-size:\d+px/, 'font-size:40px') : '') + '</span>';
		}
	}

	function otworz(partner, bezHistorii)
	{
		aktywny = partner;
		jeszczeStarszych = false;
		zapamietaj('aktywny_' + login, partner);
		brakCzatu.classList.add('d-none');
		czat.classList.remove('d-none');
		app.classList.add('otwarty');
		motyw = DOMYSLNY();
		zastosujMotyw();
		$('kom-motyw').style.display = partner === '*' ? 'none' : '';
		oknoMotywu.classList.add('d-none');
		naglowek();
		lista.innerHTML = '';
		odswiezPusto();
		pokazBlad('');
		nieprzeczytaneNowe = 0;
		nowePigulka.classList.add('d-none');
		var c = znajdzCzat(partner);
		if (c) { c.nowe = 0; }
		rysujRozmowy();
		fetch('k_lista.php?z=' + encodeURIComponent(partner === '*' ? '' : partner), { credentials: 'same-origin', cache: 'no-store' })
			.then(function (r) { return r.json(); })
			.then(function (d)
			{
				if (!d.ok || aktywny !== partner) { return; }
				motyw = normalizujMotyw(d.motyw);
				zastosujMotyw();
				lista.innerHTML = d.html;
				jeszczeStarszych = d.jeszcze;
				starszeBlok.classList.toggle('d-none', !d.jeszcze);
				separatory();
				odswiezPusto();
				naDol();
				oznaczWidoczne();
				if (!wszystkie() && window.innerWidth >= 768) { pole.focus(); }
			})
			.catch(function (x) { console.log('Error!', x); });
	}

	function pierwszyId()
	{
		var el = lista.querySelector('.kom-msg');
		return el ? parseInt(el.getAttribute('data-idk'), 10) : 0;
	}

	function starsze()
	{
		if (ladujeStarsze || !jeszczeStarszych || !pierwszyId()) { return; }
		ladujeStarsze = true;
		var partner = aktywny;
		var wysokosc = przewijak.scrollHeight, pozycja = przewijak.scrollTop;
		fetch('k_lista.php?z=' + encodeURIComponent(partner === '*' ? '' : partner) + '&przed=' + pierwszyId(), { credentials: 'same-origin', cache: 'no-store' })
			.then(function (r) { return r.json(); })
			.then(function (d)
			{
				if (!d.ok || aktywny !== partner) { return; }
				var t = document.createElement('template');
				t.innerHTML = d.html;
				var pierwszy = lista.querySelector('.kom-msg');
				Array.prototype.slice.call(t.content.children).forEach(function (el)
				{
					if (!lista.querySelector('.kom-msg[data-idk="' + el.getAttribute('data-idk') + '"]')) { lista.insertBefore(el, pierwszy); }
				});
				jeszczeStarszych = d.jeszcze;
				starszeBlok.classList.toggle('d-none', !d.jeszcze);
				separatory();
				if (!malejaco()) { przewijak.scrollTop = pozycja + (przewijak.scrollHeight - wysokosc); }
			})
			.catch(function (x) { console.log('Error!', x); })
			.then(function () { ladujeStarsze = false; });
	}

	function dzwiekPowiadomienia()
	{
		if (pamietane('dzwiek') === '0') { return; }
		try {
			var AC = window.AudioContext || window.webkitAudioContext;
			if (!AC) { return; }
			var ctx = new AC(), o = ctx.createOscillator(), g = ctx.createGain();
			o.type = 'sine'; o.frequency.value = 880;
			g.gain.setValueAtTime(0.0001, ctx.currentTime);
			g.gain.exponentialRampToValueAtTime(0.18, ctx.currentTime + 0.02);
			g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.25);
			o.connect(g); g.connect(ctx.destination);
			o.start(); o.stop(ctx.currentTime + 0.27);
			setTimeout(function () { ctx.close(); }, 500);
		} catch (e) {}
	}

	function mow(tekst)
	{
		if (!('speechSynthesis' in window) || !tekst) { return; }
		var u = new SpeechSynthesisUtterance(tekst);
		u.lang = 'pl-PL';
		window.speechSynthesis.speak(u);
	}

	function odbierz(d)
	{
		if (d.idk > ostatni) { ostatni = d.idk; }
		var el = wDokument(d.html);
		if (!el) { return; }
		var od = el.getAttribute('data-od'), dla = el.getAttribute('data-do');
		var partner = el.getAttribute('data-partner');
		var moje = od === login || dla === login;
		var obca = od !== login;
		var wWidoku = aktywny !== null && (wszystkie() || (moje && partner === aktywny));
		var widoczne = !document.hidden;
		var jestMotyw = el.hasAttribute('data-motyw');

		if (jestMotyw && obca && wWidoku && !wszystkie()) {
			try { motyw = normalizujMotyw(JSON.parse(el.getAttribute('data-motyw'))); zastosujMotyw(); rysujMotyw(); } catch (x) { console.log('Error!', x); }
		}

		if (moje) {
			var c = znajdzCzat(partner);
			if (!c) { pobierzCzaty(); }
			else {
				c.idk = parseInt(el.getAttribute('data-idk'), 10);
				c.ts = parseInt(el.getAttribute('data-ts'), 10);
				c.moja = od === login;
				c.podglad = el.getAttribute('data-podglad');
				if (obca && !jestMotyw && !(wWidoku && widoczne)) { c.nowe++; }
				czaty.sort(function (a, b) { return b.idk - a.idk || (a.user < b.user ? -1 : 1); });
				rysujRozmowy();
			}
		}
		if (wWidoku) {
			dodajDoWidoku(d.html, true, !obca);
			if (obca && widoczne) { oznaczWidoczne(); }
		}
		if (obca && moje && !jestMotyw) {
			if (!wWidoku || !widoczne) { dzwiekPowiadomienia(); }
			if (wWidoku && $('kom-glos').classList.contains('wl') && d.tekst) { mow(od + ': ' + d.tekst); }
		}
	}

	function stanPolaczenia(ok)
	{
		stan.className = 'badge rounded-pill ' + (ok ? 'text-bg-success' : 'text-bg-secondary');
		stan.innerHTML = ok ? '<i class="bi bi-wifi"></i> Na żywo' : '<i class="bi bi-wifi-off"></i> Łączenie';
	}

	function polacz()
	{
		if (zrodlo) { zrodlo.close(); }
		zrodlo = new EventSource('k_stream.php?od=' + ostatni);
		zrodlo.onopen = function () { stanPolaczenia(true); };
		zrodlo.addEventListener('msg', function (e)
		{
			try { odbierz(JSON.parse(e.data)); } catch (x) { console.log('Error!', x); }
		});
		zrodlo.addEventListener('reload', function ()
		{
			pobierzCzaty();
			if (aktywny !== null) { przeladujWidok(); }
		});
		zrodlo.onerror = function ()
		{
			stanPolaczenia(false);
			if (zrodlo.readyState === EventSource.CLOSED) {
				zrodlo = null;
				setTimeout(polacz, 3000);
			}
		};
	}

	function przeladujWidok()
	{
		var partner = aktywny;
		var min = pierwszyId();
		fetch('k_lista.php?z=' + encodeURIComponent(partner === '*' ? '' : partner) + '&od_min=' + min, { credentials: 'same-origin', cache: 'no-store' })
			.then(function (r) { return r.json(); })
			.then(function (d)
			{
				if (!d.ok || aktywny !== partner) { return; }
				var przyklejony = przyDnie();
				var pozycja = przewijak.scrollTop;
				lista.innerHTML = d.html;
				separatory();
				odswiezPusto();
				if (przyklejony) { naDol(); } else { przewijak.scrollTop = pozycja; }
			})
			.catch(function (x) { console.log('Error!', x); });
	}

	var SKROTY = [[':-)', '🙂'], [':)', '🙂'], [':D', '😄'], [':-D', '😄'], [':(', '🙁'], [':-(', '🙁'], [';)', '😉'], [';-)', '😉'], [':P', '😛'], [':p', '😛'], [':*', '😘'], [':o', '😮'], [':O', '😮'], ['xD', '😆'], ['XD', '😆'], ['<3', '❤️'], [':/', '😕'], [":'(", '😢']];
	function zamienSkroty(t)
	{
		return t.split(/(\s+)/).map(function (s) { for (var i = 0; i < SKROTY.length; i++) { if (s === SKROTY[i][0]) { return SKROTY[i][1]; } } return s; }).join('');
	}

	function adresatWysylki()
	{
		return wszystkie() ? adresat.value : aktywny;
	}

	var oczekujace = [];
	var taca = $('kom-taca');

	function rysujTace()
	{
		taca.innerHTML = '';
		taca.classList.toggle('d-none', !oczekujace.length);
		oczekujace.forEach(function (f, i)
		{
			var el = document.createElement('div');
			el.className = 'kom-zal';
			var mini = '<i class="bi bi-file-earmark"></i>';
			if (/^image\//.test(f.type)) { mini = '<img alt="" src="' + URL.createObjectURL(f) + '">'; }
			else if (/^video\//.test(f.type)) { mini = '<i class="bi bi-film"></i>'; }
			else if (/^audio\//.test(f.type)) { mini = '<i class="bi bi-music-note-beamed"></i>'; }
			el.innerHTML = '<span class="kom-zal-mini">' + mini + '</span><span class="kom-zal-nazwa">' + esc(f.name || 'plik') + '</span><button type="button" class="kom-zal-x" data-i="' + i + '" title="Usuń"><i class="bi bi-x-lg"></i></button>';
			taca.appendChild(el);
		});
	}

	function dodajOczekujace(pliki)
	{
		Array.prototype.slice.call(pliki).forEach(function (f) { oczekujace.push(f); });
		rysujTace();
		pole.focus();
	}

	taca.addEventListener('click', function (e)
	{
		var b = e.target.closest('.kom-zal-x');
		if (!b) { return; }
		oczekujace.splice(parseInt(b.getAttribute('data-i'), 10), 1);
		rysujTace();
	});

	function wyslijTekst(zdarzenie)
	{
		zdarzenie.preventDefault();
		pokazBlad('');
		var tekst = zamienSkroty(pole.value.trim());
		var odb = adresatWysylki();
		if (!odb) { pokazBlad('Wybierz adresata.'); adresat.focus(); return; }
		if (oczekujace.length) {
			var doWyslania = oczekujace;
			oczekujace = [];
			rysujTace();
			wyslijPliki(doWyslania, function () { if (tekst !== '') { wyslijTekstDo(odb, tekst); } });
			return;
		}
		if (tekst === '') { pole.focus(); return; }
		wyslijTekstDo(odb, tekst);
	}

	function wyslijTekstDo(odb, tekst)
	{
		var fd = new FormData();
		fd.append('csrf', csrf);
		fd.append('ajax', '1');
		fd.append('recipient', odb);
		fd.append('post', tekst);
		pole.disabled = true;
		fetch('k_add.php', { method: 'POST', body: fd, credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (d)
			{
				if (!d.ok) { pokazBlad(d.blad || 'Nie udało się wysłać wiadomości.'); return; }
				pole.value = '';
				if (d.idk > ostatni) { ostatni = d.idk; }
				odbierz({ idk: d.idk, html: d.html, tekst: '' });
			})
			.catch(function () { pokazBlad('Nie udało się wysłać wiadomości.'); })
			.then(function () { pole.disabled = false; pole.focus(); });
	}

	function wyslijPliki(pliki, poZakonczeniu)
	{
		pliki = Array.prototype.slice.call(pliki);
		if (!pliki.length) { return; }
		pokazBlad('');
		var odb = adresatWysylki();
		if (!odb) { pokazBlad('Wybierz adresata.'); adresat.focus(); return; }
		var pasek = postep.firstElementChild;
		var i = 0;
		postep.classList.remove('d-none');

		function nastepny()
		{
			if (i >= pliki.length) {
				setTimeout(function () { postep.classList.add('d-none'); pasek.style.width = '0%'; pasek.textContent = ''; }, 600);
				if (poZakonczeniu) { poZakonczeniu(); }
				return;
			}
			var plik = pliki[i];
			var xhr = new XMLHttpRequest();
			var fd = new FormData();
			fd.append('csrf', csrf);
			fd.append('ajax', '1');
			fd.append('recipient', odb);
			fd.append('plik[]', plik, plik.name || 'plik');
			xhr.open('POST', 'k_upload.php');
			xhr.upload.onprogress = function (e)
			{
				if (!e.lengthComputable) { return; }
				var p = Math.round(e.loaded / e.total * 100);
				pasek.style.width = p + '%';
				pasek.textContent = (i + 1) + '/' + pliki.length + ' ' + (plik.name || '') + ' ' + p + '%';
			};
			xhr.onload = function ()
			{
				var d = null;
				try { d = JSON.parse(xhr.responseText); } catch (x) {}
				if (d && d.wiadomosci) {
					d.wiadomosci.forEach(function (w) { if (w.idk > ostatni) { ostatni = w.idk; } odbierz({ idk: w.idk, html: w.html, tekst: '' }); });
				}
				if (!d) { pokazBlad((plik.name || 'Plik') + ': nie udało się wysłać pliku (limit serwera ' + limitSerwera + ')'); }
				else if (d.bledy && d.bledy.length) { pokazBlad(d.bledy.join('; ')); }
				i++;
				nastepny();
			};
			xhr.onerror = function () { pokazBlad((plik.name || 'Plik') + ': błąd połączenia'); i++; nastepny(); };
			xhr.send(fd);
		}
		nastepny();
	}

	var EMOJI = {
		'😀': '😀 😃 😄 😁 😆 😅 😂 🤣 😊 😇 🙂 🙃 😉 😌 😍 🥰 😘 😗 😙 😚 😋 😛 😝 😜 🤪 🤨 🧐 🤓 😎 🤩 🥳 😏 😒 😞 😔 😟 😕 🙁 😣 😖 😫 😩 🥺 😢 😭 😤 😠 😡 🤬 🤯 😳 🥵 🥶 😱 😨 😰 😥 😓 🤗 🤔 🤭 🤫 🤥 😶 😐 😑 😬 🙄 😯 😦 😧 😮 😲 🥱 😴 🤤 😪 😵 🤐 🥴 🤢 🤮 🤧 😷 🤒 🤕',
		'👍': '👍 👎 👌 ✌️ 🤞 🤟 🤘 🤙 👈 👉 👆 👇 ☝️ ✋ 🤚 🖐️ 🖖 👋 🤝 🙏 👏 🙌 👐 🤲 💪 🦾 ✍️ 🤳 💅 🙋 🙆 🙅 🤷 🤦 💁 🙇',
		'❤️': '❤️ 🧡 💛 💚 💙 💜 🖤 🤍 🤎 💔 ❣️ 💕 💞 💓 💗 💖 💘 💝 💟 💋 💯 💢 💥 💫 💦 💨 🔥 ✨ ⭐ 🌟 🎉 🎊 🎈 🎁 🏆 🥇',
		'🐶': '🐶 🐱 🐭 🐹 🐰 🦊 🐻 🐼 🐨 🐯 🦁 🐮 🐷 🐸 🐵 🙈 🙉 🙊 🐔 🐧 🐦 🐤 🦆 🦅 🦉 🦇 🐺 🐗 🐴 🦄 🐝 🐛 🦋 🐌 🐞 🐢 🐍 🐙 🦀 🐠 🐬 🐳 🌸 🌹 🌻 🌲 🍀',
		'🍕': '🍏 🍎 🍐 🍊 🍋 🍌 🍉 🍇 🍓 🍒 🍑 🥭 🍍 🥥 🥝 🍅 🥑 🥦 🥕 🌽 🥔 🍞 🧀 🍳 🥞 🥓 🍔 🍟 🍕 🌭 🥪 🌮 🌯 🍝 🍜 🍣 🍤 🍦 🍩 🍪 🎂 🍰 🍫 🍬 🍭 ☕ 🍵 🍺 🍷 🥂',
		'⚽': '⚽ 🏀 🏈 ⚾ 🎾 🏐 🏉 🎱 🏓 🏸 🥊 🎮 🎲 🎯 🎵 🎶 🎤 🎧 🎸 🎹 🎬 📷 📱 💻 ⌚ 📚 ✏️ 📌 🔑 🔒 💡 🚗 ✈️ 🚀 🏠 🌍 ☀️ ⛅ 🌧️ ⛄ 🌈'
	};
	var okno = $('kom-emoji-okno');
	(function ()
	{
		var klucze = Object.keys(EMOJI);
		var karty = '<div class="kom-emoji-karty">' + klucze.map(function (k, i) { return '<button type="button" data-k="' + i + '"' + (i === 0 ? ' class="wl"' : '') + '>' + k + '</button>'; }).join('') + '</div><div class="kom-emoji-siatka"></div>';
		okno.innerHTML = karty;
		var siatka = okno.querySelector('.kom-emoji-siatka');
		function pokaz(i)
		{
			siatka.innerHTML = EMOJI[klucze[i]].split(' ').map(function (e) { return '<button type="button">' + e + '</button>'; }).join('');
			okno.querySelectorAll('.kom-emoji-karty button').forEach(function (b, j) { b.classList.toggle('wl', j === i); });
		}
		pokaz(0);
		okno.addEventListener('click', function (e)
		{
			var b = e.target.closest('button');
			if (!b) { return; }
			if (b.hasAttribute('data-k')) { pokaz(parseInt(b.getAttribute('data-k'), 10)); return; }
			var s = pole.selectionStart === null ? pole.value.length : pole.selectionStart;
			var k = pole.selectionEnd === null ? s : pole.selectionEnd;
			pole.value = pole.value.slice(0, s) + b.textContent + pole.value.slice(k);
			pole.focus();
			var poz = s + b.textContent.length;
			pole.setSelectionRange(poz, poz);
		});
	})();
	$('kom-emoji').addEventListener('click', function (e) { e.stopPropagation(); okno.classList.toggle('d-none'); });
	document.addEventListener('click', function (e) { if (!okno.contains(e.target)) { okno.classList.add('d-none'); } });

	forma.addEventListener('submit', wyslijTekst);
	$('kom-zalacznik').addEventListener('click', function () { $('kom-plik').click(); });
	$('kom-plik').addEventListener('change', function () { dodajOczekujace(this.files); this.value = ''; });
	pole.addEventListener('paste', function (e)
	{
		var f = e.clipboardData && e.clipboardData.files;
		if (f && f.length) { e.preventDefault(); dodajOczekujace(f); }
	});

	['dragenter', 'dragover'].forEach(function (n)
	{
		document.addEventListener(n, function (e)
		{
			if (e.dataTransfer && Array.prototype.indexOf.call(e.dataTransfer.types, 'Files') >= 0) { e.preventDefault(); czat.classList.add('przeciaganie'); przewijak.style.outline = '3px dashed #0d6efd'; przewijak.style.outlineOffset = '-6px'; }
		});
	});
	function koniecPrzeciagania() { przewijak.style.outline = ''; }
	document.addEventListener('dragleave', function (e) { if (!e.relatedTarget) { koniecPrzeciagania(); } });
	document.addEventListener('drop', function (e)
	{
		koniecPrzeciagania();
		if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) { e.preventDefault(); if (aktywny !== null) { dodajOczekujace(e.dataTransfer.files); } }
	});

	lista.addEventListener('click', function (e)
	{
		var obraz = e.target.closest('a.kom-podglad');
		if (obraz) {
			e.preventDefault();
			var lb = $('kom-lightbox');
			lb.querySelector('img').src = obraz.getAttribute('href');
			lb.classList.remove('d-none');
			return;
		}
		var czytaj = e.target.closest('.kom-czytaj');
		if (czytaj) {
			if ('speechSynthesis' in window) { window.speechSynthesis.cancel(); }
			var t = czytaj.closest('.kom-msg').querySelector('.kom-tekst');
			mow(t ? t.textContent : '');
			return;
		}
		var usun = e.target.closest('.kom-usun');
		if (usun) {
			if (!usun.classList.contains('potwierdz')) {
				usun.classList.add('potwierdz');
				usun.textContent = 'Na pewno?';
				setTimeout(function () { usun.classList.remove('potwierdz'); usun.innerHTML = '<i class="bi bi-trash"></i>'; }, 3000);
				return;
			}
			var msg = usun.closest('.kom-msg');
			var fd = new FormData();
			fd.append('csrf', csrf);
			fd.append('idk', msg.getAttribute('data-idk'));
			fetch('k_kasuj.php', { method: 'POST', body: fd, credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (d)
				{
					if (d.ok) { msg.remove(); separatory(); odswiezPusto(); pobierzCzaty(); }
					else { pokazBlad(d.blad || 'Nie udało się usunąć wiadomości.'); }
				})
				.catch(function () { pokazBlad('Nie udało się usunąć wiadomości.'); });
		}
	});
	var lightbox = $('kom-lightbox');
	lightbox.addEventListener('click', function () { lightbox.classList.add('d-none'); lightbox.querySelector('img').removeAttribute('src'); });
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { lightbox.classList.add('d-none'); okno.classList.add('d-none'); $('kom-motyw-okno').classList.add('d-none'); } });

	przewijak.addEventListener('load', function () { if (przyklejonyPoZaladowaniu) { przewijak.scrollTop = malejaco() ? 0 : przewijak.scrollHeight; } }, true);
	var przyklejonyPoZaladowaniu = true;
	przewijak.addEventListener('scroll', function ()
	{
		przyklejonyPoZaladowaniu = przyDnie();
		if (przyDnie()) { nowePigulka.classList.add('d-none'); nieprzeczytaneNowe = 0; }
		var blisko = malejaco() ? przewijak.scrollHeight - przewijak.scrollTop - przewijak.clientHeight < 60 : przewijak.scrollTop < 60;
		if (blisko) { starsze(); }
	});
	starszeBtn.addEventListener('click', starsze);
	nowePigulka.addEventListener('click', naDol);

	panelRozmowy.addEventListener('click', function (e)
	{
		var b = e.target.closest('.kom-rozmowa');
		if (b) { otworz(b.getAttribute('data-z')); }
	});
	szukaj.addEventListener('input', rysujRozmowy);
	$('kom-wstecz').addEventListener('click', function () { app.classList.remove('otwarty'); aktywny = null; zapamietaj('aktywny_' + login, ''); rysujRozmowy(); });

	function przelacznik(id, klucz, domyslnie, naZmiane)
	{
		var b = $(id);
		var wl = pamietane(klucz);
		wl = wl === null ? domyslnie : wl === '1';
		b.classList.toggle('wl', wl);
		b.addEventListener('click', function ()
		{
			var nowy = !b.classList.contains('wl');
			b.classList.toggle('wl', nowy);
			zapamietaj(klucz, nowy ? '1' : '0');
			if (naZmiane) { naZmiane(nowy); }
		});
		if (naZmiane) { naZmiane(wl, true); }
	}
	przelacznik('kom-dzwiek', 'dzwiek', true, function (wl) { $('kom-dzwiek').querySelector('i').className = wl ? 'bi bi-bell' : 'bi bi-bell-slash'; });
	if ('speechSynthesis' in window) {
		przelacznik('kom-glos', 'glos', false, function (wl, start) { $('kom-glos').querySelector('i').className = wl ? 'bi bi-volume-up-fill' : 'bi bi-volume-up'; if (wl && !start) { mow(' '); } });
	} else {
		$('kom-glos').style.display = 'none';
	}
	przelacznik('kom-kolejnosc', 'malejaco', false, function (wl)
	{
		przewijak.classList.toggle('malejaco', wl);
		$('kom-kolejnosc').title = wl ? 'Najnowsze na dole' : 'Najnowsze u góry';
		$('kom-kolejnosc').querySelector('i').className = wl ? 'bi bi-sort-up' : 'bi bi-sort-down';
		naDol();
	});


	var TLA = [
		{ n: 'Białe', t: '#ffffff' },
		{ n: 'Jasnoszare', t: '#eceff3' },
		{ n: 'Niebo', t: 'linear-gradient(180deg,#cfe6ff,#f3f9ff)' },
		{ n: 'Zachód słońca', t: 'linear-gradient(160deg,#ffe0cc,#ffcfe6 55%,#dcd0ff)' },
		{ n: 'Las', t: 'linear-gradient(180deg,#cdeed6,#f3fbe8)' },
		{ n: 'Ocean', t: 'linear-gradient(180deg,#b9ecf2,#e6f7ff)' },
		{ n: 'Kropki', t: 'radial-gradient(circle,#c9d8f5 1.4px,transparent 1.6px) 0 0/18px 18px,#eef3ff' },
		{ n: 'Noc', t: 'linear-gradient(180deg,#1b1d2b,#2a2d45)', c: true },
		{ n: 'Grafit', t: '#18191a', c: true }
	];
	var KOLORY = ['#0d6efd', '#6f42c1', '#d63384', '#dc3545', '#fd7e14', '#198754', '#0aa2c0', '#343a40'];
	var SZARE = ['#e4e6eb', '#ffffff', '#fff3cd', '#d1e7dd', '#cff4fc', '#f8d7da', '#e2d9f3', '#495057'];
	var DOMYSLNY = function () { return { tlo: 0, tw: '', k: {} }; };
	var motyw = DOMYSLNY();
	var stylMotywu = document.createElement('style');
	document.head.appendChild(stylMotywu);
	var wysylkaMotywu = null;
	var opisyMotywu = [];

	function normalizujMotyw(m)
	{
		var r = DOMYSLNY();
		if (!m) { return r; }
		r.tlo = parseInt(m.tlo, 10) || 0;
		r.tw = m.tw || '';
		Object.keys(m.k || {}).forEach(function (u) { if (m.k[u]) { r.k[u] = m.k[u]; } });
		return r;
	}

	function kontrast(hex)
	{
		var h = hex.replace('#', '');
		if (h.length === 3) { h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2]; }
		var r = parseInt(h.substr(0, 2), 16), g = parseInt(h.substr(2, 2), 16), b = parseInt(h.substr(4, 2), 16);
		return (0.299 * r + 0.587 * g + 0.114 * b) > 160 ? '#050505' : '#ffffff';
	}

	function zastosujMotyw()
	{
		var tlo = motyw.tw || TLA[motyw.tlo || 0].t;
		var ciemne = motyw.tw ? kontrast(motyw.tw) === '#ffffff' : !!TLA[motyw.tlo || 0].c;
		przewijak.style.setProperty('--tlo', tlo);
		przewijak.classList.toggle('ciemne', ciemne);
		var css = '';
		Object.keys(motyw.k).forEach(function (u)
		{
			if (!/^[A-Za-z0-9_-]+$/.test(u) || !/^#[0-9a-fA-F]{6}$/.test(motyw.k[u])) { return; }
			var sel = '#kom-wiadomosci .kom-msg[data-od="' + u + '"] .kom-dymek';
			css += sel + '{background:' + motyw.k[u] + ';color:' + kontrast(motyw.k[u]) + '}' + sel + ' a{color:' + kontrast(motyw.k[u]) + '}';
		});
		stylMotywu.textContent = css;
	}

	function zmienMotyw(opis, cichy)
	{
		zastosujMotyw();
		if (!cichy) { rysujMotyw(); }
		if (aktywny === null || wszystkie()) { return; }
		var partner = aktywny;
		if (opisyMotywu.indexOf(opis) < 0) { opisyMotywu.push(opis); }
		clearTimeout(wysylkaMotywu);
		wysylkaMotywu = setTimeout(function ()
		{
			var opisLaczny = opisyMotywu.map(function (o, i) { return i === 0 ? o : o.replace(/^zmienił\(a\) /, ''); }).join(' oraz ');
			opisyMotywu = [];
			var fd = new FormData();
			fd.append('csrf', csrf);
			fd.append('ajax', '1');
			fd.append('recipient', partner);
			fd.append('post', '@@motyw:' + JSON.stringify({ tlo: motyw.tlo, tw: motyw.tw, k: motyw.k, o: opisLaczny }));
			fetch('k_add.php', { method: 'POST', body: fd, credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (d) { if (d.ok) { if (d.idk > ostatni) { ostatni = d.idk; } odbierz({ idk: d.idk, html: d.html, tekst: '' }); } })
				.catch(function (x) { console.log('Error!', x); });
		}, 700);
	}

	function rysujMotyw()
	{
		var okno = $('kom-motyw-okno');
		if (aktywny === null || wszystkie()) { okno.classList.add('d-none'); return; }
		var moj = motyw.k[login] || '', obcy = motyw.k[aktywny] || '';
		var h = '<h3>Tło rozmowy</h3><div class="kom-probki">';
		TLA.forEach(function (t, i) { h += '<button type="button" class="kom-probka tlo' + (!motyw.tw && (motyw.tlo || 0) === i ? ' wl' : '') + '" data-tlo="' + i + '" title="' + t.n + '" style="background:' + t.t + '"></button>'; });
		h += '<span class="kom-probka wlasny' + (motyw.tw ? ' wl' : '') + '" title="Własny kolor tła"><i class="bi bi-eyedropper"></i><input type="color" data-wlasne="tw" value="' + (motyw.tw || '#ffffff') + '"></span></div>';
		h += '<h3>Moje wiadomości (' + esc(login) + ')</h3><div class="kom-probki"><button type="button" class="kom-probka auto' + (!moj ? ' wl' : '') + '" data-u="' + esc(login) + '" data-c="" title="Kolor automatyczny"></button>';
		KOLORY.forEach(function (c) { h += '<button type="button" class="kom-probka' + (moj === c ? ' wl' : '') + '" data-u="' + esc(login) + '" data-c="' + c + '" style="background:' + c + '"></button>'; });
		h += '<span class="kom-probka wlasny' + (moj && KOLORY.indexOf(moj) < 0 ? ' wl' : '') + '" title="Własny kolor"><i class="bi bi-eyedropper"></i><input type="color" data-wlasne="' + esc(login) + '" value="' + (moj || '#0d6efd') + '"></span></div>';
		h += '<h3>Wiadomości rozmówcy (' + esc(aktywny) + ')</h3><div class="kom-probki"><button type="button" class="kom-probka auto' + (!obcy ? ' wl' : '') + '" data-u="' + esc(aktywny) + '" data-c="" title="Kolor automatyczny"></button>';
		SZARE.forEach(function (c) { h += '<button type="button" class="kom-probka' + (obcy === c ? ' wl' : '') + '" data-u="' + esc(aktywny) + '" data-c="' + c + '" style="background:' + c + '"></button>'; });
		h += '<span class="kom-probka wlasny' + (obcy && SZARE.indexOf(obcy) < 0 ? ' wl' : '') + '" title="Własny kolor"><i class="bi bi-eyedropper"></i><input type="color" data-wlasne="' + esc(aktywny) + '" value="' + (obcy || '#e4e6eb') + '"></span></div>';
		h += '<button type="button" class="btn btn-sm btn-outline-secondary kom-motyw-reset" id="kom-motyw-reset"><i class="bi bi-arrow-counterclockwise"></i> Przywróć domyślne</button>';
		okno.innerHTML = h;
	}

	function opisKoloru(u) { return u === login ? 'zmienił(a) kolor swoich wiadomości' : 'zmienił(a) kolor wiadomości użytkownika ' + u; }

	var oknoMotywu = $('kom-motyw-okno');
	$('kom-motyw').addEventListener('click', function (e)
	{
		e.stopPropagation();
		rysujMotyw();
		oknoMotywu.classList.toggle('d-none');
	});
	document.addEventListener('click', function (e) { if (!oknoMotywu.contains(e.target)) { oknoMotywu.classList.add('d-none'); } });
	oknoMotywu.addEventListener('click', function (e)
	{
		e.stopPropagation();
		if (e.target.id === 'kom-motyw-reset' || e.target.closest('#kom-motyw-reset')) { motyw = DOMYSLNY(); zmienMotyw('przywrócił(a) domyślny motyw'); return; }
		var b = e.target.closest('button.kom-probka');
		if (!b) { return; }
		if (b.hasAttribute('data-tlo')) {
			motyw.tlo = parseInt(b.getAttribute('data-tlo'), 10);
			motyw.tw = '';
			zmienMotyw('zmienił(a) tło na „' + TLA[motyw.tlo].n + '”');
		} else if (b.hasAttribute('data-u')) {
			var u = b.getAttribute('data-u'), c = b.getAttribute('data-c');
			if (c) { motyw.k[u] = c; } else { delete motyw.k[u]; }
			zmienMotyw(opisKoloru(u));
		}
	});
	oknoMotywu.addEventListener('input', function (e)
	{
		var w = e.target.getAttribute('data-wlasne');
		if (!w) { return; }
		if (w === 'tw') { motyw.tw = e.target.value; } else { motyw.k[w] = e.target.value; }
		zastosujMotyw();
	});
	oknoMotywu.addEventListener('change', function (e)
	{
		var w = e.target.getAttribute('data-wlasne');
		if (!w) { return; }
		if (w === 'tw') { motyw.tw = e.target.value; zmienMotyw('zmienił(a) tło na własny kolor'); }
		else { motyw.k[w] = e.target.value; zmienMotyw(opisKoloru(w)); }
	});
	zastosujMotyw();

	var SR = window.SpeechRecognition || window.webkitSpeechRecognition;
	if (SR) {
		var mik = $('kom-mikrofon');
		var rozpoznawanie = null;
		var baza = '';
		mik.style.display = '';
		mik.addEventListener('click', function ()
		{
			if (rozpoznawanie) { rozpoznawanie.stop(); return; }
			rozpoznawanie = new SR();
			rozpoznawanie.lang = 'pl-PL';
			rozpoznawanie.interimResults = true;
			rozpoznawanie.continuous = false;
			baza = pole.value ? pole.value.replace(/\s*$/, ' ') : '';
			rozpoznawanie.onresult = function (e)
			{
				var t = '';
				for (var i = 0; i < e.results.length; i++) { t += e.results[i][0].transcript; }
				pole.value = baza + t;
			};
			rozpoznawanie.onend = function () { mik.classList.remove('nagrywa'); rozpoznawanie = null; pole.focus(); };
			rozpoznawanie.onerror = function () { if (rozpoznawanie) { rozpoznawanie.stop(); } };
			mik.classList.add('nagrywa');
			rozpoznawanie.start();
		});
	}

	adresat.addEventListener('change', function () { zapamietaj('adresat_' + login, adresat.value); });

	document.addEventListener('visibilitychange', function () { if (!document.hidden) { oznaczWidoczne(); odswiezTytul(); } });
	window.addEventListener('focus', function () { oznaczWidoczne(); });
	window.addEventListener('resize', function () { maSzerzej = window.innerWidth >= 768; });

	stanPolaczenia(false);
	pobierzCzaty().then(function ()
	{
		var zapisany = pamietane('aktywny_' + login);
		var wybor = null;
		if (window.innerWidth >= 768) {
			if (zapisany === '*' || (zapisany && znajdzCzat(zapisany))) { wybor = zapisany; }
			else if (czaty.length) { wybor = czaty[0].user; }
			else { wybor = '*'; }
		}
		if (wybor !== null) { otworz(wybor); }
	});
	setInterval(pobierzCzaty, 30000);
	polacz();
})();
