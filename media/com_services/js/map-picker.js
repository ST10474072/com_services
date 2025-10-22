(function(){
  document.addEventListener('DOMContentLoaded', function(){
    if (typeof L === 'undefined') { return; }

    var mapEl = document.getElementById('services-map');
    if (!mapEl) { return; }

    var latInput = document.getElementById('jform_lat');
    var lngInput = document.getElementById('jform_lng');
    var locationInput = document.getElementById('jform_location');

    function parse(val, fallback) {
      var n = parseFloat(val);
      return isFinite(n) ? n : fallback;
    }

    var startLat = parse(latInput && latInput.value, 20.0); // default latitude
    var startLng = parse(lngInput && lngInput.value, 0.0);  // default longitude
    var startZoom = (latInput && latInput.value && lngInput && lngInput.value) ? 13 : 2;

    var map = L.map(mapEl).setView([startLat, startLng], startZoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var marker = L.marker([startLat, startLng], { draggable: true }).addTo(map);

    function updateInputs(latlng) {
      if (latInput) { latInput.value = (latlng.lat).toFixed(7); }
      if (lngInput) { lngInput.value = (latlng.lng).toFixed(7); }
    }

    map.on('click', function(e){
      marker.setLatLng(e.latlng);
      updateInputs(e.latlng);
    });

    marker.on('dragend', function(e){
      updateInputs(marker.getLatLng());
    });

    var searchField = document.getElementById('services-map-search');
    var searchBtn = document.getElementById('services-map-search-btn');

    function doSearch(){
      var q = '';
      if (searchField && searchField.value.trim()) { q = searchField.value.trim(); }
      else if (locationInput && locationInput.value.trim()) { q = locationInput.value.trim(); }
      if (!q) { return; }

      var url = 'https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(q);
      fetch(url, { headers: { 'Accept-Language': document.documentElement.lang || 'en' }})
        .then(function(r){ return r.json(); })
        .then(function(results){
          if (!results || !results.length) { return; }
          var r = results[0];
          var latlng = { lat: parseFloat(r.lat), lng: parseFloat(r.lon) };
          map.setView(latlng, 14);
          marker.setLatLng(latlng);
          updateInputs(latlng);
          if (locationInput && r.display_name) { locationInput.value = r.display_name; }
        })
        .catch(function(){ /* no-op */ });
    }

    if (searchBtn) { searchBtn.addEventListener('click', function(e){ e.preventDefault(); doSearch(); }); }
    if (searchField) { searchField.addEventListener('keydown', function(e){ if (e.key === 'Enter'){ e.preventDefault(); doSearch(); } }); }
  });
})();