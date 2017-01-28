<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}


// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ « » “ ” …
//


/**
 * mode: main
 */
$lang = array_merge($lang, array(
	'PPDE_ACP_DONATION'        => 'PayPal Donation',
	'PPDE_ACP_OVERVIEW'        => 'Vue d’ensemble',
	'PPDE_ACP_PAYPAL_FEATURES' => 'Fonctionnalitées PayPal IPN',
	'PPDE_ACP_SETTINGS'        => 'Paramètres généraux',
	'PPDE_ACP_DONATION_PAGES'  => 'Pages des dons',
	'PPDE_ACP_CURRENCY'        => 'Gestion des devises',
	'PPDE_ACP_TRANSACTIONS'    => 'Journal des transactions',
));

/**
 * mode: overview
 */
$lang = array_merge($lang, array(
	'PPDE_OVERVIEW'                     => 'Vue d’ensemble',

	'INFO_CURL'                         => 'cURL',
	'INFO_CURL_VERSION'                 => 'Version cURL : %1$s<br />Version SSL : %2$s',
	'INFO_FSOCKOPEN'                    => 'Fsockopen',
	'INFO_DETECTED'                     => 'Détecté',
	'INFO_NOT_DETECTED'                 => 'Non détecté',

	'PPDE_INSTALL_DATE'                 => 'Date d’installation de <strong>%s</strong>',
	'PPDE_NO_VERSIONCHECK'              => 'Cette extension ne prend pas en charge le contrôle de version.',
	'PPDE_NOT_UP_TO_DATE'               => '%s n’est pas à jour',
	'PPDE_STATS'                        => 'Statistiques des dons',
	'PPDE_STATS_SANDBOX'                => 'Statistiques Sandbox',
	'PPDE_VERSION'                      => 'Version de <strong>%s</strong>',

	'STAT_RESET_DATE'                   => 'Réinitialiser la date d’installation de l’extension',
	'STAT_RESET_DATE_EXPLAIN'           => 'La réinitialisation de la date d’installation affectera le calcul du montant total des dons et quelques autres informations.',
	'STAT_RESET_DATE_CONFIRM'           => 'Êtes-vous sûr de vouloir réinitialiser la date d’installation de cette extension ?',
	'STAT_RESYNC_STATS'                 => 'Actualiser les compteurs des donateurs et des transactions',
	'STAT_RESYNC_STATS_EXPLAIN'         => 'Actualise tous les compteurs des donateurs et des transactions. Seulement les donateurs actifs et anonymes seront pris en considération.',
	'STAT_RESYNC_STATS_CONFIRM'         => 'Êtes-vous sûr de vouloir actualiser les compteurs des donateurs et des transactions ?',
	'STAT_RESYNC_SANDBOX_STATS'         => 'Actualiser les compteurs de PayPal Sandbox',
	'STAT_RESYNC_SANDBOX_STATS_EXPLAIN' => 'Actualise tous les compteurs des donateurs et des transactions liés à PayPal Sandbox.',
	'STAT_RESYNC_SANDBOX_STATS_CONFIRM' => 'Êtes-vous sûr de vouloir actualiser les compteurs de PayPal Sandbox ?',
	'STAT_RETEST_CURL_FSOCK'            => 'Re-détecter « cURL » et « fsockopen »',
	'STAT_RETEST_CURL_FSOCK_EXPLAIN'    => 'Permet de re-détecter ces fonctionnalités si la configuration du serveur a été modifiée.',
	'STAT_RETEST_CURL_FSOCK_CONFIRM'    => 'Êtes-vous sûr de vouloir re-détecter « cURL » et « fsockopen » ?',

	'STATS_ANONYMOUS_DONORS_COUNT'      => 'Nombre de donateurs anonymes',
	'STATS_ANONYMOUS_DONORS_PER_DAY'    => 'Moyenne journalière des donateurs anonymes',
	'STATS_KNOWN_DONORS_COUNT'          => 'Nombre de donateurs connus',
	'STATS_KNOWN_DONORS_PER_DAY'        => 'Moyenne journalière des donateurs connus',
	'STATS_TRANSACTIONS_COUNT'          => 'Nombre de transactions',
	'STATS_TRANSACTIONS_PER_DAY'        => 'Moyenne journalière des transactions',
));

