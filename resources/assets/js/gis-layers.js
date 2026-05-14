import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('gisLayersMap');
  if (!el) return;

  const darkTile = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    maxZoom: 20,
    attribution: '&copy; CARTO &copy; OSM'
  });

  const map = L.map(el, { layers: [darkTile] }).setView([3.107, 101.607], 12);
  let active = null;

  document.querySelectorAll('.gis-layer-pick').forEach((btn) => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-id');
      if (!id) return;
      fetch(`/api/layers/${id}/geojson`)
        .then((r) => r.json())
        .then((data) => {
          if (active) map.removeLayer(active);
          active = L.geoJSON(data, {
            style: { 
              color: '#60a5fa', 
              weight: 2.5, 
              opacity: 1, 
              fillColor: '#60a5fa',
              fillOpacity: 0.25 
            }
          }).addTo(map);
          if (active.getBounds().isValid()) map.fitBounds(active.getBounds(), { padding: [20, 20] });
        });
    });
  });
  setTimeout(() => map.invalidateSize(), 300);
});
