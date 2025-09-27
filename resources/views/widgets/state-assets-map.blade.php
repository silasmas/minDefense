@php
    $points = $points ?? [];
    $center = $center ?? ['lat' => -2.88, 'lng' => 23.65, 'zoom' => 5];
@endphp

<div x-data x-init="
    const ensureLeaflet = () => new Promise((resolve) => {
        if (window.L) return resolve();
        const css = document.createElement('link');
        css.rel = 'stylesheet';
        css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(css);
        const js = document.createElement('script');
        js.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        js.onload = () => resolve();
        document.body.appendChild(js);
    });

    ensureLeaflet().then(() => {
        const pts = @js($points);
        const c   = @js($center);

        const map = L.map($refs.map).setView([c.lat, c.lng], c.zoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const markers = [];
        pts.forEach(p => {
            if (!p.lat || !p.lng) return;
            const m = L.marker([p.lat, p.lng]).addTo(map);
            let html = p.label || 'Bien';
            if (p.url) html += `<br><a href='${p.url}' target='_blank'>Voir</a>`;
            m.bindPopup(html);
            markers.push(m);
        });

        if (markers.length) {
            const group = L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.2));
        }

        setTimeout(() => map.invalidateSize(), 250);
    });
">
    <div x-ref="map" style="width:100%; height:420px; border-radius:12px; overflow:hidden;"></div>
</div>