/**
 * mode: settings
 */
$lang = array_merge($lang, array(
	'PPDE_SETTINGS'                   => 'Paramètres généraux',
	'PPDE_SETTINGS_EXPLAIN'           => 'Depuis cette page vous pouvez configurer les paramètres généraux de PayPal Donation.',

	// Global settings
	'PPDE_LEGEND_GENERAL_SETTINGS'    => 'Paramètres généraux',
	'PPDE_ENABLE'                     => 'Activer PayPal Donation',
	'PPDE_ENABLE_EXPLAIN'             => 'Active ou désactive l’extension PayPal Donation.',
	'PPDE_HEADER_LINK'                => 'Afficher le lien « Faire un don » dans l’entête du forum',
	'PPDE_ACCOUNT_ID'                 => 'ID du compte PayPal',
	'PPDE_ACCOUNT_ID_EXPLAIN'         => 'Saisir l’adresse e-mail ou l’ID de compte marchand.',
	'PPDE_DEFAULT_CURRENCY'           => 'Devise par défaut',
	'PPDE_DEFAULT_CURRENCY_EXPLAIN'   => 'Défini quelle devise sera sélectionnée par défaut.',
	'PPDE_DEFAULT_VALUE'              => 'Valeur de don par défaut',
	'PPDE_DEFAULT_VALUE_EXPLAIN'      => 'Défini quelle valeur de don sera proposée par défaut sur la page de dons.',
	'PPDE_DROPBOX_ENABLE'             => 'Activer le menu déroulant',
	'PPDE_DROPBOX_ENABLE_EXPLAIN'     => 'Activez cette option pour remplacer la zone de texte par un menu déroulant.',
	'PPDE_DROPBOX_VALUE'              => 'Valeurs des dons du menu déroulant',
	'PPDE_DROPBOX_VALUE_EXPLAIN'      => 'Définissez les valeurs que vous voulez faire apparaître dans le menu déroulant.<br />Séparez chaque valeur par une virgule (",") et sans espace.',

	// Stats Donation settings
	'PPDE_LEGEND_STATS_SETTINGS'      => 'Paramètres des statistiques',
	'PPDE_STATS_INDEX_ENABLE'         => 'Statistiques des dons sur l’index',
	'PPDE_STATS_INDEX_ENABLE_EXPLAIN' => 'Activez cette option si vous voulez afficher les statistiques des dons sur l’index du forum.',
	'PPDE_RAISED'                     => 'Dons recueillis',
	'PPDE_RAISED_EXPLAIN'             => 'Inscrire le montant total des dons actuellement reçus.',
	'PPDE_GOAL'                       => 'Objectif des dons',
	'PPDE_GOAL_EXPLAIN'               => 'Inscrire le montant total des dons à atteindre.',
	'PPDE_USED'                       => 'Dons utilisés',
	'PPDE_USED_EXPLAIN'               => 'Inscrire le montant des dons déjà utilisés.',
	'PPDE_AMOUNT'                     => 'Montant',
	// Note for translator: do not translate the decimal symbol
	'PPDE_DECIMAL_EXPLAIN'            => 'Utiliser le « . » comme symbole décimal.',

	'PPDE_CURRENCY_ENABLE'            => 'Activer Devise des dons',
	'PPDE_CURRENCY_ENABLE_EXPLAIN'    => 'Activez cette option, pour rendre visible le Code ISO 4217 de la devise défini par défaut dans les statistiques des dons.',
));

/**
 * mode: PayPal features
 */
