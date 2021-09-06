<?php

namespace Salesloo_Tripay;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use \Salesloo\Abstracts\Payment_Method;
use \Salesloo_Tripay\Models\Payment;

/**
 * Mybank Virtual account
 */
class Mybva extends Payment_Method
{

    /**
     * construction
     */
    public function __construct()
    {

        $this->id            = 'tripay-MYBVA';
        $this->name          = 'Tripay Maybank VA';
        $this->icon_id       = SALESLOO_TRIPAY_URL . '/assets/images/mybva.png';
        $this->title         = 'Maybank Virtual Account';
        $this->description   = sprintf(__('Pembayaran menggunakan transfer ke %s', 'salesloo-tripay'), $this->title);
        $this->currency      = 'IDR';
        $this->currency_symbol = 'Rp';
        $this->currency_rate = '';
        $this->enable        = false;
        $this->unique_number = false;
        $this->instruction   = '';
    }

    public function print_action()
    {
        return salesloo_tripay_payment_print_action($this);
    }

    public function handle_action($invoice)
    {
        salesloo_tripay_payment_handle_action($invoice);
    }
}
