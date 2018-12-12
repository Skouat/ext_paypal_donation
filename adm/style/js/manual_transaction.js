(function ($) { // Avoid conflicts with other libraries

	'use strict';

	// Update net amount when total amount or fee changes

	function truncateToDecimals(number, decimals) {
		var pow = Math.pow(10, decimals);
		return Math.trunc(pow * number) / pow;
	}

	$('#mc_gross').add('#mc_fee').on('input', function() {
		var mcGross = parseFloat($('#mc_gross').val());
		var mcFee = parseFloat($('#mc_fee').val());
		var netAmount = truncateToDecimals(mcGross - mcFee, 2);
		$('#net_amount').text(isNaN(netAmount) || mcGross <= 0 || mcFee < 0 || mcFee >= mcGross ? '-' : netAmount);
	}).trigger('input');

	// Update currency of fee and net amount when currency of total amount changes

	$('#mc_currency').on('change', function() {
		$('#mc_fee_currency').text(this.value);
		$('#net_amount_currency').text(this.value);
	}).trigger('change');

	// Date picker

	$('.date-calendar-value').datepicker({
		trigger: $('.date-calendar-container'),
		language: $('.date-calendar-container').data('language'),
		autoHide: true,
		date: new Date($('#payment_date_year').val(), $('#payment_date_month').val() - 1, $('#payment_date_day').val()),
		endDate: new Date(),
		pick: function(e) {
			$('#payment_date_year').val(e.date.getFullYear());
			$('#payment_date_month').val(e.date.getMonth() + 1);
			$('#payment_date_day').val(e.date.getDate());
		}
	}).datepicker('pick');

})(jQuery);
