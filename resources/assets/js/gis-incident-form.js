import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

document.addEventListener('DOMContentLoaded', () => {
  const dateInput = document.querySelector('#date_reported');
  if (dateInput && typeof window.flatpickr === 'function') {
    window.flatpickr(dateInput, { dateFormat: 'Y-m-d', allowInput: true });
  }

  const lat = document.getElementById('latitude');
  const lng = document.getElementById('longitude');
  const el = document.getElementById('gisIncidentMapPicker');
  if (!el || !lat || !lng) return;

  const map = L.map(el).setView([parseFloat(lat.value) || 3.107, parseFloat(lng.value) || 101.607], 14);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
  let marker = L.marker([parseFloat(lat.value), parseFloat(lng.value)]).addTo(map);

  map.on('click', (e) => {
    lat.value = e.latlng.lat.toFixed(7);
    lng.value = e.latlng.lng.toFixed(7);
    if (marker) map.removeLayer(marker);
    marker = L.marker(e.latlng).addTo(map);
  });

  setTimeout(() => map.invalidateSize(), 400);
});
