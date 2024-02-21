var map = L.map('map').setView([20.5937, 78.9629], 5);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

var drawnItems = new L.FeatureGroup();
map.addLayer(drawnItems);

var drawControl;
var drawingControlsVisible = false;

function initializeDrawControls() {
  drawControl = new L.Control.Draw({
    draw: {
      polygon: true,
      rectangle: true,
      circle: true,
      marker: true,
      polyline: true
    },
    edit: {
      featureGroup: drawnItems,
      remove: true
    }
  });
  map.addControl(drawControl);
  drawingControlsVisible = true;
}

function toggleDrawControls() {
  if (drawingControlsVisible) {
    map.removeControl(drawControl);
    drawingControlsVisible = false;
  } else {
    initializeDrawControls();
  }
}

var firebaseConfig = {
  apiKey: "AIzaSyCDKaEz_uAye0bpcefq-lYp8VSOyfNAdSA",
  authDomain: "band-o-bast-7c5f1.firebaseapp.com",
  databaseURL: "https://band-o-bast-7c5f1-default-rtdb.firebaseio.com",
  projectId: "band-o-bast-7c5f1",
  storageBucket: "band-o-bast-7c5f1.appspot.com",
  messagingSenderId: "1066451383075",
  appId: "1:1066451383075:web:9b4b0f1e1baf6976621296",
  measurementId: "G-5NCH6X0MX6"
};

firebase.initializeApp(firebaseConfig);

const database = firebase.database();


var devicesLayer = L.layerGroup();
map.addLayer(devicesLayer);

function updateDeviceMarkers(deviceData) {
  devicesLayer.clearLayers();

  if (deviceData) {
    Object.values(deviceData).forEach(function (device) {
      if (device && device.latitude && device.longitude) {
        var deviceMarker = L.marker([device.latitude, device.longitude])
          .bindPopup(`<b>Device ID:</b> ${device.device_id}<br><b>Location:</b> ${device.latitude}, ${device.longitude}`);
        devicesLayer.addLayer(deviceMarker);
      }
    });
  }
}

function fetchOptionsFromFirebase() {
  var geofencesRef = database.ref('DeviceDetails');

  geofencesRef.once('value')
  .then(function (snapshot) {
    console.log('Data from Firebase:', snapshot.val());
    var geofences = snapshot.val();
      var deviceIds = [];

      if (geofences) {
        Object.values(geofences).forEach(function (geofence) {
          if (geofence && geofence.device_id) {
            deviceIds.push(geofence.device_id);
          }
        });
      }

      console.log('Device IDs from Firebase:', deviceIds);

      var selectElement = $('#options');
      selectElement.empty();

      deviceIds.forEach(function (deviceId) {
        var optionElement = $('<option>');
        optionElement.val(deviceId).text(deviceId);
        selectElement.append(optionElement);
      });
    })
    .catch(function (error) {
      console.error("Error fetching device IDs from Firebase:", error);
    });
}

var geofenceId;

function generateRandomId() {
  return Math.random().toString(36).substr(2, 10); // Adjust the length as needed
}

map.on(L.Draw.Event.CREATED, function (event) {
  var layer = event.layer;
  drawnItems.addLayer(layer);
  geofenceCoordinates = layer.getLatLngs()[0].map(point => [point.lat, point.lng]);
  showForm();
  geofenceId = generateRandomId();
  console.log(geofenceId);
});

$('#firebaseForm').submit(function (event) {
  event.preventDefault();

  if (!geofenceId) {
    geofenceId = generateRandomId();
    console.log(geofenceId);
  }

  var formData = {
    geofenceId: geofenceId,
    name: $('#name').val(),
    date: $('#date').val(),
    durationfrom: $('#durationFrom').val(),
    durationto: $('#durationTo').val(),
    options: Array.from($('#options')[0].selectedOptions).map(option => option.value),
    geofenceCoordinates: geofenceCoordinates
  };
  
  var formsRef = database.ref('geofences');
  formsRef.child(geofenceId).set(formData)
    .then(function () {
      console.log("Form Data Saved to Firebase:", formData);
      $('#firebaseForm').trigger('reset');
      $('#form-container').modal('hide');
  
      fetchGeofenceAndDevices(geofenceId);
    })
    .catch(function (error) {
      console.error("Error saving form data:", error);
    });  
});

