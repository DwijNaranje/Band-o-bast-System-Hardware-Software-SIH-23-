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

    <!-- Include Leaflet CSS and JavaScript -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>
<body>

<div class="col-auto col-md-4 col-xl-2 px-sm-2 sticky-top" id="sidebar" style="width: 200px;">
    <div class="d-flex flex-column align-items-start px-1 position-fixed bg-body-secondary col-xl-2" id="sidebar">
    <p id="countdownTimer"></p>
        <button class="btn btn-secondary align-items-center" onclick="startCountdown()">Start Countdown</button>
    
    <br>
        <h2>Sectors</h2>
        <hr>
        <div id="scrollableContainer" style="height: 400px; overflow-y: auto;">
            <div id="sectorDropdowns">
                <!-- Dropdowns for each sector will be dynamically added here -->
                <div id="officersDetailsContainer">
                    <!-- Officers' details will be displayed here -->
                </div>
                
            </div>
        </div>
        <div id="map" style="height: 200px; margin-top: 20px;"></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap2-toggle.min.js"></script>

<!-- Include jQuery, Firebase, and your custom scripts -->
<script src="https://www.gstatic.com/firebasejs/9.0.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.0.1/firebase-database-compat.js"></script>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js" defer></script>
    <script src="https://unpkg.com/leaflet-draw" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js" defer></script>
    <script src="https://unpkg.com/leaflet-pip/leaflet-pip.js" defer></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js" defer></script>
    <script src="js/sidebar.js" defer></script>

<!--<script>
    const firebaseConfig = {
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

    // Initialize the Leaflet map
    var map = L.map('map').setView([0, 0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);


    
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

                    // Create the Bootstrap Toggle switch
                    var toggleSwitch = document.createElement("input");
                    toggleSwitch.setAttribute("type", "checkbox");
                    toggleSwitch.setAttribute("data-size", "sm");
                    toggleSwitch.setAttribute("data-on", "Active");
                    toggleSwitch.setAttribute("data-off", "Inactive");
                    toggleSwitch.setAttribute("data-toggle", "toggle");

                    // Set the initial state based on the 'isActive' property
                    toggleSwitch.checked = sectorData.isActive;

                    // Add change event to handle sector activation/deactivation
                    toggleSwitch.addEventListener("change", function () {
                        toggleSector(sectorKey, this.checked);
                    });

                    sectorDropdown.appendChild(toggleSwitch);

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

                    // Corrected syntax for data-sector attribute
                    dropdownMenu.setAttribute("data-sector", sectorKey);

                    sectorDropdown.appendChild(dropdownMenu);

                    // Append the dropdown to the container
                    sectorDropdowns.appendChild(sectorDropdown);
                }
            });
        });
    }

    // Function to fetch and display ground personnel for a specific sector
    function getGroundPersonnelForSector(sectorKey) {
        var dropdownMenu = document.querySelector(`div[data-sector="${sectorKey}"] .dropdown-menu`);

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

    // Function to toggle sector activation/deactivation
    function toggleSector(sectorKey, isActive) {
        // Reference to the sector in the database
        var sectorRef = firebase.database().ref('geofences').child(sectorKey);

        // Update the 'isActive' property in the database
        sectorRef.update({
            isActive: isActive
        });
    }

    // Countdown logic
    function startCountdown() {
        var countdownElement = document.getElementById("countdownTimer");

        // Set the date we're counting down to
        var countDownDate = new Date("Jan 5, 2024 15:37:25").getTime();

        // Update the count down every 1 second
        var countdownInterval = setInterval(function() {
            // Get today's date and time
            var now = new Date().getTime();
            
            // Find the distance between now and the count down date
            var distance = countDownDate - now;
            
            // Time calculations for days, hours, minutes, and seconds
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            // Output the result in an element with id="countdownTimer"
            countdownElement.innerHTML = days + "d " + hours + "h " + minutes + "m " + seconds + "s ";
            
            // If the count down is over, clear the interval and display "EXPIRED"
            if (distance < 0) {
                clearInterval(countdownInterval);
                countdownElement.innerHTML = "EXPIRED";
            }
        }, 1000);
    }

    // Initial call to populate the dropdowns and start countdown on page load
    updateSectorDropdowns();
    startCountdown();
</script>-->

</body>
</html>
