skouat.ppde.do_actions_completed_before
===
* Location: ext/skouat/ppde/controller/webhook_listener.php
* Since: 1.0.3
* Changed: 3.1.0 Moved from controller/ipn_listener.php to controller/webhook_listener.php following the migration from PayPal IPN to the REST API. The `transaction_data` keys are now populated from the PayPal REST webhook (PAYMENT.CAPTURE.COMPLETED) instead of the IPN POST data.
* Purpose: Event that is triggered when a transaction has been successfully completed

skouat.ppde.donors_group_user_add_before
===
* Location: ext/skouat/ppde/actions/core.php
* Since: 1.0.3
* Changed: 2.1.2 Added var $payer_donated_amount
* Purpose: Modify data before a user is added to the donors group

skouat.ppde.donors_group_user_remove_before
===
* Location: ext/skouat/ppde/actions/core.php
* Since: 3.1.0
* Purpose: Modify data before a user is removed from the donors group following a refund/reversal that brought their cumulative donated amount below the configured minimum.