$lang = array_merge($lang, array(
	'PPDE_PAYPAL_FEATURES'                 => 'PayPal IPN',
	'PPDE_PAYPAL_FEATURES_EXPLAIN'         => 'Depuis cette page vous pouvez configurer les fonctionnalités utilisant les notifications instantanées de paiement (IPN) de PayPal.',

	// PayPal IPN settings
	'PPDE_LEGEND_IPN_AUTOGROUP'            => 'Groupe automatique',
	'PPDE_LEGEND_IPN_DONORLIST'            => 'Liste des donateurs',
	'PPDE_LEGEND_IPN_NOTIFICATION'         => 'Système de notification',
	'PPDE_LEGEND_IPN_SETTINGS'             => 'Paramètres Généraux',
	'PPDE_IPN_AG_ENABLE'                   => 'Activer le groupe automatique',
	'PPDE_IPN_AG_ENABLE_EXPLAIN'           => 'Permet d’ajouter automatiquement les donateurs dans un groupe pré-défini.',
	'PPDE_IPN_AG_DONORS_GROUP'             => 'Groupe donateurs',
	'PPDE_IPN_AG_DONORS_GROUP_EXPLAIN'     => 'Sélectionnez le groupe qui accueillera les membres donateurs.',
	'PPDE_IPN_AG_GROUP_AS_DEFAULT'         => 'Définir comme groupe par défaut',
	'PPDE_IPN_AG_GROUP_AS_DEFAULT_EXPLAIN' => 'Activez cette option pour définir le groupe des donateurs comme groupe par défaut pour les membres ayant fait une donation.',
	'PPDE_IPN_DL_ENABLE'                   => 'Activer la liste des donateurs',
	'PPDE_IPN_DL_ENABLE_EXPLAIN'           => 'Permet d’activer la liste des donateurs.',
	'PPDE_IPN_ENABLE'                      => 'Activer IPN',
	'PPDE_IPN_ENABLE_EXPLAIN'              => 'Activez cette option pour utiliser IPN (Notification Instantanée de Paiement).<br />Si activé, toutes les fonctionnalités dépendant de PayPal IPN apparaitront ci-dessous.',
	'PPDE_IPN_LOGGING'                     => 'Activer le journal des erreurs',
	'PPDE_IPN_LOGGING_EXPLAIN'             => 'Cette option permet d’enregistrer les erreurs et les données liées à PayPal IPN dans le répertoire <strong>/store/ext/ppde/</strong>.',
	'PPDE_IPN_NOTIFICATION_ENABLE'         => 'Activer les notifications',
	'PPDE_IPN_NOTIFICATION_ENABLE_EXPLAIN' => 'Permet de notifier les administrateurs et les donateurs dès qu’un don est reçu.',

	// PayPal sandbox settings
	'PPDE_LEGEND_SANDBOX_SETTINGS'         => 'Paramètres PayPal Sandbox',
	'PPDE_SANDBOX_ENABLE'                  => 'Tester avec PayPal Sandbox',
	'PPDE_SANDBOX_ENABLE_EXPLAIN'          => 'Activez cette option si vous voulez utiliser PayPal Sandbox au lieu des services PayPal.<br />Pratique pour les développeurs/testeurs. Toutes les transactions sont fictives.',
	'PPDE_SANDBOX_FOUNDER_ENABLE'          => 'Sandbox pour les fondateurs',
	'PPDE_SANDBOX_FOUNDER_ENABLE_EXPLAIN'  => 'Si activé, PayPal Sandbox ne sera visible que par les fondateurs du forum.',
	'PPDE_SANDBOX_ADDRESS'                 => 'Adresse PayPal Sandbox',
	'PPDE_SANDBOX_ADDRESS_EXPLAIN'         => 'Inscrire votre adresse e-mail de vendeur PayPal Sandbox.',
));

/**
 * mode: donation pages
 * Info: language keys are prefixed with 'PPDE_DP_' for 'PPDE_DONATION_PAGES_'
 */
