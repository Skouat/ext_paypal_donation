skouat.ppde.do_actions_completed_before
===
* Location: ext/skouat/ppde/actions/donation_recorder.php
* Since: 1.0.3
* Changed: 4.0.0 Moved to the shared donation_recorder service. It is now triggered both by the webhook listener (PAYMENT.CAPTURE.COMPLETED) and the synchronous capture endpoint; the idempotency guard ensures it fires only once per transaction.
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
* Since: 4.0.0
* Purpose: Modify data before a user is removed from the donors group following a refund/reversal that brought their cumulative donated amount below the configured minimum.
