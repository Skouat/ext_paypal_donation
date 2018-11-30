(function ($) { // Avoid conflicts with other libraries

	'use strict';

	$('input[name=ppde_ipn_enable]').on('click init_toggle', function () {
		$('.ppde_toggle').toggle($('#ppde_ipn_enable').prop('checked'));
	}).trigger('init_toggle');
})(jQuery);
