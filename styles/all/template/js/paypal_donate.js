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

	function showError(message) {
		$error.find('p').text(message);
		$error.show();
	}

	function hideError() {
		$error.hide();
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

// Render the PayPal buttons once the SDK is available
	function renderButtons() {
		if (typeof paypal === 'undefined') {
			showError(cfg.errGeneric);
			return;
		}

		paypal.Buttons({
			createOrder: function() {
				hideError();

				var amount = parseFloat($('#ppde-amount').val());

				if (!amount || amount <= 0) {
					showError(cfg.errAmount);
					return Promise.reject(new Error('invalid_amount'));
				}

				return post(cfg.createUrl, {
					amount: amount,
					currency_id: cfg.currencyId
				}).then(function(data) {
					if (!data.id) {
						throw new Error(data.error || 'create_failed');
					}
					return data.id;
				});
			},
			onApprove: function(data) {
				return post(cfg.captureUrl, {
					order_id: data.orderID
				}).then(function(data) {
					window.location.href = (data.status === 'COMPLETED') ? cfg.successUrl : cfg.cancelUrl;
				});
			},
			onCancel: function() {
				window.location.href = cfg.cancelUrl;
			},
			onError: function() {
				showError(cfg.errGeneric);
			}
		}).render('#ppde-paypal-button-container');
	}

// Dynamically load the PayPal JS SDK, then render the buttons.
// Loading it here (instead of a <script> tag in the template)
// keeps the template free of inline scripts.
	function loadSdk() {
		var script = document.createElement('script');
		script.src = 'https://www.paypal.com/sdk/js'
			+ '?client-id=' + encodeURIComponent(cfg.clientId)
			+ '&currency=' + encodeURIComponent(cfg.currency)
			+ '&intent=capture&components=buttons';
		script.onload = renderButtons;
		script.onerror = function() {
			showError(cfg.errGeneric);
		};
		document.head.appendChild(script);
	}

	loadSdk();

})(jQuery);