function generateGeofenceId() {
  return $('#name').val() + $('#date').val();
}

function showForm() {
  $('#form-container').modal('show');
}

function showToast(title, message, bgClass) {
  var toast = $('<div class="toast" role="alert" aria-live="assertive" aria-atomic="true">')
    .addClass(bgClass)
    .appendTo(document.body);

  var toastHeader = $('<div class="toast-header">')
    .appendTo(toast);

  $('<strong class="me-auto">').text(title).appendTo(toastHeader);

  var toastBody = $('<div class="toast-body">').text(message).appendTo(toast);

  var autoHide = 5000;
  var delay = 1000;

  var bsToast = new bootstrap.Toast(toast, {
    animation: true,
    autohide: true,
    delay: autoHide
  });

  bsToast.show();

  setTimeout(function () {
    toast.remove();
  }, autoHide + delay);
}

function addLabelToPolygon(polygon, label) {
  var bounds = polygon.getBounds();
  var center = bounds.getCenter();
  var labelElement = $('<div class="geofence-label">').text(label);

  labelElement.css({
    top: map.latLngToLayerPoint(center).y + 'px',
    left: map.latLngToLayerPoint(center).x + 'px'
  });

  map.getContainer().appendChild(labelElement[0]);
}

function clearCoordinates() {
  geofenceCoordinates = [];
  drawnItems.clearLayers();
}

function isPointInsideGeofence(point) {
  if (!geofenceCoordinates || geofenceCoordinates.length === 0) {
    return false;
  }

  var lat = point.lat;
  var lng = point.lng;
  var polygon = {
    type: 'Polygon',
    coordinates: [geofenceCoordinates.map(coord => [coord[1], coord[0]])]
  };
  var isInside = leafletPip.pointInLayer([lng, lat], L.geoJSON(polygon));
  return isInside.length > 0;
}

$('#coordinatesForm').submit(function (event) {
  event.preventDefault();
  if (geofenceCoordinates.length < 3) {
    alert('Please add at least three coordinates for the geofence.');
    return;
  }
});

$(document).ready(function () {
  fetchOptionsFromFirebase();

  $('#form-container').on('hidden.bs.modal', function () {
    $('#firebaseForm').trigger('reset');
  });

  $('#form-container-submit').on('click', function () {
    $('#form-container').modal('hide');
  });

  $('#addMarkersBtn').on('click', function () {
    if (!geofenceId) {
      console.error('Geofence ID is not defined.');
      return;
    }
  
    if (!deviceData) {
      console.error('Device data is not available.');
      return;
    }
  
    var selectedDevices = Array.from($('#options option:selected')).map(option => option.value);
  
    if (selectedDevices.length === 0) {
      alert('Please select devices to add markers.');
      return;
    }
  
    devicesLayer.clearLayers();
  
    selectedDevices.forEach(function (deviceId) {
      var device = deviceData[deviceId];
      if (device && device.latitude && device.longitude) {
        var deviceMarker = L.marker([device.latitude, device.longitude])
          .bindPopup(`<b>Device ID:</b> ${device.device_id}<br><b>Location:</b> ${device.latitude}, ${device.longitude}`);
        devicesLayer.addLayer(deviceMarker);
      }
    });
  });
  
});

