<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Map — {{ $project->name }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    {{-- Load Google Maps first, before anything else --}}
    {{-- <script src="https://maps.googleapis.com/maps/api/js?key={{ $apiKey }}&v=weekly"></script> --}}
		<script async defer
		src="https://maps.googleapis.com/maps/api/js?key={{ $apiKey }}&callback=initMap&v=weekly">
		</script>

    <style>
        * { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        #topbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 52px;
            background: #1a3c5e;
            z-index: 1000;
            display: flex;
            align-items: center;
            padding: 0 1rem;
            gap: 0.75rem;
            box-shadow: 0 2px 8px rgba(0,0,0,.3);
        }

        #topbar .project-title {
            color: #fff;
            font-weight: 600;
            font-size: .95rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 300px;
        }

        #topbar .project-code {
            color: #90b4d4;
            font-size: .75rem;
            font-family: monospace;
        }

        /* CRITICAL: map must fill the remaining viewport exactly */
        #map {
            position: absolute;
            top: 52px;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            background: #e8e8e8; /* visible if map fails to render */
        }

        #layerPanel {
            position: fixed;
            top: 62px;
            left: 10px;
            width: 280px;
            max-height: calc(100vh - 72px);
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,.15);
            z-index: 500;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        #layerPanel.collapsed { transform: translateX(-290px); }

        #layerPanel .panel-header {
            padding: 12px 14px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        #layerPanel .panel-header h6 {
            margin: 0;
            font-size: .85rem;
            font-weight: 600;
            color: #1a3c5e;
        }

        #layerPanel .panel-body {
            overflow-y: auto;
            flex: 1;
            padding: 8px 0;
        }

        #layerPanel .panel-footer {
            padding: 8px;
            border-top: 1px solid #f0f0f0;
            flex-shrink: 0;
        }

        .layer-item {
            padding: 8px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: background .15s;
            border-bottom: 1px solid #f8f8f8;
        }

        .layer-item:hover { background: #f8fafc; }

        .layer-color-dot {
            width: 12px; height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
            border: 2px solid rgba(0,0,0,.1);
        }

        .layer-name {
            font-size: .8rem;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .layer-status { font-size: .65rem; color: #aaa; }

        .layer-item .form-check-input {
            margin: 0;
            cursor: pointer;
            flex-shrink: 0;
        }

        #mapControls {
            position: fixed;
            top: 62px;
            right: 10px;
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
            transition: background .15s;
        }

        #mapControls button:hover  { background: #f0f4f8; }
        #mapControls button.active { background: #1a3c5e; color: #fff; }

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
            max-height: 300px;
            overflow-y: auto;
        }

        #loadingOverlay {
            position: fixed;
            inset: 0;
            background: rgba(26,60,94,.85);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #fff;
        }

        #statusToast {
            position: fixed;
            bottom: 24px;
            left: 50%;
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

        #noKmlMsg {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255,255,255,.95);
            padding: 2rem 2.5rem;
            border-radius: 16px;
            text-align: center;
            z-index: 500;
            box-shadow: 0 4px 20px rgba(0,0,0,.1);
            display: none;
        }
    </style>
</head>
<body>

{{-- Loading Overlay --}}
<div id="loadingOverlay" style="
    position:fixed;
    inset:0;
    background:rgba(26,60,94,.85);
    z-index:2000;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-direction:column;
    color:#fff;
    transition: opacity 0.3s ease;
">
    <div class="spinner-border text-light mb-3" style="width:3rem;height:3rem"></div>
    <div style="font-size:.95rem;font-weight:500">Loading Map…</div>
    <div style="font-size:.8rem;opacity:.7;margin-top:.25rem">{{ $project->name }}</div>
</div>

