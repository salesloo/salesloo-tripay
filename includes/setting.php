<?php

namespace Salesloo_Tripay;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Setting
 */
class Setting
{
    /**
     * Register Menu
     * 
     * register system info menu
     */
    public function register_menu($submenu)
    {
        $submenu[] = [
            'page_title' => __('Tripay Integration', 'salesloo'),
            'menu_title' => __('Tripay Integration', 'salesloo'),
            'capability' => 'manage_options',
            'slug'       => 'salesloo-tripay',
            'callback'   => [$this, 'page'],
            'position'   => 99
        ];

        return $submenu;
    }

    public function on_save()
    {

        if (isset($_POST['save']) && isset($_POST['__nonce'])) :
            if (wp_verify_nonce($_POST['__nonce'], 'salesloo-tripay')) :

                unset($_POST['save']);
                unset($_POST['__nonce']);
                unset($_POST['_wp_http_referer']);

                foreach ($_POST as $key => $value) {
                    \update_option($key, $value);
                }

                \flush_rewrite_rules();

                add_action('admin_notices', function () {
                    echo '<div id="message" class="updated notice notice-success"><p><strong>' . __('Your settings have been saved.', 'salesloo') . '</strong></p></div>';
                });
            endif;
        endif;
    }

    public function page()
    {

        echo '<div class="wrap">';
        echo '<h2>' . __('Tripay Integration Settings', 'salesloo') . '</h2>';
        echo '<form action="" method="post" enctype="multipart/form-data" style="margin-top:30px">';

        \salesloo_field_text([
            'label'       => __('Tripay Api Key', 'salesloo'),
            'name'        => 'tripay_api_key',
            'description' => '',
            'value'       => get_option('tripay_api_key')
        ]);

        \salesloo_field_text([
            'label'       => __('Tripay Private Key', 'salesloo'),
            'name'        => 'tripay_private_key',
            'description' => '',
            'value'       => get_option('tripay_private_key')
        ]);

        \salesloo_field_text([
            'label'       => __('Tripay Merchant Code', 'salesloo'),
            'name'        => 'tripay_merchant_code',
            'description' => '',
            'value'       => get_option('tripay_merchant_code')
        ]);

        \salesloo_field_toggle([
            'label'           => __('Enable Testing Sanbox', 'salesloo'),
            'name'            => 'tripay_is_sanbox',
            'value'   => get_option('tripay_is_sanbox'),
            'description' => __('Enable sanbox only to test payments with tripay sanbox', 'salesloo')
        ]);

        \salesloo_field_heading([
            'label' => __('Tripay Webhook', 'salesloo'),
            'description' => get_rest_url(null, 'salesloo-tripay/v1/webhook'),
        ]);

        if (get_option('tripay_api_key') && get_option('tripay_private_key')) {

            $channels = \Salesloo_Tripay::instance()->tripay->get_payment_channels();

            if (is_wp_error($channels)) {
                echo '<div class="notice notice-error inline"><p>' . $channels->get_error_message() . '</p></div>';
            } else {

                \salesloo_field_heading([
                    'label' => __('Payment Channels', 'salesloo'),
                ]);

                foreach ($channels as $channel) {

                    /**
                     * skip if is not on alowed channels
                     */
                    if (!in_array($channel['code'], \Salesloo_Tripay::instance()->tripay->channels)) continue;

                    $value = \get_option('tripay_channel_' . $channel['code']);
                    $desc = 'Tripay fee Rp ' . number_format($channel['total_fee']['flat']);

                    if ($value) {
                        $link = '<a href="' . admin_url() . 'admin.php?page=salesloo-settings&tab=payment_method&section=tripay-' . $channel['code'] . '">this page</a>';
                        $desc .= '<br/>';
                        $desc .= sprintf(__('Setup %s on %s', 'salesloo'), $channel['name'], $link);
                    }
                    \salesloo_field_toggle([
                        'label'           => $channel['name'],
                        'name'            => 'tripay_channel_' . $channel['code'],
                        'description'     => $desc,
                        'value'   => $value,
                    ]);
                }
            }
        } else {
            \salesloo_field_notice([
                'type'       => 'info',
                'message' => 'Untuk mengatur chanel pembayaran, silahkan isi tripay api key dan tripay private key di atas kemudian klik simpan halaman ini'
            ]);
        }

        \salesloo_field_submit();

        wp_nonce_field('salesloo-tripay', '__nonce');
        echo '</form>';
        echo '</div>';
    }
}