function fetchGeofenceAndDevices(selectedDevices) {
  if (typeof selectedDevices === 'string') {
    selectedDevices = [selectedDevices];
  }

  if (Array.isArray(selectedDevices) && selectedDevices.length > 0) {
    selectedDevices.forEach(function (deviceId) {
      deviceId = "100";
      var deviceRef = database.ref('gpsData/' + deviceId);

      deviceRef.once('value')
        .then(function (snapshot) {
          var deviceData = snapshot.val();

          if (deviceData) {
            var dataParts = deviceData.split(',');

            // Make sure dataParts has at least 3 elements before accessing them
            if (dataParts.length >= 3) {
              var latitude = parseFloat(dataParts[1].split(':')[1]);
              var longitude = parseFloat(dataParts[2].split(':')[1]);

              var parsedDeviceData = {
                device_id: deviceId,
                latitude: latitude,
                longitude: longitude
              };

              updateMapWithDeviceCoordinates(parsedDeviceData);
            } else {
              console.log('Invalid device data format for ' + deviceId);
            }
          } else {
            console.log('Device data not available for ' + deviceId);
          }

          updateDeviceList();
        })
        .catch(function (error) {
          console.error('Error fetching device details:', error);
        });
    });
  } else {
    console.error('Selected devices are not in the expected format:', selectedDevices);
  }
}

function fetchDeviceDetails(selectedDevices) {
  selectedDevices.forEach(function (deviceId) {
    var deviceRef = database.ref('gpsData/' + deviceId);

    deviceRef.once('value')
      .then(function (snapshot) {
        var deviceData = snapshot.val();

        if (deviceData) {
          var dataParts = deviceData.split(',');
          var latitude = parseFloat(dataParts[1].split(':')[1]);
          var longitude = parseFloat(dataParts[2].split(':')[1]);

          var parsedDeviceData = {
            device_id: deviceId,
            latitude: latitude,
            longitude: longitude
          };

          updateMapWithDeviceCoordinates(parsedDeviceData);
        } else {
          console.log('Device data not available for ' + deviceId);
        }

        updateDeviceList();
      })
      .catch(function (error) {
        console.error('Error fetching device details:', error);
      });
  });
}

function updateMapWithDeviceCoordinates(deviceData) {
  if (deviceData && deviceData.latitude && deviceData.longitude) {
    var deviceMarker = L.marker([deviceData.latitude, deviceData.longitude])
      .bindPopup(`<b>Device ID:</b> ${deviceData.device_id}<br><b>Location:</b> ${deviceData.latitude}, ${deviceData.longitude}`);
    devicesLayer.addLayer(deviceMarker);

    map.setView([deviceData.latitude, deviceData.longitude], 13);
  }
}

function updateDeviceList() {
  var deviceList = $('#options');
  deviceList.empty();

  if (deviceData) {
    Object.values(deviceData).forEach(function (device) {
      if (device && device.device_id) {
        var listItem = $('<option>').val(device.device_id).text(device.device_id);
        deviceList.append(listItem);
      }
    });
  }
}

database.ref('DeviceDetails').on('value', function (snapshot) {
  deviceData = snapshot.val();

  setTimeout(function () {
    updateDeviceList();
  }, 0);
});


// ...

var sectorsLayer = L.layerGroup();
map.addLayer(sectorsLayer);

function fetchSectorsFromFirebase() {
  var sectorsRef = database.ref('geofences');

  sectorsRef.once('value')
    .then(function (snapshot) {
      var sectorsData = snapshot.val();

      if (sectorsData) {
        Object.values(sectorsData).forEach(function (sector) {
          drawSectorOnMap(sector);
        });
      }
    })
    .catch(function (error) {
      console.error("Error fetching sectors from Firebase:", error);
    });
}