{{-- Top Bar --}}
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
    <span class="badge"
          style="background:rgba(255,255,255,.15);color:#fff;font-size:.75rem">
        <i class="bi bi-layers me-1"></i>
        <span id="layerCountBadge">0</span> layer(s)
    </span>
    <button onclick="fitAllLayers()"
            style="background:rgba(255,255,255,.15);color:#fff;border:none;
                   border-radius:6px;padding:4px 10px;font-size:.8rem;cursor:pointer"
            title="Fit to layers">
        <i class="bi bi-fullscreen"></i>
    </button>
    <button onclick="togglePanel()"
            style="background:rgba(255,255,255,.15);color:#fff;border:none;
                   border-radius:6px;padding:4px 10px;font-size:.8rem;cursor:pointer"
            title="Toggle layer panel">
        <i class="bi bi-layout-sidebar"></i>
    </button>
</div>

{{-- Layer Panel --}}
<div id="layerPanel">
    <div class="panel-header">
        <h6><i class="bi bi-layers me-1"></i> Map Layers</h6>
        <span class="badge bg-light text-dark border" style="font-size:.7rem">
            {{ count($kmlFiles) }} KML
        </span>
    </div>
    <div class="panel-body" id="layerList">
        <div class="text-center py-3 text-muted small" id="layerListPlaceholder">
            <div class="spinner-border spinner-border-sm mb-2"></div><br>
            Loading layers…
        </div>
    </div>
    <div class="panel-footer d-flex gap-1">
        <button onclick="showAllLayers()"
                class="btn btn-outline-secondary btn-sm flex-fill"
                style="font-size:.75rem">
            <i class="bi bi-eye me-1"></i>All On
        </button>
        <button onclick="hideAllLayers()"
                class="btn btn-outline-secondary btn-sm flex-fill"
                style="font-size:.75rem">
            <i class="bi bi-eye-slash me-1"></i>All Off
        </button>
        <button onclick="fitAllLayers()"
                class="btn btn-outline-primary btn-sm flex-fill"
                style="font-size:.75rem">
            <i class="bi bi-arrows-fullscreen me-1"></i>Fit
        </button>
    </div>
</div>

{{-- Map Type Controls --}}
<div id="mapControls">
    <button onclick="setMapType('roadmap')"
            id="btn-roadmap" class="active" title="Road Map">
        <i class="bi bi-map"></i>
    </button>
    <button onclick="setMapType('satellite')"
            id="btn-satellite" title="Satellite">
        <i class="bi bi-globe2"></i>
    </button>
    <button onclick="setMapType('hybrid')"
            id="btn-hybrid" title="Hybrid">
        <i class="bi bi-layers"></i>
    </button>
    <button onclick="setMapType('terrain')"
            id="btn-terrain" title="Terrain">
        <i class="bi bi-triangle"></i>
    </button>
</div>

{{-- Feature Info Panel --}}
<div id="infoPanel">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <strong style="font-size:.85rem">Feature Info</strong>
        <button onclick="document.getElementById('infoPanel').style.display='none'"
                class="btn-close btn-sm"></button>
    </div>
    <div id="infoPanelContent" style="font-size:.82rem"></div>
</div>

{{-- No KML Message --}}
<div id="noKmlMsg">
    <i class="bi bi-map text-muted" style="font-size:3rem"></i>
    <h6 class="mt-3 fw-semibold">No KML Layers Found</h6>
    <p class="text-muted small mb-3">
        Upload a KML file to this project to see it on the map.
    </p>
    <a href="{{ route('projects.show', $project) }}" class="btn btn-primary btn-sm">
        <i class="bi bi-upload me-1"></i> Upload KML Files
    </a>
</div>

{{-- Status Toast --}}
<div id="statusToast"></div>

{{-- Map Canvas --}}
<div id="map"></div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- All application JS runs after DOM is ready --}}
<script>
// ── Data from Laravel ──────────────────────────────────
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
const KML_FILES  = @json($kmlFiles);

// ── State ──────────────────────────────────────────────
let map;
let kmlLayers = {};
let panelOpen = true;

// ── Boot when DOM is ready ─────────────────────────────
// document.addEventListener('DOMContentLoaded', function () {
//     console.log('✅ DOM ready — booting map');
//     console.log('KML files:', KML_FILES.length);
//     initMap();
// });

