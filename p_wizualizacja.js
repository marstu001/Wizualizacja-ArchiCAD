(function ()
{
	'use strict';
	var $ = function (id) { return document.getElementById(id); };
	function esc(t) { var d = document.createElement('div'); d.textContent = t === null || t === undefined ? '' : String(t); return d.innerHTML; }
	function zl(v) { return Number(v).toLocaleString('pl-PL', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' zł'; }

	var D = null;
	var S = { idpod: null, widok: '2d', edycja: false, typ: '', szukaj: '', warstwy: {}, konstrukcja: true, prac: true, etykiety: true, przeciaga: false, karta: null };
	var plansza = $('p-plansza'), lista = $('p-lista'), info = $('p-info');
	var P = null, P2 = null, M = null, Mladuje = false, PREF = {};
	function model() { return S.widok === 'model'; }
	function urzWl() { return S.warstwy.w !== false; }

	function plany() { return D.podklady.filter(function (p) { return p.widok === '2d'; }); }
	function widok3d(idpod) { return D.podklady.filter(function (p) { return p.widok === '3d' && p.plan === idpod; })[0] || null; }
	function podklad(id) { return D.podklady.filter(function (p) { return p.idpod === id; })[0] || null; }

	function ustaw()
	{
		P2 = podklad(S.idpod) || plany()[0] || null;
		if (!P2) { P = null; return; }
		S.idpod = P2.idpod;
		var p3 = widok3d(P2.idpod);
		P = S.widok === '3d' && p3 ? p3 : P2;
		if (P !== p3 && S.widok === '3d') { S.widok = '2d'; }
			if (model()) { P = P2; }
	}

	function rozmiarWidoku(r) { return P.widok === '3d' ? r * 1.2 * Math.hypot(P.m[0], P.m[3]) : r; }

	function poz(x, y, r, dv)
	{
		if (model()) { return { l: 0, t: 0, w: 1 }; }
		if (P.widok === '2d') { return { l: x / P.szer * 100, t: y / P.wys * 100, w: r / P.szer * 100 }; }
		var m = P.m, cx = x + r / 2, cy = y + r / 2;
		var u = m[0] * cx + m[1] * cy + m[2], v = m[3] * cx + m[4] * cy + m[5] - (dv || 0), rr = rozmiarWidoku(r);
		return { l: (u - rr / 2) / P.szer * 100, t: (v - rr / 2) / P.wys * 100, w: rr / P.szer * 100 };
	}

	function zProcentow(l, t, r, dv)
	{
		if (P.widok === '2d') { return { x: l / 100 * P.szer, y: t / 100 * P.wys }; }
		var m = P.m, rr = rozmiarWidoku(r);
		var u = l / 100 * P.szer + rr / 2 - m[2], v = t / 100 * P.wys + rr / 2 - m[5] + (dv || 0);
		var det = m[0] * m[4] - m[1] * m[3];
		var cx = (u * m[4] - m[1] * v) / det, cy = (m[0] * v - m[3] * u) / det;
		return { x: cx - r / 2, y: cy - r / 2 };
	}

	function ulozModel()
	{
		if (!M || !model()) { return; }
		var els = plansza.querySelectorAll('.p-el');
		for (var i = 0; i < els.length; i++) {
			var el = els[i];
			if (drag && drag.ruch && drag.el === el) { continue; }
			var p = M.rzut(parseFloat(el.getAttribute('data-cx')), parseFloat(el.getAttribute('data-wy')), parseFloat(el.getAttribute('data-cy')), parseFloat(el.getAttribute('data-r')));
			if (!p) { el.style.visibility = 'hidden'; continue; }
			el.style.visibility = '';
			el.style.left = p.l + '%'; el.style.top = p.t + '%'; el.style.width = p.w + '%';
		}
	}

	function zModel(el, r)
	{
		var rect = plansza.getBoundingClientRect();
		var wp = parseFloat(el.style.width), hp = wp * rect.width / rect.height;
		var pt = M.naPlan(parseFloat(el.style.left) + wp / 2, parseFloat(el.style.top) + hp / 2, parseFloat(el.getAttribute('data-wy')));
		if (!pt) { return null; }
		return { x: pt.x - r / 2, y: pt.z - r / 2 };
	}

	function mogePrzesuwac(typ, el)
	{
		if (!D.ja) { return false; }
		return true;
	}

	function pasuje(s)
	{
		if (S.typ && String(s.idnazwa) !== S.typ) { return false; }
		if (S.szukaj) {
			var q = S.szukaj.toLowerCase();
			return (s.identyfikator + ' ' + s.nazwa + ' ' + (s.pomieszczenie || '')).toLowerCase().indexOf(q) >= 0;
		}
		return true;
	}

	function stanInfo(id) { return D.stany.filter(function (s) { return s.id === id; })[0] || { nazwa: '', ikona: '', ramka: 'brak' }; }

	function rysujElementy()
	{
		Array.prototype.slice.call(plansza.querySelectorAll('.p-el')).forEach(function (e) { e.parentNode.removeChild(e); });
		if (!P) { return; }
		D.srodki.forEach(function (s)
		{
			if (s.idpod !== P2.idpod || s.x === null || s.status !== 'aktywny') { return; }
			var q = P.widok === '3d' && D.model && D.model.q3 ? D.model.q3[s.ids] : null;
			var bak = q && Math.abs(q[0] - s.x) <= 2 && Math.abs(q[1] - s.y) <= 2;
			var dv = bak ? q[2] * 0.257 : 0;
			var p = poz(s.x, s.y, s.rozmiar, dv);
			var el = document.createElement('div');
			el.className = 'p-el p-srodek' + (s.rozmiar <= 40 ? ' p-mala' : '') + (mogePrzesuwac('srodek', s) && S.edycja ? ' mozna' : '') + (pasuje(s) ? '' : ' ukryty');
			el.style.left = p.l + '%'; el.style.top = p.t + '%'; el.style.width = p.w + '%';
			var u = model() && M && urzWl() ? M.wysUrz(s.ids, PREF[s.idnazwa]) : null;
			if (u || bak) { el.className += ' p-3d'; }
			el.setAttribute('data-typ', 'srodek'); el.setAttribute('data-id', s.ids); el.setAttribute('data-dv', dv);
				el.setAttribute('data-cx', s.x + s.rozmiar / 2); el.setAttribute('data-cy', s.y + s.rozmiar / 2); el.setAttribute('data-r', u ? Math.max(u.r, 8) / 1.5 : s.rozmiar); el.setAttribute('data-wy', u ? Math.max(u.h / 2, 6) : 62); el.title = s.identyfikator + ' · ' + s.nazwa_typ;
			el.innerHTML = '<img alt="" src="' + esc(s.plik) + '"><span class="p-et">' + esc(s.identyfikator) + '</span>';
			plansza.appendChild(el);
		});
		if (S.prac) {
			D.pracownicy.forEach(function (w)
			{
				if (w.idpod !== P2.idpod || w.x === null || w.stan === 0) { return; }
				var st = stanInfo(w.stan);
				var p = poz(w.x, w.y, 60);
				var el = document.createElement('div');
				el.className = 'p-el p-prac stan-' + w.stan + ' ramka-' + st.ramka + (mogePrzesuwac('pracownik', w) && S.edycja ? ' mozna' : '');
				el.style.left = p.l + '%'; el.style.top = p.t + '%'; el.style.width = p.w + '%';
				el.setAttribute('data-typ', 'pracownik'); el.setAttribute('data-id', w.idp);
					el.setAttribute('data-cx', w.x + 30); el.setAttribute('data-cy', w.y + 30); el.setAttribute('data-r', 60); el.setAttribute('data-wy', 52);
				el.innerHTML = '<img class="p-foto" alt="" src="' + esc(w.foto) + '">' + (st.ikona ? '<span class="p-odznaka">' + st.ikona + '</span>' : '') + '<span class="p-et">' + esc(w.nazwisko) + '</span>';
				plansza.appendChild(el);
			});
		}
		ulozModel();
	}

	function wl(w)
	{
		if (S.warstwy[w.idw] !== undefined) { return S.warstwy[w.idw]; }
		return true;
	}

	function ladujModel()
	{
		if (Mladuje) { return; }
		Mladuje = true;
		var kont = $('p-model');
		kont.textContent = 'Ładowanie modelu 3D…';
		import('./' + D.model.skrypt).then(function (mod)
		{
			kont.textContent = '';
			M = mod.utworz(kont, D.model.plik, { szer: P2.szer, wys: P2.wys }, function () { M.zmiana(ulozModel); rysuj(true); }, D.model.urz, function () { rysuj(true); });
			M.zmiana(ulozModel);
			rysuj(true);
		}).catch(function (x) { console.log('Error!', x); kont.textContent = 'Nie udało się wczytać modelu 3D.'; Mladuje = false; });
	}

	function rysujModel()
	{
		var kont = $('p-model');
		if (!kont) {
			kont = document.createElement('div'); kont.id = 'p-model'; kont.className = 'p-model';
			plansza.insertBefore(kont, plansza.firstChild);
		}
		kont.style.display = '';
		plansza.style.aspectRatio = '16 / 9';
		var tlo = plansza.querySelector('img.p-tlo');
		if (tlo) { tlo.style.display = 'none'; }
		Array.prototype.slice.call(plansza.querySelectorAll('img.p-warstwa')).forEach(function (e) { e.parentNode.removeChild(e); });
		if (!M) { ladujModel(); return; }
		M.konstrukcja(S.warstwy.k !== false);
		M.ustawWarstwy([]);
		M.podlogi(D.pomieszczenia.filter(function (p) { return p.idpod === P2.idpod; }).map(function (p) { return { nazwa: p.nazwa, x1: p.x1, y1: p.y1, x2: p.x2, y2: p.y2 }; }));
		M.urzPokaz(urzWl());
		M.urzUstaw(D.srodki.filter(function (s) { return s.idpod === P2.idpod && s.x !== null && s.status === 'aktywny'; }).map(function (s)
		{
			return { ids: s.ids, pref: PREF[s.idnazwa], x: s.x + s.rozmiar / 2, z: s.y + s.rozmiar / 2, widoczny: pasuje(s) };
		}));
		M.odswiez();
	}

	function rysujTlo()
	{
		var kont = $('p-model');
		if (model()) { rysujModel(); return; }
		if (kont) { kont.style.display = 'none'; }
		var tlo = plansza.querySelector('img.p-tlo');
		if (tlo) { tlo.style.display = ''; }
		if (!tlo) { tlo = document.createElement('img'); tlo.className = 'p-tlo'; tlo.alt = ''; tlo.draggable = false; plansza.insertBefore(tlo, plansza.firstChild); }
		if (!P) { return; }
		tlo.style.visibility = S.warstwy.k === false ? 'hidden' : 'visible';
		if (tlo.getAttribute('data-src') !== P.plik) { tlo.src = P.plik; tlo.setAttribute('data-src', P.plik); }
		plansza.style.aspectRatio = P.szer + ' / ' + P.wys;
		Array.prototype.slice.call(plansza.querySelectorAll('img.p-warstwa')).forEach(function (e) { e.parentNode.removeChild(e); });
		if (P.widok !== '2d') { return; }
		P.warstwy.forEach(function (w)
		{
			if (!wl(w)) { return; }
			var im = document.createElement('img'); im.className = 'p-warstwa'; im.alt = ''; im.src = w.plik; im.draggable = false;
			plansza.insertBefore(im, plansza.querySelector('.p-el'));
		});
	}

	function rysujListe()
	{
		var h = '';
		var plan = D.srodki.filter(function (s) { return s.status === 'aktywny' && pasuje(s); });
		plan.forEach(function (s)
		{
			var poza = s.x === null || s.idpod === null;
			h += '<div class="p-poz' + (poza ? ' poza' : '') + '" data-id="' + s.ids + '"><img alt="" src="' + esc(s.plik) + '"><div class="p-poz-t"><b>' + esc(s.identyfikator) + ' · ' + esc(s.nazwa_typ) + '</b><small>' + (poza ? 'poza planem' : esc(s.pomieszczenie || 'bez pomieszczenia')) + '</small></div></div>';
		});
		if (!plan.length) { h = '<div class="text-muted small p-2">Brak elementów.</div>'; }
		lista.innerHTML = h;
		$('p-licznik').textContent = plan.length;
	}

	function rysujPrzyciski()
	{
		var h = '';
		plany().forEach(function (p) { h += '<button type="button" class="btn btn-sm ' + (p.idpod === P2.idpod ? 'btn-primary' : 'btn-outline-primary') + '" data-pod="' + p.idpod + '">' + esc(p.nazwa) + '</button>'; });
		$('p-pietra').innerHTML = h;
		var p3 = widok3d(P2.idpod);
		$('p-widok').classList.remove('d-none');
		$('p-w3d').classList.toggle('d-none', !p3);
		$('p-wm').classList.toggle('d-none', !D.model);
		$('p-model-opcje').classList.toggle('d-none', !model());
		$('p-w2d').className = 'btn btn-sm ' + (S.widok === '2d' ? 'btn-secondary' : 'btn-outline-secondary');
		$('p-w3d').className = 'btn btn-sm ' + (S.widok === '3d' ? 'btn-secondary' : 'btn-outline-secondary');
		$('p-wm').className = 'btn btn-sm ' + (model() ? 'btn-secondary' : 'btn-outline-secondary');
		var wh = '<label class="form-check form-check-inline m-0"><input class="form-check-input" type="checkbox" data-war="k"' + (S.warstwy.k === false ? '' : ' checked') + '> <span class="form-check-label">Konstrukcja</span></label>';
		if (model()) { wh += '<label class="form-check form-check-inline m-0"><input class="form-check-input" type="checkbox" data-war="w"' + (urzWl() ? ' checked' : '') + '> <span class="form-check-label">Wyposażenie 3D</span></label>'; }
		if (S.widok === '2d') { P.warstwy.forEach(function (w) { if (w.nazwa === 'Umeblowanie') { return; } wh += '<label class="form-check form-check-inline m-0"><input class="form-check-input" type="checkbox" data-war="' + w.idw + '"' + (wl(w) ? ' checked' : '') + '> <span class="form-check-label">' + esc(w.nazwa) + '</span></label>'; }); }
		$('p-warstwy').innerHTML = wh;
	}

	function rysuj(lekko)
	{
		ustaw();
		if (!P) { plansza.innerHTML = '<div class="p-2 text-muted">Brak podkładu. Administrator może go dodać w zakładce Podkłady.</div>'; return; }
		rysujTlo();
		rysujElementy();
		rysujListe();
		if (!lekko) { rysujPrzyciski(); }
		plansza.classList.toggle('etykiety-wyl', !S.etykiety);
		document.getElementById('p-wrap').classList.toggle('edycja', S.edycja);
		if (S.karta) { pokazKarte(S.karta.typ, S.karta.id, true); }
	}

	function dane(typ, id)
	{
		return typ === 'srodek' ? D.srodki.filter(function (s) { return s.ids === id; })[0] : D.pracownicy.filter(function (w) { return w.idp === id; })[0];
	}

	function pokazKarte(typ, id, odswiez)
	{
		var o = dane(typ, id);
		if (!o) { info.classList.add('d-none'); S.karta = null; return; }
		S.karta = { typ: typ, id: id };
		var h = '<button type="button" class="btn-close p-zamknij" aria-label="Zamknij"></button>';
		if (typ === 'srodek') {
			h += '<h3>' + esc(o.identyfikator) + ' · ' + esc(o.nazwa) + '</h3><dl>' +
				'<dt>Typ</dt><dd>' + esc(o.typ || '') + '</dd><dt>Pomieszczenie</dt><dd>' + esc(o.pomieszczenie || '—') + '</dd>' +
				'<dt>Data rejestracji</dt><dd>' + esc((o.data || '').substr(0, 10)) + '</dd><dt>Wartość początkowa</dt><dd>' + zl(o.koszt) + '</dd>' +
				'<dt>Wartość bieżąca</dt><dd>' + zl(o.wartosc) + '</dd><dt>Położenie</dt><dd>' + (o.x === null ? 'poza planem' : 'X ' + o.x + ', Y ' + o.y) + '</dd>' +
				(o.uwagi ? '<dt>Uwagi</dt><dd>' + esc(o.uwagi) + '</dd>' : '') + '</dl>';
			if (D.ja) {
				h += '<div class="d-flex flex-wrap gap-2"><a class="btn btn-sm btn-primary" href="p_srodek.php?ids=' + o.ids + '"><i class="bi bi-pencil"></i> Edytuj</a>';
				if (o.x !== null) { h += '<button type="button" class="btn btn-sm btn-outline-secondary" id="p-zdejmij"><i class="bi bi-box-arrow-down"></i> Zdejmij z planu</button>'; }
				h += '</div>';
			}
		} else {
			var st = stanInfo(o.stan);
			h += '<img class="p-zdjecie" alt="" src="' + esc(o.foto) + '"><h3>' + esc(o.nazwisko) + '</h3><dl><dt>Stan</dt><dd>' + (st.ikona ? st.ikona + ' ' : '') + esc(o.stan_nazwa) + '</dd></dl>';
			if (D.ja) {
				h += '<div class="input-group input-group-sm mb-2"><select class="form-select" id="p-stan-sel">';
				D.stany.forEach(function (s) { h += '<option value="' + s.id + '"' + (s.id === o.stan ? ' selected' : '') + '>' + esc(s.nazwa) + '</option>'; });
				h += '</select><button class="btn btn-primary" type="button" id="p-stan-zapisz">Zapisz stan</button></div>';
				if (mogePrzesuwac('pracownik', o) && o.x !== null) { h += '<button type="button" class="btn btn-sm btn-outline-secondary" id="p-zdejmij"><i class="bi bi-box-arrow-down"></i> Zdejmij z planu</button>'; }
			}
		}
		info.innerHTML = h;
		info.classList.remove('d-none');
		if (!odswiez) { info.scrollIntoView && info.getBoundingClientRect().top < 0 && info.scrollIntoView({ block: 'nearest' }); }
	}

	info.addEventListener('click', function (e)
	{
		if (e.target.closest('.p-zamknij')) { info.classList.add('d-none'); S.karta = null; return; }
		if (!S.karta) { return; }
		if (e.target.closest('#p-zdejmij')) { zapisz(S.karta.typ, S.karta.id, 0, 0, true); return; }
		if (e.target.closest('#p-stan-zapisz')) { zmienStan(S.karta.id, parseInt($('p-stan-sel').value, 10)); }
	});

	function wyslij(url, pola)
	{
		var fd = new FormData();
		fd.append('csrf', D.csrf);
		Object.keys(pola).forEach(function (k) { fd.append(k, pola[k]); });
		return fetch(url, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); });
	}

	function zmienStan(idp, stan)
	{
		wyslij('p_stan_zapisz.php', { idp: idp, stan: stan }).then(function (d)
		{
			if (!d.ok) { komunikat(d.blad || 'Nie udało się zmienić stanu.'); return; }
			var w = dane('pracownik', idp); if (w) { w.stan = d.stan; w.stan_nazwa = d.stan_nazwa; }
			rysuj(true);
		}).catch(function () { komunikat('Nie udało się zmienić stanu.'); });
	}

	function zapisz(typ, id, x, y, zdejmij)
	{
		wyslij('p_pozycja.php', { typ: typ, id: id, x: x, y: y, idpod: P2.idpod, zdejmij: zdejmij ? 1 : '' }).then(function (d)
		{
			if (!d.ok) { komunikat(d.blad || 'Nie udało się zapisać położenia.'); rysuj(true); return; }
			var o = dane(typ, id);
			if (o) {
				if (zdejmij) { o.x = null; o.y = null; o.idpod = null; if (typ === 'srodek') { o.pomieszczenie = ''; o.idpom = null; } }
				else { o.x = d.x; o.y = d.y; o.idpod = P2.idpod; if (typ === 'srodek') { o.pomieszczenie = d.pomieszczenie || ''; } }
			}
			rysuj(true);
		}).catch(function () { komunikat('Nie udało się zapisać położenia.'); rysuj(true); });
	}

	function komunikat(t)
	{
		var b = $('p-komunikat');
		b.textContent = t; b.classList.remove('d-none');
		clearTimeout(komunikat.t);
		komunikat.t = setTimeout(function () { b.classList.add('d-none'); }, 4000);
	}

	var drag = null;
	plansza.addEventListener('pointerdown', function (e)
	{
		var el = e.target.closest('.p-el');
		if (!el || e.button > 0) { return; }
		var typ = el.getAttribute('data-typ'), id = parseInt(el.getAttribute('data-id'), 10), o = dane(typ, id);
		var mozna = S.edycja && o && mogePrzesuwac(typ, o);
		var rect = plansza.getBoundingClientRect();
		drag = { el: el, typ: typ, id: id, o: o, mozna: mozna, sx: e.clientX, sy: e.clientY, l: parseFloat(el.style.left), t: parseFloat(el.style.top), rw: rect.width, rh: rect.height, ruch: false };
		if (mozna) { if (M) { M.sterowanie(false); } el.setPointerCapture(e.pointerId); e.preventDefault(); }
	});
	plansza.addEventListener('pointermove', function (e)
	{
		if (!drag || !drag.mozna) { return; }
		var dx = e.clientX - drag.sx, dy = e.clientY - drag.sy;
		if (!drag.ruch && Math.hypot(dx, dy) < 4) { return; }
		drag.ruch = true; S.przeciaga = true;
		drag.el.classList.add('przeciaga');
		drag.el.style.left = (drag.l + dx / drag.rw * 100) + '%';
		drag.el.style.top = (drag.t + dy / drag.rh * 100) + '%';
		if (M && model() && drag.typ === 'srodek' && drag.el.classList.contains('p-3d')) {
			var k = zModel(drag.el, drag.o.rozmiar);
			if (k) { M.urzPrzesun(drag.id, k.x + drag.o.rozmiar / 2, k.y + drag.o.rozmiar / 2); }
		}
	});
	function koniecPrzeciagania(e)
	{
		if (!drag) { return; }
		var d = drag; drag = null; S.przeciaga = false;
		if (M) { M.sterowanie(true); }
		d.el.classList.remove('przeciaga');
		if (d.mozna && d.ruch) {
			var r = d.typ === 'srodek' ? d.o.rozmiar : 60;
			var k = model() ? zModel(d.el, r) : zProcentow(parseFloat(d.el.style.left), parseFloat(d.el.style.top), r, parseFloat(d.el.getAttribute('data-dv')) || 0);
			if (!k) { rysuj(true); return; }
			zapisz(d.typ, d.id, k.x, k.y, false);
		} else if (e.type === 'pointerup' && !d.ruch) {
			pokazKarte(d.typ, d.id);
		}
	}
	plansza.addEventListener('pointerup', koniecPrzeciagania);
	plansza.addEventListener('pointercancel', koniecPrzeciagania);

	var duch = null, zListy = null;
	lista.addEventListener('pointerdown', function (e)
	{
		var w = e.target.closest('.p-poz');
		if (!w) { return; }
		var id = parseInt(w.getAttribute('data-id'), 10);
		zListy = { id: id, sx: e.clientX, sy: e.clientY, ruch: false, el: w };
	});
	document.addEventListener('pointermove', function (e)
	{
		if (!zListy) { return; }
		if (!zListy.ruch && Math.hypot(e.clientX - zListy.sx, e.clientY - zListy.sy) < 6) { return; }
		if (!S.edycja || !D.ja) { return; }
		zListy.ruch = true; S.przeciaga = true;
		if (!duch) {
			var o = dane('srodek', zListy.id);
			duch = document.createElement('img'); duch.className = 'p-duch'; duch.src = o.plik; duch.alt = '';
			document.body.appendChild(duch);
		}
		duch.style.left = e.clientX + 'px'; duch.style.top = e.clientY + 'px';
		e.preventDefault();
	});
	document.addEventListener('pointerup', function (e)
	{
		if (!zListy) { return; }
		var z = zListy; zListy = null;
		if (duch) { duch.parentNode.removeChild(duch); duch = null; }
		S.przeciaga = false;
		if (!z.ruch) { pokazKarteZListy(z.id); return; }
		var rect = plansza.getBoundingClientRect();
		if (e.clientX < rect.left || e.clientX > rect.right || e.clientY < rect.top || e.clientY > rect.bottom) { rysuj(true); return; }
		var o = dane('srodek', z.id);
		var lp = (e.clientX - rect.left) / rect.width * 100, tp = (e.clientY - rect.top) / rect.height * 100;
		var k;
		if (model()) {
			var pt = M.naPlan(lp, tp, 62);
			if (!pt) { rysuj(true); return; }
			k = { x: pt.x - o.rozmiar / 2, y: pt.z - o.rozmiar / 2 };
		} else {
			var wr = rozmiarWidoku(o.rozmiar);
			k = zProcentow(lp - wr / P.szer * 50, tp - wr / P.szer * 50 * P.szer / P.wys, o.rozmiar);
		}
		zapisz('srodek', z.id, k.x, k.y, false);
	});
	document.addEventListener('pointerup', function () { if (!zListy && !drag) { S.przeciaga = false; } }, true);

	function pokazKarteZListy(id)
	{
		pokazKarte('srodek', id);
		var el = plansza.querySelector('.p-el[data-typ="srodek"][data-id="' + id + '"]');
		if (el) {
			el.classList.add('wyroznij');
			setTimeout(function () { el.classList.remove('wyroznij'); }, 3200);
		}
	}

	$('p-pietra').addEventListener('click', function (e)
	{
		var b = e.target.closest('[data-pod]');
		if (!b) { return; }
		S.idpod = parseInt(b.getAttribute('data-pod'), 10); S.widok = '2d'; info.classList.add('d-none'); S.karta = null;
		rysuj();
	});
	$('p-w2d').addEventListener('click', function () { S.widok = '2d'; rysuj(); });
	$('p-w3d').addEventListener('click', function () { S.widok = '3d'; rysuj(); });
	$('p-wm').addEventListener('click', function () { S.widok = 'model'; rysuj(); });
	$('p-wys').addEventListener('input', function () { if (M) { M.wysokosc(parseInt(this.value, 10) / 100); } });
	$('p-warstwy').addEventListener('change', function (e)
	{
		var id = e.target.getAttribute('data-war');
		if (id === null) { return; }
		S.warstwy[id] = e.target.checked;
		rysuj(true);
	});
	$('p-typ').addEventListener('change', function () { S.typ = this.value; rysuj(true); });
	$('p-szukaj').addEventListener('input', function () { S.szukaj = this.value.trim(); rysuj(true); });
	$('p-pokaz-prac').addEventListener('change', function () { S.prac = this.checked; rysuj(true); });
	$('p-pokaz-et').addEventListener('change', function () { S.etykiety = this.checked; rysuj(true); });
	var tryb = $('p-edycja');
	if (tryb) { tryb.addEventListener('change', function () { S.edycja = this.checked; rysuj(true); }); }

	function start()
	{
		var typy = '<option value="">Wszystkie typy</option>';
		D.nazwy.forEach(function (n) { typy += '<option value="' + n.idnazwa + '">' + esc(n.nazwa) + '</option>'; PREF[n.idnazwa] = n.prefiks; });
		$('p-typ').innerHTML = typy;
		var leg = '';
		D.stany.forEach(function (s) { if (s.id === 0) { return; } leg += '<span><i class="p-kropka k-' + (s.ramka === 'zielona' ? 'zielona' : s.ramka) + '"></i>' + (s.ikona ? s.ikona + ' ' : '') + esc(s.nazwa) + '</span>'; });
		$('p-legenda').innerHTML = leg;
		var q = new URLSearchParams(location.search);
		var pids = parseInt(q.get('ids') || '0', 10), pidp = parseInt(q.get('idp') || '0', 10);
		if (pids) { var s = dane('srodek', pids); if (s && s.idpod) { S.idpod = s.idpod; } }
		rysuj();
		if (pids) { pokazKarteZListy(pids); }
		else if (pidp) { pokazKarte('pracownik', pidp); }
		if (window.EventSource) {
			var es = new EventSource('p_stream.php');
			es.addEventListener('zywe', function (e)
			{
				var z = JSON.parse(e.data);
				D.srodki = z.srodki; D.pracownicy = z.pracownicy;
				if (!S.przeciaga) { rysuj(true); }
			});
		}
	}

	fetch('p_dane.php', { credentials: 'same-origin', cache: 'no-store' })
		.then(function (r) { return r.json(); })
		.then(function (d) { D = d; start(); })
		.catch(function (x) { console.log('Error!', x); plansza.innerHTML = '<div class="p-2 text-danger">Nie udało się wczytać danych.</div>'; });
})();
