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
    <script src="https://unpkg.com/togeojson@0.16.0/dist/togeojson.min.js"></script>

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

function loadAllLayers() {
    ALL_LAYERS.forEach(layer => {
        const kml = new google.maps.KmlLayer({
            url:                 layer.url + '?t=' + Date.now(),
            map:                 map,
            preserveViewport:    true,
            suppressInfoWindows: false,
        });

        kmlLayers[layer.id] = kml;

        // Click → show feature info with project context
        kml.addListener('click', event => {
            showFeatureInfo(event.featureData, event.latLng, layer);
        });
    });

    // Fit after a short delay to let layers settle
    setTimeout(fitAllLayers, 1500);
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
            const kml = kmlLayers[l.id];
            if (kml) {
                try {
                    const vp = kml.getDefaultViewport();
                    if (vp) { bounds.union(vp); hasAny = true; }
                } catch (e) {}
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
        try {
            const vp = layer.getDefaultViewport();
            if (vp) { bounds.union(vp); hasAny = true; }
        } catch (e) {}
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

    if (featureData.name) {
        html += `<div class="fw-semibold mb-1">${featureData.name}</div>`;
    }
    if (featureData.description) {
        html += `<div class="text-muted mb-2">${featureData.description}</div>`;
    }
    if (latLng) {
        html += `<div class="text-muted mb-2" style="font-size:.75rem;font-family:monospace">
                     ${latLng.lat().toFixed(6)}, ${latLng.lng().toFixed(6)}
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