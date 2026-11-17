<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Peta — {{ $project->name }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Pin Google Maps API to version 3.65 because KmlLayer was removed in >=3.66 -->
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $apiKey }}&v=3.65"></script>
    <!-- Library for converting KML to GeoJSON (future migration) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/togeojson/0.16.0/togeojson.min.js"></script>

    <style>
        * { box-sizing: border-box; }
        html, body { height:100%; margin:0; padding:0; overflow:hidden; }

        /* ── Topbar ───────────────────────────────────── */
        #topbar {
            position:fixed; top:0; left:0; right:0; height:52px;
            background:#1a3c5e; z-index:1000;
            display:flex; align-items:center;
            padding:0 1rem; gap:.75rem;
            box-shadow:0 2px 8px rgba(0,0,0,.3);
        }
        #topbar .project-title {
            color:#fff; font-weight:600; font-size:.95rem;
            white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
            max-width:280px;
        }
        #topbar .project-code { color:#90b4d4; font-size:.75rem; font-family:monospace; }

        /* ── Map ──────────────────────────────────────── */
        #map {
            position:absolute; top:52px; left:0; right:0; bottom:0; width:100%;
            background:#e8e8e8;
        }

        /* ── Layer Panel ──────────────────────────────── */
        #layerPanel {
            position:fixed; top:62px; left:10px;
            width:290px; max-height:calc(100vh - 72px);
            background:#fff; border-radius:12px;
            box-shadow:0 4px 20px rgba(0,0,0,.15);
            z-index:500; display:flex; flex-direction:column;
            transition:transform .3s ease;
        }
        #layerPanel.collapsed { transform:translateX(-300px); }

        .panel-header {
            padding:10px 14px; border-bottom:1px solid #f0f0f0;
            display:flex; align-items:center;
            justify-content:space-between; flex-shrink:0;
        }
        .panel-header h6 { margin:0; font-size:.85rem; font-weight:600; color:#1a3c5e; }

        .panel-body { overflow-y:auto; flex:1; }

        .panel-footer {
            padding:8px; border-top:1px solid #f0f0f0; flex-shrink:0;
        }

        /* ── Layer Items ──────────────────────────────── */
        .layer-item {
            padding:8px 12px;
            border-bottom:1px solid #f5f5f5;
            transition:background .15s;
        }
        .layer-item:hover { background:#f8fafc; }

        .layer-main {
            display:flex; align-items:center; gap:8px; cursor:pointer;
        }

        .layer-color-dot {
            width:10px; height:10px; border-radius:50%;
            flex-shrink:0; border:2px solid rgba(0,0,0,.12);
        }

        .layer-name {
            font-size:.8rem; font-weight:500; flex:1;
            overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
        }

        /* ── Status badge per layer ───────────────────── */
        .layer-status-badge {
            font-size:.65rem; padding:2px 6px;
            border-radius:10px; flex-shrink:0;
        }

        .layer-status-badge.loading {
            background:#fff3cd; color:#856404;
        }
        .layer-status-badge.ok {
            background:#d1e7dd; color:#0a3622;
        }
        .layer-status-badge.error {
            background:#f8d7da; color:#842029;
        }

        /* ── Feature count bar ────────────────────────── */
        .layer-meta {
            display:flex; align-items:center; gap:8px;
            padding:4px 0 0 18px; flex-wrap:wrap;
        }
        .feature-badge {
            font-size:.65rem; padding:2px 6px;
            border-radius:8px; border:1px solid #e5e7eb;
            color:#555; background:#f9fafb;
            display:inline-flex; align-items:center; gap:3px;
        }

        /* ── Sub-layer (KML Folder) ───────────────────── */
        .sublayer-list {
            padding:4px 0 4px 20px;
            display:none;
        }
        .sublayer-list.show { display:block; }

        .sublayer-item {
            display:flex; align-items:center; gap:6px;
            padding:3px 8px; font-size:.75rem;
            color:#555; cursor:default;
            border-radius:4px; transition:background .1s;
        }
        .sublayer-item:hover { background:#f5f5f5; }

        .sublayer-dot {
            width:7px; height:7px; border-radius:50%;
            background:#aaa; flex-shrink:0;
        }

        /* ── Map controls ─────────────────────────────── */
        #mapControls {
            position:fixed; top:62px; right:10px;
            z-index:500; display:flex; flex-direction:column; gap:6px;
        }
        #mapControls button {
            background:#fff; border:none; border-radius:8px;
            width:36px; height:36px;
            box-shadow:0 2px 6px rgba(0,0,0,.15);
            cursor:pointer; font-size:.85rem; color:#333;
            transition:background .15s;
        }
        #mapControls button:hover  { background:#f0f4f8; }
        #mapControls button.active { background:#1a3c5e; color:#fff; }

        /* ── Info Panel ───────────────────────────────── */
        #infoPanel {
            position:fixed; bottom:40px; right:10px;
            width:300px; background:#fff;
            border-radius:12px;
            box-shadow:0 4px 20px rgba(0,0,0,.15);
            z-index:500; display:none;
            max-height:320px; overflow-y:auto;
        }

        #infoPanel .info-header {
            padding:10px 14px; border-bottom:1px solid #f0f0f0;
            display:flex; align-items:center; justify-content:space-between;
            position:sticky; top:0; background:#fff;
        }

        #infoPanel .info-body { padding:10px 14px; }

        .info-attr-row {
            display:flex; gap:8px; margin-bottom:4px; font-size:.8rem;
        }
        .info-attr-key {
            color:#888; flex-shrink:0; width:90px;
            white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
        }
        .info-attr-val { color:#333; flex:1; word-break:break-word; }

        /* ── Status bar (koordinat + skala) ──────────── */
        #statusBar {
            position:fixed; bottom:0; left:0; right:0;
            height:28px; background:rgba(26,60,94,.9);
            z-index:500; display:flex; align-items:center;
            padding:0 12px; gap:16px; font-size:.72rem; color:#90b4d4;
        }
        #statusBar .sb-item { display:flex; align-items:center; gap:4px; }
        #statusBar .sb-sep {
            width:1px; height:14px; background:rgba(255,255,255,.2);
        }
        #coordDisplay { font-family:monospace; color:#b8d4f0; }

        /* ── Toast ────────────────────────────────────── */
        #statusToast {
            position:fixed; bottom:36px; left:50%;
            transform:translateX(-50%);
            background:rgba(26,60,94,.9); color:#fff;
            padding:7px 18px; border-radius:20px;
            font-size:.8rem; z-index:999; display:none; white-space:nowrap;
        }

        /* ── No KML ───────────────────────────────────── */
        #noKmlMsg {
            position:fixed; top:50%; left:50%;
            transform:translate(-50%,-50%);
            background:rgba(255,255,255,.95);
            padding:2rem 2.5rem; border-radius:16px;
            text-align:center; z-index:500;
            box-shadow:0 4px 20px rgba(0,0,0,.1); display:none;
        }

        /* ── Screenshot overlay ───────────────────────── */
        #screenshotBtn {
            position:fixed; bottom:36px; right:10px;
            z-index:500;
        }
    </style>
