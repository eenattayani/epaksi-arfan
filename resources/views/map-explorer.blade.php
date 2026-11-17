<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map Explorer — EPAKSI GIS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Pin Google Maps API to version 3.65 because KmlLayer was removed in >=3.66 -->
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $apiKey }}&v=3.65"></script>
    <!-- Library for converting KML to GeoJSON (future migration) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/togeojson/0.16.0/togeojson.min.js"></script>

    <style>
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }

        #topbar {
            position: fixed;
            top: 0; left: 0; right: 0; height: 52px;
            background: #1a3c5e;
            z-index: 1000;
            display: flex;
            align-items: center;
            padding: 0 1rem;
            gap: .75rem;
            box-shadow: 0 2px 8px rgba(0,0,0,.3);
        }

        #topbar .title { color:#fff; font-weight:600; font-size:.95rem; }
        #topbar .sub   { color:#90b4d4; font-size:.75rem; }

        #map {
            position: absolute;
            top: 52px; left: 0; right: 0; bottom: 0;
            width: 100%;
        }

        /* ── Project Panel ─────────────────────────── */
        #projectPanel {
            position: fixed;
            top: 62px; left: 10px;
            width: 300px;
            max-height: calc(100vh - 72px);
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,.15);
            z-index: 500;
            display: flex;
            flex-direction: column;
            transition: transform .3s ease;
        }

        #projectPanel.collapsed { transform: translateX(-310px); }

        .panel-header {
            padding: 12px 14px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .panel-header h6 {
            margin: 0; font-size: .85rem;
            font-weight: 600; color: #1a3c5e;
        }

        .panel-search {
            padding: 10px 12px;
            border-bottom: 1px solid #f0f0f0;
            flex-shrink: 0;
        }

        .panel-body {
            overflow-y: auto;
            flex: 1;
        }

        /* ── Project card in panel ─────────────────── */
        .project-card {
            padding: 10px 14px;
            border-bottom: 1px solid #f8f8f8;
            cursor: pointer;
            transition: background .15s;
        }

        .project-card:hover { background: #f8fafc; }

        .project-card.active-card { background: #eff6ff; }

        .project-card .proj-name {
            font-size: .82rem;
            font-weight: 600;
            color: #1a3c5e;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .project-card .proj-meta {
            font-size: .72rem;
            color: #888;
            margin-top: 2px;
        }

        .layer-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 14px 4px 28px;
            font-size: .75rem;
            color: #555;
            border-bottom: 1px solid #fafafa;
        }

        .layer-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* ── Map controls ──────────────────────────── */
        #mapControls {
            position: fixed;
            top: 62px; right: 10px;
            z-index: 500;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        #mapControls button {
            background: #fff;
            border: none;
            border-radius: 8px;
            width: 36px; height: 36px;
            box-shadow: 0 2px 6px rgba(0,0,0,.15);
            cursor: pointer;
            font-size: .85rem;
            color: #333;
        }

        #mapControls button.active { background:#1a3c5e; color:#fff; }

        /* ── Info panel ────────────────────────────── */
        #infoPanel {
            position: fixed;
            bottom: 24px; right: 10px;
            width: 300px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,.15);
            z-index: 500;
            display: none;
            padding: 14px;
            max-height: 260px;
            overflow-y: auto;
        }

        #statusToast {
            position: fixed;
            bottom: 24px; left: 50%;
            transform: translateX(-50%);
            background: rgba(26,60,94,.9);
            color: #fff;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: .82rem;
            z-index: 999;
            display: none;
            white-space: nowrap;
        }

        #noLayersMsg {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%,-50%);
            background: rgba(255,255,255,.95);
            padding: 2rem 2.5rem;
            border-radius: 16px;
            text-align: center;
            z-index: 500;
            box-shadow: 0 4px 20px rgba(0,0,0,.1);
        }
    </style>
</head>
<body>

