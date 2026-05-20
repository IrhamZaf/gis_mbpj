import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { glob } from 'glob';
import path from 'path';

/**
 * Collect all page‑level JS files so they are individually compiled
 * and can be loaded with @vite() on demand.
 */
function getPageJsFiles() {
  const files = glob.sync('resources/assets/js/*.js');
  return files;
}

export default defineConfig({
  plugins: [
    laravel({
      input: [
        // ── Core CSS ──────────────────────────────────────────
        'resources/css/app.css',
        'resources/assets/vendor/fonts/iconify/iconify.css',
        'resources/assets/css/demo.css',

        // ── Vendor SCSS ───────────────────────────────────────
        'resources/assets/vendor/libs/node-waves/node-waves.scss',
        'resources/assets/vendor/libs/pickr/pickr-themes.scss',
        'resources/assets/vendor/scss/core.scss',
        'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss',
        'resources/assets/vendor/libs/typeahead-js/typeahead.scss',

        // ── Page Auth SCSS ────────────────────────────────────
        'resources/assets/vendor/scss/pages/page-auth.scss',

        // ── Core JS ───────────────────────────────────────────
        'resources/js/app.js',
        'resources/js/gis-overview-map.js',
        'resources/js/gis-map-picker.js',
        'resources/js/gis-survey-preview.js',
        'resources/js/survey-report-form.js',
        'resources/js/report-map-picker.js',
        'resources/assets/vendor/js/helpers.js',
        'resources/assets/vendor/js/template-customizer.js',
        'resources/assets/js/config.js',
        'resources/assets/js/customizer-init.js',

        // ── Vendor JS ─────────────────────────────────────────
        'resources/assets/vendor/libs/jquery/jquery.js',
        'resources/assets/vendor/libs/popper/popper.js',
        'resources/assets/vendor/js/bootstrap.js',
        'resources/assets/vendor/libs/node-waves/node-waves.js',
        'resources/assets/vendor/libs/@algolia/autocomplete-js.js',
        'resources/assets/vendor/libs/pickr/pickr.js',
        'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
        'resources/assets/vendor/libs/hammer/hammer.js',
        'resources/assets/vendor/js/menu.js',
        'resources/assets/js/main.js',

        // ── Page JS (auto-discovered) ────────────────────────
        ...getPageJsFiles(),
      ],
      refresh: true,
    }),
  ],
  css: {
    preprocessorOptions: {
      scss: {
        // Silence deprecation warnings from Vuexy vendor SCSS
        silenceDeprecations: ['mixed-decls', 'color-functions', 'global-builtin', 'import'],
      },
    },
  },
  server: {
    watch: {
      ignored: ['**/storage/framework/views/**'],
    },
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources'),
    },
  },
});