</head>
<body>

{{-- Topbar --}}
<div id="topbar">
    <a href="{{ route('projects.show', $project) }}"
       style="color:#fff;text-decoration:none;flex-shrink:0">
        <i class="bi bi-arrow-left fs-5"></i>
    </a>
    <div style="width:1px;height:24px;background:rgba(255,255,255,.2)"></div>
    <div style="overflow:hidden">
        <div class="project-title">{{ $project->name }}</div>
        <div class="project-code">{{ $project->code }}</div>
    </div>
    <div style="flex:1"></div>

    {{-- Layer count --}}
    <span class="badge" style="background:rgba(255,255,255,.15);color:#fff;font-size:.72rem">
        <i class="bi bi-layers me-1"></i>
        <span id="layerCountBadge">0</span>/{{ count($kmlFiles) }} aktif
    </span>

    {{-- Fit button --}}
    <button onclick="fitAllLayers()"
            style="background:rgba(255,255,255,.15);color:#fff;border:none;
                   border-radius:6px;padding:4px 10px;font-size:.8rem;cursor:pointer"
            title="Fit ke semua layer">
        <i class="bi bi-fullscreen"></i>
    </button>

    {{-- Toggle panel --}}
    <button onclick="togglePanel()"
            style="background:rgba(255,255,255,.15);color:#fff;border:none;
                   border-radius:6px;padding:4px 10px;font-size:.8rem;cursor:pointer"
            title="Toggle panel layer">
        <i class="bi bi-layout-sidebar"></i>
    </button>