{{-- Top Bar --}}
<div id="topbar">
    <a href="{{ route('dashboard') }}"
       style="color:#fff;text-decoration:none;flex-shrink:0">
        <i class="bi bi-arrow-left fs-5"></i>
    </a>
    <div style="width:1px;height:24px;background:rgba(255,255,255,.2)"></div>
    <div>
        <div class="title">Map Explorer</div>
        <div class="sub">All EPAKSI Projects</div>
    </div>
    <div style="flex:1"></div>
    <span class="badge" style="background:rgba(255,255,255,.15);color:#fff;font-size:.75rem">
        <i class="bi bi-folder2 me-1"></i>{{ $projects->count() }} projects
    </span>
    <span class="badge" style="background:rgba(255,255,255,.15);color:#fff;font-size:.75rem">
        <i class="bi bi-layers me-1"></i>{{ count($allLayers) }} layers
    </span>
    <button onclick="fitAllLayers()"
            style="background:rgba(255,255,255,.15);color:#fff;border:none;
                   border-radius:6px;padding:4px 10px;font-size:.8rem;cursor:pointer">
        <i class="bi bi-fullscreen"></i>
    </button>
    <button onclick="togglePanel()"
            style="background:rgba(255,255,255,.15);color:#fff;border:none;
                   border-radius:6px;padding:4px 10px;font-size:.8rem;cursor:pointer">
        <i class="bi bi-layout-sidebar"></i>
    </button>
</div>

{{-- Project Panel --}}
<div id="projectPanel">
    <div class="panel-header">
        <h6><i class="bi bi-folder2-open me-1"></i> Projects</h6>
        <span class="badge bg-light text-dark border" style="font-size:.7rem">
            {{ $projects->count() }}
        </span>
    </div>

    <div class="panel-search">
        <input type="text"
               id="projectSearch"
               class="form-control form-control-sm"
               placeholder="Search projects…"
               oninput="filterProjects(this.value)">
    </div>

    <div class="panel-body" id="projectList">
        @forelse($projects as $project)
        <div class="project-card" id="proj-card-{{ $project->id }}"
             onclick="focusProject({{ $project->id }})">
            <div class="d-flex align-items-start justify-content-between gap-2">
                <div style="min-width:0;flex:1">
                    <div class="proj-name">{{ $project->name }}</div>
                    <div class="proj-meta">
                        <span class="font-monospace">{{ $project->code }}</span>
                        @if($project->district)
                            · {{ $project->district }}
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-1 flex-shrink-0">
                    <a href="{{ route('projects.map', $project) }}"
                       onclick="event.stopPropagation()"
                       class="btn btn-outline-primary"
                       style="padding:2px 7px;font-size:.7rem"
                       title="Open project map">
                        <i class="bi bi-map"></i>
                    </a>
                </div>
            </div>
            {{-- KML layer rows --}}
            @foreach($project->kmlFiles as $file)
            <div class="layer-row mt-1">
                <div class="layer-dot"
                     style="background:{{ $file->layer_color }}"></div>
                <span class="text-truncate">{{ $file->original_name }}</span>
            </div>
            @endforeach
        </div>
        @empty
        <div class="text-center py-4 text-muted small">
            <i class="bi bi-folder-x fs-2 d-block mb-2 opacity-25"></i>
            No projects with KML files found.
        </div>
        @endforelse
    </div>
</div>

{{-- Map Controls --}}
<div id="mapControls">
    <button onclick="setMapType('roadmap')"   id="btn-roadmap"   class="active" title="Road">
        <i class="bi bi-map"></i>
    </button>
    <button onclick="setMapType('satellite')" id="btn-satellite" title="Satellite">
        <i class="bi bi-globe2"></i>
    </button>
    <button onclick="setMapType('hybrid')"    id="btn-hybrid"    title="Hybrid">
        <i class="bi bi-layers"></i>
    </button>
    <button onclick="setMapType('terrain')"   id="btn-terrain"   title="Terrain">
        <i class="bi bi-triangle"></i>
    </button>
</div>

{{-- Info Panel --}}
<div id="infoPanel">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <strong style="font-size:.85rem">Feature Info</strong>
        <button onclick="document.getElementById('infoPanel').style.display='none'"
                class="btn-close btn-sm"></button>
    </div>
    <div id="infoPanelContent" style="font-size:.82rem"></div>
</div>

{{-- No Layers Message --}}
@if(count($allLayers) === 0)
<div id="noLayersMsg">
    <i class="bi bi-map text-muted" style="font-size:3rem"></i>
    <h6 class="mt-3 fw-semibold">No KML Layers Found</h6>
    <p class="text-muted small mb-3">
        Upload KML files to your projects to see them here.
    </p>
    <a href="{{ route('projects.index') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-folder2-open me-1"></i> Go to Projects
    </a>
</div>
@endif

