{{-- resources/views/assets/map.blade.php --}}
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Carte des biens | Anciens combattants</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- Leaflet CSS --}}
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  {{-- Cluster CSS (optionnel) --}}
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

  <style>
    html, body { height: 100%; margin:0; }
    #map { height: calc(100vh - 80px); }
    .topbar {
      display:flex; gap:8px; align-items:center; padding:10px; background:#f7f7f9; border-bottom:1px solid #e6e6ee;
    }
    .legend {
      background:white; padding:8px 10px; border-radius:6px; box-shadow:0 1px 6px rgba(0,0,0,0.15);
      line-height:1.3; font-size:13px;
    }
    .legend .item { display:flex; align-items:center; gap:6px; margin:4px 0; }
    .legend .swatch { width:14px; height:14px; border-radius:3px; display:inline-block; }
    .swatch-immobilier { background:#8b5cf6; } /* violet polygon */
    .swatch-materiel   { background:#10b981; } /* vert points */
    .search-input {
      flex:1; max-width:420px; padding:8px 10px; border:1px solid #d0d4dc; border-radius:8px; outline:none;
    }
    .btn { padding:8px 12px; border:none; background:#2563eb; color:white; border-radius:8px; cursor:pointer; }
    .btn:disabled { opacity:.5; cursor:not-allowed; }
  </style>
</head>
<body>

  <div class="topbar">
    <input id="matricule" class="search-input" type="text" placeholder="Rechercher par matricule…"
           value="{{ $prefillMatricule }}">
    <button id="btnSearch" class="btn">Rechercher</button>
    <button id="btnReset" class="btn" style="background:#64748b">Réinitialiser</button>
  </div>

  <div id="map"></div>

  {{-- Leaflet JS --}}
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  {{-- Cluster JS (optionnel) --}}
  <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

  <script>
    // === 1) Carte de base ===
    const map = L.map('map', { zoomSnap: 0.5 }).setView([-2.88, 23.65], 5); // centre RDC

    // Fond de carte (ESRI World Topo comme sur ton exemple)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 18, attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    // === 2) Groupes et styles ===
    const clusterGroup = L.markerClusterGroup(); // pour les points "matériel"
    const polygonGroup = L.featureGroup();       // pour les polygones "immobilier"
    map.addLayer(clusterGroup);
    map.addLayer(polygonGroup);

    // Icône personnalisée pour "matériel"
    const materielIcon = L.icon({
      iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
      iconSize: [25,41], iconAnchor: [12,41], popupAnchor: [1,-34],
      shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
      shadowSize:[41,41]
    });

    // Style polygone "immobilier"
    function styleImmobilier() {
      return { color:'#7c3aed', weight:2, fillColor:'#8b5cf6', fillOpacity:0.25 };
    }

    // === 3) Chargement des données ===
    async function loadData({ matricule = '', type = [] } = {}) {
      const url = new URL(`{{ route('api.assets') }}`, window.location.origin);
      if (matricule) url.searchParams.set('matricule', matricule);
      type.forEach(t => url.searchParams.append('type', t));

      const res = await fetch(url);
      const geo = await res.json();
      renderGeoJSON(geo);
    }

    // === 4) Rendu sur la carte ===
    function renderGeoJSON(geojson) {
      clusterGroup.clearLayers();
      polygonGroup.clearLayers();

      const bounds = L.latLngBounds();

      (geojson.features || []).forEach(f => {
        const p = f.properties || {};
        if (!f.geometry) return;

        // IMMOBILIER -> polygon/multipolygon
        if (p.type === 'immobilier' && (f.geometry.type === 'Polygon' || f.geometry.type === 'MultiPolygon')) {
          const layer = L.geoJSON(f, { style: styleImmobilier(), onEachFeature: onEachFeature });
          polygonGroup.addLayer(layer);
          try { bounds.extend(layer.getBounds()); } catch (e) {}
        } else {
          // MATERIEL -> point
          const [lng, lat] = f.geometry.coordinates || [null, null];
          if (lat && lng) {
            const m = L.marker([lat, lng], { icon: materielIcon }).bindPopup(popupHtml(p));
            clusterGroup.addLayer(m);
            bounds.extend([lat, lng]);
          }
        }
      });

      if (bounds.isValid()) {
        map.fitBounds(bounds.pad(0.15));
      }
    }

    function popupHtml(p) {
      return `
        <div style="min-width:220px">
          <strong>${escapeHtml(p.name || 'Bien')}</strong><br>
          Type: <em>${p.type}</em><br>
          Matricule: <code>${escapeHtml(p.service_number || '-')}</code><br>
          <a href="/veteran-assets/${p.id}" target="_blank">Ouvrir la fiche</a>
        </div>
      `;
    }

    function onEachFeature(feature, layer) {
      const p = feature.properties || {};
      layer.bindPopup(popupHtml(p));
    }

    function escapeHtml(str){ return (''+str)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }

    // === 5) Légende ===
    const legend = L.control({position:'bottomleft'});
    legend.onAdd = function() {
      const div = L.DomUtil.create('div','legend');
      div.innerHTML = `
        <div class="item"><span class="swatch swatch-immobilier"></span> Biens immobiliers (polygones)</div>
        <div class="item"><span class="swatch swatch-materiel"></span> Biens matériels (points)</div>
      `;
      return div;
    };
    legend.addTo(map);

    // === 6) Recherche (bouton + préremplissage) ===
    const input = document.getElementById('matricule');
    const btnSearch = document.getElementById('btnSearch');
    const btnReset  = document.getElementById('btnReset');

    btnSearch.addEventListener('click', () => {
      const m = input.value.trim();
      loadData({ matricule: m });
      // garde le matricule dans l'URL pour partage
      const u = new URL(window.location.href);
      if (m) u.searchParams.set('matricule', m); else u.searchParams.delete('matricule');
      history.replaceState({}, '', u.toString());
    });

    btnReset.addEventListener('click', () => {
      input.value = '';
      loadData({});
      const u = new URL(window.location.href);
      u.searchParams.delete('matricule');
      history.replaceState({}, '', u.toString());
    });

    // Entrée pour lancer la recherche
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') btnSearch.click();
    });

    // Chargement initial (avec éventuel ?matricule=)
    (function init(){
      const m = input.value.trim();
      loadData({ matricule: m });
    })();

  </script>
</body>
</html>