</div>

{{-- Layer Panel --}}
<div id="layerPanel">
    <div class="panel-header">
        <h6><i class="bi bi-layers me-1"></i>Layer KML</h6>
        <span class="badge bg-light text-dark border" style="font-size:.7rem">
            {{ count($kmlFiles) }} file
        </span>
    </div>
    <div class="panel-body" id="layerList">
        <div class="text-center py-4 text-muted small" id="layerListPlaceholder">
            <div class="spinner-border spinner-border-sm mb-2"></div><br>
            Memuat layer…
        </div>
    </div>
    <div class="panel-footer d-flex gap-1">
        <button onclick="showAllLayers()"
                class="btn btn-outline-secondary btn-sm flex-fill"
                style="font-size:.72rem">
            <i class="bi bi-eye me-1"></i>Semua On
        </button>
        <button onclick="hideAllLayers()"
                class="btn btn-outline-secondary btn-sm flex-fill"
                style="font-size:.72rem">
            <i class="bi bi-eye-slash me-1"></i>Semua Off
        </button>
        <button onclick="fitAllLayers()"
                class="btn btn-outline-primary btn-sm flex-fill"
                style="font-size:.72rem">
            <i class="bi bi-arrows-fullscreen me-1"></i>Fit
        </button>
    </div>
</div>

{{-- Map Type Controls --}}
<div id="mapControls">
    <button onclick="setMapType('roadmap')"   id="btn-roadmap"   class="active" title="Jalan">
        <i class="bi bi-map"></i>
    </button>
    <button onclick="setMapType('satellite')" id="btn-satellite" title="Satelit">
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
    <div class="info-header">
        <div>
            <strong style="font-size:.82rem">Info Fitur</strong>
            <div id="infoPanelLayerName"
                 style="font-size:.7rem;color:#888;margin-top:1px"></div>
        </div>
        <button onclick="document.getElementById('infoPanel').style.display='none'"
                class="btn-close btn-sm"></button>
    </div>
    <div class="info-body" id="infoPanelContent"></div>
</div>

{{-- No KML --}}
<div id="noKmlMsg">
    <i class="bi bi-map text-muted" style="font-size:3rem"></i>
    <h6 class="mt-3 fw-semibold">Tidak Ada Layer KML</h6>
    <p class="text-muted small mb-3">Upload file .kml ke project ini untuk ditampilkan di peta.</p>
    <a href="{{ route('projects.show', $project) }}" class="btn btn-success btn-sm">
        <i class="bi bi-upload me-1"></i>Upload KML
    </a>
</div>

{{-- Status Toast --}}
<div id="statusToast"></div>

{{-- Map Canvas --}}
<div id="map"></div>