{{-- Status Toast --}}
<div id="statusToast"></div>

{{-- Map Canvas --}}
<div id="map"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
const ALL_LAYERS = @json($allLayers);

let map;
let kmlLayers  = {};
let panelOpen  = true;
let activeProj = null;

document.addEventListener('DOMContentLoaded', function () {
    initMap();
});

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center:            { lat: -2.5, lng: 118 },
        zoom:              5,
        mapTypeId:         'roadmap',
        mapTypeControl:    false,
        streetViewControl: false,
        fullscreenControl: false,
        zoomControl:       true,
        zoomControlOptions: {
            position: google.maps.ControlPosition.RIGHT_BOTTOM
        },
        styles: mapStyles(),
    });

    if (ALL_LAYERS.length === 0) return;

    loadAllLayers();
}


// ── Sanitasi GeoJSON untuk mencegah error koordinat NaN / kosong dari KML ──
function sanitizeGeoJson(geoJson) {
    if (!geoJson || !Array.isArray(geoJson.features)) return geoJson;

    function isValidCoord(c) {
        return Array.isArray(c) && c.length >= 2 &&
               typeof c[0] === 'number' && !isNaN(c[0]) &&
               typeof c[1] === 'number' && !isNaN(c[1]);
    }

    function isValidGeometry(geom) {
        if (!geom || !geom.type || !geom.coordinates) return false;
        const coords = geom.coordinates;
        if (!Array.isArray(coords) || coords.length === 0) return false;

        switch (geom.type) {
            case 'Point':
                return isValidCoord(coords);
            case 'LineString':
            case 'MultiPoint':
                return coords.length > 0 && coords.every(isValidCoord);
            case 'Polygon':
            case 'MultiLineString':
                return coords.length > 0 && coords.every(ring => Array.isArray(ring) && ring.length > 0 && ring.every(isValidCoord));
            case 'MultiPolygon':
                return coords.length > 0 && coords.every(poly => Array.isArray(poly) && poly.length > 0 && poly.every(ring => Array.isArray(ring) && ring.every(isValidCoord)));
            case 'GeometryCollection':
                return Array.isArray(geom.geometries) && geom.geometries.every(isValidGeometry);
            default:
                return true;
        }
    }

    geoJson.features = geoJson.features.filter(f => f && f.geometry && isValidGeometry(f.geometry));
    return geoJson;
}

// ── Kustomisasi Ikon Tematik & Modern untuk Fitur Titik (Point) ──
function getFeatureIcon(feature, layerColor) {
    const geom = feature.getGeometry();
    if (!geom || geom.getType() !== 'Point') return null;

    const name = (feature.getProperty('name') || feature.getProperty('nama') || '').toLowerCase();
    const kAset = (feature.getProperty('k_aset') || '').toLowerCase();
    const stroke = feature.getProperty('stroke') || layerColor;

    // 1. Bendung / Dam / Intake / Bangunan Utama
    if (name.includes('bendung') || name.includes('dam') || name.includes('intake') || kAset.startsWith('b01')) {
        return {
            // Ikon Gerbang Bangunan Air / Bendung (SVG Shield Gate)
            path: "M -7,-16 L 7,-16 L 7,0 L -7,0 Z M -4,-13 L 4,-13 L 4,-3 L -4,-3 Z",
            fillColor: '#dc2626', // Merah tegas
            fillOpacity: 1,
            strokeColor: '#ffffff',
            strokeWeight: 2,
            scale: 1.25,
            anchor: new google.maps.Point(0, -8),
        };
    }

    // 2. Bangunan Bagi / Sadap / Pintu Air
    if (name.includes('bangunan') || name.includes('bagi') || name.includes('sadap') || name.includes('pintu')) {
        return {
            // Ikon Belah Ketupat (Diamond khas Simbol GIS Bangunan Air)
            path: "M 0,-18 L 8,-9 L 0,0 L -8,-9 Z",
            fillColor: '#ea580c', // Oranye hidraulik
            fillOpacity: 1,
            strokeColor: '#ffffff',
            strokeWeight: 2,
            scale: 1.2,
            anchor: new google.maps.Point(0, -9),
        };
    }

    // 3. Titik Patok / Dimensi / Ujung Saluran / Default Point
    return {
        // Pin Peta Teardrop Modern dengan lubang/cincin di tengah
        path: "M 0,0 C -2,-12 -8,-14 -8,-21 A 8,8 0 1,1 8,-21 C 8,-14 2,-12 0,0 Z M 0,-24 A 3,3 0 1,0 0,-18 A 3,3 0 1,0 0,-24 Z",
        fillColor: stroke,
        fillOpacity: 1,
        strokeColor: '#ffffff',
        strokeWeight: 1.5,
        scale: 1.15,
        anchor: new google.maps.Point(0, 0),
    };
}

