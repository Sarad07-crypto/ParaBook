<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map Debug</title>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
    body {
        margin: 0;
        padding: 20px;
        font-family: Arial, sans-serif;
    }

    .debug-info {
        background: #f0f0f0;
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 5px;
        font-family: monospace;
    }

    .map-container {
        border: 2px solid #007bff;
        border-radius: 8px;
        overflow: hidden;
        margin: 20px 0;
    }

    #locationMap {
        height: 400px;
        width: 100%;
        background: #e0e0e0;
    }

    .controls {
        margin: 20px 0;
    }

    button {
        padding: 10px 20px;
        margin: 5px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    button:hover {
        background: #0056b3;
    }

    .error {
        background: #ffe6e6;
        color: #d63384;
        padding: 10px;
        border-radius: 4px;
        margin: 10px 0;
    }

    .success {
        background: #e6ffe6;
        color: #198754;
        padding: 10px;
        border-radius: 4px;
        margin: 10px 0;
    }
    </style>
</head>

<body>
    <h1>OpenStreetMap Debug Test</h1>

    <div class="debug-info" id="debugInfo">
        <div>Debug Information:</div>
        <div id="debugLog"></div>
    </div>

    <div class="controls">
        <button onclick="testMapInitialization()">Test Map Initialization</button>
        <button onclick="testLeafletLoading()">Test Leaflet Loading</button>
        <button onclick="testNetworkAccess()">Test Network Access</button>
        <button onclick="reinitializeMap()">Reinitialize Map</button>
    </div>

    <div class="map-container">
        <div id="locationMap"></div>
    </div>

    <div id="mapStatus"></div>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
    let map;
    let marker;
    let debugLog = [];

    function addDebugLog(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        debugLog.push(`[${timestamp}] ${message}`);
        updateDebugDisplay();

        if (type === 'error') {
            console.error(message);
        } else {
            console.log(message);
        }
    }

    function updateDebugDisplay() {
        const debugElement = document.getElementById('debugLog');
        debugElement.innerHTML = debugLog.slice(-10).join('<br>');
    }

    function showStatus(message, type = 'info') {
        const statusElement = document.getElementById('mapStatus');
        statusElement.className = type;
        statusElement.textContent = message;
    }

    function testLeafletLoading() {
        addDebugLog('Testing Leaflet loading...');

        if (typeof L === 'undefined') {
            addDebugLog('ERROR: Leaflet (L) is not loaded!', 'error');
            showStatus('Leaflet library failed to load', 'error');
            return false;
        }

        addDebugLog('Leaflet loaded successfully');
        addDebugLog(`Leaflet version: ${L.version}`);
        showStatus('Leaflet loaded successfully', 'success');
        return true;
    }

    function testNetworkAccess() {
        addDebugLog('Testing network access to OpenStreetMap...');

        const testUrl = 'https://tile.openstreetmap.org/1/0/0.png';
        const img = new Image();

        img.onload = function() {
            addDebugLog('Network access to OpenStreetMap: SUCCESS');
            showStatus('Network access working', 'success');
        };

        img.onerror = function() {
            addDebugLog('Network access to OpenStreetMap: FAILED', 'error');
            showStatus('Network access failed - check internet connection', 'error');
        };

        img.src = testUrl;
    }

    function testMapInitialization() {
        addDebugLog('Testing map initialization...');

        if (!testLeafletLoading()) {
            return;
        }

        const mapContainer = document.getElementById('locationMap');
        if (!mapContainer) {
            addDebugLog('ERROR: Map container not found!', 'error');
            showStatus('Map container not found', 'error');
            return;
        }

        addDebugLog(`Map container found: ${mapContainer.offsetWidth}x${mapContainer.offsetHeight}`);

        if (mapContainer.offsetWidth === 0 || mapContainer.offsetHeight === 0) {
            addDebugLog('WARNING: Map container has zero dimensions!', 'error');
            showStatus('Map container has zero dimensions', 'error');
            return;
        }

        try {
            // Clear existing map
            if (map) {
                map.remove();
                map = null;
            }

            // Initialize map
            map = L.map('locationMap').setView([27.7172, 85.3240], 13);
            addDebugLog('Map object created successfully');

            // Add tile layer
            const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            });

            tileLayer.addTo(map);
            addDebugLog('Tile layer added to map');

            // Test tile loading
            tileLayer.on('load', function() {
                addDebugLog('Tiles loaded successfully');
                showStatus('Map loaded successfully!', 'success');
            });

            tileLayer.on('tileerror', function(e) {
                addDebugLog(`Tile loading error: ${e.error}`, 'error');
                showStatus('Tile loading failed', 'error');
            });

            // Add click event
            map.on('click', function(e) {
                addDebugLog(`Map clicked at: ${e.latlng.lat.toFixed(6)}, ${e.latlng.lng.toFixed(6)}`);

                if (marker) {
                    map.removeLayer(marker);
                }

                marker = L.marker([e.latlng.lat, e.latlng.lng]).addTo(map);
                showStatus(`Location selected: ${e.latlng.lat.toFixed(6)}, ${e.latlng.lng.toFixed(6)}`,
                    'success');
            });

            addDebugLog('Map initialization completed');

        } catch (error) {
            addDebugLog(`ERROR during map initialization: ${error.message}`, 'error');
            showStatus(`Map initialization failed: ${error.message}`, 'error');
        }
    }

    function reinitializeMap() {
        addDebugLog('Reinitializing map...');

        if (map) {
            map.remove();
            map = null;
            addDebugLog('Existing map removed');
        }

        setTimeout(() => {
            testMapInitialization();
        }, 100);
    }

    // Auto-run tests when page loads
    document.addEventListener('DOMContentLoaded', function() {
        addDebugLog('DOM Content Loaded');

        setTimeout(() => {
            testLeafletLoading();
            testNetworkAccess();
            testMapInitialization();
        }, 500);
    });

    // Window load event
    window.addEventListener('load', function() {
        addDebugLog('Window fully loaded');
    });

    // Error handling
    window.addEventListener('error', function(e) {
        addDebugLog(`Global error: ${e.message} at ${e.filename}:${e.lineno}`, 'error');
    });
    </script>
</body>

</html>