// ── initMap ────────────────────────────────────────────
function initMap() {
    console.log('✅ initMap called');

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
        // styles: mapStyles(),
    });

    console.log('✅ Map object created');

    // Hide loading overlay
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.opacity    = '0';
        overlay.style.pointerEvents = 'none';
        setTimeout(() => overlay.remove(), 300); // remove after fade
				console.log("overlay removed");
    }

    if (KML_FILES.length === 0) {
        document.getElementById('noKmlMsg').style.display = 'block';
        document.getElementById('layerListPlaceholder').innerHTML =
            '<p class="text-muted small px-3 py-2 mb-0">No KML files attached.</p>';
        return;
    }

    loadAllKmlLayers();
}

// ── Load KML layers ────────────────────────────────────
function loadAllKmlLayers() {
    console.log('✅ Loading', KML_FILES.length, 'KML layers');

    document.getElementById('layerListPlaceholder').style.display = 'none';

    const layerList  = document.getElementById('layerList');
    let   fitPending = KML_FILES.filter(f => f.is_visible).length;

    KML_FILES.forEach(file => {
        console.log('Loading KML:', file.name, '|', file.url);

        const layer = new google.maps.KmlLayer({
            url:                 file.url + '?t=' + Date.now(),
            map:                 file.is_visible ? map : null,
            preserveViewport:    true,
            suppressInfoWindows: false,
        });

        kmlLayers[file.id] = layer;

        layer.addListener('status_changed', function () {
            console.log('KML status for', file.name, ':', layer.getStatus());
        });

        layer.addListener('click', event => {
            showFeatureInfo(event.featureData, event.latLng);
        });

        layer.addListener('defaultviewport_changed', () => {
						const vp = layer.getDefaultViewport();
						if (vp) {
								fitPending--;
								if (fitPending <= 0) fitAllLayers();
						}
				});

        layerList.appendChild(buildLayerRow(file));
    });

    updateLayerBadge();
}

// ── Build layer row ────────────────────────────────────
function buildLayerRow(file) {
    const div         = document.createElement('div');
    div.className     = 'layer-item';
    div.id            = 'layer-row-' + file.id;
    div.style.opacity = file.is_visible ? '1' : '0.5';

    div.innerHTML = `
        <div class="layer-color-dot" style="background:${file.color}"></div>
        <div style="flex:1;overflow:hidden">
            <div class="layer-name" title="${file.name}">${file.name}</div>
            <div class="layer-status" id="layer-status-${file.id}">
                ${file.is_visible ? 'Visible' : 'Hidden'}
            </div>
        </div>
        <div class="form-check form-switch mb-0">
            <input class="form-check-input"
                   type="checkbox"
                   id="toggle-${file.id}"
                   ${file.is_visible ? 'checked' : ''}
                   onchange="toggleLayer(${file.id}, this.checked, '${file.toggle_url}')">
        </div>
    `;

    div.addEventListener('click', e => {
        if (e.target.type === 'checkbox') return;
        fitToLayer(file.id);
        showToast('Zoomed to: ' + file.name);
    });

    return div;
}

// ── Toggle layer ───────────────────────────────────────
function toggleLayer(fileId, visible, toggleUrl) {
    const layer  = kmlLayers[fileId];
    const status = document.getElementById('layer-status-' + fileId);
    const row    = document.getElementById('layer-row-'    + fileId);

    layer.setMap(visible ? map : null);
    if (status) status.textContent = visible ? 'Visible' : 'Hidden';
    if (row)    row.style.opacity  = visible ? '1' : '0.5';

    updateLayerBadge();

    fetch(toggleUrl, {
        method:  'PATCH',
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept':       'application/json',
        }
    })
    .then(r => r.json())
    .then(() => showToast(visible ? 'Layer visible' : 'Layer hidden'))
    .catch(() => showToast('Could not save state', true));
}

// ── Show / hide all ────────────────────────────────────
function showAllLayers() {
    KML_FILES.forEach(file => {
        kmlLayers[file.id].setMap(map);
        const chk = document.getElementById('toggle-'       + file.id);
        const row = document.getElementById('layer-row-'    + file.id);
        const st  = document.getElementById('layer-status-' + file.id);
        if (chk) chk.checked      = true;
        if (row) row.style.opacity = '1';
        if (st)  st.textContent   = 'Visible';
    });
    updateLayerBadge();
    showToast('All layers visible');
}

