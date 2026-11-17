<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Map Test</title>
    <style>
        #map { width: 100vw; height: 100vh; }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/togeojson/0.16.0/togeojson.min.js"></script>
</head>
<body>

<div id="map"></div>

<script>
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
            case 'Point': return isValidCoord(coords);
            case 'LineString':
            case 'MultiPoint': return coords.length > 0 && coords.every(isValidCoord);
            case 'Polygon':
            case 'MultiLineString': return coords.length > 0 && coords.every(r => Array.isArray(r) && r.length > 0 && r.every(isValidCoord));
            case 'MultiPolygon': return coords.length > 0 && coords.every(p => Array.isArray(p) && p.length > 0 && p.every(r => Array.isArray(r) && r.length > 0 && r.every(isValidCoord)));
            case 'GeometryCollection': return Array.isArray(geom.geometries) && geom.geometries.every(isValidGeometry);
            default: return true;
        }
    }
    geoJson.features = geoJson.features.filter(f => f && f.geometry && isValidGeometry(f.geometry));
    return geoJson;
}

async function initMap() {
    // Step 1: Inisialisasi Map
    const map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: -6.2, lng: 106.8 },
        zoom: 10,
    });

    // Step 2: Ambil URL KML
    const kmlUrl = '{{ isset($kmlFiles[0]) ? $kmlFiles[0]["url"] : "" }}';

    if (kmlUrl) {
        try {
            const res = await fetch(kmlUrl + '?t=' + Date.now());
            if (!res.ok) throw new Error('HTTP error ' + res.status);
            const kmlText = await res.text();
            const dom = new DOMParser().parseFromString(kmlText, 'text/xml');
            const parser = window.toGeoJSON || window.togeojson;
            if (!parser || !parser.kml) throw new Error('toGeoJSON tidak tersedia');
            const geoJson = parser.kml(dom);

            const dataLayer = new google.maps.Data({ map: map });
            dataLayer.addGeoJson(sanitizeGeoJson(geoJson));

            // Fit bounds
            const bounds = new google.maps.LatLngBounds();
            let hasAny = false;
            dataLayer.forEach(feature => {
                const geom = feature.getGeometry();
                if (geom) {
                    geom.forEachLatLng(latLng => {
                        bounds.extend(latLng);
                        hasAny = true;
                    });
                }
            });
            if (hasAny) map.fitBounds(bounds);

            console.log('KML berhasil dimuat via Data Layer');
        } catch (err) {
            console.error('KML Load Error:', err);
            alert('Gagal memuat KML: ' + err.message);
        }
    } else {
        alert('No KML files found for this project.');
    }
}
</script>

<script src="https://maps.googleapis.com/maps/api/js?key={{ $apiKey }}&callback=initMap&v=3.65"
        async defer></script>

</body>
</html>
