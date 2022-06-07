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

/***/ "./modules/delivery-time/assets/src/js/admin.js":
/*!******************************************************!*\
  !*** ./modules/delivery-time/assets/src/js/admin.js ***!
  \******************************************************/
/***/ (() => {

eval("jQuery(function ($) {\n  var Dokan_Handle_Delivery_Time_Admin_Meta_Box = {\n    init: function init() {\n      Dokan_Handle_Delivery_Time_Admin_Meta_Box.set_admin_delivery_time_slots($(\"#dokan-delivery-admin-date-picker\"));\n      $(\"#dokan-delivery-admin-date-picker\").datepicker(\"option\", {\n        dateFormat: dokan_get_i18n_date_format(),\n        altFormat: 'yy-mm-dd',\n        altField: \"#dokan_delivery_date_input\",\n        minDate: 0\n      }).on('change', function (e) {\n        e.preventDefault();\n        Dokan_Handle_Delivery_Time_Admin_Meta_Box.set_admin_delivery_time_slots(this);\n      });\n    },\n    set_admin_delivery_time_slots: function set_admin_delivery_time_slots(context) {\n      $(context).attr('value', $('#dokan-delivery-admin-date-picker').val());\n      var self = $(context);\n      var vendor_id = self.data('vendor_id');\n      var selected_date = $('#dokan_delivery_date_input').val();\n      var nonce = self.data('nonce');\n      var data = {\n        action: 'dokan_get_delivery_time_slot',\n        vendor_id: vendor_id,\n        nonce: nonce,\n        date: selected_date\n      };\n\n      if (data.date) {\n        Dokan_Handle_Delivery_Time_Admin_Meta_Box.get_admin_delivery_time_slots(data);\n      }\n    },\n    get_admin_delivery_time_slots: function get_admin_delivery_time_slots(data) {\n      $(\"#dokan-delivery-admin-time-slot-picker\").prop(\"disabled\", true);\n      $.post(dokan_admin.ajaxurl, data, function (response) {\n        if (response.success) {\n          $(\"#dokan-delivery-admin-time-slot-picker option:gt(0)\").remove();\n          $.each(response.data.vendor_delivery_slots, function (key, value) {\n            $(\"#dokan-delivery-admin-time-slot-picker\").append($(\"<option></option>\").attr(\"value\", key).text(key));\n          });\n          $(\"#dokan-delivery-admin-time-slot-picker\").prop(\"disabled\", false);\n        }\n      });\n    }\n  };\n  jQuery(document).ready(function ($) {\n    Dokan_Handle_Delivery_Time_Admin_Meta_Box.init();\n  });\n});\n\n//# sourceURL=webpack://dokan-pro/./modules/delivery-time/assets/src/js/admin.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./modules/delivery-time/assets/src/js/admin.js"]();
/******/ 	
/******/ })()
;