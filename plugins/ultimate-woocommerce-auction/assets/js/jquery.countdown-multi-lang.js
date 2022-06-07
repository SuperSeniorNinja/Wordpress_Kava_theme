/* http://keith-wood.name/countdown.html
 * Spanish initialisation for the jQuery countdown extension
 * Written by Sergio Carracedo Martinez webmaster@neodisenoweb.com (2008) */
(function($) {	
	$.WooUacountdown.regional['hr'] = {		
		labels: [multi_lang_data.labels.Years, multi_lang_data.labels.Months, multi_lang_data.labels.Weeks, multi_lang_data.labels.Days, multi_lang_data.labels.Hours, multi_lang_data.labels.Minutes, multi_lang_data.labels.Seconds],
		labels1: [multi_lang_data.labels1.Year, multi_lang_data.labels1.Month, multi_lang_data.labels1.Week, multi_lang_data.labels1.Day, multi_lang_data.labels1.Hour, multi_lang_data.labels1.Minute, multi_lang_data.labels1.Second],
		
		compactLabels: [multi_lang_data.compactLabels.y, multi_lang_data.compactLabels.m, multi_lang_data.compactLabels.w, multi_lang_data.compactLabels.d],
		whichLabels: function(amount) {
			return (amount == 1 ? 1 : (amount >= 2 && amount <= 4 ? 2 : 0));
		},
		digits: ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
		timeSeparator: ':', isRTL: false};
	$.WooUacountdown.setDefaults($.WooUacountdown.regional['hr']);
})(jQuery);

