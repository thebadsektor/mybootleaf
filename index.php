<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GIS - Santa Cruz, Laguna</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <!-- Add Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css">

    <style>
    #map {
        width: 100%;
        height: 100vh;
    }
    </style>
</head>

<body>
    <!-- Add a Bootstrap side nav bar -->
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 p-2 d-none d-md-block bg-light sidebar">
                <div class="sidebar-sticky">
                    <!-- <h5>RPT-GIS</h5> -->
                    <!-- <img src="../dist/img/logo.png" width="20%"> -->
                    <div class="d-flex align-items-center"
                        style="padding-bottom: 10px; border-bottom: 1px solid #e1e1e1; margin-bottom: 10px;">
                        <img src="logo.png" width="40px">
                        <div style="font-size: 20px; font-weight: 700; color: #343a40; padding-left: 10px;">LGU</div>
                    </div>
                    <ul class="nav flex-column" id="polygon-buttons"></ul>
                </div>
            </nav>
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pr-md-4">
                <div id="map"></div>
            </main>
        </div>
    </div>

    <script>
    // Define a color palette
    const colorPalette = ['#FF8C42', '#FFB142', '#FFEA5A', '#8AFF5A', '#42FFC9', '#4292FF'];

    // Initialize the map and set its view to Santa Cruz, Laguna
    var map = L.map('map').setView([14.282332, 121.423933], 13);

    // Add a tile layer from OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Style the GeoJSON features with a color palette
    function styleFeature(feature) {
        const propertyValue = feature.properties.value; // replace with the actual property name
        let fillColor;

        if (propertyValue < 10) {
            fillColor = colorPalette[0];
        } else if (propertyValue < 20) {
            fillColor = colorPalette[1];
        } else if (propertyValue < 30) {
            fillColor = colorPalette[2];
        } else if (propertyValue < 40) {
            fillColor = colorPalette[3];
        } else if (propertyValue < 50) {
            fillColor = colorPalette[4];
        } else {
            fillColor = colorPalette[5];
        }

        return {
            fillColor: fillColor,
            fillOpacity: 0.3,
            weight: 1,
            color: 'white'
        };
    }

    // Function to handle different types of features (e.g., points, lines, and polygons)
    function onEachFeature(feature, layer) {
        // You can add popups, event listeners, or other interactions for each feature here
        layer.bindPopup(feature.properties.name || 'No name available');
        // Add click event listener
        layer.on('click', function(e) {
            // Pan and zoom to the clicked polygon
            map.fitBounds(e.target.getBounds(), {
                padding: [50, 50]
            });

            // Open the popup with the polygon's information
            e.target.openPopup();
        });
    }

    // Function to load the GeoJSON data and add it to the map
    function loadGeoJSONFiles() {
        // Load other GeoJSON files
        const otherFiles = ['santacruz_lines.geojson', 'santacruz_point.geojson'];

        otherFiles.forEach(file => {
            fetch(file)
                .then(response => response.json())
                .then(data => {
                    L.geoJSON(data, {
                        style: styleFeature,
                        onEachFeature: onEachFeature
                    }).addTo(map);
                })
                .catch(error => {
                    console.error('Error loading GeoJSON data:', error);
                });
        });

        // Load the santacruz_multipolygon.geojson file separately
        const multipolygonFile = 'santacruz_multipolygon.geojson';

        fetch(multipolygonFile)
            .then(response => response.json())
            .then(data => {
                const polygonLayer = L.geoJSON(data, {
                    style: styleFeature,
                    onEachFeature: onEachFeature
                }).addTo(map);

                // Create buttons for each polygon and add click events
                data.features.forEach((feature, index) => {
                    const button = document.createElement('button');
                    button.classList.add('btn', 'btn-sm', 'btn-outline-primary', 'my-1');
                    button.textContent = feature.properties.name || `Polygon ${index + 1}`;
                    button.addEventListener('click', () => {
                        const polygon = polygonLayer.getLayers()[index];
                        map.fitBounds(polygon.getBounds(), {
                            padding: [50, 50]
                        });
                        polygon.openPopup();
                    });
                    document.getElementById('polygon-buttons').appendChild(button);
                });
            })
            .catch(error => {
                console.error('Error loading GeoJSON data:', error);
            });
    }

    loadGeoJSONFiles();
    </script>
</body>

</html>