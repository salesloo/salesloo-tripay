<?php

namespace Salesloo_Tripay;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use \Salesloo\Abstracts\Payment_Method;
use \Salesloo_Tripay\Models\Payment;

/**
 * Alfamidi
 */
class Alfamidi extends Payment_Method
{

    /**
     * construction
     */
    public function __construct()
    {

        $this->id            = 'tripay-ALFAMIDI';
        $this->name          = 'Tripay Alfamidi';
        $this->icon_id       = SALESLOO_TRIPAY_URL . '/assets/images/alfamidi.png';;
        $this->title         = 'Alfamidi';
        $this->description   = sprintf(__('Pembayaran melalui %s', 'salesloo-tripay'), $this->title);
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
