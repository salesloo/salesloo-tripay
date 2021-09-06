<?php

namespace Salesloo_Tripay;

class Tripay
{
    /**
     * server base url
     */
    public $base;

    /**
     * Instance.
     *
     * @since 1.0.0
     * @access public
     */
    public static $instance = null;

    public $channels = [
        'MYBVA',
        'PERMATAVA',
        'BNIVA',
        'BRIVA',
        'MANDIRIVA',
        'BCAVA',
        'SMSVA',
        'MUAMALATVA',
        'CIMBVA',
        'ALFAMART',
        'ALFAMIDI',
        'QRIS'
    ];

    /**
     * Init
     *
     * @since 1.0.0
     */
    public static function init()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->set_base();
    }

    /**
     * set_base
     *
     * @return void
     */
    private function set_base()
    {
        $this->base = 'https://tripay.co.id/api/';

        if (get_option('tripay_is_sanbox')) {
            $this->base = 'https://tripay.co.id/api-sandbox/';
        }
    }

    /**
     * args
     *
     * @param  mixed $args
     * @return void
     */
    private function args($args = [])
    {
        $args['headers']['Authorization'] = 'Bearer ' . get_option('tripay_api_key');

        return $args;
    }

    /**
     * get_payment_channel
     *
     * @param  string $code
     * @return mixed
     */
    public function get_payment_channel($code)
    {
        $parameter = [
            'code' => $code
        ];

        $url = $this->base . 'merchant/payment-channel?' . http_build_query($parameter);
        $response = wp_remote_get($url, $this->args());
        if (is_wp_error($response)) {
            return $response;
        }

        $data = $this->response($response);

        return $data['data'][0];
    }

    /**
     * get_payment_channels
     * 
     * get all tripay payemnnt channles
     *
     * @return mixed
     */
    public function get_payment_channels()
    {
        $url = $this->base . 'merchant/payment-channel';
        $response = wp_remote_get($url, $this->args());

        return $this->response($response);
    }

    /**
     * singnature
     *
     * @param  string $ref [invoice number]
     * @param  integer $amount
     * @return string
     */
    public function singnature($ref, $amount)
    {
        $privateKey   = get_option('tripay_private_key');
        $merchantCode = get_option('tripay_merchant_code');
        $merchantRef  = $ref;
        $amount       = $amount;

        $signature = hash_hmac('sha256', $merchantCode . $merchantRef . $amount, $privateKey);

        return $signature;
    }

    /**
     * request_payment
     *
     * @param  string $channel
     * @param  array $args
     * @param  array $items
     * @return mixed
     */
    public function request_payment($args, $calback_url, $return_url)
    {
        $default_args = [
            'method'            => '',
            'merchant_ref'      => '', //invoice number
            'amount'            => '',
            'customer_name'     => '',
            'customer_email'    => '',
            'customer_phone'    => '',
            'order_items'       => [],
        ];

        $body = wp_parse_args($args, $default_args);
        $body['callback_url'] = $calback_url;
        $body['return_url']   = $return_url;
        $body['expired_time'] = strtotime('+' . salesloo_get_option('invoice_due_date_duration', 7) . ' day');
        $body['signature']    = $this->singnature($body['merchant_ref'], floatval($body['amount']));

        $url = $this->base . 'transaction/create';
        $response = wp_remote_post($url, $this->args([
            'body' => $body
        ]));

        return $this->response($response);
    }

    public function get_payment($reference)
    {
        $parameter = [
            'reference' => sanitize_text_field($reference)
        ];

        $url = $this->base . 'transaction/detail?' . http_build_query($parameter);
        $response = wp_remote_get($url, $this->args());

        $data = $this->response($response);

        return $data;
    }

    /**
     * response
     *
     * parse response from tripay api
     * 
     * @param  mixed $response
     * @return mixed
     */
    private function response($response)
    {
        if (is_wp_error($response)) {
            return $response;
        }
        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);
        $data = json_decode($body, true);

        if (intval($code) !== 200) {
            return new \WP_Error('error', $data['message']);
        }

        return $data['data'];
    }
}