$lang = array_merge($lang, array(
	// Donation Page settings
	'PPDE_DP_CONFIG'           => 'Pages des dons',
	'PPDE_DP_CONFIG_EXPLAIN'   => 'Permet d’améliorer le rendu des pages personnalisables de l’extension.',

	'PPDE_DP_PAGE'             => 'Type de page',
	'PPDE_DP_LANG'             => 'Langue',
	'PPDE_DP_LANG_SELECT'      => 'Sélectionnez une langue',

	// Donation Page Body settings
	'DONATION_BODY'            => 'Page principale',
	'DONATION_BODY_EXPLAIN'    => 'Saisir le texte que vous souhaitez afficher sur la page principale.',

	// Donation Success settings
	'DONATION_SUCCESS'         => 'Page des dons validés',
	'DONATION_SUCCESS_EXPLAIN' => 'Saisir le texte que vous souhaitez afficher sur la page des dons validés.',

	// Donation Cancel settings
	'DONATION_CANCEL'          => 'Page des dons annulés',
	'DONATION_CANCEL_EXPLAIN'  => 'Saisir le texte que vous souhaitez afficher sur la page des dons annulés.',

	// Donation Page Template vars
	'PPDE_DP_PREDEFINED_VARS'  => 'Variables prédéfinies',
	'PPDE_DP_VAR_EXAMPLE'      => 'Exemple',
	'PPDE_DP_VAR_NAME'         => 'Nom',
	'PPDE_DP_VAR_VAR'          => 'Variable',

	'PPDE_DP_BOARD_CONTACT'    => 'E-mail de contact',
	'PPDE_DP_BOARD_EMAIL'      => 'E-mail du forum',
	'PPDE_DP_BOARD_SIG'        => 'Signature du forum',
	'PPDE_DP_SITE_DESC'        => 'Description du site',
	'PPDE_DP_SITE_NAME'        => 'Nom du site',
	'PPDE_DP_USER_ID'          => 'ID de l’utilisateur',
	'PPDE_DP_USERNAME'         => 'Nom de l’utilisateur',
));

/**
 * mode: currency
 * Info: language keys are prefixed with 'PPDE_DC_' for 'PPDE_DONATION_CURRENCY_'
 */
$lang = array_merge($lang, array(
	// Currency Management
	'PPDE_DC_CONFIG'           => 'Gestion des devises',
	'PPDE_DC_CONFIG_EXPLAIN'   => 'Permet de gérer les devises pour faire un don.',
	'PPDE_DC_CREATE_CURRENCY'  => 'Ajouter une devise',
	'PPDE_DC_ENABLE'           => 'Activer la devise',
	'PPDE_DC_ENABLE_EXPLAIN'   => 'Si activée, la devise sera disponible dans la liste de sélection.',
	'PPDE_DC_ISO_CODE'         => 'Code ISO 4217',
	'PPDE_DC_ISO_CODE_EXPLAIN' => 'Code alphabétique de la devise.<br />En savoir plus sur la norme ISO 4217… Consultez la <a href="http://www.phpbb.com/customise/db/mod/paypal_donation_mod/faq/f_746" title="FAQ PayPal Donation">FAQ</a> de l’extension PayPal Donation (lien externe en anglais).',
	'PPDE_DC_NAME'             => 'Nom de la devise',
	'PPDE_DC_NAME_EXPLAIN'     => 'Exemple : Euro.',
	'PPDE_DC_POSITION'         => 'Position du symbole',
	'PPDE_DC_POSITION_EXPLAIN' => 'Définit où le symbole de la devise sera positionné par rapport au montant affiché.<br />Exemple : <strong>$20</strong> ou <strong>15€</strong>.',
	'PPDE_DC_POSITION_LEFT'    => 'À gauche',
	'PPDE_DC_POSITION_RIGHT'   => 'À droite',
	'PPDE_DC_SYMBOL'           => 'Symbole de la devise',
	'PPDE_DC_SYMBOL_EXPLAIN'   => 'Inscrire le symbole de la devise.<br />Exemple : <strong>€</strong> pour Euro.',
));

/**
 * mode: transactions
 * Info: language keys are prefixed with 'PPDE_DT_' for 'PPDE_DONATION_TRANSACTION_'
 */
