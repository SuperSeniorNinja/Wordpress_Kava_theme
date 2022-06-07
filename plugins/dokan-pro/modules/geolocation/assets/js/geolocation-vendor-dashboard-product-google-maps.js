/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./modules/geolocation/assets/src/js/vendor-dashboard-product-google-maps.js":
/*!***********************************************************************************!*\
  !*** ./modules/geolocation/assets/src/js/vendor-dashboard-product-google-maps.js ***!
  \***********************************************************************************/
/***/ (() => {

eval("(function ($) {\n  if (!$('#dokan-geolocation-product-location').length) {\n    return;\n  }\n\n  var gmap, marker, address, geocoder;\n\n  function initMap() {\n    var lat = $('[name=\"_dokan_geolocation_product_dokan_geo_latitude\"]').val(),\n        lng = $('[name=\"_dokan_geolocation_product_dokan_geo_longitude\"]').val(),\n        map_area = $('#dokan-geolocation-product-location-map');\n    address = $('#_dokan_geolocation_product_location');\n    var curpoint = new google.maps.LatLng(lat, lng);\n    gmap = new google.maps.Map(map_area.get(0), {\n      center: curpoint,\n      zoom: 13,\n      mapTypeId: google.maps.MapTypeId.ROADMAP\n    });\n    marker = new google.maps.Marker({\n      position: curpoint,\n      map: gmap,\n      draggable: true\n    });\n    geocoder = new google.maps.Geocoder();\n    var autocomplete = new google.maps.places.Autocomplete(address.get(0));\n    autocomplete.addListener('place_changed', function () {\n      var place = autocomplete.getPlace(),\n          location = place.geometry.location;\n      updateMap(location.lat(), location.lng(), place.formatted_address);\n    });\n    gmap.addListener('click', function (e) {\n      updateMap(e.latLng.lat(), e.latLng.lng());\n    });\n    marker.addListener('dragend', function (e) {\n      updateMap(e.latLng.lat(), e.latLng.lng());\n    });\n  }\n\n  function updateMap(lat, lng, formatted_address) {\n    $('[name=\"_dokan_geolocation_product_dokan_geo_latitude\"]').val(lat), $('[name=\"_dokan_geolocation_product_dokan_geo_longitude\"]').val(lng);\n    var curpoint = new google.maps.LatLng(lat, lng);\n    gmap.setCenter(curpoint);\n    marker.setPosition(curpoint);\n\n    if (!formatted_address) {\n      geocoder.geocode({\n        location: {\n          lat: lat,\n          lng: lng\n        }\n      }, function (results, status) {\n        if ('OK' === status) {\n          address.val(results[0].formatted_address);\n        }\n      });\n    }\n  }\n\n  $('#_dokan_geolocation_use_store_settings').on('change', function () {\n    $('#dokan-geolocation-product-location-no-store-settings').toggleClass('dokan-hide');\n    $('#dokan-geolocation-product-location').toggleClass('dokan-hide');\n  });\n  var locate_btn = $('#dokan-geolocation-product-location').find('.locate-icon');\n\n  if (!navigator.geolocation) {\n    locate_btn.addClass('dokan-hide');\n  } else {\n    locate_btn.on('click', function () {\n      navigator.geolocation.getCurrentPosition(function (position) {\n        updateMap(position.coords.latitude, position.coords.longitude);\n      });\n    });\n  }\n\n  initMap();\n})(jQuery);\n\n//# sourceURL=webpack://dokan-pro/./modules/geolocation/assets/src/js/vendor-dashboard-product-google-maps.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./modules/geolocation/assets/src/js/vendor-dashboard-product-google-maps.js"]();
/******/ 	
/******/ })()
;