async function loadSingleLayerGeoJson(layer, onDone) {
    try {
        const response = await fetch(layer.url + '?t=' + Date.now());
        if (!response.ok) throw new Error('Fetch failed: ' + response.statusText);
        const text = await response.text();
        const dom  = new DOMParser().parseFromString(text, 'text/xml');
        const parser = window.toGeoJSON || window.togeojson;
        if (!parser || typeof parser.kml !== 'function') throw new Error('toGeoJSON not ready');
        const geoJson = parser.kml(dom);

        const dataLayer = new google.maps.Data({ map: map });
        const color = layer.color || layer.layer_color || '#1a3c5e';

        dataLayer.setStyle(feature => {
            const stroke = feature.getProperty('stroke') || color;
            const fill   = feature.getProperty('fill') || color;
            const isPoint= feature.getGeometry().getType() === 'Point';

            // Kustomisasi Icon Point tematik modern
            const icon = getFeatureIcon(feature, color);

            return {
                strokeColor:   stroke,
                strokeWeight:  2.5,
                strokeOpacity: 0.9,
                fillColor:     fill,
                fillOpacity:   0.35,
                icon:          icon,
            };
        });

        // Ambil SEMUA properti fitur termasuk ExtendedData
        dataLayer.addListener('click', event => {
            const properties = {};
            event.feature.forEachProperty((val, key) => {
                properties[key] = val;
            });
            showFeatureInfo(properties, event.latLng, layer);
        });

        // Sanitasi GeoJSON agar koordinat kosong/NaN tidak crash
        const cleanGeoJson = sanitizeGeoJson(geoJson);
        dataLayer.addGeoJson(cleanGeoJson);
        kmlLayers[layer.id] = dataLayer;
        if (typeof onDone === 'function') onDone();
    } catch (e) {
        console.warn('Error loading KML for layer ' + layer.id, e);
        if (typeof onDone === 'function') onDone();
    }
}

function loadAllLayers() {
    let pending = ALL_LAYERS.length;
    if (pending === 0) return;

    ALL_LAYERS.forEach(layer => {
        loadSingleLayerGeoJson(layer, () => {
            pending--;
            if (pending <= 0) {
                fitAllLayers();
            }
        });
    });
}

// Focus on one project's layers
function focusProject(projectId) {
    // Highlight card
    document.querySelectorAll('.project-card')
            .forEach(c => c.classList.remove('active-card'));
    const card = document.getElementById('proj-card-' + projectId);
    if (card) card.classList.add('active-card');

    activeProj = projectId;

    // Fit to layers belonging to this project
    const bounds = new google.maps.LatLngBounds();
    let   hasAny = false;

    ALL_LAYERS
        .filter(l => l.project_id === projectId)
        .forEach(l => {
            const layer = kmlLayers[l.id];
            if (layer && layer.getMap()) {
                layer.forEach(feature => {
                    const geometry = feature.getGeometry();
                    if (geometry) {
                        geometry.forEachLatLng(latLng => {
                            bounds.extend(latLng);
                            hasAny = true;
                        });
                    }
                });
            }
        });

    if (hasAny) {
        map.fitBounds(bounds);
        showToast('Focused: ' + ALL_LAYERS.find(l => l.project_id === projectId)?.project_name);
    } else {
        showToast('Layer not loaded yet — try again shortly', true);
    }
}

function fitAllLayers() {
    const bounds = new google.maps.LatLngBounds();
    let   hasAny = false;

    Object.values(kmlLayers).forEach(layer => {
        if (layer && layer.getMap()) {
            layer.forEach(feature => {
                const geometry = feature.getGeometry();
                if (geometry) {
                    geometry.forEachLatLng(latLng => {
                        bounds.extend(latLng);
                        hasAny = true;
                    });
                }
            });
        }
    });

    if (hasAny) map.fitBounds(bounds);
}

