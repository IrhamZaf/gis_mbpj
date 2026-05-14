import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('gisSurveyPreviewMap');
  if (!el) return;

  const satelliteTile = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    maxZoom: 19,
    attribution: '&copy; Esri, Maxar'
  });

  const map = L.map(el, { layers: [satelliteTile] }).setView([3.107, 101.607], 12);
  
  const input = document.querySelector('input[name="geojson_file"]');
  if (input) {
    input.addEventListener('change', () => {
      const file = input.files && input.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = () => {
        try {
          const gj = JSON.parse(String(reader.result));
          const layer = L.geoJSON(gj, {
            style: {
              color: '#00e676',
              weight: 3,
              opacity: 1,
              fillOpacity: 0.3
            }
          }).addTo(map);
          if (layer.getBounds().isValid()) map.fitBounds(layer.getBounds(), { padding: [20, 20] });
        } catch (e) {
          /* ignore invalid */
        }
      };
      reader.readAsText(file);
    });
  }
  setTimeout(() => map.invalidateSize(), 400);
});