$lang = array_merge($lang, array(
	// Transactions log
	'PPDE_DT_CONFIG'                => 'Journal des transactions',
	'PPDE_DT_CONFIG_EXPLAIN'        => 'Depuis cette page vous pouvez consulter le détail des transactions PayPal.',
	'PPDE_DT_IPN_STATUS'            => 'État de la transaction',
	'PPDE_DT_IPN_TEST'              => 'Test IPN',
	'PPDE_DT_PAYMENT_STATUS'        => 'État du paiement',
	'PPDE_DT_TXN_ID'                => 'Numéro de transaction',
	'PPDE_DT_USERNAME'              => 'Nom du donateur',

	// Display transactions
	'PPDE_DT_BOARD_USERNAME'        => 'Donateur',
	'PPDE_DT_DETAILS'               => 'Détails de la transaction',
	'PPDE_DT_EXCHANGE_RATE'         => 'Taux de change',
	'PPDE_DT_EXCHANGE_RATE_EXPLAIN' => 'Basé sur le taux de change effectif au %s',
	'PPDE_DT_FEE_AMOUNT'            => 'Montant de la commission',
	'PPDE_DT_ITEM_NAME'             => 'Titre de l’objet',
	'PPDE_DT_ITEM_NUMBER'           => 'Numéro d’objet',
	'PPDE_DT_NAME'                  => 'Nom',
	'PPDE_DT_NET_AMOUNT'            => 'Montant net',
	'PPDE_DT_PAYER_ID'              => 'Identifiant de l’émetteur du paiement',
	'PPDE_DT_PAYER_EMAIL'           => 'E-mail',
	'PPDE_DT_PAYER_STATUS'          => 'État de l’émetteur du paiement',
	'PPDE_DT_PAYMENT_DATE'          => 'Date du paiement',
	'PPDE_DT_RECEIVER_EMAIL'        => 'Paiement envoyé à',
	'PPDE_DT_RECEIVER_ID'           => 'Ident. compte marchand',
	'PPDE_DT_SETTLE_AMOUNT'         => 'Conversion en « %s »',
	'PPDE_DT_SORT_TXN_ID'           => 'Numéro transaction',
	'PPDE_DT_SORT_DONORS'           => 'Donateur',
	'PPDE_DT_SORT_IPN_STATUS'       => 'État de la transaction',
	'PPDE_DT_SORT_IPN_TYPE'         => 'Type de transaction',
	'PPDE_DT_SORT_PAYMENT_STATUS'   => 'État du paiement',
	'PPDE_DT_TOTAL_AMOUNT'          => 'Montant total',
	'PPDE_DT_UNVERIFIED'            => 'Non vérifié',
	'PPDE_DT_VERIFIED'              => 'Vérifié',
));

/**
 * mode: transactions
 * Info: language keys are prefixed with 'PPDE_DT_' for 'PPDE_DONATION_TRANSACTION_'
 * Info: This array is out of the previous because there is an issue with Transifex platform
 */
$lang = array_merge($lang, array(
	/**
	 * TRANSLATORS PLEASE NOTE
	 * The line below has a special note.
	 * "## For translate:" followed by one "Don't" and one "Yes"
	 * "Don't" means do not change this column, and "Yes" means you can translate this column.
	 */

	## For translate:					Don't					Yes
	'PPDE_DT_PAYMENT_STATUS_VALUES' => array(
										'canceled_reversal' => 'Annulation invalidée',
										'completed'         => 'Effectué',
										'created'           => 'Créé',
										'denied'            => 'Rejeté',
										'expired'           => 'Expiré',
										'failed'            => 'Échoué',
										'pending'           => 'En attente',
										'refunded'          => 'Remboursé',
										'reversed'          => 'Annulé',
										'processed'         => 'Accepté',
										'voided'            => 'Annulé',
	),
));

/**
 * logs
 */
$lang = array_merge($lang, array(
	'LOG_PPDE_DC_ADDED'            => '<strong>PayPal Donation : Nouvelle devise ajoutée</strong><br />» %s',
	'LOG_PPDE_DC_DELETED'          => '<strong>PayPal Donation : Devise supprimée</strong><br />» %s',
	'LOG_PPDE_DC_DEACTIVATED'      => '<strong>PayPal Donation : Devise désactivée</strong><br />» %s',
	'LOG_PPDE_DC_ACTIVATED'        => '<strong>PayPal Donation : Devise activée</strong><br />» %s',
	'LOG_PPDE_DC_MOVE_DOWN'        => '<strong>PayPal Donation : Déplacement vers le bas de la devise</strong> « %s »',
	'LOG_PPDE_DC_MOVE_UP'          => '<strong>PayPal Donation : Déplacement vers le haut de la devise</strong> « %s »',
	'LOG_PPDE_DC_UPDATED'          => '<strong>PayPal Donation : Devise mise à jour</strong><br />» %s',
	'LOG_PPDE_DP_ADDED'            => '<strong>PayPal Donation : Nouvelle page de dons ajoutée</strong><br />» « %1$s » pour la langue « %2$s »', // eg: » “Donation success” for the language “British English”',
	'LOG_PPDE_DP_DELETED'          => '<strong>PayPal Donation : Page des dons supprimée</strong><br />» « %1$s » pour la langue « %2$s »',
	'LOG_PPDE_DP_UPDATED'          => '<strong>PayPal Donation : Page de dons mise à jour</strong><br />» « %1$s » pour la langue « %2$s »',
	'LOG_PPDE_DT_PURGED'           => '<strong>PayPal Donation : Purge du journal des transactions</strong>',
	'LOG_PPDE_SETTINGS_UPDATED'    => '<strong>PayPal Donation : Configuration mise à jour</strong>',
	'LOG_PPDE_STAT_RESET_DATE'     => '<strong>PayPal Donation : Date d’installation réinitialisée</strong>',
	'LOG_PPDE_STAT_RESYNC'         => '<strong>PayPal Donation : Actualisation des statistiques</strong>',
	'LOG_PPDE_STAT_RETEST_REMOTE'  => '<strong>PayPal Donation : Re-détection des connexions distantes</strong>',
	'LOG_PPDE_STAT_SANDBOX_RESYNC' => '<strong>PayPal Donation : Actualisation des statistiques PayPal Sandbox</strong>',
));

