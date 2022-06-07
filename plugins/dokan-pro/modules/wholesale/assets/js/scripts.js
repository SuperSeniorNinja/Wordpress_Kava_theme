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

/***/ "./modules/wholesale/assets/src/js/scripts.js":
/*!****************************************************!*\
  !*** ./modules/wholesale/assets/src/js/scripts.js ***!
  \****************************************************/
/***/ (() => {

eval(";\n\n(function ($) {\n  var DokanWholesaleFrontend = {\n    init: function init() {\n      $('body').on('click', 'a#dokan-become-wholesale-customer-btn', this.makeWholesaleCustomer);\n      $(\"input[name=variation_id]\").on('change', this.triggerVariationWholesale);\n      $('.dokan-wholesale-options').on('change', '.wholesaleCheckbox', this.toggleWholesaleWrapper);\n      $('body').on('change', '.dokan-variation-wholesale .wholesaleCheckbox', this.toggleWholesaleVariationWrapper);\n    },\n    triggerVariationWholesale: function triggerVariationWholesale(e) {\n      e.preventDefault();\n      var variations = $(\".variations_form\").data(\"product_variations\");\n      var variation_id = $(\"input[name=variation_id]\").val();\n\n      for (var x = 0; x < variations.length; x++) {\n        if (variations[x].variation_id == variation_id) {\n          var variation = variations[x];\n\n          if (DokanWholesale.check_permission) {\n            if (variation._enable_wholesale == 'yes') {\n              var wholesale_string = DokanWholesale.variation_wholesale_string.wholesale_price + ': ' + '<strong>' + DokanWholesale.currency_symbol + variation._wholesale_price + '</strong>' + ' (' + DokanWholesale.variation_wholesale_string.minimum_quantity + ': ' + '<strong>' + variation._wholesale_quantity + '</strong>' + ')';\n              $('.single_variation').append('<div class=\"woocommerce-variation-wholesale\">' + wholesale_string + '</div>');\n            } else {\n              $('.single_variation').find('.woocommerce-variation-wholesale').remove();\n            }\n          }\n        }\n      }\n    },\n    makeWholesaleCustomer: function makeWholesaleCustomer(e) {\n      e.preventDefault();\n      var self = $(this),\n          url = dokan.rest.root + dokan.rest.version + '/wholesale/register',\n          data = {\n        id: self.data('id')\n      };\n      jQuery('.dokan-wholesale-migration-wrapper').block({\n        message: null,\n        overlayCSS: {\n          background: '#fff url(' + dokan.ajax_loader + ') no-repeat center',\n          opacity: 0.6\n        }\n      });\n      $.post(url, data, function (resp) {\n        if (resp.wholesale_status == 'active') {\n          self.closest('li').html('<div class=\"woocommerce-message\" style=\"margin-bottom:0px\">' + dokan.wholesale.activeStatusMessage + '</div>');\n        } else {\n          self.closest('li').html('<div class=\"woocommerce-info\" style=\"margin-bottom:0px\">' + dokan.wholesale.deactiveStatusMessage + '</div>');\n        }\n\n        jQuery('.dokan-wholesale-migration-wrapper').unblock();\n      });\n    },\n    toggleWholesaleWrapper: function toggleWholesaleWrapper() {\n      if ($(this).is(':checked')) {\n        $('.show_if_wholesale').slideDown('fast');\n      } else {\n        $('.show_if_wholesale').slideUp('fast');\n      }\n    },\n    toggleWholesaleVariationWrapper: function toggleWholesaleVariationWrapper() {\n      if ($(this).is(':checked')) {\n        $(this).closest('.dokan-variation-wholesale').find('.show_if_variation_wholesale').slideDown('fast');\n      } else {\n        $(this).closest('.dokan-variation-wholesale').find('.show_if_variation_wholesale').slideUp('fast');\n      }\n    }\n  };\n  $(document).ready(function () {\n    DokanWholesaleFrontend.init();\n  });\n})(jQuery);\n\n//# sourceURL=webpack://dokan-pro/./modules/wholesale/assets/src/js/scripts.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = {};
/******/ 	__webpack_modules__["./modules/wholesale/assets/src/js/scripts.js"]();
/******/ 	
/******/ })()
;