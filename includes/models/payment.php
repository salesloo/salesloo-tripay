<?php

namespace Salesloo_Tripay\Models;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use \Salesloo\Abstracts\Database;

/**
 * Tripay Payment Model
 */
class Payment extends Database
{
    public $table = 'salesloo_tripay_payment';

    protected $columns = [
        'ID'         => 'integer',
        'invoice_id' => 'integer',
        'reference'  => 'array',
        'created_at' => 'string',
        'status'     => 'string',
    ];

    protected $attributes = [];
}
