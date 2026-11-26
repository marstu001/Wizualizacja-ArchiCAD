import * as THREE from './p_three/three.module.js';
import { OrbitControls } from './p_three/OrbitControls.js';
import { podlogi } from './p_podlogi.js';

var KOLORY = {
	'Farba_-_Biel_tytanowa': 0xf3f3f1,
	'Farba_-_Jasna_szarość': 0xb9bdc3,
	'Farba_-_Beż_piaskowy': 0xd9caa7,
	'Beton_-_02': 0x9a9c9f,
	'Drewno_-_Sosna_sęki_poziomo': 0x9b6b3b,
	'Szkło_-_Czyste_Szybkie': 0x8fc6ea,
	'Metal_-_Aluminium': 0xc3c7cc,
	'Metal_-_Żeliwo': 0x4b4e54,
	'Metal_-_Stal_nierdzewna': 0xcbced2,
	'Plastik_-_Laminat': 0xd8d5ce,
	'Parkiet_-_01': 0x7b5331,
	'Linoleum_-_Niebieskie': 0x5b86c5,
	'Farba_-_Królewski_błękit': 0x2c50a8
};

export function utworz(kontener, plik, wymiary, gotowe, plikUrz, gotoweUrz)
{
	var W = wymiary.szer, H = wymiary.wys, WYS = 189;
	var renderer = new THREE.WebGLRenderer({ antialias: true });
	renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
	renderer.localClippingEnabled = true;
	var canvas = renderer.domElement;
	canvas.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;display:block;';
	kontener.insertBefore(canvas, kontener.firstChild);

	var scena = new THREE.Scene();
	scena.background = new THREE.Color(0xdde5ee);
	scena.add(new THREE.AmbientLight(0xffffff, 1.15));
	var slonce = new THREE.DirectionalLight(0xffffff, 1.2);
	slonce.position.set(-800, 1800, 600);
	scena.add(slonce);

	var kamera = new THREE.PerspectiveCamera(32, 16 / 9, 20, 30000);
	var cel = new THREE.Vector3(W * 0.5, 0, H * 0.5 + 20);
	kamera.position.set(cel.x + 250, 3200, cel.z + 3250);
	var ster = new OrbitControls(kamera, kontener);
	ster.target.copy(cel);
	ster.maxPolarAngle = Math.PI * 0.495;
	ster.minDistance = 150;
	ster.maxDistance = 9000;
	ster.update();

	var ciecie = new THREE.Plane(new THREE.Vector3(0, -1, 0), WYS + 5);
	var grupa = new THREE.Group();
	scena.add(grupa);

	var podloga = new THREE.Mesh(new THREE.PlaneGeometry(W * 1.25, H * 1.15), new THREE.MeshLambertMaterial({ color: 0xaebf9a }));
	podloga.rotation.x = -Math.PI / 2;
	podloga.position.set(W / 2, -0.6, H / 2);
	scena.add(podloga);

	var warstwy = {};
	var podGrupa = new THREE.Group(), podPodpis = '', konstrWl = true;
	scena.add(podGrupa);
	var loader = new THREE.TextureLoader();
	var zmiana = null, rys = false;

	function render()
	{
		rys = false;
		renderer.render(scena, kamera);
		if (zmiana) { zmiana(); }
	}
	function popros() { if (!rys) { rys = true; requestAnimationFrame(render); } }
	ster.addEventListener('change', popros);

	function rozmiar()
	{
		var w = kontener.clientWidth, h = kontener.clientHeight;
		if (!w || !h) { return; }
		renderer.setSize(w, h, false);
		kamera.aspect = w / h;
		kamera.updateProjectionMatrix();
		popros();
	}
	if (window.ResizeObserver) { new ResizeObserver(rozmiar).observe(kontener); }
	window.addEventListener('resize', rozmiar);

	fetch(plik, { cache: 'force-cache' }).then(function (r) { return r.arrayBuffer(); }).then(function (buf)
	{
		var dv = new DataView(buf), jl = dv.getUint32(0, true);
		var meta = JSON.parse(new TextDecoder().decode(new Uint8Array(buf, 4, jl)));
		var baza = 4 + jl;
		meta.forEach(function (m)
		{
			var g = new THREE.BufferGeometry();
			g.setAttribute('position', new THREE.BufferAttribute(new Float32Array(buf, baza + m.vp, m.vn * 3), 3));
			g.setIndex(new THREE.BufferAttribute(new Uint32Array(buf, baza + m.ip, m.in_ * 3), 1));
			g.computeVertexNormals();
			var szklo = m.m.indexOf('Szkło') === 0;
			var mat = new THREE.MeshLambertMaterial({
				color: KOLORY[m.m] !== undefined ? KOLORY[m.m] : 0xcccccc,
				side: THREE.DoubleSide,
				clippingPlanes: [ciecie],
				transparent: szklo,
				opacity: szklo ? 0.4 : 1,
				depthWrite: !szklo
			});
			grupa.add(new THREE.Mesh(g, mat));
		});
		rozmiar();
		popros();
		if (gotowe) { gotowe(); }
	});

	var urzKorzen = new THREE.Group(), urzDane = null, urzBuf = null, urzBaza = 0, urzGeo = {}, urzMat = {}, urzGrupy = {};
	scena.add(urzKorzen);

	function urzMaterial(c, t)
	{
		var k = c + '_' + t;
		if (!urzMat[k]) {
			urzMat[k] = new THREE.MeshLambertMaterial({ color: c, flatShading: true, side: THREE.DoubleSide, transparent: !!t, opacity: t ? 0.4 : 1, depthWrite: !t });
		}
		return urzMat[k];
	}

	function urzGeometrie(czesci)
	{
		return czesci.map(function (p)
		{
			var g = new THREE.BufferGeometry();
			g.setAttribute('position', new THREE.BufferAttribute(new Float32Array(urzBuf, urzBaza + p.vp, p.vn * 3), 3));
			g.setIndex(new THREE.BufferAttribute(p.w === 2 ? new Uint16Array(urzBuf, urzBaza + p.ip, p.n * 3) : new Uint32Array(urzBuf, urzBaza + p.ip, p.n * 3), 1));
			return { g: g, m: urzMaterial(p.c, p.t) };
		});
	}

	function urzRef(nazwa)
	{
		if (!urzGeo[nazwa]) { urzGeo[nazwa] = urzGeometrie(urzDane.ref[nazwa].parts); }
		return urzGeo[nazwa];
	}

	function urzNazwa(ids, pref)
	{
		if (!urzDane) { return null; }
		var n = urzDane.przypisz[ids] || urzDane.domyslne[pref];
		return n && urzDane.ref[n] ? n : null;
	}

	if (plikUrz) {
		fetch(plikUrz, { cache: 'force-cache' }).then(function (r) { return r.arrayBuffer(); }).then(function (buf)
		{
			var dv = new DataView(buf), jl = dv.getUint32(0, true);
			urzDane = JSON.parse(new TextDecoder().decode(new Uint8Array(buf, 4, jl)));
			urzBuf = buf; urzBaza = 4 + jl;
			var st = new THREE.Group();
			urzGeometrie(urzDane.stat).forEach(function (e) { st.add(new THREE.Mesh(e.g, e.m)); });
			urzKorzen.add(st);
			popros();
			if (gotoweUrz) { gotoweUrz(); }
		}).catch(function (x) { console.log('Error!', x); });
	}

	var v = new THREE.Vector3(), v2 = new THREE.Vector3(), prawo = new THREE.Vector3();

	return {
		ustawWarstwy: function (lista)
		{
			lista.forEach(function (w)
			{
				var o = warstwy[w.idw];
				if (!o) {
					var tex = loader.load(w.plik, popros);
					tex.colorSpace = THREE.SRGBColorSpace;
					tex.anisotropy = renderer.capabilities.getMaxAnisotropy();
					var m = new THREE.Mesh(new THREE.PlaneGeometry(W, H), new THREE.MeshBasicMaterial({ map: tex, transparent: true, depthWrite: false, polygonOffset: true, polygonOffsetFactor: -2, polygonOffsetUnits: -2 }));
					m.rotation.x = -Math.PI / 2;
					m.position.set(W / 2, 1.2, H / 2);
					m.renderOrder = 2;
					scena.add(m);
					o = warstwy[w.idw] = m;
				}
				o.visible = !!w.wid;
			});
			popros();
		},
		podlogi: function (lista)
		{
			var pod = JSON.stringify(lista);
			if (pod === podPodpis) { return; }
			podPodpis = pod;
			scena.remove(podGrupa);
			podGrupa = podlogi(lista, -0.2);
			podGrupa.visible = konstrWl;
			scena.add(podGrupa);
			popros();
		},
		wysUrz: function (ids, pref)
		{
			var n = urzNazwa(ids, pref);
			return n ? { h: urzDane.ref[n].h, r: urzDane.ref[n].r } : null;
		},
		urzUstaw: function (lista)
		{
			if (!urzDane) { return; }
			var zyje = {};
			lista.forEach(function (o)
			{
				var n = urzNazwa(o.ids, o.pref);
				if (!n) { return; }
				zyje[o.ids] = true;
				var g = urzGrupy[o.ids];
				if (!g || g.userData.nazwa !== n) {
					if (g) { urzKorzen.remove(g); }
					g = new THREE.Group();
					g.userData.nazwa = n;
					urzRef(n).forEach(function (e) { g.add(new THREE.Mesh(e.g, e.m)); });
					urzKorzen.add(g);
					urzGrupy[o.ids] = g;
				}
				g.position.set(o.x, 0, o.z);
				g.visible = !!o.widoczny;
			});
			Object.keys(urzGrupy).forEach(function (k) { if (!zyje[k]) { urzKorzen.remove(urzGrupy[k]); delete urzGrupy[k]; } });
			popros();
		},
		urzPrzesun: function (ids, x, z)
		{
			var g = urzGrupy[ids];
			if (g) { g.position.set(x, 0, z); popros(); }
		},
		urzPokaz: function (b) { urzKorzen.visible = !!b; popros(); },
		konstrukcja: function (wl) { konstrWl = !!wl; grupa.visible = konstrWl; podGrupa.visible = konstrWl; popros(); },
		wysokosc: function (u) { ciecie.constant = Math.max(0.5, u * (WYS + 5)); popros(); },
		sterowanie: function (b) { ster.enabled = !!b; },
		zmiana: function (f) { zmiana = f; },
		odswiez: popros,
		rzut: function (x, y, z, r)
		{
			v.set(x, y, z).project(kamera);
			if (v.z > 1 || v.z < -1) { return null; }
			prawo.setFromMatrixColumn(kamera.matrixWorld, 0).multiplyScalar(r);
			v2.set(x, y, z).add(prawo).project(kamera);
			var cw = kontener.clientWidth, ch = kontener.clientHeight;
			var wp = Math.abs(v2.x - v.x) * cw / 2 * 1.5;
			if (wp < 12) { wp = 12; }
			var cxp = (v.x * 0.5 + 0.5) * cw, cyp = (-v.y * 0.5 + 0.5) * ch;
			return { l: (cxp - wp / 2) / cw * 100, t: (cyp - wp / 2) / ch * 100, w: wp / cw * 100 };
		},
		naPlan: function (lp, tp, y)
		{
			var nx = lp / 100 * 2 - 1, ny = -(tp / 100 * 2 - 1);
			var a = new THREE.Vector3(nx, ny, -1).unproject(kamera), b = new THREE.Vector3(nx, ny, 1).unproject(kamera);
			var d = b.sub(a);
			if (Math.abs(d.y) < 1e-6) { return null; }
			var t = (y - a.y) / d.y;
			return { x: a.x + d.x * t, z: a.z + d.z * t };
		}
	};
}
