<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.salesloo.com
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Salesloo Tripay
 * Plugin URI:        https://www.salesloo.com
 * Description:       Indonesian Payment gateway for Salesloo
 * Version:           1.0.7
 * Author:            Salesloo
 * Author URI:        https://www.salesloo.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       salesloo-tripay
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('SALESLOO_TRIPAY_VERSION', '1.0.7');
define('SALESLOO_TRIPAY_URL', plugin_dir_url(__FILE__));
define('SALESLOO_TRIPAY_PATH', plugin_dir_path(__FILE__));
define('SALESLOO_TRIPAY_ROOT', __FILE__);

require 'update-checker/plugin-update-checker.php';

/**
 * The code that runs during plugin activation.
 */
function activate_salesloo_tripay()
{
	require_once SALESLOO_TRIPAY_PATH . 'includes/activator.php';
	Salesloo_Tripay\Activator::run();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_salesloo_tripay()
{
	require_once SALESLOO_TRIPAY_PATH . 'includes/deactivator.php';
	Salesloo_Tripay\Deactivator::run();
}

register_activation_hook(__FILE__, 'activate_salesloo_tripay');
register_deactivation_hook(__FILE__, 'deactivate_salesloo_tripay');

/**
 * Main salesloo Addon class
 */
class Salesloo_Tripay
{
	/**
	 * Instance
	 */
	private static $_instance = null;

	public $tripay = null;

	public $setting = null;

	public $webhook = null;

	/**
	 * run
	 *
	 * @return Salesloo_Tripay An instance of class
	 */
	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct()
	{
		add_action('plugins_loaded', [$this, 'on_plugins_loaded']);
	}

	/**
	 * Load Textdomain
	 *
	 * Load plugin localization files.
	 *
	 * Fired by `init` action hook.
	 *
	 * @access public
	 */
	public function i18n()
	{
		load_plugin_textdomain(
			'salesloo-tripay',
			false,
			dirname(plugin_basename(__FILE__)) . '/languages'
		);
	}

	/**
	 * On Plugins Loaded
	 *
	 * Checks if Salesloo has loaded, and performs some compatibility checks.
	 *
	 * @access public
	 */
	public function on_plugins_loaded()
	{

		if ($this->is_compatible()) {
			$this->i18n();
			$this->load_scripts();
			$this->init();
			$this->install_hooks();
		}
	}

	/**
	 * load_scripts
	 *
	 * @return void
	 */
	private function load_scripts()
	{
		require_once SALESLOO_TRIPAY_PATH . 'includes/models/payment.php';
		require_once SALESLOO_TRIPAY_PATH . 'includes/tripay.php';
		require_once SALESLOO_TRIPAY_PATH . 'includes/setting.php';
		require_once SALESLOO_TRIPAY_PATH . 'includes/function.php';
		require_once SALESLOO_TRIPAY_PATH . 'includes/webhook.php';
	}

	/**
	 * init salesloo tripay
	 *
	 * @return void
	 */
	private function init()
	{
		$this->tripay = new \Salesloo_Tripay\Tripay;
		$this->setting = new \Salesloo_Tripay\Setting;
		$this->webhook = new \Salesloo_Tripay\Webhook;
	}

	/**
	 * install hooks
	 *
	 * @return void
	 */
	private function install_hooks()
	{
		add_filter('salesloo/admin/submenu', [$this->setting, 'register_menu'], 10);
		add_filter('admin_init', [$this->setting, 'on_save']);

		add_action('rest_api_init', [$this->webhook, 'register_rest_api']);

		add_filter('salesloo/payment_method/classes', [$this, 'payment_channels_classes']);
	}

	public function payment_channels_classes($classes)
	{
		if (get_option('tripay_channel_MYBVA')) {
			require_once SALESLOO_TRIPAY_PATH . 'includes/channels/mybva.php';
			$classes[] = 'Salesloo_Tripay\Mybva';
		}

		if (get_option('tripay_channel_PRMATAVA')) {
			require_once SALESLOO_TRIPAY_PATH . 'includes/channels/permatava.php';
			$classes[] = 'Salesloo_Tripay\Permatava';
		}

		if (get_option('tripay_channel_BNIVA')) {
			require_once SALESLOO_TRIPAY_PATH . 'includes/channels/bniva.php';
			$classes[] = 'Salesloo_Tripay\Bniva';
		}

		if (get_option('tripay_channel_BRIVA')) {
			require_once SALESLOO_TRIPAY_PATH . 'includes/channels/briva.php';
			$classes[] = 'Salesloo_Tripay\Briva';
		}

		if (get_option('tripay_channel_MANDIRIVA')) {
			require_once SALESLOO_TRIPAY_PATH . 'includes/channels/mandiriva.php';
			$classes[] = 'Salesloo_Tripay\Mandiriva';
		}

		if (get_option('tripay_channel_BCAVA')) {
			require_once SALESLOO_TRIPAY_PATH . 'includes/channels/bcava.php';
			$classes[] = 'Salesloo_Tripay\Bcava';
		}

		if (get_option('tripay_channel_SMSVA')) {
			require_once SALESLOO_TRIPAY_PATH . 'includes/channels/smsva.php';
			$classes[] = 'Salesloo_Tripay\Smsva';
		}

		if (get_option('tripay_channel_MUAMALATVA')) {
			require_once SALESLOO_TRIPAY_PATH . 'includes/channels/muamalatva.php';
			$classes[] = 'Salesloo_Tripay\Muamalatva';
		}

		if (get_option('tripay_channel_CIMBVA')) {
			require_once SALESLOO_TRIPAY_PATH . 'includes/channels/cimbva.php';
			$classes[] = 'Salesloo_Tripay\Cimbva';
		}

		if (get_option('tripay_channel_ALFAMART')) {
			require_once SALESLOO_TRIPAY_PATH . 'includes/channels/alfamart.php';
			$classes[] = 'Salesloo_Tripay\Alfamart';
		}

		if (get_option('tripay_channel_ALFAMIDI')) {
			require_once SALESLOO_TRIPAY_PATH . 'includes/channels/alfamidi.php';
			$classes[] = 'Salesloo_Tripay\Alfamidi';
		}

		if (get_option('tripay_channel_QRIS')) {
			require_once SALESLOO_TRIPAY_PATH . 'includes/channels/qris.php';
			$classes[] = 'Salesloo_Tripay\Qris';
		}

		return $classes;
	}

	/**
	 * Compatibility Checks
	 *
	 * @access public
	 */
	public function is_compatible()
	{
		// Check if Salesloo installed and activated
		if (!did_action('salesloo/loaded')) {
			add_action('admin_notices', [$this, 'admin_notice_missing_main_plugin']);
			return false;
		}

		if (version_compare(SALESLOO_VERSION, '1.0.5', '<')) {
			add_action('admin_notices', [$this, 'admin_notice_missing_main_plugin_version']);
			return false;
		}

		return true;
	}


	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Salesloo installed or activated.
	 *
	 * @access public
	 */
	public function admin_notice_missing_main_plugin()
	{

		if (isset($_GET['activate'])) unset($_GET['activate']);

		$message = sprintf(
			/* translators: 1: Salesloo Affiliate Bonus 2: Salesloo */
			esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'salesloo-tripay'),
			'<strong>' . esc_html__('Salesloo Tripay', 'salesloo-tripay') . '</strong>',
			'<strong>' . esc_html__('Salesloo', 'salesloo-tripay') . '</strong>'
		);

		printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Salesloo installed or activated.
	 *
	 * @access public
	 */
	public function admin_notice_missing_main_plugin_version()
	{

		if (isset($_GET['activate'])) unset($_GET['activate']);

		$message = sprintf(
			/* translators: 1: Salesloo Affiliate Bonus 2: Salesloo */
			esc_html__('"%1$s" requires "%2$s" version 1.0.5 or higher.', 'salesloo-tripay'),
			'<strong>' . esc_html__('Salesloo Tripay', 'salesloo-tripay') . '</strong>',
			'<strong>' . esc_html__('Salesloo', 'salesloo-tripay') . '</strong>'
		);

		printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
	}
}

Salesloo_Tripay::instance();
