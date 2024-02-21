<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Page Title</title>

    <!-- Include Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    

    <!-- Include Bootstrap Toggle CSS -->
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap2-toggle.min.css" rel="stylesheet">
</head>
<body>

<div class="col-auto col-md-4 col-xl-2 px-sm-2 sticky-top" id="sidebar" style="width: 200px;">
    <div class="d-flex flex-column align-items-middle px-1 position-fixed bg-body-secondary col-xl-2" id="sidebar">
        <h2>Sectors</h2>
        <hr>
        <div id="scrollableContainer" style="height: 600px; overflow-y: auto;">
            <div id="sectorDropdowns">
                <!-- Dropdowns for each sector will be dynamically added here -->
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap2-toggle.min.js"></script>

<!-- Include jQuery, Firebase, and your custom scripts -->
<script src="https://www.gstatic.com/firebasejs/9.0.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.0.1/firebase-database-compat.js"></script>

<script>
  
    const firebaseConfig = {
        apiKey: "AIzaSyCDKaEz_uAye0bpcefq-lYp8VSOyfNAdSA",
  authDomain: "band-o-bast-7c5f1.firebaseapp.com",
  databaseURL: "https://band-o-bast-7c5f1-default-rtdb.firebaseio.com",
  projectId: "band-o-bast-7c5f1",
  storageBucket: "band-o-bast-7c5f1.appspot.com",
  messagingSenderId: "1066451383075",
  appId: "1:1066451383075:web:9b4b0f1e1baf6976621296"
        // ...
    };
    </script>
    <script>

firebase.initializeApp(firebaseConfig);

function updateSectorDropdowns() {
  var sectorDropdowns = document.getElementById("sectorDropdowns");

  // Reference to the geofences in the database
  var sectorsRef = firebase.database().ref('geofences');

  // Fetch sector data
  sectorsRef.once('value').then(function (snapshot) {
    snapshot.forEach(function (childSnapshot) {
      var sectorKey = childSnapshot.key;
      var sectorData = childSnapshot.val();

      // Check if sector data is valid
      if (sectorData && sectorData.name) {
        // Create a new dropdown for each sector
        var sectorDropdown = document.createElement("div");
        sectorDropdown.classList.add("dropdown", "mb-3");

        // Create the dropdown button
        var dropdownButton = document.createElement("button");
        dropdownButton.classList.add("btn", "btn-secondary", "dropdown-toggle", "dropdown-button");
        dropdownButton.type = "button";
        dropdownButton.textContent = sectorData.name;

        // Add Bootstrap attributes for the dropdown
        dropdownButton.setAttribute("data-toggle", "dropdown");
        dropdownButton.setAttribute("aria-haspopup", "true");
        dropdownButton.setAttribute("aria-expanded", "false");

        // Add click event to fetch and display ground personnel
        dropdownButton.addEventListener("click", function () {
          getGroundPersonnelForSector(sectorKey);
        });

        sectorDropdown.appendChild(dropdownButton);

        // Create the dropdown menu
        var dropdownMenu = document.createElement("div");
        dropdownMenu.classList.add("dropdown-menu");
        dropdownMenu.setAttribute("data-sector", sectorKey); // Add data-sector attribute

        sectorDropdown.appendChild(dropdownMenu);

        // Append the dropdown to the container
        sectorDropdowns.appendChild(sectorDropdown);
      }
    });
  });
}

// Function to fetch and display ground personnel for a specific sector
function getGroundPersonnelForSector(sectorKey) {
  var dropdownMenu = document.querySelector(div[data-sector="${sectorKey}"] .dropdown-menu);

  // Clear existing dropdown items
  dropdownMenu.innerHTML = "";

  // Reference to ground personnel in the database for the specified sector
  var groundPersonnelRef = firebase.database().ref('geofences').child(sectorKey).child('options');

  // Fetch ground personnel data
  groundPersonnelRef.once('value').then(function (snapshot) {
    snapshot.forEach(function (childSnapshot) {
      var personnelData = childSnapshot.val();

      // Check if personnel data is valid
      if (personnelData && personnelData.name) {
        // Create a new dropdown item for each personnel
        var dropdownItem = document.createElement("a");
        dropdownItem.classList.add("dropdown-item");
        dropdownItem.href = "#";
        dropdownItem.textContent = personnelData.name;

        dropdownMenu.appendChild(dropdownItem);
      }
    });
  });
}

// Initial call to populate the dropdowns on page load
updateSectorDropdowns();

</script>

</body>
</html>