// Function to draw ground personnel markers within the sector
function drawGroundPersonnelMarkers(sector) {
  if (sector && sector.options && sector.geofenceCoordinates) {
    sector.options.forEach(function (deviceId) {
      // Fetch device details from the database
      var deviceRef = database.ref('DeviceDetails/' + deviceId);

      deviceRef.once('value').then(function (snapshot) {
        var deviceData = snapshot.val();

        if (deviceData && deviceData.latitude && deviceData.longitude) {
          var latitude = parseFloat(deviceData.latitude);
          var longitude = parseFloat(deviceData.longitude);

          if (!isNaN(latitude) && !isNaN(longitude)) {
            // Create a marker at the original location with a location symbol
            var personnelMarker = L.marker([latitude, longitude], {
              icon: L.divIcon({
                className: 'location-icon',
                html: '<i class="fas fa-map-marker-alt"></i>', // Use your preferred location symbol here
              }),
            }).addTo(sectorsLayer);
            

            // Bind the personnel ID as a popup to the marker
            personnelMarker.bindPopup('<b>Personnel ID:</b> ' + deviceId);
          }
        }
      }).catch(function (error) {
        console.error('Error fetching device details for ' + deviceId + ':', error);
      });
    });
  }
}



function drawSectorOnMap(sector) {
  if (sector && sector.geofenceCoordinates) {
    var polygon = L.polygon(sector.geofenceCoordinates, { color: 'red' }).addTo(sectorsLayer);

    // Optionally, you can add a popup with sector details
    polygon.bindPopup(`<b>Sector ID:</b> ${sector.geofenceId}<br><b>Name:</b> ${sector.name}<br><b>Date:</b> ${sector.date}`);

    // Draw ground personnel markers within the sector
    drawGroundPersonnelMarkers(sector);
  }
}

$(document).ready(function () {
  fetchSectorsFromFirebase();
  // ... Other code
});

function handleMarkerDraw(marker) {
  var latlng = marker.getLatLng();
  // You can save the marker coordinates to the database or perform other actions
  console.log('Marker drawn at:', latlng);

  // Optionally, show the form for additional details
  showForm({ latlng: latlng });
}

// Function to handle drawing of geofence (sector)
function handleGeofenceDraw(geofence) {
  var geofenceCoordinates = geofence.getLatLngs()[0].map(point => [point.lat, point.lng]);
  // Save geofenceCoordinates to the database or perform other actions
  console.log('Geofence drawn with coordinates:', geofenceCoordinates);

  // Optionally, show the form for additional details
  showForm({ geofenceCoordinates: geofenceCoordinates });
}


function showForm(details) {
  $('#form-container').modal('show');

  // Check if additional details are provided
  if (details && details.latlng) {
    // Populate form with marker coordinates
    $('#latitude').val(details.latlng.lat);
    $('#longitude').val(details.latlng.lng);
  } else if (details && details.geofenceCoordinates) {
    // Populate form with sector coordinates
    // You may want to store and manage the sector data in your application
    // For demonstration purposes, I'm using a global variable here
    geofenceCoordinates = details.geofenceCoordinates;
  }
}

// Form submission logic
$('#firebaseForm').submit(function (event) {
  event.preventDefault();

  if (!geofenceCoordinates) {
    console.error('Geofence coordinates not available.');
    return;
  }

  // Collect form data
  var formData = {
    // ... Other form fields
    latitude: $('#latitude').val(),
    longitude: $('#longitude').val(),
    // ... Other form fields
  };

  // Check if the submitted location is within the geofence
  if (isPointInsideGeofence({ lat: formData.latitude, lng: formData.longitude })) {
    // Save ground personnel data to the database or perform other actions
    saveGroundPersonnelData(formData);
    showToast('Success', 'Ground personnel added successfully.', 'bg-success');
  } else {
    showToast('Error', 'Ground personnel must be within the sector.', 'bg-danger');
  }

  // Optionally, close the form
  $('#form-container').modal('hide');
});

// Function to save ground personnel data
function saveGroundPersonnelData(formData) {
  // Save the ground personnel data to the database
  // You may want to use Firebase or any other database of your choice
  // For demonstration purposes, I'm logging the data to the console
  console.log('Ground Personnel Data:', formData);
}

// ...