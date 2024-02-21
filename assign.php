<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <?php include('php/links.php'); ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw/dist/leaflet.draw.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <!-- Font Awesome for map icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-p5U7bh6O78DumT5Jq0usqLChBxZDcJLqf9UgdPa3WmtFVuQskPso9efBic+21lEoEcklmyS0vqG6oGq2xPuOwA==" crossorigin="anonymous" />

    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include('components/titlebar.php'); ?>
        </div>
        <div class="row">
            <?php include('components/sidebar.php'); ?>
            <div class="col my-1 ps-0 me-2 border border-dark">
                <div id="map"></div>
            </div>
            <?php include('components/sidebar_R.php'); ?>
          
        </div>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js" defer></script>
    <script src="https://unpkg.com/leaflet-draw" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js" defer></script>
    <script src="https://unpkg.com/leaflet-pip/leaflet-pip.js" defer></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js" defer></script>
    <script src="js/sidebar.js" defer></script>
   
</body>
</html>