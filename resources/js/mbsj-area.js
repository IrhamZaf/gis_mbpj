/**
 * Approximate MBSJ (Majlis Bandaraya Subang Jaya) jurisdiction bounds.
 * Keep in sync with App\Support\MbsjArea.
 */
export const MBSJ_CENTER = { lat: 3.0565, lng: 101.5851 };

/** south, west, north, east */
export const MBSJ_BBOX = {
  south: 2.97,
  west: 101.51,
  north: 3.165,
  east: 101.69,
};

/** Nominatim viewbox: west,south,east,north */
export const MBSJ_VIEWBOX = `${MBSJ_BBOX.west},${MBSJ_BBOX.south},${MBSJ_BBOX.east},${MBSJ_BBOX.north}`;

export function isInsideMbsj(lat, lng) {
  const la = Number(lat);
  const ln = Number(lng);
  if (!Number.isFinite(la) || !Number.isFinite(ln)) return false;
  return (
    la >= MBSJ_BBOX.south &&
    la <= MBSJ_BBOX.north &&
    ln >= MBSJ_BBOX.west &&
    ln <= MBSJ_BBOX.east
  );
}

/** Leaflet LatLngBounds [[south, west], [north, east]] */
export function mbsjLeafletBounds(L) {
  return L.latLngBounds(
    [MBSJ_BBOX.south, MBSJ_BBOX.west],
    [MBSJ_BBOX.north, MBSJ_BBOX.east]
  );
}

export function applyMbsjMapLimits(map, L, options = {}) {
  if (!map || !L) return null;

  const bounds = mbsjLeafletBounds(L);
  map.setMaxBounds(bounds.pad(0.02));
  map.options.maxBoundsViscosity = 1.0;
  map.setMinZoom(options.minZoom ?? 12);

  let outline = null;
  if (options.drawOutline !== false) {
    outline = L.rectangle(bounds, {
      color: '#0d6efd',
      weight: 2,
      dashArray: '6 4',
      fill: false,
      interactive: false,
      className: 'mbsj-boundary-outline',
    }).addTo(map);
  }

  return { bounds, outline };
}
