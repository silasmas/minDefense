<x-filament-widgets::widget>
    <x-filament::section>
        <div x-data="{ ready:false }" x-init="
            const load = () => {
                if (!window.L) {
                    let css = document.createElement('link'); css.rel='stylesheet';
                    css.href='https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'; document.head.appendChild(css);
                    let js = document.createElement('script'); js.src='https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    js.onload = () => { window.dispatchEvent(new Event('leaflet:ready')); };
                    document.head.appendChild(js);
                }
                const init = () => {
                    const map = L.map($refs.map).setView([-4.322447, 15.307045], 5); // centre RDC approximatif
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

                    const data = @json($assets);
                    data.forEach(a => {
                        const color = a.asset_type === 'immobilier' ? 'green' : 'blue';
                        const icon = L.divIcon({
                            className: 'custom-pin',
                            html: `<div style='width:10px;height:10px;border-radius:50%;background:${color};border:2px solid white;box-shadow:0 0 0 1px #1f2937'></div>`
                        });
                        const m = L.marker([a.lat, a.lng], { icon }).addTo(map);
                        const type = a.asset_type === 'immobilier' ? 'Immobilier' : 'Matériel';
                        const stat = {active:'Actif', under_maintenance:'Maintenance', disposed:'Cédé'}[a.status] ?? a.status;
                        m.bindPopup(`<b>${a.title}</b><br/>${type} — ${stat}<br/>${a.province ?? ''} ${a.city ?? ''}`);
                    });

                    setTimeout(() => { map.invalidateSize(); }, 200);
                };
                if (window.L) init(); else window.addEventListener('leaflet:ready', init);
            };
            load(); ready = true;
        ">
            <div x-ref="map" style="height: 480px; border-radius: 12px; overflow: hidden;"></div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
