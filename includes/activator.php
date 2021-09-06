<?php

namespace Salesloo_Tripay;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Salesloo_Addon
 * @subpackage Salesloo_Addon/includes
 * @author     Taufik Hidayat <taufik@fiqhidayat.com>
 */
class Activator
{

  /**
   * Instance.
   *
   * Holds the plugin activator instance.
   *
   * @since 1.0.0
   * @access public
   */
  public static $instance = null;

  /**
   * run the plugin activator
   */
  public static function run()
  {
    update_option('salesloo_version', SALESLOO_VERSION);

    if (is_null(self::$instance)) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function __construct()
  {
    $this->create_tripay_payment_table();
  }


  /**
   * create_tripay_payment_table
   *
   * @return void
   */
  public function create_tripay_payment_table()
  {
    global $wpdb;

    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}salesloo_tripay_payment (
          ID bigint NOT NULL AUTO_INCREMENT,
          invoice_id int(11) NOT NULL,
          reference varchar(255) NOT NULL,
          created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
          PRIMARY KEY (ID)
        )";
    $wpdb->query($sql);
  }
}
