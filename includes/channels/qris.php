<?php

namespace Salesloo_Tripay;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use \Salesloo\Abstracts\Payment_Method;
use \Salesloo_Tripay\Models\Payment;

/**
 * QRIS
 */
class Qris extends Payment_Method
{

    /**
     * construction
     */
    public function __construct()
    {

        $this->id            = 'tripay-QRIS';
        $this->name          = 'Tripay QRIS';
        $this->icon_id       = SALESLOO_TRIPAY_URL . '/assets/images/qris.png';;
        $this->title         = 'QRIS';
        $this->description   = __('Pembayaran melalui qrcode qris via DANA, OVO, LINKAJA, GOPAY dan lain lain', 'salesloo-tripay');
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