function hideAllLayers() {
    KML_FILES.forEach(file => {
        kmlLayers[file.id].setMap(null);
        const chk = document.getElementById('toggle-'       + file.id);
        const row = document.getElementById('layer-row-'    + file.id);
        const st  = document.getElementById('layer-status-' + file.id);
        if (chk) chk.checked      = false;
        if (row) row.style.opacity = '0.5';
        if (st)  st.textContent   = 'Hidden';
    });
    updateLayerBadge();
    showToast('All layers hidden');
}

// ── Fit bounds ─────────────────────────────────────────
function fitAllLayers() {
    const bounds = new google.maps.LatLngBounds();
    let   hasAny = false;

    Object.values(kmlLayers).forEach(layer => {
        if (layer.getMap()) {
            try {
                const vp = layer.getDefaultViewport();
                if (vp) { bounds.union(vp); hasAny = true; }
            } catch (e) {}
        }
    });

    if (hasAny) map.fitBounds(bounds);
}

function fitToLayer(fileId) {
    const layer = kmlLayers[fileId];
    if (!layer || !layer.getMap()) {
        showToast('Layer is hidden — turn it on first', true);
        return;
    }
    try {
        const vp = layer.getDefaultViewport();
        if (vp) map.fitBounds(vp);
    } catch (e) {}
}

// ── Feature info ───────────────────────────────────────
function showFeatureInfo(featureData, latLng) {
    const panel   = document.getElementById('infoPanel');
    const content = document.getElementById('infoPanelContent');
    let   html    = '';

    if (featureData.name) {
        html += `<div class="fw-semibold mb-1">${featureData.name}</div>`;
    }
    if (featureData.description) {
        html += `<div class="text-muted mb-2">${featureData.description}</div>`;
    }
    if (latLng) {
        html += `<hr class="my-2">
                 <div class="text-muted" style="font-size:.75rem;font-family:monospace">
                     Lat: ${latLng.lat().toFixed(6)}<br>
                     Lng: ${latLng.lng().toFixed(6)}
                 </div>`;
    }

    content.innerHTML = html || '<span class="text-muted">No info available.</span>';
    panel.style.display = 'block';
}

// ── Map type ───────────────────────────────────────────
function setMapType(type) {
    map.setMapTypeId(type);
    document.querySelectorAll('#mapControls button')
            .forEach(b => b.classList.remove('active'));
    const btn = document.getElementById('btn-' + type);
    if (btn) btn.classList.add('active');
}

// ── Toggle panel ───────────────────────────────────────
function togglePanel() {
    panelOpen = !panelOpen;
    document.getElementById('layerPanel')
            .classList.toggle('collapsed', !panelOpen);
}

// ── Badge ──────────────────────────────────────────────
function updateLayerBadge() {
    const visible = Object.values(kmlLayers)
        .filter(l => l.getMap() !== null).length;
    document.getElementById('layerCountBadge').textContent = visible;
}

// ── Toast ──────────────────────────────────────────────
function showToast(msg, isError = false) {
    const toast         = document.getElementById('statusToast');
    toast.textContent   = msg;
    toast.style.display = 'block';
    toast.style.background = isError
        ? 'rgba(185,28,28,.9)'
        : 'rgba(26,60,94,.9)';
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => toast.style.display = 'none', 2500);
}

// ── Map styles ─────────────────────────────────────────
function mapStyles() {
    return [
        { featureType: 'water',
          elementType: 'geometry',
          stylers: [{ color: '#a0c8e8' }] },
        { featureType: 'landscape',
          elementType: 'geometry',
          stylers: [{ color: '#f5f5f0' }] },
        { featureType: 'road',
          elementType: 'geometry',
          stylers: [{ color: '#ffffff' }, { weight: 0.8 }] },
        { featureType: 'poi',
          stylers: [{ visibility: 'off' }] },
        { featureType: 'administrative',
          elementType: 'geometry.stroke',
          stylers: [{ color: '#c9c9c9' }] },
        { elementType: 'labels.text.fill',
          stylers: [{ color: '#555' }] },
    ];
}
</script>

</body>
</html>