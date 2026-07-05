/* global paypal */
(function($) { // Avoid conflicts with other libraries

	'use strict';

	var $root = $('#ppde-donate');

	if (!$root.length) {
		return;
	}

// Read configuration injected by the template via data-* attributes
	var cfg = {
		clientId:   $root.data('client-id'),
		currency:   $root.data('currency-code'),
		currencyId: $root.data('currency-id'),
		hash:       $root.data('hash'),
		createUrl:  $root.data('create-url'),
		captureUrl: $root.data('capture-url'),
		successUrl: $root.data('success-url'),
		cancelUrl:  $root.data('cancel-url'),
		errAmount:  $root.data('err-amount'),
		errGeneric: $root.data('err-generic')
	};

	var $error = $('#ppde-error');

// Tracks whether a specific (already meaningful) error message has just been
// shown. When true, the generic onError() handler must NOT overwrite it.
	var errorHandled = false;

	function showError(message) {
		$error.find('p').text(message);
		$error.show();
		errorHandled = true;
	}

	function resetError() {
		$error.hide();
		errorHandled = false;
	}

	function post(url, params) {
		params.hash = cfg.hash;

		return $.ajax({
			url: url,
			type: 'POST',
			dataType: 'json',
			data: params,
			headers: {'X-Requested-With': 'XMLHttpRequest'}
		});
	}

	function renderButtons() {
		if (typeof paypal === 'undefined') {
			showError(cfg.errGeneric);
			return;
		}

		paypal.Buttons({
			createOrder: function() {
				// Each new attempt starts with a clean error state.
				resetError();

				var amount = parseFloat($('#ppde-amount').val());

				if (!amount || amount <= 0) {
					showError(cfg.errAmount);
					return $.Deferred().reject(new Error('invalid_amount')).promise();
				}

				return post(cfg.createUrl, {
					amount: amount,
					currency_id: cfg.currencyId
				}).then(function(data) {
					if (!data.id) {
						// Show the server-provided message when available.
						showError(data.error || cfg.errGeneric);
						throw new Error(data.error || 'create_failed');
					}
					return data.id;
				}, function() {
					// AJAX/network failure on order creation.
					showError(cfg.errGeneric);
					return $.Deferred().reject(new Error('create_failed')).promise();
				});
			},
			onApprove: function(data) {
				return post(cfg.captureUrl, {
					order_id: data.orderID
				}).then(function(data) {
					window.location.href = (data.status === 'COMPLETED') ? cfg.successUrl : cfg.cancelUrl;
				}, function() {
					// Capture failed: show a message instead of a silent redirect.
					showError(cfg.errGeneric);
					return $.Deferred().reject(new Error('capture_failed')).promise();
				});
			},
			onCancel: function() {
				window.location.href = cfg.cancelUrl;
			},
			onError: function() {
				// Only fall back to the generic message if no specific error
				// has already been displayed by the handlers above.
				if (!errorHandled) {
					showError(cfg.errGeneric);
				}
			}
		}).render('#ppde-paypal-button-container');
	}

	function loadSdk() {
		var script = document.createElement('script');
		script.src = 'https://www.paypal.com/sdk/js' +
			'?client-id=' + encodeURIComponent(cfg.clientId) +
			'&currency=' + encodeURIComponent(cfg.currency) +
			'&intent=capture&components=buttons';
		script.onload = renderButtons;
		script.onerror = function() {
			showError(cfg.errGeneric);
		};
		document.head.appendChild(script);
	}

	loadSdk();

})(jQuery);
