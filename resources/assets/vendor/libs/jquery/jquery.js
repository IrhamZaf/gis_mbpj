import jQuery from 'jquery';

const $ = jQuery;
try {
  window.jQuery = window.$ = jQuery;
} catch (e) {}

export { jQuery, $ };