{{-- Status Bar (koordinat + info) --}}
<div id="statusBar">
    <div class="sb-item">
        <i class="bi bi-geo-alt" style="font-size:.7rem"></i>
        <span id="coordDisplay">Arahkan kursor ke peta…</span>
    </div>
    <div class="sb-sep"></div>
    <div class="sb-item">
        <i class="bi bi-layers" style="font-size:.7rem"></i>
        <span id="featureCountDisplay">0 fitur dimuat</span>
    </div>
    <div class="sb-sep"></div>
    <div class="sb-item">
        <i class="bi bi-zoom-in" style="font-size:.7rem"></i>
        <span id="zoomDisplay">Zoom: 5</span>
    </div>
    <div style="flex:1"></div>
    {{-- Screenshot button --}}
    <button onclick="exportMapInfo()"
            style="background:rgba(255,255,255,.1);border:none;color:#90b4d4;
                   border-radius:4px;padding:2px 8px;font-size:.7rem;cursor:pointer"
            title="Export info peta">
        <i class="bi bi-download me-1"></i>Export Info
    </button>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ── Data dari Laravel ──────────────────────────────────
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
const KML_FILES  = @json($kmlFiles);
const PROJECT    = @json(['name' => $project->name, 'code' => $project->code]);

// ── State ──────────────────────────────────────────────
let map;
let kmlLayers = {}; // deprecated placeholder
let dataLayers = {}; // { fileId: google.maps.Data }


let kmlFolders    = {};   // { fileId: [folderNames] }
let featureCount  = 0;
let panelOpen     = true;


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

// ── Load KML, convert ke GeoJSON, dan tampilkan via Data layer
async function loadKmlAsGeoJson(file, onDone) {
    try {
        const response = await fetch(file.url + '?t=' + Date.now());
        if (!response.ok) throw new Error('Gagal mengambil file KML (status ' + response.status + ')');
        const kmlText = await response.text();
        const dom = new DOMParser().parseFromString(kmlText, 'text/xml');

        // Mendukung global object toGeoJSON / togeojson
        const parser = window.toGeoJSON || window.togeojson;
        if (!parser || typeof parser.kml !== 'function') {
            throw new Error('Library toGeoJSON belum siap dimuat');
        }

        const geoJson = parser.kml(dom);

        // Buat google.maps.Data layer
        const dataLayer = new google.maps.Data({
            map: file.is_visible ? map : null,
        });

        // Styling layer berdasarkan warna project / kml & icon point tematik modern
        const layerColor = file.color || '#2563eb';
        dataLayer.setStyle(feature => {
            const stroke = feature.getProperty('stroke') || layerColor;
            const fill   = feature.getProperty('fill') || layerColor;
            const icon   = getFeatureIcon(feature, layerColor);

            return {
                strokeColor:   stroke,
                strokeWeight:  2.5,
                strokeOpacity: 0.9,
                fillColor:     fill,
                fillOpacity:   0.35,
                icon:          icon,
            };
        });

        // Listener klik fitur: ambil SEMUA properties (termasuk ExtendedData KML)
        dataLayer.addListener('click', event => {
            const properties = {};
            event.feature.forEachProperty((val, key) => {
                properties[key] = val;
            });
            showFeatureInfo(properties, event.latLng, file.name);
        });

        // Sanitasi GeoJSON untuk menyaring koordinat kosong/NaN dari KML
        const cleanGeoJson = sanitizeGeoJson(geoJson);
        dataLayer.addGeoJson(cleanGeoJson);

        // Simpan referensi layer
        dataLayers[file.id] = dataLayer;
        kmlLayers[file.id]  = dataLayer; // alias kompatibilitas

        setLayerStatus(file.id, 'ok');

        // Optimasi: Gunakan DOM yang sudah di-fetch, hilangkan HTTP fetch kedua
        parseKmlStructure(file, dom);

        if (typeof onDone === 'function') onDone();
    } catch (e) {
        console.warn('loadKmlAsGeoJson error:', e);
        setLayerStatus(file.id, 'error', e.message);
        if (typeof onDone === 'function') onDone();
    }
}
// ── Init ───────────────────────────────────────────────
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

    // #7 — Koordinat kursor di status bar
    map.addListener('mousemove', e => {
        const lat = e.latLng.lat().toFixed(6);
        const lng = e.latLng.lng().toFixed(6);
        document.getElementById('coordDisplay').textContent =
            `${lat}, ${lng}`;
    });

    // #8 — Zoom level di status bar
    map.addListener('zoom_changed', () => {
        document.getElementById('zoomDisplay').textContent =
            `Zoom: ${map.getZoom()}`;
    });

    if (KML_FILES.length === 0) {
        document.getElementById('noKmlMsg').style.display   = 'block';
        document.getElementById('layerListPlaceholder').innerHTML =
            '<p class="text-muted small px-3 py-2 mb-0">Tidak ada file KML.</p>';
        return;
    }

    loadAllKmlLayers();
}

