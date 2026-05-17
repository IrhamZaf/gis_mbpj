/**
 * Config
 * -------------------------------------------------------------------------------------
 * ! IMPORTANT: Make sure you clear the browser local storage In order to see the config changes in the template.
 * ! To clear local storage: (https://www.leadshook.com/help/how-to-clear-local-storage-in-google-chrome-browser/).
 */

'use strict';

import { Helpers } from '../vendor/js/helpers.js';

/* JS global variables
 !Please use the hex color code (#000) here. Don't use rgba(), hsl(), etc
*/
window.config = {
  // global color variables for charts except chartjs
  colors: {
    primary: Helpers.getCssVar('primary'),
    secondary: Helpers.getCssVar('secondary'),
    success: Helpers.getCssVar('success'),
    info: Helpers.getCssVar('info'),
    warning: Helpers.getCssVar('warning'),
    danger: Helpers.getCssVar('danger'),
    dark: Helpers.getCssVar('dark'),
    black: Helpers.getCssVar('pure-black'),
    white: Helpers.getCssVar('white'),
    cardColor: Helpers.getCssVar('paper-bg'),
    bodyBg: Helpers.getCssVar('body-bg'),
    bodyColor: Helpers.getCssVar('body-color'),
    headingColor: Helpers.getCssVar('heading-color'),
    textMuted: Helpers.getCssVar('secondary-color'),
    borderColor: Helpers.getCssVar('border-color')
  },
  colors_label: {
    primary: Helpers.getCssVar('primary-bg-subtle'),
    secondary: Helpers.getCssVar('secondary-bg-subtle'),
    success: Helpers.getCssVar('success-bg-subtle'),
    info: Helpers.getCssVar('info-bg-subtle'),
    warning: Helpers.getCssVar('warning-bg-subtle'),
    danger: Helpers.getCssVar('danger-bg-subtle'),
    dark: Helpers.getCssVar('dark-bg-subtle')
  },
  fontFamily: Helpers.getCssVar('font-family-base'),
  enableMenuLocalStorage: true // Enable menu state with local storage support
};

window.assetsPath = document.documentElement.getAttribute('data-assets-path');
window.baseUrl = document.documentElement.getAttribute('data-base-url') + '/';
window.templateName = document.documentElement.getAttribute('data-template');

