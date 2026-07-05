(function($) { // Avoid conflicts with other libraries

	'use strict';

	var $container = $('#ppde_connection_test');

	if (!$container.length) {
		return;
	}

// Select the webhook URL on click for easy copying (no inline handler)
	$('#ppde_webhook_url').on('click', function() {
		this.select();
	});

	$('.ppde-test-connection').on('click', function() {
		var env = $(this).data('env');
		var $result = $('#ppde-test-result-' + env);

		$result.removeClass('successbox errorbox').text($container.data('testing'));

		$.ajax({
			url: $container.data('action-url'),
			type: 'POST',
			dataType: 'json',
			data: {
				test_connection: 1,
				env: env,
				hash: $container.data('hash')
			},
			headers: {'X-Requested-With': 'XMLHttpRequest'}
		}).done(function(data) {
			$result
				.addClass(data.success ? 'successbox' : 'errorbox')
				.text(data.MESSAGE_TEXT);
		}).fail(function() {
			$result.addClass('errorbox').text($container.data('error'));
		});
	});

})(jQuery);
