<?php

namespace Salesloo_Tripay;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use \Salesloo\Abstracts\Payment_Method;
use \Salesloo_Tripay\Models\Payment;

/**
 * BNI Virtual account
 */
class Briva extends Payment_Method
{

    /**
     * construction
     */
    public function __construct()
    {

        $this->id            = 'tripay-BRIVA';
        $this->name          = 'Tripay BRI VA';
        $this->icon_id       = SALESLOO_TRIPAY_URL . '/assets/images/briva.png';;
        $this->title         = 'BRI Virtual Account';
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
