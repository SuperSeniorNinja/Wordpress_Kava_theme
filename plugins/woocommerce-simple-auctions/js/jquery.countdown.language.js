/* http://keith-wood.name/countdown.html
 * Croatian Latin initialisation for the jQuery countdown extension
 * Written by Dejan Broz info@hqfactory.com (2011) */
(function($) {
	$.SAcountdown.regional['hr'] = {
		labels: [countdown_language_data.labels.Years, countdown_language_data.labels.Months, countdown_language_data.labels.Weeks, countdown_language_data.labels.Days, countdown_language_data.labels.Hours, countdown_language_data.labels.Minutes, countdown_language_data.labels.Seconds],
		labels1: [countdown_language_data.labels1.Year, countdown_language_data.labels1.Month, countdown_language_data.labels1.Week, countdown_language_data.labels1.Day, countdown_language_data.labels1.Hour, countdown_language_data.labels1.Minute, countdown_language_data.labels1.Second],
		
		compactLabels: [countdown_language_data.compactLabels.y, countdown_language_data.compactLabels.m, countdown_language_data.compactLabels.w, countdown_language_data.compactLabels.d],
		whichLabels: function(amount) {
			return (amount == 1 ? 1 : (amount >= 2 && amount <= 4 ? 2 : 0));
		},
		digits: ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
		timeSeparator: ':', isRTL: false};
	$.SAcountdown.setDefaults($.SAcountdown.regional['hr']);
})(jQuery);