// ── Load semua KML ─────────────────────────────────────
function loadAllKmlLayers() {
    document.getElementById('layerListPlaceholder').style.display = 'none';
    const layerList  = document.getElementById('layerList');
    let   fitPending = KML_FILES.filter(f => f.is_visible).length || 1;

    KML_FILES.forEach(file => {

        // Buat layer item di panel dengan status LOADING dulu (#3)
        layerList.appendChild(buildLayerRow(file));
        setLayerStatus(file.id, 'loading');

// Load KML, convert ke GeoJSON, dan tampilkan via Data layer
loadKmlAsGeoJson(file, () => {
    fitPending--;
    if (fitPending <= 0) fitAllLayers();
});
    });

    updateLayerBadge();
}

// ── #3 Set status badge per layer ─────────────────────
function setLayerStatus(fileId, status, message = '') {
    const badge = document.getElementById(`status-badge-${fileId}`);
    if (!badge) return;

    const configs = {
        loading: { cls: 'loading', text: '⟳ Memuat…' },
        ok:      { cls: 'ok',      text: '✓ Dimuat'  },
        error:   { cls: 'error',   text: '✗ Gagal'   },
    };

    const cfg = configs[status] || configs.error;
    badge.className = `layer-status-badge ${cfg.cls}`;
    badge.textContent = message || cfg.text;
    badge.title = message;
}

// ── #5 Parse struktur folder & jumlah fitur KML (Single Fetch) ──
function parseKmlStructure(file, kmlDoc) {
    try {
        // Hitung jumlah fitur
        const placemarks = kmlDoc.querySelectorAll('Placemark');
        const points     = kmlDoc.querySelectorAll('Placemark Point').length;
        const lines      = kmlDoc.querySelectorAll('Placemark LineString, Placemark MultiGeometry LineString').length;
        const polygons   = kmlDoc.querySelectorAll('Placemark Polygon, Placemark MultiGeometry Polygon').length;

        featureCount += placemarks.length;
        document.getElementById('featureCountDisplay').textContent =
            `${featureCount} fitur dimuat`;

        // Update meta row di panel
        updateLayerMeta(file.id, points, lines, polygons);

        // Ambil struktur folder/sub-layer
        const folders = kmlDoc.querySelectorAll('Document > Folder, Folder > Folder');
        if (folders.length > 0) {
            const folderNames = [];
            folders.forEach(folder => {
                const name = folder.querySelector(':scope > name');
                if (name) folderNames.push(name.textContent.trim());
            });
            kmlFolders[file.id] = folderNames;
            updateSubLayers(file.id, folderNames);
        }

    } catch (e) {
        console.warn('Could not parse KML structure:', file.name, e);
    }
}

// ── Update meta (jumlah fitur) di panel ───────────────
function updateLayerMeta(fileId, points, lines, polygons) {
    const meta = document.getElementById(`layer-meta-${fileId}`);
    if (!meta) return;

    let html = '';
    if (points   > 0) html += `<span class="feature-badge"><i class="bi bi-geo-alt"></i>${points}</span>`;
    if (lines    > 0) html += `<span class="feature-badge"><i class="bi bi-dash-lg"></i>${lines}</span>`;
    if (polygons > 0) html += `<span class="feature-badge"><i class="bi bi-square"></i>${polygons}</span>`;

    meta.innerHTML = html;
    meta.style.display = html ? 'flex' : 'none';
}

