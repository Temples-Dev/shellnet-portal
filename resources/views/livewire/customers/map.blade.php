<flux:main class="h-full">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">Customer Map</flux:heading>
            <flux:subheading>{{ $this->totalMapped }} customers with location data</flux:subheading>
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-3 text-xs text-zinc-500">
            <span class="flex items-center gap-1.5"><span class="inline-block size-2.5 rounded-full bg-emerald-500"></span> Active</span>
            <span class="flex items-center gap-1.5"><span class="inline-block size-2.5 rounded-full bg-zinc-400"></span> Inactive</span>
            <span class="flex items-center gap-1.5"><span class="inline-block size-2.5 rounded-full bg-red-500"></span> Banned</span>
            <span class="flex items-center gap-1.5"><span class="inline-block size-2.5 rounded-full bg-amber-500"></span> Suspended</span>
            <span class="flex items-center gap-1.5"><span class="inline-block size-2.5 rounded-full bg-blue-500"></span> Limited</span>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-3 flex flex-wrap gap-3">
        <flux:select wire:model.live="statusFilter" class="w-40">
            <flux:option value="">All Statuses</flux:option>
            @foreach(\App\Models\Customer::STATUSES as $s)
                <flux:option :value="$s">{{ $s }}</flux:option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="serviceFilter" class="w-40">
            <flux:option value="">All Services</flux:option>
            @foreach(\App\Models\Customer::SERVICE_TYPES as $t)
                <flux:option :value="$t">{{ $t }}</flux:option>
            @endforeach
        </flux:select>
        <flux:text class="self-center text-sm text-zinc-400">
            {{ count($this->geoJson['features']) }} shown
        </flux:text>
    </div>

    {{-- Map (wire:ignore keeps MapLibre out of Livewire's DOM morphing) --}}
    <div
        wire:ignore
        x-data="customerMap(@js($this->geoJson))"
        x-on:map-data-updated.window="setData($event.detail.geoJson)"
        class="relative"
    >
        <div id="customer-map" class="h-[68vh] min-h-96 w-full overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700"></div>

        {{-- Loading indicator --}}
        <div
            x-show="!ready"
            class="absolute inset-0 flex items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800"
        >
            <div class="flex items-center gap-2 text-sm text-zinc-400">
                <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                Loading map…
            </div>
        </div>
    </div>
</flux:main>

@script
<script>
Alpine.data('customerMap', (initialData) => ({
    map: null,
    ready: false,

    init() {
        this.map = new maplibregl.Map({
            container: 'customer-map',
            style: 'https://tiles.openfreemap.org/styles/liberty',
            center: [20, 5],
            zoom: 2,
            attributionControl: { compact: true },
        });

        this.map.addControl(new maplibregl.NavigationControl(), 'top-right');
        this.map.addControl(new maplibregl.FullscreenControl(), 'top-right');
        this.map.addControl(
            new maplibregl.GeolocateControl({ positionOptions: { enableHighAccuracy: true }, trackUserLocation: false }),
            'top-right'
        );

        this.map.on('load', () => {
            this.ready = true;
            this._addLayers(initialData);

            if (initialData.features.length > 0) {
                this._fitBounds(initialData);
            }
        });
    },

    _addLayers(data) {
        this.map.addSource('customers', {
            type: 'geojson',
            data,
            cluster: true,
            clusterMaxZoom: 13,
            clusterRadius: 45,
        });

        // Cluster shadow
        this.map.addLayer({
            id: 'cluster-shadow',
            type: 'circle',
            source: 'customers',
            filter: ['has', 'point_count'],
            paint: {
                'circle-color': 'rgba(0,0,0,0.1)',
                'circle-radius': ['step', ['get', 'point_count'], 24, 10, 34, 50, 44],
                'circle-blur': 1,
                'circle-translate': [1, 2],
            },
        });

        // Cluster circles
        this.map.addLayer({
            id: 'clusters',
            type: 'circle',
            source: 'customers',
            filter: ['has', 'point_count'],
            paint: {
                'circle-color': [
                    'step', ['get', 'point_count'],
                    '#3b82f6',  // blue  < 10
                    10,  '#8b5cf6',  // violet 10–49
                    50,  '#ef4444', // red   50+
                ],
                'circle-radius': ['step', ['get', 'point_count'], 20, 10, 28, 50, 38],
                'circle-opacity': 0.9,
            },
        });

        // Cluster count text
        this.map.addLayer({
            id: 'cluster-count',
            type: 'symbol',
            source: 'customers',
            filter: ['has', 'point_count'],
            layout: {
                'text-field': '{point_count_abbreviated}',
                'text-size': 13,
            },
            paint: { 'text-color': '#ffffff' },
        });

        // Individual marker — outer glow
        this.map.addLayer({
            id: 'point-glow',
            type: 'circle',
            source: 'customers',
            filter: ['!', ['has', 'point_count']],
            paint: {
                'circle-color': ['get', 'color'],
                'circle-radius': 14,
                'circle-opacity': 0.2,
            },
        });

        // Individual marker
        this.map.addLayer({
            id: 'unclustered-point',
            type: 'circle',
            source: 'customers',
            filter: ['!', ['has', 'point_count']],
            paint: {
                'circle-color': ['get', 'color'],
                'circle-radius': 7,
                'circle-stroke-width': 2,
                'circle-stroke-color': '#ffffff',
            },
        });

        // Click cluster → zoom in
        this.map.on('click', 'clusters', (e) => {
            const [feature] = this.map.queryRenderedFeatures(e.point, { layers: ['clusters'] });
            const clusterId = feature.properties.cluster_id;
            this.map.getSource('customers').getClusterExpansionZoom(clusterId).then((zoom) => {
                this.map.easeTo({ center: feature.geometry.coordinates, zoom });
            });
        });

        // Click marker → popup
        this.map.on('click', 'unclustered-point', (e) => {
            const coords = e.features[0].geometry.coordinates.slice();
            const p = e.features[0].properties;

            const statusPill = `<span style="background:${p.color}20;color:${p.color};padding:1px 8px;border-radius:9999px;font-size:11px;font-weight:500">${p.status}</span>`;
            const html = `
                <div style="min-width:160px;padding:2px 0">
                    <p style="font-weight:600;font-size:13px;margin:0 0 2px">${p.fullname}</p>
                    <p style="font-size:11px;color:#71717a;margin:0 0 6px">${p.username}${p.service_type ? ' · ' + p.service_type : ''}</p>
                    ${statusPill}
                    <div style="margin-top:8px">
                        <a href="/customers/${p.id}" style="font-size:11px;color:#3b82f6;text-decoration:none">View profile →</a>
                    </div>
                </div>`;

            new maplibregl.Popup({ offset: 12, closeButton: false })
                .setLngLat(coords)
                .setHTML(html)
                .addTo(this.map);
        });

        // Cursor changes
        ['clusters', 'unclustered-point'].forEach(layer => {
            this.map.on('mouseenter', layer, () => { this.map.getCanvas().style.cursor = 'pointer'; });
            this.map.on('mouseleave', layer, () => { this.map.getCanvas().style.cursor = ''; });
        });
    },

    setData(geoJson) {
        const source = this.map?.getSource('customers');
        if (!source) return;
        source.setData(geoJson);
        if (geoJson.features.length > 0) this._fitBounds(geoJson);
    },

    _fitBounds(geoJson) {
        const coords = geoJson.features.map(f => f.geometry.coordinates);
        if (coords.length === 1) {
            this.map.flyTo({ center: coords[0], zoom: 13 });
            return;
        }
        const bounds = coords.reduce(
            (b, c) => b.extend(c),
            new maplibregl.LngLatBounds(coords[0], coords[0])
        );
        this.map.fitBounds(bounds, { padding: 60, maxZoom: 14 });
    },
}));
</script>
@endscript