/**
 * Confirm box
 */
$lang = array_merge($lang, array(
	'PPDE_DC_CONFIRM_DELETE'        => 'Êtes-vous sûr de vouloir supprimer cette devise ?',
	'PPDE_DC_GO_TO_PAGE'            => '%sModifier la devise existante%s',
	'PPDE_DC_ADDED'                 => 'Une devise a été ajoutée.',
	'PPDE_DC_UPDATED'               => 'Une devise a été mise à jour.',
	'PPDE_DC_DELETED'               => 'Une devise a été supprimée.',
	'PPDE_DP_CONFIRM_DELETE'        => 'Êtes-vous sûr de vouloir supprimer cette page de dons ?',
	'PPDE_DP_GO_TO_PAGE'            => '%sModifier la page de dons existante%s',
	'PPDE_DP_ADDED'                 => 'Une page de dons pour la langue « %s » a été ajoutée.',
	'PPDE_DP_DELETED'               => 'Une page de dons pour la langue « %s » a été supprimée.',
	'PPDE_DP_UPDATED'               => 'Une page de dons pour la langue « %s » a été mise à jour.',
	'PPDE_SETTINGS_SAVED'           => 'Les paramètres de PayPal Donation ont été sauvegardés.',
	'PPDE_PAYPAL_FEATURES_SAVED'    => 'Les paramètres IPN PayPal ont été sauvegardés.',
));

/**
 * Errors
 */
$lang = array_merge($lang, array(
	'PPDE_CANNOT_DISABLE_ALL_CURRENCIES' => 'Vous ne pouvez pas désactiver toutes les devises.',
	'PPDE_DC_EMPTY_NAME'                 => 'Saisissez un nom de devise.',
	'PPDE_DC_EMPTY_ISO_CODE'             => 'Saisissez un code ISO.',
	'PPDE_DC_EMPTY_SYMBOL'               => 'Saisissez un symbole.',
	'PPDE_DC_EXISTS'                     => 'Cette devise existe déjà.',
	'PPDE_DC_INVALID_HASH'               => 'Le lien est corrompu. Le hachage n’est pas valide.',
	'PPDE_DC_NO_CURRENCY'                => 'Aucune devise n’a été trouvée.',
	'PPDE_DP_EMPTY_LANG_ID'              => 'Aucune langue n’a été sélectionnée.',
	'PPDE_DP_EMPTY_NAME'                 => 'La page de dons sélectionnée n’existe pas.',
	'PPDE_DP_EXISTS'                     => 'Cette page de dons existe déjà.',
	'PPDE_DP_NO_DONATION_PAGES'          => 'Aucune page de dons n’a été trouvée.',
	'PPDE_DT_NO_TRANSACTION'             => 'Aucune transaction n’a été trouvée.',
	'PPDE_DISABLE_BEFORE_DELETION'       => 'Vous devez désactiver la devise avant de la supprimer.',
	'PPDE_SETTINGS_MISSING'              => 'Veuillez vérifier le paramètre « ID du compte PayPal ».',
	'PPDE_PAYPAL_FEATURES_MISSING'       => 'Veuillez vérifier le paramètre « Adresse PayPal Sandbox ».',
));