// ── #5 Update sub-layers dari folder KML ──────────────
function updateSubLayers(fileId, folderNames) {
    const container = document.getElementById(`sublayers-${fileId}`);
    if (!container || folderNames.length === 0) return;

    container.innerHTML = folderNames.map(name => `
        <div class="sublayer-item">
            <div class="sublayer-dot"></div>
            <span class="text-truncate">${name}</span>
        </div>
    `).join('');

    // Tampilkan toggle untuk expand/collapse sub-layer
    const toggle = document.getElementById(`sublayer-toggle-${fileId}`);
    if (toggle) {
        toggle.style.display = 'inline-block';
        toggle.textContent   = `${folderNames.length} folder`;
    }
}

// ── Build layer row di panel ───────────────────────────
function buildLayerRow(file) {
    const div      = document.createElement('div');
    div.className  = 'layer-item';
    div.id         = `layer-row-${file.id}`;
    div.style.opacity = file.is_visible ? '1' : '0.5';

    div.innerHTML = `
        <div class="layer-main">
            <div class="layer-color-dot"
                 style="background:${file.color}"></div>
            <div class="layer-name" title="${file.name}">
                ${file.name}
            </div>
            <span class="layer-status-badge loading" id="status-badge-${file.id}">
                ⟳ Memuat…
            </span>
            <div class="form-check form-switch mb-0 ms-1">
                <input class="form-check-input" type="checkbox"
                       id="toggle-${file.id}"
                       ${file.is_visible ? 'checked' : ''}
                       style="cursor:pointer"
                       onchange="toggleLayer(${file.id}, this.checked, '${file.toggle_url}')">
            </div>
        </div>

        {{-- Meta: jumlah fitur (#3) --}}
        <div class="layer-meta" id="layer-meta-${file.id}" style="display:none"></div>

        {{-- Sub-layer toggle (#5) --}}
        <div style="padding:2px 0 0 18px">
            <span id="sublayer-toggle-${file.id}"
                  style="display:none;font-size:.68rem;color:#0d6efd;cursor:pointer"
                  onclick="toggleSubLayers(${file.id})">
            </span>
        </div>

        {{-- Sub-layer list (#5) --}}
        <div class="sublayer-list" id="sublayers-${file.id}"></div>
    `;

    // Klik area nama → fit ke layer itu
    div.querySelector('.layer-name').addEventListener('click', () => {
        fitToLayer(file.id);
        showToast('Zoom ke: ' + file.name);
    });

    return div;
}

// ── Toggle sub-layer expand/collapse ──────────────────
function toggleSubLayers(fileId) {
    const list   = document.getElementById(`sublayers-${fileId}`);
    const toggle = document.getElementById(`sublayer-toggle-${fileId}`);
    const isOpen = list.classList.toggle('show');

    const count = (kmlFolders[fileId] || []).length;
    toggle.textContent = isOpen
        ? `▲ ${count} folder`
        : `▼ ${count} folder`;
}

// ── Toggle layer on/off ────────────────────────────────
function toggleLayer(fileId, visible, toggleUrl) {
    const layer  = kmlLayers[fileId];
    const row    = document.getElementById(`layer-row-${fileId}`);
    const status = document.getElementById(`layer-status-${fileId}`);

    layer.setMap(visible ? map : null);
    if (row) row.style.opacity = visible ? '1' : '0.5';

    updateLayerBadge();
    showToast(visible ? 'Layer ditampilkan' : 'Layer disembunyikan');

    fetch(toggleUrl, {
        method:  'PATCH',
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept':       'application/json',
        }
    }).catch(() => showToast('Gagal menyimpan status', true));
}

function showAllLayers() {
    KML_FILES.forEach(file => {
        if (kmlLayers[file.id]) kmlLayers[file.id].setMap(map);
        const chk = document.getElementById(`toggle-${file.id}`);
        const row = document.getElementById(`layer-row-${file.id}`);
        if (chk) chk.checked     = true;
        if (row) row.style.opacity = '1';
    });
    updateLayerBadge();
    showToast('Semua layer ditampilkan');
}

