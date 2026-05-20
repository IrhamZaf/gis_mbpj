/**
 * Survey report map — survives Livewire file uploads via wire:ignore on #gis-map + recovery hooks.
 */
import './gis-map-picker.js';
import { bootSurveyReportMap } from './survey-report-map-boot.js';

const watchedMapIds = new Set();

function parseJsonAttr(value, fallback = null) {
  if (!value) return fallback;
  try {
    return JSON.parse(value);
  } catch {
    return fallback;
  }
}

function readMapConfig(root) {
  const lat = root.dataset.initialLat;
  const lng = root.dataset.initialLng;
  return {
    mapElementId: root.dataset.mapElementId || 'gis-map',
    initialLat: lat !== undefined && lat !== '' ? parseFloat(lat) : null,
    initialLng: lng !== undefined && lng !== '' ? parseFloat(lng) : null,
    initialZoom: parseInt(root.dataset.initialZoom || '14', 10),
    initialGisData: parseJsonAttr(root.dataset.initialGisData),
    initialLocationLabel: root.dataset.initialLocationLabel || '',
  };
}

function wireCall(wire, method, ...args) {
  if (!wire) return;
  if (typeof wire[method] === 'function') {
    wire[method](...args);
    return;
  }
  if (typeof wire.$call === 'function') {
    wire.$call(method, ...args);
    return;
  }
  if (typeof wire.call === 'function') {
    wire.call(method, ...args);
  }
}

function resolveWire(root) {
  const host = root.closest('[wire\\:id]');
  const wireId = host?.getAttribute('wire:id');
  if (wireId && typeof Livewire !== 'undefined' && typeof Livewire.find === 'function') {
    return Livewire.find(wireId);
  }
  return window.surveyReportLivewire ?? null;
}

function buildConfig(root, wire = null) {
  const config = readMapConfig(root);
  config.livewire = wire;
  config.onCoordinatesChange = (lat, lng, label) => wireCall(wire, 'setCoordinates', lat, lng, label);
  config.onGisDataChange = (data) => wireCall(wire, 'setGisData', data);
  return config;
}

function getFormRoot() {
  return document.querySelector('[data-survey-report-form]');
}

function scheduleMapRecovery() {
  [0, 80, 250, 600, 1200].forEach((ms) => {
    setTimeout(ensureMapAlive, ms);
  });
}

/** Re-create map if Livewire morph wiped the Leaflet container. */
function ensureMapAlive() {
  const root = getFormRoot();
  if (!root || !window.gisMapPicker) return;

  const mapId = root.dataset.mapElementId || 'gis-map';
  const el = document.getElementById(mapId);
  if (!el) return;

  if (window.gisMapPicker.mapContainerIsAlive?.(mapId)) {
    window.gisMapPicker.refreshMapLayout?.();
    return;
  }

  const config = window.__surveyReportMapConfig || buildConfig(root, resolveWire(root));
  window.__surveyReportMapConfig = config;
  window.surveyReportLivewire = config.livewire ?? resolveWire(root);
  window.gisMapPicker.destroyMapInstance?.();
  bootSurveyReportMap(config);
}

function watchMapContainer(mapId) {
  if (watchedMapIds.has(mapId)) return;
  const el = document.getElementById(mapId);
  if (!el) return;

  watchedMapIds.add(mapId);

  const observer = new MutationObserver(() => {
    const host = document.getElementById(mapId);
    if (!host) return;
    const alive =
      host.classList.contains('leaflet-container') || host.querySelector('.leaflet-container');
    if (!alive) {
      scheduleMapRecovery();
    }
  });

  observer.observe(el, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] });
}

function paintMapLayers(layers) {
  ensureMapAlive();

  if (!window.gisMapPicker?.getMap?.()) return;

  window.gisMapPicker.clearSurveyLayers?.();

  if (!Array.isArray(layers)) return;

  let i = 0;
  layers.forEach((layer) => {
    if (layer.parse_status === 'ok' && layer.parsed_data && layer.parsed_data.type !== '1d') {
      window.gisMapPicker.addSurveyLayer(layer.parsed_data, layer.file_name ?? '', i > 0);
      if (layer.parsed_data.type === '2d') {
        window._lastSurvey2dParsed = layer.parsed_data;
      }
      i += 1;
    }
  });

  window.gisMapPicker.refreshMapLayout?.();
}

function loadInitialLayers(root) {
  const layers = parseJsonAttr(root.dataset.surveyLayers, []);
  if (layers.length) {
    paintMapLayers(layers);
  }
}

function bootSurveyReportForm() {
  const root = getFormRoot();
  if (!root) return;

  const wire = resolveWire(root);
  const config = buildConfig(root, wire);
  window.__surveyReportMapConfig = config;
  window.surveyReportLivewire = wire;

  watchMapContainer(config.mapElementId);
  bootSurveyReportMap(config);

  setTimeout(() => {
    ensureMapAlive();
    loadInitialLayers(root);
  }, 200);
}

window.initSurveyReportForm = function (root, wire) {
  if (root) {
    window.surveyReportLivewire = wire ?? resolveWire(root);
  }
  bootSurveyReportForm();
};

function registerLivewireHooks() {
  if (window.__surveyReportHooksRegistered) return;
  window.__surveyReportHooksRegistered = true;

  Livewire.on('survey-map-layers-updated', (payload) => {
    const layers = payload?.layers ?? payload?.[0]?.layers ?? payload;
    paintMapLayers(layers);
    scheduleMapRecovery();
  });

  Livewire.hook('commit', ({ succeed }) => {
    succeed(() => {
      if (!getFormRoot()) return;
      scheduleMapRecovery();
    });
  });

  Livewire.hook('morph.updated', () => {
    if (!getFormRoot()) return;
    scheduleMapRecovery();
  });
}

function tryBoot() {
  if (typeof Livewire !== 'undefined' && Livewire.hook) {
    registerLivewireHooks();
  }
  if (getFormRoot()) {
    bootSurveyReportForm();
    return true;
  }
  return false;
}

document.addEventListener('livewire:init', () => tryBoot());
document.addEventListener('livewire:navigated', () => {
  watchedMapIds.clear();
  tryBoot();
});
document.addEventListener('livewire:upload-finish', () => scheduleMapRecovery());
document.addEventListener('DOMContentLoaded', () => tryBoot());

// Scripts in @stack often load after livewire:init — retry until form + Livewire are ready.
let bootAttempts = 0;
const bootInterval = setInterval(() => {
  bootAttempts += 1;
  tryBoot();
  if (bootAttempts > 60) clearInterval(bootInterval);
}, 100);
