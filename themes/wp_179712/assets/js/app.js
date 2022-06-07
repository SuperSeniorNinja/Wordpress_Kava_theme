/* -----------------------------------------------
/* How to use? : Check the GitHub README
/* ----------------------------------------------- */

jQuery(document).ready(function($) {

  /* To load a config file (particles.json) you need to host this demo (MAMP/WAMP/local)... */
  particlesJS.load('particles-js', window.app_settings.json, function() {
    console.log('particles.js loaded - callback');
  });
  particlesJS.load('particles-js-1', window.app_settings.json, function() {
    console.log('particles.js loaded - callback 2');
  });
});