function hideAllLayers() {
    KML_FILES.forEach(file => {
        if (kmlLayers[file.id]) kmlLayers[file.id].setMap(null);
        const chk = document.getElementById(`toggle-${file.id}`);
        const row = document.getElementById(`layer-row-${file.id}`);
        if (chk) chk.checked     = false;
        if (row) row.style.opacity = '0.5';
    });
    updateLayerBadge();
    showToast('Semua layer disembunyikan');
}

// ── Fit bounds ─────────────────────────────────────────
function fitAllLayers() {
    const bounds = new google.maps.LatLngBounds();
    let hasAny = false;

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

function fitToLayer(fileId) {
    const layer = kmlLayers[fileId];
    if (!layer || !layer.getMap()) {
        showToast('Layer tidak aktif', true);
        return;
    }

    const bounds = new google.maps.LatLngBounds();
    let hasAny = false;

    layer.forEach(feature => {
        const geometry = feature.getGeometry();
        if (geometry) {
            geometry.forEachLatLng(latLng => {
                bounds.extend(latLng);
                hasAny = true;
            });
        }
    });

    if (hasAny) map.fitBounds(bounds);
}

// ── #4 Info panel lengkap & detail (Mendukung ExtendedData) ──
function showFeatureInfo(featureData, latLng, layerName) {
    const panel   = document.getElementById('infoPanel');
    const content = document.getElementById('infoPanelContent');
    const header  = document.getElementById('infoPanelLayerName');

    header.textContent = layerName || '';

    let html = '';

    // 1. Judul Fitur (nama atau nomenklatur)
    const title = featureData.name || featureData.nama || featureData.nomenklatur || 'Fitur Tanpa Nama';
    html += `<div class="fw-bold mb-2 pb-1 border-bottom" style="font-size:.88rem; color:#1a3c5e">
                <i class="bi bi-geo-alt-fill me-1 text-primary"></i>${title}
             </div>`;

    // 2. Kumpulkan ExtendedData & Properties KML
    const excludedKeys = ['name', 'description', 'styleUrl', 'styleHash', 'stroke', 'stroke-width', 'stroke-opacity', 'fill', 'fill-opacity', 'icon'];
    const customAttrs = [];

    Object.keys(featureData).forEach(key => {
        if (!excludedKeys.includes(key)) {
            const val = featureData[key];
            if (val !== null && val !== undefined && String(val).trim() !== '') {
                // Format label yang rapi (misal id_di -> ID DI, k_aset -> K Aset)
                const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                customAttrs.push({ key: label, val: String(val) });
            }
        }
    });

    if (customAttrs.length > 0) {
        html += '<div class="mb-2 pb-2 border-bottom">';
        html += '<div class="text-muted fw-semibold mb-1" style="font-size:.7rem; text-transform:uppercase; letter-spacing:0.5px">Data Atribut KML</div>';
        customAttrs.forEach(attr => {
            html += `<div class="info-attr-row">
                         <div class="info-attr-key" title="${attr.key}">${attr.key}</div>
                         <div class="info-attr-val fw-medium">${attr.val}</div>
                     </div>`;
        });
        html += '</div>';
    }

    // 3. Deskripsi Fitur (HTML atau Teks Berformat)
    if (featureData.description) {
        const isHtml = /<[a-z][\s\S]*>/i.test(featureData.description);
        if (isHtml) {
            html += `<div class="mb-2" style="font-size:.78rem; overflow-x:auto">${featureData.description}</div>`;
        } else {
            const lines = featureData.description.trim().split('\n');
            const hasKV = lines.some(l => l.includes(':'));

            if (hasKV && lines.length > 1) {
                html += '<div class="mb-2">';
                lines.forEach(line => {
                    const colonIdx = line.indexOf(':');
                    if (colonIdx > 0) {
                        const key = line.substring(0, colonIdx).trim();
                        const val = line.substring(colonIdx + 1).trim();
                        if (key && val) {
                            html += `<div class="info-attr-row">
                                         <div class="info-attr-key">${key}</div>
                                         <div class="info-attr-val">${val}</div>
                                     </div>`;
                        }
                    } else if (line.trim()) {
                        html += `<div class="text-muted mb-1" style="font-size:.78rem">${line}</div>`;
                    }
                });
                html += '</div>';
            } else {
                html += `<div class="text-muted mb-2" style="font-size:.78rem">${featureData.description}</div>`;
            }
        }
    }

    // 4. Koordinat Geografis (Latitude & Longitude)
    if (latLng) {
        html += `<div style="border-top:1px solid #f0f0f0; margin-top:6px; padding-top:6px">
                     <div class="info-attr-row">
                         <div class="info-attr-key"><i class="bi bi-compass me-1"></i>Latitude</div>
                         <div class="info-attr-val font-monospace">${latLng.lat().toFixed(7)}</div>
                     </div>
                     <div class="info-attr-row">
                         <div class="info-attr-key"><i class="bi bi-compass me-1"></i>Longitude</div>
                         <div class="info-attr-val font-monospace">${latLng.lng().toFixed(7)}</div>
                     </div>
                 </div>`;
    }

    if (!html) {
        html = '<span class="text-muted small">Tidak ada informasi atribut untuk fitur ini.</span>';
    }

    content.innerHTML = html;
    panel.style.display = 'block';
}

// ── Map type ───────────────────────────────────────────
function setMapType(type) {
    map.setMapTypeId(type);
    document.querySelectorAll('#mapControls button')
            .forEach(b => b.classList.remove('active'));
    document.getElementById(`btn-${type}`)?.classList.add('active');
}

// ── Toggle panel ───────────────────────────────────────
function togglePanel() {
    panelOpen = !panelOpen;
    document.getElementById('layerPanel')
            .classList.toggle('collapsed', !panelOpen);
}

// ── Layer badge ────────────────────────────────────────
function updateLayerBadge() {
    const visible = Object.values(kmlLayers)
        .filter(l => l.getMap() !== null).length;
    document.getElementById('layerCountBadge').textContent = visible;
}

// ── #8 Export info peta ────────────────────────────────
function exportMapInfo() {
    const center = map.getCenter();
    const zoom   = map.getZoom();
    const layers = KML_FILES.map(f => ({
        nama    : f.name,
        aktif   : kmlLayers[f.id]?.getMap() !== null,
        folder  : (kmlFolders[f.id] || []).join(', '),
    }));

    const info = [
        `EPAKSI GIS — Info Peta`,
        `========================`,
        `Project : ${PROJECT.name}`,
        `Kode    : ${PROJECT.code}`,
        `Tanggal : ${new Date().toLocaleString('id-ID')}`,
        ``,
        `Posisi Peta`,
        `-----------`,
        `Latitude  : ${center.lat().toFixed(6)}`,
        `Longitude : ${center.lng().toFixed(6)}`,
        `Zoom      : ${zoom}`,
        ``,
        `Layer KML`,
        `---------`,
        ...layers.map(l =>
            `[${l.aktif ? '✓' : ' '}] ${l.nama}` +
            (l.folder ? `\n    Folder: ${l.folder}` : '')
        ),
    ].join('\n');

    const blob = new Blob([info], { type: 'text/plain;charset=utf-8' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = `epaksi-map-${PROJECT.code}-${Date.now()}.txt`;
    a.click();
    URL.revokeObjectURL(url);

    showToast('Info peta berhasil diexport');
}

// ── Toast ──────────────────────────────────────────────
function showToast(msg, isError = false) {
    const toast         = document.getElementById('statusToast');
    toast.textContent   = msg;
    toast.style.display = 'block';
    toast.style.background = isError
        ? 'rgba(185,28,28,.9)' : 'rgba(26,60,94,.9)';
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => toast.style.display = 'none', 2500);
}

// ── Map styles ─────────────────────────────────────────
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