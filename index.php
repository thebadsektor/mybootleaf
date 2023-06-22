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

    .sidebar {
        height: 100vh;
        overflow-y: auto;
    }

    .table-container {
        height: 50%;
        overflow-y: auto;
        margin-bottom: 10px;
    }

    .checkboxes {
        margin-top: 10px;
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
                    <div>
                    <input type="text" class="form-control" placeholder="Search" aria-label="search" aria-describedby="basic-addon1">
                    </div>
                    <div class="table-container">
                        <table id="polygon-table" class="table table-striped table-hover">
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="checkboxes mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="residentialCheckbox" checked>
                            <label class="form-check-label" for="residentialCheckbox">
                                Unclassified
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="commercialCheckbox" checked>
                            <label class="form-check-label" for="commercialCheckbox">
                                Secondary
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="agriculturalCheckbox" checked>
                            <label class="form-check-label" for="agriculturalCheckbox">
                                Residential
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="governmentCheckbox" checked>
                            <label class="form-check-label" for="governmentCheckbox">
                                Tertiary
                            </label>
                        </div>
                    </div>
                </div>
            </nav>
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pr-md-4">
                <div id="map"></div>
            </main>
        </div>
    </div>

    <script>
    // Define a color palette
    const colorPalette = ['#22A699', '#F2BE22', '#F29727', '#F24C3D', '#42FFC9', '#4292FF'];

    // Initialize the map and set its view to Santa Cruz, Laguna
    var map = L.map('map').setView([14.282332, 121.423933], 13);

    // Add a tile layer from OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Style the GeoJSON features with a color palette based on the "highway" feature
function styleFeature(feature) {
    const highway = feature.properties.highway;
    let fillColor;

    switch (highway) {
        case 'unclassified':
            fillColor = '#22A699';
            break;
        case 'secondary':
            fillColor = '#F2BE22';
            break;
        case 'residential':
            fillColor = '#F29727';
            break;
        case 'tertiary':
            fillColor = '#F24C3D';
            break;
        default:
            fillColor = '#42FFC9';
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
      const polygonTable = document.getElementById('polygon-table');

      data.features.forEach((feature, index) => {
        const row = document.createElement('tr');
        const nameCell = document.createElement('td');

        nameCell.textContent = feature.properties.name || `Polygon ${index + 1}`;
        row.appendChild(nameCell);
        polygonTable.appendChild(row);

        row.addEventListener('click', () => {
          const polygon = polygonLayer.getLayers()[index];
          map.fitBounds(polygon.getBounds(), {
            padding: [50, 50]
          });
          polygon.openPopup();
        });
      });

      const polygonLayer = L.geoJSON(data, {
        style: styleFeature,
        onEachFeature: onEachFeature
      }).addTo(map);
    })
    .catch(error => {
      console.error('Error loading GeoJSON data:', error);
    });
}



    loadGeoJSONFiles();
    </script>
</body>

</html>