function showFeatureInfo(featureData, latLng, layerMeta) {
    const panel   = document.getElementById('infoPanel');
    const content = document.getElementById('infoPanelContent');

    let html = `
        <div class="mb-2 pb-2 border-bottom">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle"
                  style="font-size:.7rem">
                ${layerMeta.project_code}
            </span>
            <div class="fw-semibold small mt-1">${layerMeta.project_name}</div>
        </div>
    `;

    const title = featureData.name || featureData.nama || featureData.nomenklatur || 'Fitur';
    html += `<div class="fw-bold mb-2 pb-1 border-bottom" style="font-size:.85rem; color:#1a3c5e">
                <i class="bi bi-geo-alt-fill me-1 text-primary"></i>${title}
             </div>`;

    // ExtendedData KML
    const excludedKeys = ['name', 'description', 'styleUrl', 'styleHash', 'stroke', 'stroke-width', 'stroke-opacity', 'fill', 'fill-opacity', 'icon'];
    const customAttrs = [];

    Object.keys(featureData).forEach(key => {
        if (!excludedKeys.includes(key)) {
            const val = featureData[key];
            if (val !== null && val !== undefined && String(val).trim() !== '') {
                const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                customAttrs.push({ key: label, val: String(val) });
            }
        }
    });

    if (customAttrs.length > 0) {
        html += '<div class="mb-2 pb-2 border-bottom" style="max-height:120px; overflow-y:auto">';
        customAttrs.forEach(attr => {
            html += `<div class="d-flex justify-content-between gap-2" style="font-size:.75rem; margin-bottom:2px">
                         <span class="text-muted">${attr.key}:</span>
                         <span class="fw-medium text-end">${attr.val}</span>
                     </div>`;
        });
        html += '</div>';
    }

    if (featureData.description) {
        html += `<div class="text-muted mb-2 small" style="max-height:80px; overflow-y:auto">${featureData.description}</div>`;
    }

    if (latLng) {
        html += `<div class="text-muted mb-2" style="font-size:.73rem;font-family:monospace">
                     <i class="bi bi-compass me-1"></i>${latLng.lat().toFixed(6)}, ${latLng.lng().toFixed(6)}
                 </div>`;
    }

    html += `<div class="d-flex gap-1 mt-2">
                 <a href="${layerMeta.project_url}"
                    class="btn btn-outline-primary btn-sm flex-fill"
                    style="font-size:.75rem">
                     <i class="bi bi-folder2 me-1"></i>Detail
                 </a>
                 <a href="${layerMeta.map_url}"
                    class="btn btn-outline-success btn-sm flex-fill"
                    style="font-size:.75rem">
                     <i class="bi bi-map me-1"></i>Full Map
                 </a>
             </div>`;

    content.innerHTML = html;
    panel.style.display = 'block';
}

// Search / filter project list
function filterProjects(query) {
    document.querySelectorAll('.project-card').forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(query.toLowerCase()) ? '' : 'none';
    });
}

function setMapType(type) {
    map.setMapTypeId(type);
    document.querySelectorAll('#mapControls button')
            .forEach(b => b.classList.remove('active'));
    const btn = document.getElementById('btn-' + type);
    if (btn) btn.classList.add('active');
}

function togglePanel() {
    panelOpen = !panelOpen;
    document.getElementById('projectPanel')
            .classList.toggle('collapsed', !panelOpen);
}

function showToast(msg, isError = false) {
    const toast         = document.getElementById('statusToast');
    toast.textContent   = msg;
    toast.style.display = 'block';
    toast.style.background = isError
        ? 'rgba(185,28,28,.9)' : 'rgba(26,60,94,.9)';
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => toast.style.display = 'none', 2500);
}

function mapStyles() {
    return [
        { featureType:'water', elementType:'geometry',
          stylers:[{color:'#a0c8e8'}] },
        { featureType:'landscape', elementType:'geometry',
          stylers:[{color:'#f5f5f0'}] },
        { featureType:'road', elementType:'geometry',
          stylers:[{color:'#ffffff'},{weight:0.8}] },
        { featureType:'poi', stylers:[{visibility:'off'}] },
        { featureType:'administrative', elementType:'geometry.stroke',
          stylers:[{color:'#c9c9c9'}] },
        { elementType:'labels.text.fill', stylers:[{color:'#555'}] },
    ];
}
</script>

</body>
</html>