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
            <input type="text" class="form-control" placeholder="Search" aria-label="search"
              aria-describedby="basic-addon1">
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
                Residential
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="commercialCheckbox" checked>
              <label class="form-check-label" for="commercialCheckbox">
                Commercial
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="agriCheckbox" checked>
              <label class="form-check-label" for="agriCheckbox">
                Agricultural
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

    // Style the GeoJSON features with a color palette based on the "name" attribute
    function styleFeature(feature) {
        const name = feature.properties.name;
        const index = name ? [...name].reduce((sum, char) => sum + char.charCodeAt(0), 0) % colorPalette.length : 0;
        const fillColor = colorPalette[index];

        return {
            fillColor: fillColor,
            fillOpacity: 0.3,
            weight: 1,
            color: 'white'
        };
    }

    // Function to get a contrasting color for the text
    function getContrastingColor(color) {
    const hexColor = color.substr(1); // Remove the leading #
    const rgbColor = parseInt(hexColor, 16); // Convert the hex color to RGB
    const brightness = (rgbColor >> 16) + (rgbColor >> 8 & 0x00FF) + (rgbColor & 0x0000FF); // Calculate the brightness

    // Use a light color for dark backgrounds and a dark color for light backgrounds
    return brightness > 382.5 ? '#000000' : '#FFFFFF'; // Brightness threshold: 382.5 (out of 765)
    }

    // Function to handle different types of features (e.g., points, lines, and polygons)
    function onEachFeature(feature, layer) {
      // You can add popups, event listeners, or other interactions for each feature here
      layer.bindPopup(feature.properties.name || 'No name available');
      // Add click event listener
      layer.on('click', function (e) {
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

          // Event listeners for checkbox changes
          const residentialCheckbox = document.getElementById('residentialCheckbox');
          const commercialCheckbox = document.getElementById('commercialCheckbox');
          const agriCheckbox = document.getElementById('agriCheckbox');

          residentialCheckbox.addEventListener('change', function () {
            const residentialShapes = ['Barangay I', 'Barangay IV']; // Replace with your residential shape names
            if (this.checked) {
              residentialShapes.forEach(shape => {
                const residentialPolygons = polygonLayer.getLayers().filter(layer => layer.feature.properties.name === shape);
                residentialPolygons.forEach(polygon => map.addLayer(polygon));
              });
            } else {
              residentialShapes.forEach(shape => {
                const residentialPolygons = polygonLayer.getLayers().filter(layer => layer.feature.properties.name === shape);
                residentialPolygons.forEach(polygon => map.removeLayer(polygon));
              });
            }
          });

          commercialCheckbox.addEventListener('change', function () {
            const commercialShapes = ['Barangay V', 'Barangay III', 'Barangay II']; // Replace with your commercial shape names
            if (this.checked) {
              commercialShapes.forEach(shape => {
                const commercialPolygons = polygonLayer.getLayers().filter(layer => layer.feature.properties.name === shape);
                commercialPolygons.forEach(polygon => map.addLayer(polygon));
              });
            } else {
              commercialShapes.forEach(shape => {
                const commercialPolygons = polygonLayer.getLayers().filter(layer => layer.feature.properties.name === shape);
                commercialPolygons.forEach(polygon => map.removeLayer(polygon));
              });
            }
          });

          agriCheckbox.addEventListener('change', function () {
            const agriShapes = ['Bagumbayan']; // Replace with your agricultural shape names
            if (this.checked) {
              agriShapes.forEach(shape => {
                const agriPolygons = polygonLayer.getLayers().filter(layer => layer.feature.properties.name === shape);
                agriPolygons.forEach(polygon => map.addLayer(polygon));
              });
            } else {
              agriShapes.forEach(shape => {
                const agriPolygons = polygonLayer.getLayers().filter(layer => layer.feature.properties.name === shape);
                agriPolygons.forEach(polygon => map.removeLayer(polygon));
              });
            }
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
