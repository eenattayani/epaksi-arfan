<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Map Test</title>
    <style>
        #map { width: 100vw; height: 100vh; }
    </style>
</head>
<body>

<div id="map"></div>

<script>
function initMap() {
    // Step 1: Does a basic map render?
    const map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: -6.2, lng: 106.8 },
        zoom: 10,
    });

    // Step 2: Does KML load? Replace URL with your actual KML public URL
    const kmlUrl = '{{ isset($kmlFiles[0]) ? $kmlFiles[0]["url"] : "" }}';

    if (kmlUrl) {
        const layer = new google.maps.KmlLayer({
            url: kmlUrl + '?t=' + Date.now(),
            map: map,
        });

        layer.addListener('status_changed', function() {
            // This tells you exactly what went wrong with KML loading
            alert('KML Status: ' + layer.getStatus());
        });
    } else {
        alert('No KML files found for this project.');
    }
}
</script>

<script src="https://maps.googleapis.com/maps/api/js?key={{ $apiKey }}&callback=initMap&v=weekly"
        async defer></script>

</body>
</html>