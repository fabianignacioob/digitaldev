(function() {
  'use strict';

  // initialize google map
  $(document).ready(initMap);

  function initMap() {
    var mapElement = document.getElementById('map');
    if (mapElement) {
      // for more options go to https://developers.google.com/maps/documentation/javascript/reference#MapOptions
      var mapOptions = {
        zoom: 15,
        center: new google.maps.LatLng(40.7804019, -73.9822179)
      };

      var map = new google.maps.Map(mapElement, mapOptions);

      var icon = {
        url: 'img/pin.png',
        scaledSize: new google.maps.Size(35, 50)
      };

      // marker on the map
      var marker = new google.maps.Marker({
        position: new google.maps.LatLng(40.7804019, -73.9822179),
        map: map
      });
    }
  }
})();
