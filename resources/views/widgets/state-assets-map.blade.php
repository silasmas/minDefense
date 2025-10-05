{{-- resources/views/widgets/state-assets-map.blade.php --}}
<div
    wire:ignore
    x-data="{
        boot(){
            // Attendre Leaflet + conteneur dimensionné
            const wait = (t=0)=>{
                const ok = (typeof window.L !== 'undefined')
                    && document.getElementById('state-assets-leaflet')
                    && document.getElementById('state-assets-leaflet').getBoundingClientRect().height > 0;
                if (ok) return this.init();
                if (t>80) { console.error('Map init timeout'); return; }
                setTimeout(()=>wait(t+1), 50);
            };
            wait();
        },
        init(){
            console.log('Leaflet version:', L.version);
            const map = L.map('state-assets-leaflet').setView([-2.88, 23.65], 5);
            // Si OSM est bloqué, décommente la ligne Carto ci-dessous:
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 20}).addTo(map);
            // L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {maxZoom:20}).addTo(map);

            // Marqueur test
            L.marker([-4.32, 15.31]).addTo(map).bindPopup('Kinshasa').openPopup();

            // Repeindre après rendus Livewire/resize
            setTimeout(()=>map.invalidateSize(), 80);
            window.addEventListener('resize', ()=>map.invalidateSize());
            document.addEventListener('livewire:navigated', ()=>map.invalidateSize());
        }
    }"
    x-init="$nextTick(() => boot())"
    class="w-full"
>
    <div class="rounded-xl border overflow-hidden">
        <div id="state-assets-leaflet" style="height:560px; min-height:560px; width:100%"></div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
@endpush
