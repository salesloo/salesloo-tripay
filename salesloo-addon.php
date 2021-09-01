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
 * Plugin Name:       Salesloo Addon Template
 * Plugin URI:        https://www.salesloo.com
 * Description:       Description
 * Version:           1.0.0
 * Author:            Salesloo
 * Author URI:        https://www.salesloo.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       addon-template
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
define('ADDON_VERSION', '1.0.0');
define('ADDON_URL', plugin_dir_url(__FILE__));
define('ADDON_PATH', plugin_dir_path(__FILE__));
define('ADDON_ROOT', __FILE__);

/**
 * The code that runs during plugin activation.
 */
function activate_salesloo_addon()
{
	require_once ADDON_PATH . 'includes/activator.php';
	Salesloo_Addon\Activator::run();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_salesloo_addon()
{
	require_once ADDON_PATH . 'includes/deactivator.php';
	Salesloo_Addon\Deactivator::run();
}

register_activation_hook(__FILE__, 'activate_salesloo_addon');
register_deactivation_hook(__FILE__, 'deactivate_salesloo_addon');

/**
 * Main salesloo Addon class
 */
class Salesloo_Addon
{
	/**
	 * Instance
	 */
	private static $_instance = null;

	/**
	 * run
	 *
	 * @return Salesloo_Addon An instance of class
	 */
	public static function run()
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
			'salesloo-addon',
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

			//require file and run your plugin here;
		}
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
			esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'salesloo-addon'),
			'<strong>' . esc_html__('Salesloo Affiliate Bonus', 'salesloo-addon') . '</strong>',
			'<strong>' . esc_html__('Salesloo', 'salesloo-addon') . '</strong>'
		);

		printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
	}
}

Salesloo_Addon::run();
